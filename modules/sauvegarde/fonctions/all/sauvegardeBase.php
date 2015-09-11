<?php

if (empty($_SESSION)) { session_start(); }

$retour['erreur'] = 'Erreur lors de la sauvegarde de la base de données.';

if ($_SESSION) {
	if (file_exists('../../../../fonctions/fonctions.php')) {
		if (file_exists('../../../../fonctions/api.class.php')) {
			if (file_exists('../../../../fonctions/connectBDD.php')) {
				require_once('../../../../fonctions/fonctions.php');
				require_once('../../../../fonctions/api.class.php');
				require_once('../../../../fonctions/connectBDD.php');

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
	
				function addFolderToZip($dir, $zipArchive, $rep = '.'){
					if (is_dir($dir)) {
						if ($dh = opendir($dir)) {
							if ($rep != '.') {
								$zipArchive->addEmptyDir($rep);
							}
							while (($file = readdir($dh)) !== false) {
								// $temp = $rep $file:$dir.'/'.$file;
								$temp = ($rep != '.')?$rep.'/'.$file:$file;
								// echo $temp.'<br>';
								if (is_file($dir.'/'.$file)) {
									$zipArchive->addFile($dir.'/'.$file, $temp);
								} else {
									if (($file != ".") && ($file != "..")) {
										addFolderToZip($dir.'/'.$file, $zipArchive, $temp);
									}
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

				$zip = new ZipArchive;
				if($zip->open($repertoire.'.zip', ZIPARCHIVE::CREATE) === true) {
					addFolderToZip($repertoire, $zip);
					$zip->close();
					rmdirr($repertoire);
					unset($retour['erreur']);
					$retour['resultat'] = 'La base de données à bien été sauvegardée.';
				}
			}
		}
	}
}

echo json_encode($retour);