<?php
/**
 * Définitions pour la personnalisation du message pour le plugin
 * Message personnalisé https://github.com/abelass/message_personnalise.
 *
 * @plugin     Réservation suivi
 * @copyright  2018
 * @author     Rainer
 * @licence    GNU/GPL
 * @package    SPIP\Reservation_suivi\Mp_messages
 */

/**
 *
 * @param array $contexte
 *        	Variables du contexte.
 * @return array Définition.
 */
function messages_personnalises_reservation_dist($contexte) {
	$reservations = lister_tables_objets_sql('spip_reservations');
	$statuts = array();
	foreach ($reservations['statut_textes_instituer'] as $statut => $chaine) {
		$statuts[$statut] = _T($chaine);
	}

	//print_r($reservations['statut_textes_instituer']);
	return array(
		'nom' => _T('reservation:titre_reservation'),
		'declencheurs' => array(
			'statut' => $statuts,
			'qui' => array(
				'client' => _T('reservation:notifications_client_label'),
				'vendeur' => _T('reservation:notifications_vendeur_label')
			),
		),
		'champs_disponibles' => array_keys($reservations['field']),
	);
}
