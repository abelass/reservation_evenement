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

	$interfaces['table_des_tables']['evenements_participants'] = 'evenements_participants';

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

	$tables['spip_evenements_participants'] = array(
		'type' => 'evenements_participant',
		'principale' => "oui", 
		'table_objet_surnoms' => array('evenementsparticipant'), // table_objet('evenements_participant') => 'evenements_participants' 
		'field'=> array(
			"id_evenements_participant" => "bigint(21) NOT NULL",
			"id_evenement"       => "bigint(21) NOT NULL DEFAULT '0'",
			"id_auteur"          => "bigint(21) NOT NULL DEFAULT '0'",
			"reponse"            => "char(3) NOT NULL DEFAULT '?'",
			"statut"             => "varchar(25) NOT NULL DEFAULT ''",
			"date"               => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
			"date_paiement"      => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
			"type_paiement"      => "varchar(50) NOT NULL DEFAULT ''",
			"descriptif"         => "text NOT NULL DEFAULT ''",
			"quantite"           => "int(11) NOT NULL DEFAULT 0",
			"prix_unitaire_ht"   => "float",
			"objet"              => "varchar(25) NOT NULL DEFAULT ''",
			"id_objet"           => "bigint(21) NOT NULL DEFAULT 0",
			"maj"                => "timestamp",
			"date"               => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'", 
			"statut"             => "varchar(20)  DEFAULT '0' NOT NULL", 
			"maj"                => "TIMESTAMP"
		),
		'key' => array(
			"PRIMARY KEY"        => "id_evenements_participant",
			"KEY statut"         => "statut,id_evenement,id_article", 
		),
		'titre' => "CONCAT(id_evenements_participant,'-',descriptif) AS titre, '' AS lang",
		'date' => "date",
		'champs_editables'  => array('id_auteur', 'date_paiement', 'quantite', 'prix_unitaire_ht'),
		'champs_versionnes' => array('id_auteur', 'date_paiement', 'prix_unitaire_ht'),
		'rechercher_champs' => array(),
		'tables_jointures'  => array(),
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
		'texte_changer_statut' => 'evenements_participant:texte_changer_statut_evenements_participant', 
		

	);

	return $tables;
}



?>