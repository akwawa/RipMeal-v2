<?php

function week_dates($week, $year) {
	$week_dates = array();
	$first_day = mktime(12, 0, 0, 1, 1, $year);
	$first_week = date("W", $first_day);
	if ($first_week > 1) {$first_day = strtotime("+1 week",$first_day);}
	$timestamp = strtotime("+$week week",$first_day);

	$what_day = date("w",$timestamp);
	if ($what_day==0) {
		$timestamp = strtotime("-6 days",$timestamp);
	} elseif ($what_day > 1) {
		$what_day--;
		$timestamp = strtotime("-$what_day days",$timestamp);
	}
	// $week_dates[2] = date("Y-m-d",strtotime("+1 day",$timestamp));
	return($timestamp);
}

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	{ /* GESTION DES DATES */
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
	$temps = mktime(0, 0, 0, $dateMois, $dateJour, $dateAnnee);
	/*********************/ }

	if (file_exists('../../../../fonctions/api.class.php')) {include('../../../../fonctions/api.class.php');}

	$tempRegime = array();
	$tabMenu = array();
	$tabRegime = array();
	$numSemaine = strftime("%U", $temps);
	// $numSemaine = date("W", $temps);
	$menuSeul = isset($_REQUEST['menuSeul'])?true:false;
	$nbTotal= array();
	
	$requete = new requete();
	$requete->select('regime', 'r');
	$requete->where(array('r' => array('nom' => 'REMPLACEMENT')));
	$requete->executer_requete();
	$liste = $requete->resultat;
	$tempRegime[$liste[0]['id']] = 0;
	
	foreach ($_GET as $reg => $val) {
		if (substr($reg, 0, 4) == 'reg_' && $val > 0) {
			$tempRegime[substr($reg, 4)] = $val;
		}
	}
	$temp = week_dates($numSemaine, $dateAnnee);

	foreach ($tempRegime as $reg => $val) {
		$requete = new requete();
		$requete->select('regime', 'r');
		$requete->where(array('r' => array('id' => $reg)));
		// echo $requete->requete_complete().'<br><br>';
		$requete->executer_requete();
		$liste = $requete->resultat;
		
		$typeRepas = array('MIDI', 'SOIR');
		$retour['resultat'] = '';
		
		foreach ($liste as $regime) {
			$lundiSemaine = $temp;
			$tabRegime[$regime['nom']] = array('id' => $regime['id'], 'nom' => $regime['nom'], 'nomComplet' => $regime['nomComplet']);
			foreach ($typeRepas as $type) {
				for ($i=0; $i<7; $i++) {
					$timestampJour = $lundiSemaine+($i*86400);
					$requete = new requete();
					$requete->select(array('calendrier' => 'id'), 'c');
					$requete->where(array('c' => array('jour' => date('d', $timestampJour), 'mois' => date('m', $timestampJour), 'annee' => date('Y', $timestampJour), 'typeCalendrier' => $type)));
					// echo $requete->requete_complete().'<br><br>';
					$requete->grand_tableau = false;
					$requete->executer_requete();
					$result = $requete->resultat;
					$requete->reset();
					if ($result) {
						$requete->alias = true;
						$requete->select('menu_regime', 'mr');
						$requete->select('menu', 'm');
						$requete->select(array('menu_entree' => 'nom'), 'me');
						$requete->select(array('menu_viande' => 'nom'), 'mv');
						$requete->select(array('menu_legume' => 'nom'), 'ml');
						$requete->select(array('menu_fromage' => 'nom'), 'mf');
						$requete->select(array('menu_dessert' => 'nom'), 'md');
						$requete->where(array('mr' => array('idCalendrier' => $result['c.id'],'idRegime' => $regime['id'])));
						// echo $requete->requete_complete().'<br><br>';
						$requete->grand_tableau = false;
						$requete->executer_requete();
						// var_dump($requete->resultat);
						$tabRegime[$regime['nom']][$timestampJour][$type] = $requete->resultat;
						$requete->reset();
					}
				}
			}
		}
	}

	$typeCal = array('MIDI', 'SOIR');
	$jour = array('LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE');
	// var_dump($tabRegime);
	foreach ($tabRegime as $regime => $liste) {
		$lundiSemaine = $temp;
		$menu = '<div class="logo"><img src="../../img/logo.png" /></div><p style="text-align:center;">Téléphone : 03.83.19.37.81</p><table class="menu"><caption>Menus '.$liste['nom'].' - SEMAINE n°'.$numSemaine++.' du '.date('d/m/Y', $lundiSemaine).' au '.date('d/m/Y', strtotime("+6 day",$lundiSemaine)).'</caption><thead><tr><th>&nbsp;</th><th>MIDI</th><th>Remplacement MIDI</th><th>Soir</th><th>Remplacement SOIR</th></tr></thead><tbody>';
		for ($i=0;$i<7;$i++) {
			$timestampJour = $lundiSemaine+($i*86400);
			$menuDefault = array('me.nom' => '', 'mv.nom' => '', 'ml.nom' => '', 'md.nom' => '', 'mf.nom' => '');
			$menuMIDI = isset($liste[$timestampJour]['MIDI'])?$liste[$timestampJour]['MIDI']:$menuDefault;
			$menuMIDIremp = isset($tabRegime['REMPLACEMENT'][$timestampJour]['MIDI'])?$tabRegime['REMPLACEMENT'][$timestampJour]['MIDI']:$menuDefault;
			$menuSOIR = isset($liste[$timestampJour]['SOIR'])?$liste[$timestampJour]['SOIR']:$menuDefault;
			$menuSOIRremp = isset($tabRegime['REMPLACEMENT'][$timestampJour]['SOIR'])?$tabRegime['REMPLACEMENT'][$timestampJour]['SOIR']:$menuDefault;
			$menu .= '<tr><th>'.$jour[$i].'</th><td>';
			$menu .= $menuMIDI['me.nom'].'<br>'.$menuMIDI['mv.nom'].'<br>'.$menuMIDI['ml.nom'].'<br>'.$menuMIDI['mf.nom'].'<br>'.$menuMIDI['md.nom'];
			$menu .= '</td><td>';
			$menu .= $menuMIDIremp['me.nom'].'<br>'.$menuMIDIremp['mv.nom'].'<br>'.$menuMIDIremp['ml.nom'].'<br>'.$menuMIDIremp['mf.nom'].'<br>'.$menuMIDIremp['md.nom'];
			$menu .= '</td><td>';
			$menu .= $menuSOIR['me.nom'].'<br>'.$menuSOIR['mv.nom'].'<br>'.$menuSOIR['ml.nom'].'<br>'.$menuSOIR['mf.nom'].'<br>'.$menuSOIR['md.nom'];
			$menu .= '</td><td>';
			$menu .= $menuSOIRremp['me.nom'].'<br>'.$menuSOIRremp['mv.nom'].'<br>'.$menuSOIRremp['ml.nom'].'<br>'.$menuSOIRremp['mf.nom'].'<br>'.$menuSOIRremp['md.nom'];
			$menu .= '</td>';
			$menu .= '</tr>';
		}
		$menu .= '</tbody></table><div class="page">&nbsp;</div>';
		$tabMenu[$liste['id']] = $menu;
	}
	
	/******/
	$requete = new requete();
	$requete->requete_direct('SELECT t.id as "t.id", t.nom as "t.nom", r.id as "r.id", r.nom as "r.nom", r.nomComplet as "r.nomComplet", count(idPersonne) as "quantite" FROM (SELECT DISTINCT idPersonne, idRegime FROM v2__tper_reg) t INNER JOIN v2__regime r ON t.idRegime = r.id INNER JOIN v2__personne p ON t.idPersonne = p.id INNER JOIN v2__tournee t ON p.numTournee = t.id WHERE t.nom <> "PAS DE TOURNEE" AND p.actif = true GROUP BY numTournee, idRegime');
	$requete->executer_requete();
	$liste = $requete->resultat;
	$tournee = '';
	for ($i=0;$i<count($liste);$i++) {
		// if (isset($tempRegime[$liste[$i]['r.id']]) && $liste[$i]['quantite'] != $tempRegime[$liste[$i]['r.id']]){
		if (isset($tempRegime[$liste[$i]['r.id']]) && $menuSeul){
			$liste[$i]['quantite'] = $tempRegime[$liste[$i]['r.id']];
		}
		if ($tournee != $liste[$i]['t.id']) {
			$tournee = $liste[$i]['t.id'];
			$requete->requete_direct('SELECT DISTINCT CONCAT(p.nom," ",p.prenom) as "p.nom", r.nom as "r.nom" FROM v2__personne p INNER JOIN v2__tper_reg tr ON p.id = tr.idPersonne INNER JOIN v2__regime r ON tr.idRegime = r.id WHERE p.actif = true AND p.numTournee = '.$tournee.' ORDER BY p.numTournee, p.numPerTou');
			$requete->order(array('p' => array('numTournee', 'numPerTou')));
			$requete->executer_requete();
			$listeRegime = $requete->resultat;
			$nbListeRegime = count($listeRegime);
			$tabTemp = array();
			for ($k=0;$k<$nbListeRegime;$k++) {
				$per = $listeRegime[$k]['p.nom'];
				$reg = $listeRegime[$k]['r.nom'];
				if (isset($tabTemp[$per])) {
					$tabTemp[$per] .= ', '.$reg;
				} else {
					$tabTemp[$per] = $reg;
				}
			}
			if (!$menuSeul) {
				$retour['resultat'] .= '<h1>'.$liste[$i]['t.nom'].'</h1><table><thead><tr><th>Nom de la personne</th><th>Régime</th></tr><thead><tbody>';
				foreach ($tabTemp as $per => $reg) {
					$retour['resultat'] .= '<tr><td>'.$per.'</td><td>'.$reg.'</td></tr>';
				}
				$retour['resultat'] .= '</tbody></table><div class="page">&nbsp;</div>';
			}
		}
		$reg = $liste[$i]['r.id'];
		$nb = $liste[$i]['quantite'];
		for ($j=0;$j<$nb;$j++) {
			if ($menuSeul) {
				if (empty($nbTotal[$reg])) {$nbTotal[$reg] = 0;}
				if ($nbTotal[$reg] < $nb && isset($tabMenu[$reg])) {
					$retour['resultat'] .= $tabMenu[$reg];
					$nbTotal[$reg] += 1;
				}
			} else {
				if (isset($tabMenu[$reg])) {
					$retour['resultat'] .= $tabMenu[$reg];
				}
			}
		}
	}
	if (!$menuSeul) {
		$retour['resultat'] .= '<table><thead><tr><th>Tournée</th><th>Régime</th><th>nombre d\'exemplaires</th></tr><thead><tbody>';
		$nbExemplaires = 0;
		for ($i=0;$i<count($liste);$i++) {
			$nbExemplaires += $liste[$i]['quantite'];
			$retour['resultat'] .= '<tr><td>'.$liste[$i]['t.nom'].'</td><td>'.$liste[$i]['r.nom'].'</td><td>'.$liste[$i]['quantite'].'</td></tr>';
		}
		$retour['resultat'] .= '<tr><th colspan="2">TOTAL</th><td>'.$nbExemplaires.'</td></tr></tbody></table>';
	}
	/******/
	echo '<!DOCTYPE html><html xml:lang="fr" lang="fr"><head><meta charset="utf-8"><link rel="stylesheet" type="text/css" href="../../../../css/main.css" media="all" /><link rel="stylesheet" type="text/css" href="../../../../css/print.css" media="all" /><link rel="stylesheet" type="text/css" href="../../css/menu.css" media="all" /><title>Saveurs Maison</title></head><body>'.$retour['resultat'].'</body></html>';
}