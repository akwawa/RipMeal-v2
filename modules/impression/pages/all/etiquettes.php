<?php

if (empty($_SESSION)) session_start();

if ($_SESSION) {
	/* GESTION DES DATES */
	$dateCalendrier = (empty($_REQUEST['dateCalendrier']))?false:$_REQUEST['dateCalendrier'];
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
	/*********************/


	$retour['resultat'] = '<form action="?menu=impression&amp;sousmenu=etiquettes_visu" method="post" id="impression" onsubmit="etiquettes_visu(this); return false;"><p><input type="submit" name="imprimer" value="Imprimer les étiquettes"></p></form>';

	echo $retour['resultat'];
}