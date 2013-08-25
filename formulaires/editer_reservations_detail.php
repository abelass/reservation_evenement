<?php
/**
 * Gestion du formulaire de d'édition de evenements_participant
 *
 * @plugin     Réservation Événements
 * @copyright  2013
 * @author     Rainer Müller
 * @licence    GNU/GPL
 * @package    SPIP\Reservation_evenement\Formulaires
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/actions');
include_spip('inc/editer');

/**
 * Identifier le formulaire en faisant abstraction des paramètres qui ne représentent pas l'objet edité
 *
 * @param int|string $id_evenements_participant
 *     Identifiant du evenements_participant. 'new' pour un nouveau evenements_participant.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un evenements_participant source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du evenements_participant, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return string
 *     Hash du formulaire
 */
function formulaires_editer_reservations_detail_identifier_dist($id_reservations_detail='new', $retour='', $lier_trad=0, $config_fonc='', $row=array(), $hidden=''){
	return serialize(array(intval($id_reservations_detail)));
}

/**
 * Chargement du formulaire d'édition de evenements_participant
 *
 * Déclarer les champs postés et y intégrer les valeurs par défaut
 *
 * @uses formulaires_editer_objet_charger()
 *
 * @param int|string $id_evenements_participant
 *     Identifiant du evenements_participant. 'new' pour un nouveau evenements_participant.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un evenements_participant source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du evenements_participant, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Environnement du formulaire
 */
function formulaires_editer_reservations_detail_charger_dist($id_reservations_detail='new', $retour='', $lier_trad=0, $config_fonc='', $row=array(), $hidden=''){
	$valeurs = formulaires_editer_objet_charger('reservations_detail',$id_reservations_detail,'',$lier_trad,$retour,$config_fonc,$row,$hidden);
    
    $valeurs['evenements']=array();
    
    $sql=sql_select('id_evenement,titre,date_debut,date_fin','spip_evenements','statut="publie"','','date_debut');
    
    while($data=sql_fetch($sql)){
        // On établit les dates        
        if($date_debut=$data['date_debut']!=$date_fin=$data['date_debut']){
            if(affdate($date_debut,'d-m-Y')==affdate($date_fin,'d-m-Y')){
                $date=affdate($date_debut,'d/m/Y').','.affdate($date_debut,'G:i').'-'.affdate($date_fin,'G:i');
            }
            else {
                $date=affdate($date_debut,'d/m/Y').'-'.affdate($date_fin,'d/m/Y').', '.affdate($date_debut,'nom_jour').' '.affdate($date_debut,'G:i').'-'.affdate($date_fin,'G:i'); 
                
                }
            }
        else{
            if(affdate($date_debut,'G:i')=='0:00')$date=affdate($date_debut,'d/m/Y');
            else $date=affdate($date_debut,'d/m/Y G:i');
        }        
        
       $valeurs['evenements'][$data['id_evenement']] =$data['titre'];
    }
    
	return $valeurs;
}

/**
 * Vérifications du formulaire d'édition de evenements_participant
 *
 * Vérifier les champs postés et signaler d'éventuelles erreurs
 *
 * @uses formulaires_editer_objet_verifier()
 *
 * @param int|string $id_evenements_participant
 *     Identifiant du evenements_participant. 'new' pour un nouveau evenements_participant.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un evenements_participant source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du evenements_participant, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Tableau des erreurs
 */
function formulaires_editer_reservations_detail_verifier_dist($id_reservations_detail='new', $retour='', $lier_trad=0, $config_fonc='', $row=array(), $hidden=''){
	return formulaires_editer_objet_verifier('reservations_detail',$id_reservations_detail, array('id_evenement','descriptif'));
}

/**
 * Traitement du formulaire d'édition de evenements_participant
 *
 * Traiter les champs postés
 *
 * @uses formulaires_editer_objet_traiter()
 *
 * @param int|string $id_evenements_participant
 *     Identifiant du evenements_participant. 'new' pour un nouveau evenements_participant.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un evenements_participant source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du evenements_participant, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Retours des traitements
 */
function formulaires_editer_reservations_detail_traiter_dist($id_evenements_participant='new', $retour='', $lier_trad=0, $config_fonc='', $row=array(), $hidden=''){
	return formulaires_editer_objet_traiter('reservations_detail',$id_reservations_detail,'',$lier_trad,$retour,$config_fonc,$row,$hidden);
}


?>