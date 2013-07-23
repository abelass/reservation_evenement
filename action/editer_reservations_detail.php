<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function reservations_detail_inserer($id_parent=null, $set=null) {
    $table_sql = table_objet_sql('reservations_detail');
    $champs = array();
    $champs['statut'] = 'attente';

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
            if(array_sum($reservations)>=$places)$champs['statut']=$statut_ancien;
            
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
