<?php

if (empty($_SESSION)) { session_start(); }

$retour['erreur'] = 'Erreur lors du changement de version.';

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		if (file_exists('../../../../fonctions/connectBDD.php')) {
			require_once('../../../../fonctions/api.class.php');
			require_once('../../../../fonctions/connectBDD.php');
			$version = (empty($_REQUEST['version']))?false:$_REQUEST['version'];

			if ($version) {
				$requete = new requete();
				$requete->update('param', array('texte' => $version));
				$requete->where(array('param' => array('intitule' => 'version')));
				$requete->executer_requete();
				unset($retour['erreur']);
				$retour['resultat'] = 'nouvelle version : v. '.$version;
			}
		}
	}
}
echo json_encode($retour);