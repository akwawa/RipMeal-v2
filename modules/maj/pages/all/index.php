<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	function lire_fichier($urlFichier) {
		$retour = false;
		$file = @fopen($urlFichier, "r");
		if ($file) {
			$textjson = '';
			while (!feof($file)) { $textjson .= fgets ($file, 1024); }
			fclose($file);
			$retour = json_decode($textjson, true);
		}
		return $retour;
	}

	function verif_maj($urlBase, $fichierVersion, $nomApplication = false, $resultat = false) {
		$retour = false;
		if ($fichier = lire_fichier($urlBase.$fichierVersion)) {
			foreach ($fichier as $fileVersion) {
				if ($fileVersion["nomApplication"] == $nomApplication) {
					if (floatval($resultat['version']) < floatval($fileVersion['version'])) {
						// if (floatval($resultat['version']) < floatval($fileVersion['minVersion'])) {
							// $ancienneVersion = verif_maj($urlBase, $fileVersion['minVersion']);
						// } else {
							$ancienneVersion = true;
						// }
						if ($ancienneVersion) {
							$retour[] =  array('version' => $fileVersion['version'], 'fichier' => 'fichierMaj_v'.$fileVersion['version'].'.zip', 'minVersion' => $fileVersion['minVersion'], 'fichierMaj' => $fileVersion['fichierMaj']);
							if (gettype($ancienneVersion) == 'array') { $retour[] = $ancienneVersion; }
						}
					}
				}
			}
		}
		return $retour;
	}

	if (file_exists('fonctions/connectBDD.php')) {
		include_once('fonctions/connectBDD.php');
		$racineSite = $nomApplication.'.'.$siteDistant;
		// $lien = $racineSite.'/'.$fichierVersion; // http://repas.perette.info/last_version.json
		if ($sock = @fsockopen($racineSite, 80, $num, $error, 5)) {
			$requete = new requete();
			$requete->where(array('param' => array('intitule' => 'version')));
			// echo $requete->requete_complete().'<br>';
			$requete->grand_tableau = false;
			$requete->executer_requete();
			$temp = $requete->resultat;
			if ($temp) {
				$resultat[$temp['intitule']] = $temp['texte'];
				if ($retour = verif_maj('http://'.$racineSite.'/', $fichierVersion, $nomApplication, $resultat)) {
					echo '<p>Version actuelle : V '.$resultat['version'].' </p><div>Version disponible : <ul>';
					foreach ($retour as $nouvelleVersion) {
						if ($nouvelleVersion['minVersion'] <= $resultat['version']) {
							echo '<li style="color:green;">V '.$nouvelleVersion['version'].' (version requise : V '.$nouvelleVersion['minVersion'].') <input type="button" value="Installer" data-urlFichier="'.$racineSite.'/'.$nouvelleVersion['fichierMaj'].'" data-version="'.$nouvelleVersion['version'].'" data-fichier="'.$nouvelleVersion['fichier'].'" onclick="miseAJour(this);" /></li>';
						} else {
							echo '<li style="color:red;">V '.$nouvelleVersion['version'].' (version requise : V '.$nouvelleVersion['minVersion'].')</li>';
						}
					}
					echo '</ul></div>';
				} else {
					echo 'Aucune nouvelle version. '.$racineSite;
				}
			} else {
				echo 'Erreur : impossible de trouver la version installée';
			}
		} else {
			echo 'Erreur : impossible de se connecter au site distant';
		}
	} else {
		echo 'Erreur : paramètres de connexion éronné';
	}
}