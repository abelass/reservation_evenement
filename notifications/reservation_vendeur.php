<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function notifications_reservation_vendeur_destinataires_dist($id_reservation, $options) {	
	include_spip('inc/config');
	$config = lire_config('reservation_evenement');
	return $config['vendeur_'.$config['vendeur']];	

}

?>