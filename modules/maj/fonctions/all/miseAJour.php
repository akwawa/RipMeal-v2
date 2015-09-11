<?php

if (empty($_SESSION)) { session_start(); }

$retour['erreur'] = 'Erreur lors de l\'extraction de l\'archive.';

if ($_SESSION) {
	if (file_exists('../../../../fonctions/fonctions.php')) {
		require_once('../../../../fonctions/fonctions.php');
		
		$archive = empty($_REQUEST['archive'])?false:$_REQUEST['archive'];
		$cheminRacine = chemin_racine();
		$cheminArchive = $cheminRacine.'maj/'.$archive;
		$cheminExtraction = $cheminRacine.'tmpMaj/';
		
		function delTree($dir) {
			if (is_dir($dir)) {
				$files = array_diff(scandir($dir), array('.','..'));
				foreach ($files as $file) {
					(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
				}
				return rmdir($dir);
			}
		}
		
		function transfertFile($fileNext, $fileNow) {
			if (file_exists($fileNow)) {unlink($fileNow);}
			copy($fileNext, $fileNow);
		}
		
		function compare($dir, $racine) {
			$retour = 'terminée avec succès.<br>';
			$files = array_diff(scandir($dir), array('.','..'));
			foreach ($files as $file) {
				if (is_dir($dir.$file)) {
					if (file_exists($racine.$file)) {
						compare($dir.$file.'/', $racine.$file.'/');
					} else {
						mkdir($racine.$file);
					}
				} else {
					if (file_exists($racine.$file)) {
						$shaNow = sha1_file($racine.$file);
						$shaNext = sha1_file($dir.$file);
						if ($shaNow != $shaNext) {
							if (filemtime($dir.$file) >= filemtime($racine.$file)) {
								$retour .= $racine.$file.' Le fichier actuel a une date de modification plus récente<br>';
							} else {
								transfertFile($dir.'/'.$file, $racine.$file);
							}
						}
					} else {
						transfertFile($dir.'/'.$file, $racine.$file);
					}
				}
			}
			return $retour;
		}
		
		$zip = new ZipArchive;
		if ($zip->open($cheminArchive) === TRUE) {
			delTree($cheminExtraction);
			$zip->extractTo($cheminExtraction);
			$retour['resultat'] = compare($cheminExtraction, $cheminRacine);
			$zip->close();
			unset($retour['erreur']);
		} else {
			$retour['erreur'] = $archive.' : Archive de mise à jour non trouvée';
		}
	}
}

echo json_encode($retour);