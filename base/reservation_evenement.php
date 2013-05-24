<?php
/**
 * Déclarations relatives à la base de données
 *
 * @plugin     Réservation Événements
 * @copyright  2013
 * @author     Rainer Müller
 * @licence    GNU/GPL
 * @package    SPIP\Reservation_evenement\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Déclaration des alias de tables et filtres automatiques de champs
 *
 * @pipeline declarer_tables_interfaces
 * @param array $interfaces
 *     Déclarations d'interface pour le compilateur
 * @return array
 *     Déclarations d'interface pour le compilateur
 */
function reservation_evenement_declarer_tables_interfaces($interfaces) {

	$interfaces['table_des_tables']['reservations'] = 'reservations';
	$interfaces['table_des_tables']['reservations_details'] = 'reservations_details';    

	return $interfaces;
}


/**
 * Déclaration des objets éditoriaux
 *
 * @pipeline declarer_tables_objets_sql
 * @param array $tables
 *     Description des tables
 * @return array
 *     Description complétée des tables
 */
function reservation_evenement_declarer_tables_objets_sql($tables) {

	$tables['spip_reservations'] = array(
		'type' => 'reservation',
		'principale' => "oui",
		'field'=> array(
			"id_reservation"     => "bigint(21) NOT NULL",
			"id_auteur"          => "bigint(21) NOT NULL DEFAULT '0'",
            "reference"          => "varchar(255) NOT NULL DEFAULT ''",			
			"statut"             => "varchar(25) NOT NULL DEFAULT ''",
			"date"               => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
			"date_paiement"      => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
			"type_paiement"      => "varchar(50) NOT NULL DEFAULT ''",
			"nom"                => "varchar(255) NOT NULL DEFAULT ''",
			"email"              => "varchar(255) NOT NULL DEFAULT ''",
			"maj"                => "timestamp",
			"donnees_auteur"     => "text NOT NULL DEFAULT ''",
			"date"               => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'", 
			"statut"             => "varchar(20)  DEFAULT '0' NOT NULL", 
			"maj"                => "TIMESTAMP"
		),
		'key' => array(
			"PRIMARY KEY"        => "id_reservation",
			"KEY statut"         => "statut,id_auteur", 
		),
		'titre' => "reference AS titre, '' AS lang",
		'date' => "date",
		'champs_editables'  => array('id_auteur', 'date_paiement', 'nom', 'email', 'donnees_auteur', 'reference'),
		'champs_versionnes' => array('id_auteur', 'date_paiement', 'nom', 'email', 'donnees_auteur', 'reference'),
		'rechercher_champs' => array("reference" => 8,"id_reservation"=>8),
		'tables_jointures'  => array('id_reservation','id_auteur'),
'statut_textes_instituer' => array(
            'attente'    => 'evenements_participant:texte_statut_en_attente',
            'accepte'     => 'evenements_participant:texte_statut_accepte',
            'refuse'   => 'evenements_participant:texte_statut_refuse',
            'poubelle' => 'evenements_participant:texte_statut_poubelle',
        ),
        'statut_images' => array(
            'attente'          => 'puce-reservation-attente.png',
            'accepte'          => 'puce-reservation-accepte.png',
            'refuse'             => 'puce-reservation-refuse.png',
            'poubelle'           => 'puce-reservation-poubelle.png',
        ),
        'statut'=> array(
            array(
                'champ'     => 'statut',
                'publie'    => 'accepte',
                'previsu'   => 'accepte,attente',
                'post_date' => 'date', 
                'exception' => array('statut','tout')
            )
        ),
		'texte_changer_statut' => 'reservation:texte_changer_statut_reservation', 
		

	);

	return $tables;
}

function reservation_evenement_declarer_tables_principales($tables_principales){

    // Table reservations_details
    $reservations_details = array(
        'id_reservations_detail' => 'bigint(21) not null',
        'id_reservation' => 'bigint(21) not null default 0',
        'id_evenement' => 'bigint(21) not null default 0',        
        'descriptif' => 'text not null default ""',
        'quantite' => 'int not null default 0',
        'prix_unitaire_ht' => 'float not null default 0',
        'taxe' => 'decimal(4,3) not null default 0',
        'maj' => 'timestamp'
    );
    
    $reservations_details_cles = array(
        'PRIMARY KEY' => 'id_reservations_detail',
        'KEY id_reservation' => 'id_reservation',
        'KEY id_evenement' => 'id_evenement',        
    );
    
    $tables_principales['spip_reservations_details'] = array(
        'field' => &$reservations_details,
        'key' => &$reservations_details_cles,
        'join'=> array(
            'id_reservations_detail' => 'id_reservations_detail',
            'id_reservation' => 'id_reservation',
            'id_evenement' => 'id_evenement'
        )
    );

    return $tables_principales;
}

?>