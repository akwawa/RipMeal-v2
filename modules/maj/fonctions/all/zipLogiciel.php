<?php

if (empty($_SESSION)) { session_start(); }

$retour['erreur'] = 'Erreur lors de la sauvegarde du logiciel.';

if ($_SESSION) {
	if (file_exists('../../../../fonctions/fonctions.php')) {
		require_once('../../../../fonctions/fonctions.php');

		function addFolderToZip($dir, $zipArchive, $rep = '.'){
			if (is_dir($dir) && $dh = opendir($dir)) {
				if ($rep != '.') {
					$zipArchive->addEmptyDir($rep);
				}
				while (($file = readdir($dh)) !== false) {
					$temp = ($rep != '.')?$rep.'/'.$file:$file;
					if (is_file($dir.'/'.$file)) {
						$zipArchive->addFile($dir.'/'.$file, $temp);
					} else {
						if (($file != ".") && ($file != "..") && ($file != "sav")) {
							addFolderToZip($dir.'/'.$file, $zipArchive, $temp);
						}
					}
				}
			}
		}
		
		$temps = date('Y-m-d H-i-s');
		$cheminArchive = chemin_racine().'sav/logiciel/';
		$repertoire = $cheminArchive.$temps;
		if (!file_exists($cheminArchive)){mkdir($cheminArchive, 0777, true);}
		$zip = new ZipArchive;
		if($zip->open($repertoire.'.zip', ZIPARCHIVE::CREATE)===true ) {
			addFolderToZip(chemin_racine(), $zip);
			$zip->close();
			unset($retour['erreur']);
			$retour['resultat'] = 'Le logiciel à bien été sauvegardé.';
		}
	}
}

echo json_encode($retour);