<?php
/**
 * Plugin Agenda 4 pour Spip 3.0
 * Licence GPL 3
 *
 * 2006-2011
 * Auteurs : cf paquet.xml
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');

function formulaires_reservation_charger_dist($id=''){

	
	// si pas d'evenement ou d'inscription, on echoue silencieusement
	
	$where=array('date_fin>NOW()');
    if(intval($id))$where[]='id_evenement='.intval($id);
	$sql = sql_select('*','spip_evenements',$where,'','date_debut,date_fin');

    $evenements=array();
    while ($row=sql_fetch($sql)){
        $evenements[$row['id_evenement']]=$row;
    };
    
    $valeurs = array('evenements'=>$evenements);
    
    if(intval($GLOBALS['visiteur_session'])){
        $session=$GLOBALS['visiteur_session'];
        $nom=$session['nom'];
        $email=$session['email'];                
        
    }


	// valeurs d'initialisation
	$valeurs['id_evenement'] = _request('id_evenement')?_request('id_evenement'):array();
    $valeurs['id_auteur']=$id_auteur; 
    $valeurs['nom']=$nom; 
    $valeurs['email']=$email; 
    $valeurs['enregistrer']=_request('enregistrer');  
    $valeurs['new_pass']=_request('new_pass');    
    $valeurs['new_pass2']=_request('new_pass2');  
    $valeurs['new_login']=_request('new_login');       
	$valeurs['statut'] = 'encours'; 
    
    //les champs extras auteur
    include_spip('cextras_pipelines');
    
    if(function_exists('champs_extras_objet')){
        //Charger les définitions pour la création des formulaires
        $valeurs['champs_extras_auteurs']=champs_extras_objet(table_objet_sql('auteur'));
       foreach($valeurs['champs_extras_auteurs'] as $value){
           $valeurs[$value['options']['nom']]=$session[$value['options']['nom']]; 
            }
        }
    
   

    $valeurs['_hidden'].='<input type="hidden" name="statut" value="'.$valeurs['statut'].'"/>'; 
   


	return $valeurs;
}

function formulaires_reservation_verifier_dist($id=''){
	$erreurs = array();
    $email=_request('email');
    
    if(isset($GLOBALS['visiteur_session']['id_auteur'])){
                $id_auteur=$GLOBALS['visiteur_session']['id_auteur'];
            }
        
         if(_request('enregistrer'))  {
            include_spip('inc/auth');
             $obligatoires=array('nom','email','new_pass','new_login','id_evenement');
             foreach($obligatoires AS $champ){
                   if(!_request($champ))$erreurs[$champ]=_T("info_obligatoire");
                  }
            //Vérifier le login
            if ($err = auth_verifier_login($auth_methode, _request('new_login'), $id_auteur)){
                $erreurs['new_login'] = $err;
                $erreurs['message_erreur'] .= $err;
            }
             
              //Vérifier les mp
             if ($p = _request('new_pass')) {
                    if ($p != _request('new_pass2')) {
                        $erreurs['new_pass'] = _T('info_passes_identiques');
                        $erreurs['message_erreur'] .= _T('info_passes_identiques');
                    }
                    elseif ($err = auth_verifier_pass($auth_methode, _request('new_login'),$p, $id_auteur)){
                        $erreurs['new_pass'] = $err;
                    }
                }
             }
        else{
            $obligatoires=array('nom','email','id_evenement');  
            foreach($obligatoires AS $champ){
                if(!_request($champ))$erreurs[$champ]=_T("info_obligatoire");
            }
        } 

         if ($email){
            include_spip('inc/filtres');
            // un redacteur qui modifie son email n'a pas le droit de le vider si il y en avait un
            if (!email_valide($email)){
                $erreurs['email'] = (($id_auteur==$GLOBALS['visiteur_session']['id_auteur'])?_T('form_email_non_valide'):_T('form_prop_indiquer_email'));
                }
            elseif(!$id_auteur){
                if($email_utilise=sql_getfetsel('email','spip_auteurs','email='.sql_quote($email))) $erreurs['email']=_T('reservation:erreur_email_utilise');
                }
            }

	return $erreurs;
}

function formulaires_reservation_traiter_dist($id=''){
    include_spip('inc/session');    
    include_spip('inc/config');
    $config=lire_config('reservation_evenement');
    $statut = $config['statut_defaut']?$config['statut_defaut']:'encours'; 

    //Créer la réservation
    $action=charger_fonction('editer_objet','action');
    // La référence
    $fonction_reference = charger_fonction('reservation_reference', 'inc/');
    if(intval($GLOBALS['visiteur_session']['id_auteur']))$id_auteur=$GLOBALS['visiteur_session']['id_auteur'];  
     $set=array('statut'=>$statut);

   if(_request('enregistrer')){
            include_spip('actions/editer_auteur');
            
            if(!$id_auteur){
                $res = formulaires_editer_objet_traiter('auteur','new','','',$retour,$config_fonc,$row,$hidden);
                $id_auteur=$res['id_auteur'];
                sql_updateq('spip_auteurs',array('statut'=>'6forum'),'id_auteur='.$id_auteur);
                }
        
        $set['reference']=$fonction_reference($id_auteur);
        }
   elseif(!$id_auteur){
       $set['nom']=_request('nom');
       $set['email']=_request('email'); 
   }
    $set['reference']=$fonction_reference();      
    $set['id_auteur']=$id_auteur;
    $id_reservation=$action('new','reservation',$set);
    $message='<strong>'._T('reservation:details_reservation').'</strong>';
    $message.=recuperer_fond('inclure/reservation',array('id_reservation'=>$id_reservation[0]));
	
	return array('message_ok'=>$message,'editable'=>false);
}

?>