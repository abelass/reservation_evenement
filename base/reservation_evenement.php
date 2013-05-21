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
		'type' => 'evenementid_auteur',
		'principale' => "oui", 
		'table_objet_surnoms' => array('evenementsparticipant'), // table_objet('evenementid_auteur') => 'evenements_participants' 
		'field'=> array(
			"id_evenement,id_auteur" => "bigint(21) NOT NULL",
			"id_evenement"       => "bigint(21) NOT NULL DEFAULT '0'",
			"id_auteur"          => "bigint(21) NOT NULL DEFAULT '0'",
			"reponse"            => "char(3) NOT NULL DEFAULT '?'",
			"date"               => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'", 
			"statut"             => "varchar(20)  DEFAULT '0' NOT NULL", 
			"maj"                => "TIMESTAMP"
		),
		'key' => array(
			"PRIMARY KEY"        => "id_evenement,id_auteur",
			"KEY statut"         => "statut", 
		),
		'titre' => "'' AS titre, '' AS lang",
		'date' => "date",
		'champs_editables'  => array(),
		'champs_versionnes' => array(),
		'rechercher_champs' => array(),
		'tables_jointures'  => array(),
		'statut_textes_instituer' => array(
			'prepa'    => 'texte_statut_en_cours_redaction',
			'prop'     => 'texte_statut_propose_evaluation',
			'publie'   => 'texte_statut_publie',
			'refuse'   => 'texte_statut_refuse',
			'poubelle' => 'texte_statut_poubelle',
		),
		'statut'=> array(
			array(
				'champ'     => 'statut',
				'publie'    => 'publie',
				'previsu'   => 'publie,prop,prepa',
				'post_date' => 'date', 
				'exception' => array('statut','tout')
			)
		),
		'texte_changer_statut' => 'evenementid_auteur:texte_changer_statut_evenementid_auteur', 
		

	);

	return $tables;
}



?>