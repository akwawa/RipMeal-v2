<?php

if (empty($_SESSION)) { session_start(); }

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

function rmdirr($dir) {
	if($objs = glob($dir."/*")){
		foreach($objs as $obj) {
			is_dir($obj)?rmdirr($obj):unlink($obj);
		}
	}
	rmdir($dir);
}


if ($_SESSION) {
	if (file_exists('fonctions/fonctions.php')) {
		if (file_exists('fonctions/api.class.php')) {
			if (file_exists('fonctions/connectBDD.php')) {
				require_once('fonctions/fonctions.php');
				require_once('fonctions/api.class.php');
				require_once('fonctions/connectBDD.php');
			
				$date_semaine_precedente=strtotime('-1 week 1 day');
				$alerte_sauvegarde=true;

				$repertoire = chemin_racine().'sav/base/';
				if (is_dir($repertoire) && $dh = opendir($repertoire)) {
					while (($file = readdir($dh)) !== false) {
						if (is_file($repertoire.$file)) {
							$timestamp_fichier=mktime(0, 0, 0, substr($file, 5, 2), substr($file, 8, 2), substr($file, 0, 4));
							if ( $timestamp_fichier > $date_semaine_precedente ) { $alerte_sauvegarde=false; }
						}
					}
				}
				
				if ($alerte_sauvegarde) {
					$requete = new requete();
					$requete->requete_direct('SHOW TABLES');
					// echo $requete->requete_complete();
					$requete->executer_requete();
					$liste = $requete->resultat;
					unset($requete);
					
					$nbTables = count($liste);
					$temps = date('Y-m-d_H-i-s');
					// echo chemin_racine();
					$repertoire = chemin_racine().'sav/base/'.$temps;
					if (!file_exists($repertoire)){mkdir($repertoire, 0777, true);}
					for ($i=0;$i<$nbTables;$i++) {
						$table = $liste[$i]["Tables_in_".$PARAM_nom_bd];
						passthru(sprintf('c:/xampp/mysql/bin/mysqldump.exe --opt -h '.$PARAM_hote.' -u '.$PARAM_utilisateur.' --password='.$PARAM_mot_passe.' '.$PARAM_nom_bd.' '.$table.' > "'.$repertoire.'/'.$temps.'_'.$table.'.sql"'));
					}



					$zip = new ZipArchive;
					if($zip->open($repertoire.'.zip', ZIPARCHIVE::CREATE) === true) {
						addFolderToZip($repertoire, $zip);
						$zip->close();
						rmdirr($repertoire);
					}
				}
				
				$repertoire = chemin_racine().'sav/logiciel/';
				if (is_dir($repertoire)) {
					if ($dh = opendir($repertoire)) {
						while (($file = readdir($dh)) !== false) {
							if (is_file($repertoire.$file)) {
								$timestamp_fichier=mktime(0, 0, 0, substr($file, 5, 2), substr($file, 8, 2), substr($file, 0, 4));
								if ( $timestamp_fichier > $date_semaine_precedente ) { $alerte_sauvegarde=false; }
							}
						}
					}
				}
				
				if ($alerte_sauvegarde) {					
					$temps = date('Y-m-d H-i-s');
					$cheminArchive = chemin_racine().'sav/logiciel/';
					$repertoire = $cheminArchive.$temps;
					if (!file_exists($cheminArchive)){mkdir($cheminArchive, 0777, true);}
					$zip = new ZipArchive;
					if($zip->open($repertoire.'.zip', ZIPARCHIVE::CREATE)===true ) {
						addFolderToZip(chemin_racine(), $zip);
						$zip->close();
					}
				}
			
			}
		}
	}
}

header("Location: index.php?menu=maj&sousmenu=automatique");
