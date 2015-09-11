<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('fonctions/fonctions.php')) {
		if (file_exists('fonctions/api.class.php')) {
			if (file_exists('fonctions/connectBDD.php')) {
				require_once('fonctions/fonctions.php');
				require_once('fonctions/api.class.php');
				require_once('fonctions/connectBDD.php');
				
				$date_semaine_precedente=strtotime('-1 week 1 day');
				$alerte_sauvegarde=true;

				$repertoire = chemin_racine().'sav\base\\';
				if (is_dir($repertoire)) {
					if ($dh = opendir($repertoire)) {
						echo '<table><thead><tr><th>Fichier Base de données</th><th>Télécharger</th><th>Supprimer</th></tr></thead><tbody>';
						while (($file = readdir($dh)) !== false) {
							if (is_file($repertoire.$file)) {
								$timestamp_fichier=mktime(0, 0, 0, substr($file, 5, 2), substr($file, 8, 2), substr($file, 0, 4));
								if ( $timestamp_fichier > $date_semaine_precedente ) { $alerte_sauvegarde=false; }
								echo '<tr><th>'.$file.'</th><th><a href="sav/base/'.$file.'">Télécharger</a></th><th><a href="#" onclick="return supprimerSauvegarde(\''.urlencode ($repertoire.$file).'\');">Supprimer</a></th></tr>';
							}
						}
						echo '</tbody></table>';
					}
				}
				
				if ($alerte_sauvegarde) { echo '<p style="background-color:red;color:white;">La sauvegarde date de plus de 7 jours; Vous devriez faire une sauvegarde !</p>'; }
				
				$repertoire = chemin_racine().'sav/logiciel/';
				if (is_dir($repertoire)) {
					if ($dh = opendir($repertoire)) {
						echo '<table><thead><tr><th>Fichier Logiciel</th><th>Actions</th><th>Supprimer</th></tr></thead><tbody>';
						while (($file = readdir($dh)) !== false) {
							if (is_file($repertoire.'/'.$file)) {
								$timestamp_fichier=mktime(0, 0, 0, substr($file, 5, 2), substr($file, 8, 2), substr($file, 0, 4));
								if ( $timestamp_fichier > $date_semaine_precedente ) { $alerte_sauvegarde=false; }
								echo '<tr><th>'.$file.'</th><th><a href="sav/logiciel/'.$file.'">Télécharger</a></th><th><a href="#" onclick="return supprimerSauvegarde(\''.urlencode ($repertoire.$file).'\');">Supprimer</a></th></tr>';
							}
						}
						echo '</tbody></table>';
					}
				}
				
				if ($alerte_sauvegarde) { echo '<p style="background-color:red;color:white;">La sauvegarde date de plus de 7 jours; Vous devriez faire une sauvegarde !</p>'; }
				
				echo '<p><a href="?menu=sauvegarde" onclick="return nouvelleSauvegarde();">Créer une nouvelle sauvegarde</a></p>';
			
			}
		}
	}
}


// header("Location: index.php?menu=maj");