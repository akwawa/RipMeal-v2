<?php

function couleur_inverse($c_orig) {
	$a = substr($c_orig,0,1); $b = substr($c_orig,1,1); $c = substr($c_orig,2,1); $d = substr($c_orig,3,1); $e = substr($c_orig,4,1); $f = substr($c_orig,5,1); $ai = DecHex(15-HexDec($a)); $bi = DecHex(15-HexDec($b)); $ci = DecHex(15-HexDec($c)); $di = DecHex(15-HexDec($d)); $ei = DecHex(15-HexDec($e)); $fi = DecHex(15-HexDec($f)); $c_inv = $ai.$bi.$ci.$di.$ei.$fi;
	return $c_inv;
}

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

	$typeRepas = array('MIDI', 'SOIR');
	$lundiSemaine = week_dates(strftime("%U", $temps), date('Y', $temps));

	$requete = new requete();
	$requete->select('regime', 'r');
	// echo $requete->requete_complete().'<br><br>';
	$requete->executer_requete();
	$liste = $requete->resultat;
	$erreur = array_merge($erreur, $requete->liste_erreurs);
	unset($requete);
	
	if ($liste) {
		$retour['resultat'] = '<table><caption>Menus - SEMAINE n°'. strftime("%U", $temps).' du '.date('d/m/Y', $lundiSemaine).' au '.date('d/m/Y', strtotime("+6 day",$lundiSemaine)).'</caption>';
		
		$thead = '<tr><th colspan="2">&nbsp;</th>';
		foreach ($liste as $regime) {
			$regime['couleur'] = (empty($regime['couleur'])?'FFFFFF':$regime['couleur']);
			$thead .= '<th style="background-color:#'.$regime['couleur'].';color:#'.couleur_inverse($regime['couleur']).';">'.$regime['nom'].'</th>';
		}
		$thead .= '</tr>';
		$retour['resultat'] .= '<thead>'.$thead.'</thead><tfoot>'.$thead.'</tfoot><tbody>';
		$tab_jour = array('LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE');
		for ($i=0; $i<7; $i++) {
			$retour['resultat'] .= '<tr><th rowspan="2">'.$tab_jour[$i].'</th>';
			$timestampJour = strtotime('+'.$i.' day', $lundiSemaine);
			$premiereLigne = true;
			foreach ($typeRepas as $type) {
				if (!$premiereLigne) { $retour['resultat'] .= '<tr>'; }
				$retour['resultat'] .= '<th>'.$type.'</th>';
				
				$requete = new requete();
				$requete->select(array('calendrier' => 'id'), 'c');
				$requete->where(array('c' => array('jour' => date('d', $timestampJour), 'mois' => date('m', $timestampJour), 'annee' => date('Y', $timestampJour), 'typeCalendrier' => $type)));
				// echo $requete->requete_complete().'<br><br>';
				$requete->grand_tableau = false;
				$requete->executer_requete();
				$result = $requete->resultat;
				$requete->reset();
				if ($result) {
					foreach ($liste as $regime) {
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
						$retour['resultat'] .= '<td>';
						if ($resultat) {
							$retour['resultat'] .= $resultat['me.nom'].'<br>'.$resultat['mv.nom'].'<br>'.$resultat['ml.nom'].'<br>'.$resultat['mf.nom'].'<br>'.$resultat['md.nom'];
						} else {
							$retour['resultat'] .= '&nbsp;';
						}
						$retour['resultat'] .= '</td>';
					}
				} else {
					foreach ($liste as $regime) {
						$retour['resultat'] .= '<td>&nbsp;</td>';
					}
				}
				$retour['resultat'] .= '</tr>';
				$premiereLigne = false;
			}
		}
		$retour['resultat'] .= '</tbody></table>';
	}
	echo $retour['resultat'];
}