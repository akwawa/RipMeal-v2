<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		
		$origin_addresses = (empty($_POST['origin_addresses']))?false:$_POST['origin_addresses'];
		$destination_addresses = (empty($_POST['destination_addresses']))?false:$_POST['destination_addresses'];
		$distance = (empty($_POST['distance']))?false:$_POST['distance'];
		$duration = (empty($_POST['duration']))?false:$_POST['duration'];
		
		if ($origin_addresses && $destination_addresses && $distance && $duration) {
			$requete = new requete();
			$requete->insert('trajets', array('origin_addresses' => $origin_addresses, 'destination_addresses' => $destination_addresses, 'distance' => $distance, 'duration' => $duration));
			// echo $requete->requete_complete();
			$requete->executer_requete();
			unset($requete);
		}
	} else {
		$retour['resultat'] = 'erreur importation API';
	}
	echo $retour['resultat'];
}