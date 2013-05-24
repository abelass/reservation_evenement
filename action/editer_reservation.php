<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

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
        if ($s!='publie' AND autoriser('modifier','reservation', $id))
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
    objet_editer_heritage('reservation', $id, $id_rubrique, $statut_ancien, $champs, $calcul_rub);

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

    // Notifications
    include_spip('inc/config');
    $config = lire_config('reservation_evenement');
    if (($statut != $statut_ancien) &&
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
