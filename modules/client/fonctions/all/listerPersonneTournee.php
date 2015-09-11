<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$retour = array();
	
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		$id = $_POST['idTournee'];

		$requete = new requete();
		$requete->select(array('personne' => array('id', 'nom', 'prenom', 'numPerTou')), 'p');
		$requete->where(array('p' => array('numTournee' => $id)));
		// echo $requete->requete_complete();
		$requete->executer_requete();
		$retour = $requete->resultat;
		unset($requete);
	}
	echo json_encode($retour);
}