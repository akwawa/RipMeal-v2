<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$retour = array('corps');
	
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		$id = (empty($_POST['id']))?1:$_POST['id'];

		$requete_personne = new requete();
		$requete_personne->grand_tableau = false;
		$requete_personne->select('personne', 'p');
		$requete_personne->where(array('p' => array('id' => $id)));
		$requete_personne->executer_requete();
		$details_personne = $requete_personne->resultat;
		unset($requete_personne);		
		
		if (!empty($details_personne)) {
			$retour['corps'] = '<form method="post" onsubmit="validerFormAdresse(this); return false;"><p><label for="adresse">Adresse : <input type="text" name="adresse" id="adresse" value="'.$details_personne['adresse'].'" size="50" /></label></p><p><label for="complementAdresse">Complément de l\'adresse : <input type="text" name="complementAdresse" id="complementAdresse" value="'.$details_personne['complementAdresse'].'" /></label></p><p><label for="codePostal">Code postal : <input type="text" name="codePostal" id="codePostal" value="'.$details_personne['codePostal'].'" /></label></p><p><label for="ville">Ville : <input type="text" name="ville" id="ville" value="'.$details_personne['ville'].'" /></label></p><p><input type="hidden" name="idPersonne" id="idPersonne" value="'.$id.'" /><input type="submit" id="valider" name="valider" value="valider l\'adresse" /><input type="submit" id="enregistrer" name="enregistrer" value="Enregistrer l\'adresse" disabled="disabled" /></p></form>';
			// $retour['corps'] = '<p>Nom : '.$details_personne['nom'].'</p>';
			// $retour['corps'] .= '<p>Prénom : '.$details_personne['prenom'].'</p>';
			// $retour['corps'] .= '<p>Sexe : '.$details_personne['sexe'].'</p>';
			// $retour['corps'] .= '<p>Adresse : '.$details_personne['adresse'].'</p>';
			// $retour['corps'] .= '<p>Code postal : '.$details_personne['codePostal'].'</p>';
			// $retour['corps'] .= '<p>Ville : '.$details_personne['ville'].'</p>';
			// $retour['corps'] .= '<p>Téléphone : '.$details_personne['telephone'].'</p>';
			// $retour['corps'] .= '<p>Téléphone secondaire : '.$details_personne['telephoneSecond'].'</p>';
			// $retour['corps'] .= '<p>Nom tournée : '.$details_personne['tnom'].'</p>';
			// $retour['corps'] .= '<p>Numéro dans la tournée : '.$details_personne['numPerTou'].'</p>';
			// $retour['corps'] .= '<p>Pain : '.$details_personne['pain'].'</p>';
			// $retour['corps'] .= '<p>Potage : '.$details_personne['potage'].'</p>';
			// $retour['corps'] .= '<p>Date début livraison : '.$details_personne['dateActif'].'</p>';
			// $retour['corps'] .= '<p>Actif : '.$details_personne['actif'].'</p>';
			// $retour['corps'] .= '<p>Sac devant la porte : '.$details_personne['sacPorte'].'</p>';
			// $retour['corps'] .= '<p>Informations : '.$details_personne['info'].'</p>';
		} else {
			$retour['corps'] = 'Aucun résultat.';
		}
	}
	echo json_encode($retour);
}