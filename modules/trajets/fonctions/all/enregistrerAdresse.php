<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		
		$adresse = (empty($_POST['adresse']))?false:$_POST['adresse'];
		$codePostal = (empty($_POST['codePostal']))?false:$_POST['codePostal'];
		$ville = (empty($_POST['ville']))?false:$_POST['ville'];
		$idPersonne = (empty($_POST['idPersonne']))?false:$_POST['idPersonne'];
		
		if ($adresse && $codePostal && $ville && $idPersonne) {
			$requete = new requete();
			$requete->update('personne', array('adresse' => $adresse, 'codePostal' => $codePostal, 'ville' => $ville));
			$requete->where(array('personne' =>array('id' => $idPersonne)));
			$requete->executer_requete();
			unset($requete);	
		}
	} else {
		$retour['resultat'] = 'erreur importation API';
	}
	echo $retour['resultat'];
}