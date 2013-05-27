<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function reservation_inserer($id_parent=null, $set=null) {
    $table_sql = table_objet_sql('reservation');
    $champs = array();
    $champs['statut'] = 'encours';
    $champs['date'] = date('Y-m-d H:i:s');

    if ($set)
        $champs = array_merge($champs, $set);

    // Envoyer aux plugins
    $champs = pipeline('pre_insertion',
        array(
            'args' => array(
                'table' => $table_sql,
            ),
            'data' => $champs
        )
    );

    $id = sql_insertq($table_sql, $champs);

    if ($id){
        pipeline('post_insertion',
            array(
                'args' => array(
                    'table' => $table_sql,
                    'id_objet' => $id,
                ),
                'data' => $champs
            )
        );
    }
    return $id;
}

function reservation_instituer($id, $c, $calcul_rub=true) {
    


    $table_sql = table_objet_sql('reservation');
    $trouver_table = charger_fonction('trouver_table','base');


    include_spip('inc/autoriser');
    include_spip('inc/rubriques');
    include_spip('inc/modifier');

    $row = sql_fetsel('statut,date,id_auteur','spip_reservations','id_reservation='.intval($id));

    $d = isset($c['date']) ? $c['date'] : null;
    $s = isset($c['statut']) ? $c['statut'] : $statut;
    
    
    

    $statut_ancien = $statut = $row['statut'];
    $date_ancienne = $date = $row['date'];
    $champs = array();
    // cf autorisations dans inc/instituer_objet
    if ($s != $statut OR ($d AND $d != $date)) {
        if (autoriser('modifier','reservation', $id))
            $statut = $champs['statut'] = $s;
        else
            spip_log("editer_reservation $id refus " . join(' ', $c));

        // En cas de publication, fixer la date a "maintenant"
        // sauf si $c commande autre chose
        // ou si l'objet est deja date dans le futur
        // En cas de proposition d'un objet (mais pas depublication), idem
        if ($champ_date) {
            if ($champs['statut'] == 'publie'
             OR ($champs['statut'] == 'prop' AND !in_array($statut_ancien, array('publie', 'prop')))
             OR $d
            ) {
                if ($d OR strtotime($d=$date)>time())
                    $champs[$champ_date] = $date = $d;
                else
                    $champs[$champ_date] = $date = date('Y-m-d H:i:s');
            }
        }
    }


    // Envoyer aux plugins
    $champs = pipeline('pre_edition',
        array(
            'args' => array(
                'table' => 'spip_reservations',
                'id_reservation' => $id,
                'action'=>'instituer',
                'statut_ancien' => $statut_ancien,
                'date_ancienne' => $date_ancienne,
            ),
            'data' => $champs
        )
    );

    if (!count($champs)) return '';
    
        // Envoyer les modifs.
    objet_editer_heritage('reservation', $id,'', $statut_ancien, $champs);

    // Invalider les caches
    include_spip('inc/invalideur');
    suivre_invalideur("id='reservation/$id'");


    // Pipeline
    pipeline('post_edition',
        array(
            'args' => array(
                'table' => 'spip_reservations',
                'id_reservation' => $id,
                'action'=>'instituer',
                'statut_ancien' => $statut_ancien,
                'date_ancienne' => $date_ancienne,
                'id_parent_ancien' => $id_rubrique,
            ),
            'data' => $champs
        )
    );
    
    $id_reservation=$id;

    $action=charger_fonction('editer_objet','action');
    $quantite=_request('quantite');
    $set=array(
        'id_reservation'=>$id_reservation,
        'statut'=>$statut
    );
    $evenements=_request('id_evenement');
    if(!is_array($evenements)){
        $evenements=array();
        $sql=sql_select('id_evenement','spip_reservations_details','id_reservation');
        while($data=sql_fetch($sql)){
            $evenements[]=$data['id_evenement'];
        }
    }
    foreach($evenements AS  $id_evenement){
        
        $set['id_evenement']=$id_evenement;
        $evenement=sql_fetsel('titre,places','spip_evenements','id_evenement='.$id_evenement);
        $set['descriptif']=$evenement['titre'];
        if(intval($evenement['places']))$set['places']=$evenement['places'];
        if(intval($quantite[$id_evenement]))$set['quantite']=$quantite[$id_evenement];
        else $set['quantite']=1; 
        if(!$id_reservations_detail=sql_getfetsel('id_reservations_detail','spip_reservations_details','id_reservation='.$id_reservation.' AND id_evenement='.$id_evenement))
        $id_reservations_detail='new';
        
        if($shop_prix=test_plugin_actif('shop_prix')){
            $fonction_prix = charger_fonction('prix', 'inc/');
            $fonction_prix_ht = charger_fonction('ht', 'inc/prix');                
            $p=sql_getfetsel('prix_ht,id_prix_objet','spip_prix_objets','objet='.sql_quote('evenement'),'id_objet='.$id_evenement); 
            
            $prix_ht = $fonction_prix_ht('prix_objet', $p['id_prix_objet']);
            $prix = $fonction_prix('prix_objet',$p['id_prix_objet']);
            $taxe = round(($prix - $prix_ht) / $prix_ht, 3);
            $set['prix_unitaire_ht']=$prix_ht; 
            $set['taxe']=$taxe;                 
        }         
        $detail=$action($id_reservations_detail,'reservations_detail',$set);
    }

    // Notifications
    include_spip('inc/config');
    $config = lire_config('reservation_evenement');
    if ((!$statut_ancien OR $statut != $statut_ancien ) &&
         ($config['activer']) &&
         (in_array($statut,$config['quand'])) &&
         ($notifications = charger_fonction('notifications', 'inc', true))
        ) {

        // Determiner l'expediteur
        $options = array();
        if( $config['expediteur'] != "facteur" )
            $options['expediteur'] = $config['expediteur_'.$config['expediteur']];

        // Envoyer au vendeur et au client
        $notifications('reservation_vendeur', $id, $options);
        if($config['client'])
            $notifications('reservation_client', $id, $options);
    }

    return ''; // pas d'erreur
}
