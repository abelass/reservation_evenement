<?php
if (!defined("_ECRIRE_INC_VERSION")) return;


function notifications_reservation_client_dist($quoi,$id_reservation, $options) {
    include_spip('inc/config');
    $config = lire_config('reservation_evenement');

    $envoyer_mail = charger_fonction('envoyer_mail','inc');
    
    $options['id_reservation']=$id_reservation;  
    $options['qui']='client';     
    $subject=_T('reservation:votre_reservation_sur',array('nom'=>$GLOBALS['meta']['nom_site']));

    $message=recuperer_fond('notifications/contenu_reservation_mail',$options);
     
    //
    // Envoyer les emails
    //
    //
    //
    $nom='TissusBruxelles.pdf';
    $fichier=realpath(find_in_path('docs/'.$nom));
    $o=array('html'=>$message);
    if($options['statut']=='accepte')$o['pieces_jointes'] = array(
               array('chemin' => $fichier,
               'nom' => $nom,
               'encodage' => 'base64',
               'mime' => 'application/pdf')
               );

    $envoyer_mail($options['email'],$subject,$o);
}

?>