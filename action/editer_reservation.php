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
    
    
    // Les traitements spécifiques
    $id_reservation=$id;

    $action=charger_fonction('editer_objet','action');
    $quantite=_request('quantite');
    $set=array(
        'id_reservation'=>$id_reservation,
        'statut'=>$statut
    );
    
    // Gérer les détails des réservations
    $evenements=_request('id_evenement');
    
    //Si les déclinaisons sont actives on récupère les évenements via le prix
     if(test_plugin_actif('shop_declinaisons')){
         $evenements=array();
        if($id_prix_objet=_request('id_objet_prix')){
            foreach(array_keys($id_prix_objet )AS $id_evenement){
                $evenements[]=$id_evenement;
            }
        }
     }
    
    // Si on n'est pas dans le cas d'une crátion, on récupère les détails attachées ' la réservation
    if(!is_array($evenements)){ 
        $evenements=array();
        $sql=sql_select('id_evenement','spip_reservations_details','id_reservation');
        while($data=sql_fetch($sql)){
            $evenements[]=$data['id_evenement'];
        }
    }
    

    foreach($evenements AS  $id_evenement){
        // Ñes données de l'évènenement
        $set['id_evenement']=$id_evenement;
        $evenement=sql_fetsel('*','spip_evenements','id_evenement='.$id_evenement);
        $date_debut=$evenement['date_debut'];
        $date_fin=$evenement['date_fin'];
        // On établit les dates        
        if($date_debut!=$date_fin){
            if(affdate($date_debut,'d-m-Y')==affdate($date_fin,'d-m-Y')){
                $date=affdate($date_debut,'d/m/Y').','.affdate($date_debut,'G:i').'-'.affdate($date_fin,'G:i');
            }
            else $date=affdate($date_debut,'d/m/Y').'-'.affdate($date_fin,'d/m/Y'); 
            }
        else{
            if(affdate($date_debut,'G:i')=='0:00')$date=affdate($date_debut,'d/m/Y');
            else $date=affdate($date_debut,'d/m/Y G:i');
        }
        // Les déscriptif
        $set['descriptif']=$evenement['titre'].' - '.$date;
        if(intval($evenement['places']))$set['places']=$evenement['places'];
        if(intval($quantite[$id_evenement]))$set['quantite']=$quantite[$id_evenement];
        else $set['quantite']=1; 
        
        // Si aucun détail n'est attaché à l'evénement, on le crée
        if(!$id_reservations_detail=sql_getfetsel('id_reservations_detail','spip_reservations_details','id_reservation='.$id_reservation.' AND id_evenement='.$id_evenement))
        $id_reservations_detail='new';
        
        /*Existence d'un prix via le plugin Shop Prix https://github.com/abelass/shop_prix_objet */
        if($shop_prix=test_plugin_actif('shop_prix')){
            $fonction_prix = charger_fonction('prix', 'inc/');
            $fonction_prix_ht = charger_fonction('ht', 'inc/prix');
             /*si le plugin déclinaison est active il peut y avoir plusieurs prix par évenement*/
            if(test_plugin_actif('shop_declinaisons')){
                spip_log($id_prix_objet[$id_evenement],'teste');
                if(is_array($id_prix_objet))$id_prix=isset($id_prix_objet[$id_evenement])?$id_prix_objet[$id_evenement]:'';
                else $id_prix=$id_prix_objet;
                
                $p=sql_fetsel('prix_ht,id_prix_objet,id_declinaison','spip_prix_objets','id_prix_objet='.$id_prix); 
                if($p['id_declinaison']>0)$set['descriptif'].='-'.sql_getfetsel('titre','spip_declinaisons','id_declinaison='.$p['id_declinaison']);
                }                
            else $p=sql_fetsel('prix_ht,id_prix_objet','spip_prix_objets','objet='.sql_quote('evenement') AND 'id_objet='.$id_evenement); 
            
            $prix_ht = $fonction_prix_ht('prix_objet', $p['id_prix_objet']);
            $prix = $fonction_prix('prix_objet',$p['id_prix_objet']);
            $taxe = round(($prix - $prix_ht) / $prix_ht, 3);
            $set['prix_ht']=$prix_ht; 
            $set['taxe']=$taxe;                 
            }
         /*Sinon un prix attaché 'a l'évenement*/
        elseif(intval($evenement['prix'])){
            $fonction_prix = charger_fonction('prix', 'inc/');
            $fonction_prix_ht = charger_fonction('ht', 'inc/prix');  
            $prix_ht = $fonction_prix_ht('evenement', $id_evenement); 
            $prix = $fonction_prix('evenement',$id_evenement);
            $taxe = round(($prix - $prix_ht) / $prix_ht, 3);
            $set['prix_ht']=$prix_ht; 
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
