<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function notifications_reservation_client_destinataires_dist($id_reservation, $options) {	
	$donnees=sql_fetsel("id_auteur,email","spip_reservations","id_reservation=".$id_reservation);
    if(intval($donnees['id_auteur']))$dest=$donnees['id_auteur'];
    else $dest=$donnees['email'];
	return array($dest);	
}

?>