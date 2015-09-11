<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$retour = array('corps');
	
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		$id = $_POST['id'];

		$requete_personne = new requete();
		$requete_personne->grand_tableau = false;
		$requete_personne->select('ressource', 'r');
		$requete_personne->where(array('r' => array('id' => $id)));
		// $retour['corps'] = $requete_personne->requete_complete();
		$requete_personne->executer_requete();
		$details_personne = $requete_personne->resultat;
		unset($requete_personne);		
		
		$retour['corps'] = '<p>Nom : '.$details_personne['nom'].'</p>';
		$retour['corps'] .= '<p>Prénom : '.$details_personne['prenom'].'</p>';
		$retour['corps'] .= '<p>Sexe : '.$details_personne['sexe'].'</p>';
		$retour['corps'] .= '<p>Adresse : '.$details_personne['adresse'].'</p>';
		$retour['corps'] .= '<p>Code postal : '.$details_personne['codePostal'].'</p>';
		$retour['corps'] .= '<p>Ville : '.$details_personne['ville'].'</p>';
		$retour['corps'] .= '<p>Téléphone : '.$details_personne['telephone'].'</p>';
		$retour['corps'] .= '<p>Téléphone secondaire : '.$details_personne['telephoneSecond'].'</p>';
	}
	echo json_encode($retour);
}