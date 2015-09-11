<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		
		$origin_addresses = (empty($_POST['origin_addresses']))?false:$_POST['origin_addresses'];
		$destination_addresses = (empty($_POST['destination_addresses']))?false:$_POST['destination_addresses'];
		// SELECT t.distance AS "t.distance", t.duration AS "t.duration" FROM v2__trajets t WHERE t.origin_addresses = "26 Rue Abbé Devaux, 54140 Jarville-la-Malgrange, France" AND t.destination_addresses = "15 Allée de l'Aire, 54520 Laxou, France"
		if ($origin_addresses && $destination_addresses) {
			$requete = new requete();
			$requete->select(array('trajets' => array('distance', 'duration')), 't');
			$requete->where(array('t' => array('origin_addresses' => $origin_addresses, 'destination_addresses' => $destination_addresses)));
			$requete->executer_requete();
			$retour['resultat'] = $requete->resultat;
			// $retour['resultat'] = $requete->requete_complete();
			unset($requete);
		} else {
			$retour['resultat'] = 'erreur passage adresse';
		}
	} else {
		$retour['resultat'] = 'erreur importation API';
	}
	echo json_encode($retour['resultat']);
	// echo $retour['resultat'];
}