<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function notifications_reservation_client_destinataires_dist($id_reservation, $options) {	
	$donnees=sql_fetsel("id_auteur,email","spip_reservations","id_reservation=".$id_reservation);
    if(intval($donnees['email']))$dest=$donnees['email'];
    else $dest=$donnees['id_auteur'];
	return array($dest);	
}

?>