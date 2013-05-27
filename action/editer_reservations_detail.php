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

    $s = isset($c['statut']) ? $c['statut'] : $statut;
    
    $statut_ancien = $statut = $row['statut'];

    $champs = array();
    $champs['statut']= $s ;
    // cf autorisations dans inc/instituer_objet
    if ($s != $statut and $s=='accepte') {
        if(intval($places) AND $places>0){
            $sql=sql_select('*','spip_reservations_details','id_evenement='.$c['id_evenement'].' AND statut ='.sql_quote('accepte'));
            if(sql_count($sql)>=$places)$champs['statut']=$statut_ancien;
            
        }

    }

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
    suivre_invalideur("id='$objet/$id'");

    /*
    if ($date) {
        $t = strtotime($date);
        $p = @$GLOBALS['meta']['date_prochain_postdate'];
        if ($t > time() AND (!$p OR ($t < $p))) {
            ecrire_meta('date_prochain_postdate', $t);
        }
    }*/

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
