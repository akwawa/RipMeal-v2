<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		
		$adresse = (empty($_POST['adresse']))?false:$_POST['adresse'];
		$codePostal = (empty($_POST['codePostal']))?false:$_POST['codePostal'];
		$ville = (empty($_POST['ville']))?false:$_POST['ville'];
		
		if ($adresse && $codePostal && $ville) {
			$requete = new requete();
			$requete->select('coordonnees', 'c');
			$requete->where(array('c' => array('adresse' => $adresse, 'codePostal' => $codePostal, 'ville' => $ville)));
			$requete->executer_requete();
			$liste = $requete->resultat;
		
			if (count($liste) == 0) {
				$retour['resultat'] = false;
			} else {
				$retour['resultat'] = $liste;
			}
		}
	} else {
		$retour['resultat'] = 'erreur importation API';
	}
	echo json_encode($retour['resultat']);
}