<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	/* GESTION DES DATES */
	$dateCalendrier = (empty($_REQUEST['dateCalendrier']))?false:$_REQUEST['dateCalendrier'];;
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
	$timestampJour = mktime(0, 0, 0, $dateMois, $dateJour, $dateAnnee);
	/*********************/
	
	if (file_exists('../../../../fonctions/api.class.php')) { include('../../../../fonctions/api.class.php'); }
	$connectBDD = (file_exists('../../../../connectBDD.php'))?'../../../../connectBDD.php':'';
	
	$liste_personnes = array();
	$requete_personnes = new requete();
	$requete_personnes->select(array('personne' => array('id', 'nom', 'prenom')), 'p');
	$requete_personnes->select(array('per_reg' => array('idRegime', 'quantite', 'quantiteRemp')), 'pr');
	$requete_personnes->select(array('regime' => array('nom', 'couleur')), 'r');
	$requete_personnes->select(array('tournee' => array('nom')), 't');
	$requete_personnes->select(array('calendrier' => array('typeCalendrier')), 'c');
	$requete_personnes->where(array('c' => array('annee' => $dateAnnee, 'mois' => $dateMois, 'jour' => $dateJour)));
	$requete_personnes->order(array('p' => 'numTournee'));
	$requete_personnes->order(array('p' => 'numPerTou'));
	// echo $requete_personnes->requete_complete().'<br>';
	$requete_personnes->executer_requete();
	$resultat = $requete_personnes->resultat;
	unset($requete_personnes);

	$liste_menu = array();
	$liste_menu_regime = array();
	$requete_menu = new requete();
	$requete_menu->select(array('regime' => 'nom'), 'r');
	$requete_menu->select(array('menu_regime' => 'idRegime'), 'mr');
	$requete_menu->select(array('menu' => 'supplement'), 'm');
	$requete_menu->select(array('calendrier' => 'typeCalendrier'), 'c');
	$requete_menu->select(array('menu_entree' => 'nom'), 'me');
	$requete_menu->select(array('menu_viande' => 'nom'), 'mv');
	$requete_menu->select(array('menu_legume' => 'nom'), 'ml');
	$requete_menu->select(array('menu_fromage' => 'nom'), 'mf');
	$requete_menu->select(array('menu_dessert' => 'nom'), 'md');
	$requete_menu->where(array('c' => array('annee' => $dateAnnee, 'mois' => $dateMois, 'jour' => $dateJour)));
	// echo $requete_menu->requete_complete().'<br>';
	$requete_menu->executer_requete();
	$resultat_menu = $requete_menu->resultat;
	unset($requete_menu);
	foreach ($resultat_menu as $menu) {
		$liste_menu_regime[$menu['r.nom']] = $menu['mr.idRegime'];
		$liste_menu[$menu['mr.idRegime']][$menu['c.typeCalendrier']] = array('entree' => $menu['me.nom'], 'viande' => $menu['mv.nom'], 'legume' => $menu['ml.nom'], 'fromage' => $menu['mf.nom'], 'dessert' => $menu['md.nom'], 'supplement' => $menu['m.supplement']);
	}

	if ($resultat) {
		$page = array();
		$date = ucfirst(strftime("%A %d %B %Y", $timestampJour));
		$nbClients = count($resultat);
		$class="droite";
		for ($i=0;$i<$nbClients;$i++) {
			if (intval($resultat[$i]['pr.quantite']) > 0) {
				if ($i > 0 && $class==="droite") { $page[] = '<div style="clear:both;"></div>'; }
				$class=($class==="droite")?"gauche":"droite";
				$page[] = '<div class="'.$class.'">';
				$page[] = '<table class="etiquette"><tr><td>'.$date.'</td><td rowspan="2">'.$resultat[$i]['p.nom'].' '.$resultat[$i]['p.prenom'].'</td><td rowspan="2"><img src="../../img/autorisation.png" class="autorisation" /></td></tr><tr><td rowspan="2"><img src="../../img/logo.png" class="logo"/></td></tr><tr><td colspan="2" style="color:#'.$resultat[$i]['r.couleur'].'">'.$resultat[$i]['pr.quantite'].' x '.$resultat[$i]['r.nom'].' - '.$resultat[$i]['c.typeCalendrier'].'</td></tr>';
					if (isset($liste_menu[$resultat[$i]['pr.idRegime']][$resultat[$i]['c.typeCalendrier']])) {
					$page[] = '<tr><td colspan="3">'.$liste_menu[$resultat[$i]['pr.idRegime']][$resultat[$i]['c.typeCalendrier']]['entree'].'</td></tr><tr><td colspan="3">'.$liste_menu[$resultat[$i]['pr.idRegime']][$resultat[$i]['c.typeCalendrier']]['viande'].'</td></tr><tr><td colspan="3">'.$liste_menu[$resultat[$i]['pr.idRegime']][$resultat[$i]['c.typeCalendrier']]['legume'].'</td></tr><tr><td colspan="3">'.$liste_menu[$resultat[$i]['pr.idRegime']][$resultat[$i]['c.typeCalendrier']]['fromage'].'</td></tr><tr><td colspan="3">'.$liste_menu[$resultat[$i]['pr.idRegime']][$resultat[$i]['c.typeCalendrier']]['dessert'].'</td></tr>';
					if ($liste_menu[$resultat[$i]['pr.idRegime']][$resultat[$i]['c.typeCalendrier']]['supplement'] != '') {
						$page[] = '<tr><td colspan="3">'.$liste_menu[$resultat[$i]['pr.idRegime']][$resultat[$i]['c.typeCalendrier']]['supplement'].'</td></tr>';
					}
				}
				$page[] = '<tr><td colspan="3" class="italique">'.$resultat[$i]['t.nom'].'</td></tr></table></div>';
			}

			if (intval($resultat[$i]['pr.quantiteRemp']) > 0) {
				if ($i > 0 && $class==="droite") { $page[] = '<div style="clear:both;"></div>'; }
				$class=($class==="droite")?"gauche":"droite";
				$page[] = '<div class="'.$class.'">';
				$page[] = '<table class="etiquette"><tr><td>'.$date.'</td><td rowspan="2">'.$resultat[$i]['p.nom'].' '.$resultat[$i]['p.prenom'].'</td><td rowspan="2"><img src="../../img/autorisation.png" class="autorisation" /></td></tr><tr><td rowspan="2"><img src="../../img/logo.png" class="logo"/></td></tr><tr><td colspan="2" style="color:#'.$resultat[$i]['r.couleur'].'">'.$resultat[$i]['pr.quantiteRemp'].' REMP x '.$resultat[$i]['r.nom'].' - '.$resultat[$i]['c.typeCalendrier'].'</td></tr>';
				$idRegime = $liste_menu_regime["REMPLACEMENT"];
				if (isset($liste_menu[$idRegime][$resultat[$i]['c.typeCalendrier']])) {
					$page[] = '<tr><td colspan="3">'.$liste_menu[$idRegime][$resultat[$i]['c.typeCalendrier']]['entree'].'</td></tr><tr><td colspan="3">'.$liste_menu[$idRegime][$resultat[$i]['c.typeCalendrier']]['viande'].'</td></tr><tr><td colspan="3">'.$liste_menu[$idRegime][$resultat[$i]['c.typeCalendrier']]['legume'].'</td></tr><tr><td colspan="3">'.$liste_menu[$idRegime][$resultat[$i]['c.typeCalendrier']]['fromage'].'</td></tr><tr><td colspan="3">'.$liste_menu[$idRegime][$resultat[$i]['c.typeCalendrier']]['dessert'].'</td></tr>';
					if ($liste_menu[$idRegime][$resultat[$i]['c.typeCalendrier']]['supplement'] != '') {
						$page[] = '<tr><td colspan="3">'.$liste_menu[$idRegime][$resultat[$i]['c.typeCalendrier']]['supplement'].'</td></tr>';
					}
				}
				$page[] = '<tr><td colspan="3" class="italique">'.$resultat[$i]['t.nom'].'</td></tr></table></div>';
			}
		}
	} else {
		$page[] = '<p class="erreur">Il n\'y a aucun repas pour le '.strftime("%A %d %B %Y", $timestampJour).'.</p>';
	}
	
	echo '<!DOCTYPE html><html xml:lang="fr" lang="fr"><head><meta charset="utf-8"><link rel="stylesheet" type="text/css" href="../../css/etiquettes.css" media="all" /><title>Saveurs Maison</title></head><body>'.implode($page, '').'</body></html>';
}