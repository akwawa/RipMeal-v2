<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('fonctions/fonctions.php')) {
		if (file_exists('fonctions/api.class.php')) {
			if (file_exists('fonctions/connectBDD.php')) {
				require_once('fonctions/fonctions.php');
				require_once('fonctions/api.class.php');
				require_once('fonctions/connectBDD.php');
				
				$repertoire = chemin_racine().'requete/';
				if (is_dir($repertoire) && $dh = opendir($repertoire)) {
					while (($file = readdir($dh)) !== false) {
						if (is_file($repertoire.$file)) {
						echo sprintf('D:\Perso\xampp\mysql\bin\mysql.exe -h '.$PARAM_hote.' -u '.$PARAM_utilisateur.' --password='.$PARAM_mot_passe.' '.$PARAM_nom_bd.' < "'.$repertoire.$file.'"');
							passthru(sprintf('D:\Perso\xampp\mysql\bin\mysql.exe -h '.$PARAM_hote.' -u '.$PARAM_utilisateur.' --password='.$PARAM_mot_passe.' '.$PARAM_nom_bd.' < "'.$repertoire.$file.'"'));
							unlink($repertoire.$file);
						}
					}
				}
			}
		}
	}
}

header("Location: index.php?menu=maj");
