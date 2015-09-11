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

	$requete_tournees = new requete();
	$requete_tournees->select('tournee', 't');
	$requete_tournees->where(array('t' => array('nom' => array('OPERATEUR' => '<>', 'VALEUR' => 'PAS DE TOURNEE'))));
	$requete_tournees->executer_requete();
	$liste_tournees = $requete_tournees->resultat;
	$erreur = array_merge($erreur, $requete_tournees->liste_erreurs);
	unset($requete_tournees);

	if ($liste_tournees) {
		$valeur = array(0 => "regime", 1 => "boisson", 2 => "remplacement", 3 => "supplement");
		$repasJournee = array(0 => "MIDI", 1 => "SOIR");

		$retour['resultat'] = '<form action="?menu=impression&amp;sousmenu=fichesRepas_visu" method="post" id="impression" onsubmit="fichesRepas_visu(this); return false;"><p>Choix des tournées : ';
		foreach ($liste_tournees as $tournee) {
			$retour['resultat'] .= '<label><input type="checkbox" name="tournee[]" value="'.$tournee['id'].'" checked>'.$tournee['nom'].'</label>';
		}
		$retour['resultat'] .= '</p><p><label><input type="checkbox" name="recapitulatifChef" value="recapitulatifChef" checked>Récapitulatif chef</label></p><p><label><input type="checkbox" name="livraison" value="livraison" checked>Livraison</label></p><p><label><input type="checkbox" name="preparation" value="preparation" checked>Préparation</label></p><p><label><input type="checkbox" name="eauEtPain" value="eauEtPain" checked>Eau et Pain</label></p><p><input type="submit" name="imprimer" value="Imprimer"></p></form>';
	} else {
		$retour['resultat'] = '<h2>Aucune tournée n\'est déclarée.</h2>';
	}

	echo $retour['resultat'];
}