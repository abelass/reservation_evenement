<?php
/**
 * Utilisations de pipelines par Réservation Événements
 *
 * @plugin     Réservation Événements
 * @copyright  2013
 * @author     Rainer Müller
 * @licence    GNU/GPL
 * @package    SPIP\Reservation_evenement\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) return;
	

/*
 * Un fichier de pipelines permet de regrouper
 * les fonctions de branchement de votre plugin
 * sur des pipelines existants.
 */



/**
 * Ajout de liste sur la vue d'un auteur
 *
 * @pipeline affiche_auteurs_interventions
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function reservation_evenement_affiche_auteurs_interventions($flux) {
	if ($id_auteur = intval($flux['args']['id_auteur'])) {

		$flux['data'] .= recuperer_fond('prive/objets/liste/reservations', array(
			'id_auteur' => $id_auteur,
			'titre' => _T('reservation:info_reservations_auteur')
		), array('ajax' => true));

	}
	return $flux;
}
/*
function reservation_evenement_affiche_milieu($flux) {
    $texte = "";
    $e = trouver_objet_exec($flux['args']['exec']);



    // reservations sur les evenements
    if (!$e['edition'] AND in_array($e['type'], array('evenement'))) {
        $texte .= recuperer_fond('prive/objets/editer/liens', array(
            'table_source' => 'reservations',
            'objet' => $e['type'],
            'id_objet' => $flux['args'][$e['id_table_objet']]
        ));
    }

    if ($texte) {
        if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
            $flux['data'] = substr_replace($flux['data'],$texte,$p,0);
        else
            $flux['data'] .= $texte;
    }

    return $flux;
}*/

?>