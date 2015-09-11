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
		$requete_personne->select(array('tournee' => array('nom' => 'tnom')), 't');
		$requete_personne->where(array('p' => array('id' => $id)));
		// echo $requete_personne->requete_complete().'<br>';
		$requete_personne->executer_requete();
		$details_personne = $requete_personne->resultat;
		unset($requete_personne);		
		
		if (!empty($details_personne)) {
			$retour['corps'] = '<p>Nom : '.$details_personne['nom'].'</p>';
			$retour['corps'] .= '<p>Prénom : '.$details_personne['prenom'].'</p>';
			$retour['corps'] .= '<p>Sexe : '.$details_personne['sexe'].'</p>';
			$retour['corps'] .= '<p>Adresse : '.$details_personne['adresse'].'</p>';
			$retour['corps'] .= '<p>Code postal : '.$details_personne['codePostal'].'</p>';
			$retour['corps'] .= '<p>Ville : '.$details_personne['ville'].'</p>';
			$retour['corps'] .= '<p>Téléphone : '.$details_personne['telephone'].'</p>';
			$retour['corps'] .= '<p>Téléphone secondaire : '.$details_personne['telephoneSecond'].'</p>';
			$retour['corps'] .= '<p>Nom tournée : '.$details_personne['tnom'].'</p>';
			$retour['corps'] .= '<p>Numéro dans la tournée : '.$details_personne['numPerTou'].'</p>';
			$retour['corps'] .= '<p>Pain : '.$details_personne['pain'].'</p>';
			$retour['corps'] .= '<p>Potage : '.$details_personne['potage'].'</p>';
			$retour['corps'] .= '<p>Date début livraison : '.$details_personne['dateActif'].'</p>';
			$retour['corps'] .= '<p>Actif : '.$details_personne['actif'].'</p>';
			$retour['corps'] .= '<p>Sac devant la porte : '.$details_personne['sacPorte'].'</p>';
			$retour['corps'] .= '<p>Informations : '.$details_personne['info'].'</p>';
		} else {
			$retour['corps'] = 'Aucun résultat.';
		}
	}
	echo json_encode($retour);
}