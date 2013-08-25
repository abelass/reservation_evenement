<?php
if (!defined('_ECRIRE_INC_VERSION')) return; 

function inc_donnees_reservations_details_dist($set) {
    
     // Les données de l'évènenement
    if(isset($set['id_evenement'])){
        $id_evenement=$set['id_evenement'];
        }
    else $set['id_evenement']=$id_evenement=sql_getfetsel('id_evenement','reservations_details','id_reservations_detail='.$set['id_reservations_detail']);
        $id_prix_objet=$set['id_objet_prix'];
        $evenement=sql_fetsel('*','spip_evenements','id_evenement='.$id_evenement);
        $date_debut=$evenement['date_debut'];
        $date_fin=$evenement['date_fin'];
        // On établit les dates        
        if($date_debut!=$date_fin){
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
        // Les déscriptif
        $set['descriptif']=$evenement['titre'].' - '.$date;
        if(intval($evenement['places']))$set['places']=$evenement['places'];
        if(intval($quantite[$id_evenement]))$set['quantite']=$quantite[$id_evenement];
        else $set['quantite']=1; 
        
        // Si aucun détail n'est attaché à l'evénement, on le crée
        
        if(!$reservations_detail=sql_fetsel('*','spip_reservations_details','id_reservation='.$set['id_reservation'].' AND id_evenement='.$id_evenement)) $id_reservations_detail='new';
        else{
            $id_reservations_detail=$reservations_detail['id_reservations_detail'];
            $id_prix_objet=$reservations_detail['id_prix_objet'];
            }
        
        /*Existence d'un prix via le plugin Shop Prix https://github.com/abelass/shop_prix_objet */
        if($shop_prix=test_plugin_actif('shop_prix')){
            $fonction_prix = charger_fonction('prix', 'inc/');
            $fonction_prix_ht = charger_fonction('ht', 'inc/prix');
             /*si le plugin déclinaison est active il peut y avoir plusieurs prix par évenement*/
             spip_log($id_prix_objet,'teste');
            if(test_plugin_actif('shop_declinaisons')){
                if(is_array($id_prix_objet))$id_prix=isset($id_prix_objet[$id_evenement])?$id_prix_objet[$id_evenement]:'';
                else $id_prix=$id_prix_objet;
                
                $p=sql_fetsel('prix_ht,id_prix_objet,id_declinaison','spip_prix_objets','id_prix_objet='.$id_prix); 
                if($p['id_declinaison']>0)$set['descriptif'].=' - '.supprimer_numero(sql_getfetsel('titre','spip_declinaisons','id_declinaison='.$p['id_declinaison']));
                }                
            else $p=sql_fetsel('prix_ht,id_prix_objet','spip_prix_objets','objet='.sql_quote('evenement') AND 'id_objet='.$id_evenement); 
            
            $prix_ht = $fonction_prix_ht('prix_objet', $p['id_prix_objet']);
            $prix = $fonction_prix('prix_objet',$p['id_prix_objet']);
            $taxe = round(($prix - $prix_ht) / $prix_ht, 3);
            $set['prix_ht']=$prix_ht; 
            $set['taxe']=$taxe;  
            $set['id_prix_objet']=$id_prix;                 
            }
         /*Sinon un prix attaché 'a l'évenement*/
        elseif(intval($evenement['prix'])){
            $fonction_prix = charger_fonction('prix', 'inc/');
            $fonction_prix_ht = charger_fonction('ht', 'inc/prix');  
            $prix_ht = $fonction_prix_ht('evenement', $id_evenement); 
            $prix = $fonction_prix('evenement',$id_evenement);
            $taxe = round(($prix - $prix_ht) / $prix_ht, 3);
            $set['prix_ht']=$prix_ht; 
            $set['taxe']=$taxe;                         
        }
    return $set;
}
