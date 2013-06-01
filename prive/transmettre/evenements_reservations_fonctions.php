<?php
/**
 * Plugin Agenda 4 pour Spip 3.0
 * Licence GPL 3
 *
 * 2006-2011
 * Auteurs : cf paquet.xml
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function champs_extras_reservation(){
    //les champs extras auteur
    include_spip('cextras_pipelines');
    
    if(function_exists('champs_extras_objet')){
        //Charger les définitions pour la création des formulaires
        $champs_extras_auteurs=champs_extras_objet(table_objet_sql('auteur'));

    }
    
    return $champs_extras_auteurs;
}
