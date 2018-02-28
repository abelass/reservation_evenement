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
 * @param array $args
 *        	Variables du contexte.
 * @return array Définition.
 */
function messages_personnalises_notification_reservation_vendeur_dist($args) {
	$definitions = charger_fonction('notification_reservation_client', 'messages_personnalises');
	$definitions = $definitions($args);
	$definitions['label'] = _T('reservation:titre_reservation_vendeur');
	$definitions['declencheurs']['qui']  = array(
		'vendeur' => _T('reservation:notifications_vendeur_label')
	);

	return $definitions;
}
