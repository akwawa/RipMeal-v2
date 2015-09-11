<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		
		$adresse = (empty($_POST['adresse']))?false:$_POST['adresse'];
		$codePostal = (empty($_POST['codePostal']))?false:$_POST['codePostal'];
		$ville = (empty($_POST['ville']))?false:$_POST['ville'];
		$lat = (empty($_POST['lat']))?false:$_POST['lat'];
		$lng = (empty($_POST['lng']))?false:$_POST['lng'];
		$formatted_address = (empty($_POST['formatted_address']))?false:$_POST['formatted_address'];
		
		if ($adresse && $codePostal && $ville && $lat && $lng && $formatted_address) {
			$requete = new requete();
			$requete->select('coordonnees', 'c');
			$requete->where(array('c' => array('adresse' => $adresse, 'codePostal' => $codePostal, 'ville' => $ville)));
			$requete->executer_requete();
			$liste = $requete->resultat;
		
			if (count($liste) == 0) {
				$requete = new requete();
				$requete->insert('coordonnees', array('adresse' => $adresse, 'codePostal' => $codePostal, 'ville' => $ville, 'lat' => $lat, 'lng' => $lng, 'formatted_address' => $formatted_address));
				// echo $requete->requete_complete();
				$requete->executer_requete();
				unset($requete);
			}
		}
	} else {
		$retour['resultat'] = 'erreur importation API';
	}
	echo $retour['resultat'];
}