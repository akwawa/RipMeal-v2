<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	/* GESTION DES DATES */
	$dateCalendrier = (empty($_POST['dateCalendrier']))?false:$_POST['dateCalendrier'];
	if ($dateCalendrier) {
		list($dateAnnee, $dateMois, $dateJour) = explode('-', $dateCalendrier);
		$nbJour = date("t", mktime(0, 0, 0, $dateMois, 1, $dateAnnee));
	} else {
		$dateAnnee = date("Y");
		$dateMois = date("m");
		$dateJour = date("d");
		$nbJour = date("t", mktime(0, 0, 0, $dateMois, 1, $dateAnnee));
		if ($dateJour == $nbJour) { $dateJour = 1; if ($dateMois == 12) { $dateMois = 1; $dateAnnee++; } else { $dateMois++; } } else { $dateJour++; }
	}
	$temps = mktime(0, 0, 0, $dateMois, $dateJour, $dateAnnee);
	/*********************/

	$retour['resultat'] = '<p>Choisissez le type d\'affichage :</p><ul><li><a href="?menu=menu&sousmenu=cal_sem_reg">Un tableau par semaine et par régime</a></li><li><a href="?menu=menu&sousmenu=cal_sem">Un tableau regroupant tous les régimes de la semaine</a></li><li><a href="?menu=menu&amp;sousmenu=uploadSemaine">Importer les menus d\'un tableau</a></li><li><a href="?menu=menu&amp;sousmenu=loadSemaine">Charger depuis un fichier CSV</a></li></ul>';

	echo $retour['resultat'];
}