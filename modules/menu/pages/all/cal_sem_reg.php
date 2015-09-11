<?php

function week_dates($week, $year) {
	$week_dates = array();
	$first_day = mktime(12, 0, 0, 1, 1, $year);
	$first_week = date("W", $first_day);
	if ($first_week > 1) {
		$first_day = strtotime("+1 week",$first_day);
	}
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

	$requete = new requete();
	$requete->select('regime', 'r');
	$requete->where(array('r' => array('nom' => 'NORMAL')));
	// echo $requete->requete_complete().'<br><br>';
	$requete->executer_requete();
	$liste = $requete->resultat;
	$erreur = array_merge($erreur, $requete->liste_erreurs);
	unset($requete);
	
	if ($liste) {
		$typeRepas = array('MIDI', 'SOIR');
		$temp = week_dates(strftime("%U"), date('Y'));
		$retour['resultat'] = '';
		foreach ($liste as $regime) {
			$lundiSemaine = $temp;
			$retour['resultat'] .= '<table><caption>Menus '.$regime['nom'].' - SEMAINE n°'. strftime("%U").' du '.date('d/m/Y', $lundiSemaine).' au '.date('d/m/Y', strtotime("+6 day",$lundiSemaine)).'</caption><thead><tr><th>LUNDI</th><th>MARDI</th><th>MERCREDI</th><th>JEUDI</th><th>VENDREDI</th><th>SAMEDI</th><th>DIMANCHE</th></tr></thead><tbody>';
			foreach ($typeRepas as $type) {
				$retour['resultat'] .= '<tr><th colspan="7">'.$type.'</th></tr><tr>';
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
						$resultat = $requete->resultat;
						$requete->reset();
						$retour['resultat'] .= '<td data-typeCal="'.$type.'" data-timestampJour="'.$timestampJour.'" data-idRegime="'.$regime['id'].'" ondblclick="associer_menu(this);">';
						if ($resultat) {
							$retour['resultat'] .= ''.$resultat['me.nom'].'<br>'.$resultat['mv.nom'].'<br>'.$resultat['ml.nom'].'<br>'.$resultat['mf.nom'].'<br>'.$resultat['md.nom'];
						} else {
							$retour['resultat'] .= '&nbsp';
						}
						$retour['resultat'] .= '</td>';
					} else {
						$retour['resultat'] .= '<td data-typeCal="'.$type.'" data-timestampJour="'.$timestampJour.'" data-idRegime="'.$regime['id'].'" ondblclick="associer_menu(this);">&nbsp</td>';
					}
				}
				$retour['resultat'] .= '</tr>';
			}
			$retour['resultat'] .= '</tbody></table>';
		}
	} else {
		$retour['resultat'] = '<p class="erreur">Aucun régime n\'est déclaré.</p>';
	}
	
	$requete = new requete();
	$requete->alias = true;
	$requete->select(array('menu' => array('id', 'supplement')), 'm');
	$requete->select(array('menu_entree' => 'nom'), 'me');
	$requete->select(array('menu_viande' => 'nom'), 'mv');
	$requete->select(array('menu_legume' => 'nom'), 'ml');
	$requete->select(array('menu_fromage' => 'nom'), 'mf');
	$requete->select(array('menu_dessert' => 'nom'), 'md');
	$requete->order('me.nom');
	$requete->order('mv.nom');
	$requete->order('ml.nom');
	$requete->order('mf.nom');
	$requete->order('md.nom');
	$requete->order('m.supplement');
	// echo $requete->requete_complete().'<br><br>';
	$requete->executer_requete();
	$liste = $requete->resultat;
	$erreur = array_merge($erreur, $requete->liste_erreurs);
	unset($requete);
	$retour['resultat'] .= '<select name="liste_menu" id="liste_menu" style="display:none;">';
	foreach ($liste as $membre) {
		$retour['resultat'] .= '<option value="'.$membre['m.id'].'">'.$membre['me.nom'].' ; '.$membre['mv.nom'].' ; '.$membre['ml.nom'].' ; '.$membre['mf.nom'].' ; '.$membre['md.nom'].' ; '.$membre['m.supplement'].'</option>';
	}
	$retour['resultat'] .= '</select>';
	
	// $retour['resultat'] .= '<p><a href="?menu=menu&amp;sousmenu=ajouter&amp;type='.$type.'">Ajouter un nouveau '.$type.'</a></p>';
	echo $retour['resultat'];
}