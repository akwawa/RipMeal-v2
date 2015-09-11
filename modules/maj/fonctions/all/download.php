<?php

if (empty($_SESSION)) { session_start(); }

$retour['erreur'] = 'Erreur lors du téléchargement de la mise à jour ';

if ($_SESSION) {
	if (file_exists('../../../../fonctions/fonctions.php')) {
		require_once('../../../../fonctions/fonctions.php');
		$cheminRacine = chemin_racine();
		$version = empty($_REQUEST['version'])?false:$_REQUEST['version'];
		$urlFichier = empty($_REQUEST['urlFichier'])?false:'http://'.$_REQUEST['urlFichier'];
		$retour['erreur'] .= $urlFichier;

		$fileNow = $cheminRacine.'maj/fichierMaj_v'.$version.'.zip';
		$fileNext = $cheminRacine.'maj/temp_fichierMaj_v'.$version.'.zip';
		if ($version && $urlFichier) {
			if (file_exists($fileNow)) {
				file_put_contents($fileNext, file_get_contents($urlFichier));
				$shaNow = sha1_file($fileNow);
				$shaNext = sha1_file($fileNext);
				if ($shaNow != $shaNext) {
					if (filemtime($fileNow) >= filemtime($fileNext)) {
						$retour .= $fileNow.' L\'archive actuelle a une date de modification plus récente<br>';
					} else {
						unlink($fileNow);
						rename($fileNext, $fileNow);
					}
				} else {
					unlink($fileNext);
				}
			} else {
				if (!file_exists(dirname($fileNow))) { mkdir(dirname($fileNow)); }
				file_put_contents($fileNow, file_get_contents($urlFichier));
			}
			unset($retour['erreur']);
			$retour['resultat'] = ' archive téléchargée.';
		}
	}
}

echo json_encode($retour);