<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function action_editer_reservations_detail_dist($reservations_detail=null,$set=null) {
    $objet='reservations_detail';
    // appel direct depuis une url interdit
    if (is_null($reservations_detail) OR is_null($objet)){
        include_spip('inc/minipres');
        echo minipres(_T('info_acces_interdit'));
        die();
    }

    // si id n'est pas un nombre, c'est une creation
    // mais on verifie qu'on a toutes les donnees qu'il faut.
    if (!$id_reservations_detail = intval($id_reservations_detail)) {
        // on ne sait pas si un parent existe mais on essaye

        $id_reservations_detail = reservations_detail_inserer($set);
    }

    if (!($id_reservations_detail = intval($id_reservations_detail))>0)
        return array($id,_L('echec enregistrement en base'));

    // Enregistre l'envoi dans la BD
    $err = objet_modifier($objet,$id_reservations_detail, $set);

    return array($id,$err);
}


function reservations_detail_inserer($set=null) {
    $table_sql = 'reservations_details';
    
    $donnees_reservations_details=charger_fonction('donnees_reservations_details','inc');
    spip_log($set,'teste').
    $champs = $donnees_reservations_details($set);
    $champs['statut'] = 'attente';

    if ($set) $champs = array_merge($champs, $set);

    // Envoyer aux plugins
    $champs = pipeline('pre_insertion',
        array(
            'args' => array(
                'table' => $table_sql,
            ),
            'data' => $champs
        )
    );

    $id_reservations_detail = sql_insertq($table_sql, $champs);
    
 

    if ($id_reservations_detail){
        pipeline('post_insertion',
            array(
                'args' => array(
                    'table' => $table_sql,
                    'id_objet' => $$id_reservations_detail,
                ),
                'data' => $champs
            )
        );
    }
    return $id;
}


function reservations_detail_instituer($id, $c, $calcul_rub=true) {

    include_spip('inc/autoriser');
    include_spip('inc/rubriques');
    include_spip('inc/modifier');
    
    $row = sql_fetsel('statut,id_evenement','spip_reservations_details','id_reservations_detail='.intval($id));
    
    
    if(!$places=$c[places]){
        $places=sql_getfetsel('places','spip_evenements','id_evenement='.$row['id_evenement']);
        }
    $statut_ancien = $statut = $row['statut'];

    $s = isset($c['statut']) ? $c['statut'] : $statut;
    
    
    $champs = array();
    $champs['statut']= $s ;
    // compter les réservations
    if ($s != $statut and $s=='accepte') {
        if(intval($places) AND $places>0){
            $sql=sql_select('quantite','spip_reservations_details','id_evenement='.$c['id_evenement'].' AND statut ='.sql_quote('accepte'));
            
            $reservations=array();
            while($data=sql_fetch($sql)){
                $reservations[]=$data['quantite'];
            }
            if(array_sum($reservations)>=$places)$champs['statut']='attente';
            
        }

    }
    spip_log($champs['statut']. 'detail','teste');
    
    // Envoyer aux plugins
    $champs = pipeline('pre_edition',
        array(
            'args' => array(
                'table' => 'spip_reservations_details',
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
    objet_editer_heritage('reservations_detail', $id,'', $statut_ancien, $champs);

    // Invalider les caches
    include_spip('inc/invalideur');
    suivre_invalideur("id='reservations_detail/$id'");


    // Pipeline
    pipeline('post_edition',
        array(
            'args' => array(
                'table' => 'spip_reservations_details',
                'id_reservation' => $id,
                'action'=>'instituer',
                'statut_ancien' => $statut_ancien,
                'date_ancienne' => $date_ancienne,
                'id_parent_ancien' => $id_rubrique,
            ),
            'data' => $champs
        )
    );

    return ''; // pas d'erreur
}
