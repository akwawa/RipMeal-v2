<?php

if (empty($_SESSION)) { session_start(); }

$retour['erreur'] = 'Erreur lors de la suppression de la sauvegarde.';

if ($_SESSION) {
	$fichier=urldecode($_REQUEST['fichier']);

	if (is_file($fichier)) {
		unlink($fichier);
		unset($retour['erreur']);
		$retour['resultat'] = 'La sauvegarde a été supprimée.';
	} else {
		$retour['erreur'] = 'Le fichier est introuvable: '.$fichier;
	}
}

echo json_encode($retour);