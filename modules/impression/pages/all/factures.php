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
	$dateTimestamp = mktime(0, 0, 0, $dateMois, $dateJour, $dateAnnee);
	$typeCalendrier = array('MIDI', 'SOIR');
	/*********************/
	$liste_personnes = array();
	foreach ($typeCalendrier as $type) {
		$requete_personnes = new requete();
		$requete_personnes->select(array('personne' => array('id', 'nom', 'prenom')), 'p');
		$requete_personnes->select(array('SUM' => array('per_reg' => 'quantite')), 'pr');
		$requete_personnes->select(array('SUM' => array('per_reg' => 'quantiteRemp')), 'pr');
		$requete_personnes->select(array('per_reg' => 'idRegime'), 'pr');
		$requete_personnes->where(array('calendrier' => array('annee' => $dateAnnee, 'mois' => $dateMois, 'typeCalendrier' => $type)));
		$requete_personnes->order(array('p' => 'nom'));
		$requete_personnes->group('p', 'id');
		// echo $requete_personnes->requete_complete().'<br>';
		$requete_personnes->executer_requete();
		$resultat = $requete_personnes->resultat;
		foreach ($resultat as $temp) {
			$liste_personnes[$temp['p.nom']][$type] = $temp['SUM(pr.quantite)']+$temp['SUM(pr.quantiteRemp)'];
		}
	}
	if ($liste_personnes) {
		echo '<table><thead><tr><th>Nom</th><th>Nombre de repas MIDI</th><th>Nombre de repas SOIR</th></tr></thead><tbody>';
		foreach ($liste_personnes as $a => $b) {
			echo '<tr><td>'.$a.'</td><td>'.(isset($b['MIDI'])?$b['MIDI']:0).'</td><td>'.(isset($b['SOIR'])?$b['SOIR']:0).'</td></tr>';
		}
		echo '</tbody></table>';
	} else {
		echo '<p class="erreur">Il n\'y a aucun repas à facturer pour le mois de '.strftime("%B %Y", $dateTimestamp).'.</p>';
	}
}