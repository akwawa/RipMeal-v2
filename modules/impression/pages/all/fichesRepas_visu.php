<?php

if (empty($_SESSION)) session_start();

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
	$page = array();
	
	// /***** Livraison + prépa variable *****/
	{
		$liste_tournee = explode(',', $_GET['tournee']);
		// var_dump($liste_tournee);
		$info_tables = array('regime', 'boisson', 'remplacement', 'supplement');
		$info_tablesTournee = array('boisson');
		$info_tablesPrepation = array('regime', 'remplacement', 'supplement');
		$tab_typeRepas = array('MIDI', 'SOIR');
		$requete = new requete();
		$requete->select(array('calendrier'));
		$requete->where(array('calendrier' => array('annee' => $dateAnnee, 'mois' => $dateMois, 'jour' => $dateJour)));
		// echo $requete->requete_complete().'<br>';
		$requete->executer_requete();
		$liste = $requete->resultat;
		unset($requete);
		$tab_idCalendrier = array();
		foreach ($liste as $cal) {
			$tab_idCalendrier[] = $cal['id'];
		}
	}
	
	// tournee[] recapitulatifChef preparation eauEtPain
	// /***** Préparation *****/
	{
		$preparation = (empty($_GET['preparation']))?false:$_GET['preparation'];
		if ($tab_idCalendrier && $preparation != "false") {
			$tab_tournee = array();
			foreach ($liste_tournee as $tournee) {
				$tab_tournee[$tournee] = array();
				foreach ($info_tablesPrepation as $table) {
					// A trier suivant la tournee
					$requete = new requete();
					$requete->select(array($table => array('id', 'nom')), 'r');
					$requete->select('per_'.substr($table, 0, 3), 'pr');
					$requete->where(array('pr' => array('idCalendrier' => $tab_idCalendrier[0])));
					$requete->group('r', 'id');
					// echo $requete->requete_complete().'<br>';
					$requete->executer_requete();
					$tab_tournee[$tournee][$table] = $requete->resultat;
					unset($requete);
				}
			}
			foreach ($tab_tournee as $tournee => $valeurs) {
				$tab_nbPains[$tournee]['nbPotages'] = 0;
				$nbColonnes = 0;
				$requete = new requete();
				$requete->select(array('tournee' => 'nom'), 't');
				$requete->where(array('t' => array('id' => $tournee)));
				// echo $requete->requete_complete();
				$requete->executer_requete();
				$liste = $requete->resultat;
				unset($requete);
				if (!empty($liste)) {
					$nomTournee = $liste[0]['t.nom'];
					unset($liste);
					$page_tournee = '<div>Préparation au '.date("d/m/Y", $timestampJour).' pour la tournée '.$nomTournee.'</div><table class="prepa"><thead><tr><th rowspan="3">&nbsp;</th><th rowspan="3">Potage</th>';
					foreach ($valeurs as $a => $key) {
						if ($key) {
							$page_tournee .= '<th colspan="'.(count($key)*2).'">'.$a.'</th>';
							$nbColonnes += count($key)*2;
						}
					}
					$page_tournee .= '<th rowspan="3">Aliments Interdit</th></tr><tr>';
					foreach ($valeurs as $key) {
						foreach ($key as $val) {
							$page_tournee .= '<th colspan="2">'.$val['r.nom'].'</th>';
						}
					}
					$page_tournee .= '</tr><tr>';
					for ($i = 0; $i < $nbColonnes; $i++) {
						$page_tournee .= '<th>'.$tab_typeRepas[($i%2)][0].'</th>';
					}
					$page_tournee .= '</tr></thead><tbody>';
					$tableau_tournee = array();
					for ($i = 0; $i < count($tab_idCalendrier); $i++) {
						$idCalendrier = $tab_idCalendrier[$i];
						$requete = new requete();
						$requete->requete_direct('SELECT p.numPerTou, p.id, p.nom, p.prenom, p.potage, p.alimentInterdit, pr.idRegime, pr.quantite as "pr.quantite", pb.idBoisson, pb.quantite as "pb.quantite", pm.idRemplacement, pm.quantite as "pm.quantite", ps.idSupplement, ps.quantite as "ps.quantite", pr.quantiteRemp as "pr.quantiteRemp", pb.idBoisson, pb.quantiteRemp as "pb.quantiteRemp", pm.idRemplacement, pm.quantiteRemp as "pm.quantiteRemp", ps.idSupplement, ps.quantiteRemp as "ps.quantiteRemp" FROM v2__personne p INNER JOIN v2__per_reg pr ON p.id = pr.idPersonne LEFT JOIN v2__per_boi pb ON p.id = pb.idPersonne AND pr.idCalendrier = pb.idCalendrier LEFT JOIN v2__per_rem pm ON p.id = pm.idPersonne AND pr.idCalendrier = pm.idCalendrier LEFT JOIN v2__per_sup ps ON p.id = ps.idPersonne AND pr.idCalendrier = ps.idCalendrier WHERE pr.idCalendrier = "'.$idCalendrier.'" AND p.numTournee = "'.$tournee.'" AND p.corbeille = false ORDER BY p.numPerTou');
						// echo $requete->requete_complete().'<br>';
						$requete->executer_requete();
						$liste_repas = $requete->resultat;
						// echo count($liste_repas);
						unset($requete);
						foreach ($liste_repas as $table) {
							$temp = $table['numPerTou'];
							if (empty($tableau_tournee[$temp])) {
								$tab_nbPains[$tournee]['nbPotages'] += $table['pr.quantite']+$table['pr.quantiteRemp'];
								$tableau_tournee[$temp] = array();
								$tableau_tournee[$temp]['nom'] = $table['nom'].' '.$table['prenom'];
								$tableau_tournee[$temp]['potage'] = $table['potage'];
								$tableau_tournee[$temp]['alimentInterdit'] = $table['alimentInterdit'];
							}
							if ($tab_typeRepas[$i] == "SOIR") {
								$tableau_tournee[$temp]['potage'] += $table['pr.quantite']+$table['pr.quantiteRemp'];
							}
							if ($table['idRegime']) {
								$tableau_tournee[$temp]['regime'][$table['idRegime']][$tab_typeRepas[$i]]["NORMAL"] = $table['pr.quantite'];
								$tableau_tournee[$temp]['regime'][$table['idRegime']][$tab_typeRepas[$i]]["REMP"] = $table['pr.quantiteRemp'];
							}
							if ($table['idBoisson']) {
								$tableau_tournee[$temp]['boisson'][$table['idBoisson']][$tab_typeRepas[$i]]["NORMAL"] = $table['pb.quantite'];
								$tableau_tournee[$temp]['boisson'][$table['idBoisson']][$tab_typeRepas[$i]]["REMP"] = $table['pb.quantiteRemp'];
							}
							if ($table['idRemplacement']) {
								$tableau_tournee[$temp]['remplacement'][$table['idRemplacement']][$tab_typeRepas[$i]]["NORMAL"] = $table['pm.quantite'];
								$tableau_tournee[$temp]['remplacement'][$table['idRemplacement']][$tab_typeRepas[$i]]["REMP"] = $table['pm.quantiteRemp'];
							}
							if ($table['idSupplement']) {
								$tableau_tournee[$temp]['supplement'][$table['idSupplement']][$tab_typeRepas[$i]]["NORMAL"] = $table['ps.quantite'];
								$tableau_tournee[$temp]['supplement'][$table['idSupplement']][$tab_typeRepas[$i]]["REMP"] = $table['ps.quantiteRemp'];
							}
						}
					}
					// $taille_tab = count($tableau_tournee);
					// echo $taille_tab.' ';
					ksort($tableau_tournee);
					foreach ($tableau_tournee as $personne) {
						$page_tournee .= '<tr><td>'.$personne['nom'].'</td><td>'.$personne['potage'].'</td>';
						foreach ($valeurs as $table => $key) {
							foreach ($key as $val) {
								$qteMidi = (isset($personne[$table][$val['r.id']]['MIDI']["NORMAL"]))?$personne[$table][$val['r.id']]['MIDI']["NORMAL"]:'-';
								$qteMidiRemp = (isset($personne[$table][$val['r.id']]['MIDI']["REMP"]))?$personne[$table][$val['r.id']]['MIDI']["REMP"]:'-';
								$qteSoir = (isset($personne[$table][$val['r.id']]['SOIR']["NORMAL"]))?$personne[$table][$val['r.id']]['SOIR']["NORMAL"]:'-';
								$qteSoirRemp = (isset($personne[$table][$val['r.id']]['SOIR']["REMP"]))?$personne[$table][$val['r.id']]['SOIR']["REMP"]:'-';
								
								if (intval($qteMidiRemp) > 0) {
									if (intval($qteMidi) > 0) {
										$page_tournee .= '<td>'.$qteMidi.'<span style="color:red;"> + '.$qteMidiRemp.'</span></td>';
									} else {
										$page_tournee .= '<td><span style="color:red;">'.$qteMidiRemp.'</span></td>';
									}
								} else {
									$page_tournee .= '<td>'.$qteMidi.'</td>';
								}
								
								if (intval($qteSoirRemp) > 0) {
									if (intval($qteSoir) > 0) {
										$page_tournee .= '<td style="background-color:cyan;">'.$qteSoir.'<span style="color:red;"> + '.$qteSoirRemp.'</span></td>';
									} else {
										$page_tournee .= '<td><span style="color:red;">'.$qteSoirRemp.'</span></td>';
									}
								} else {
									if (intval($qteSoir) > 0) {
										$page_tournee .= '<td style="background-color:cyan;">'.$qteSoir.'</td>';
									} else {
										$page_tournee .= '<td>'.$qteSoir.'</td>';
									}
								}
							}
						}
						$page_tournee .= '<td>'.$personne['alimentInterdit'].'</td></tr>';
					}
					// var_dump($valeurs);
					$page_tournee .= '</tbody></table>';
					$page[] = $page_tournee.'<div class="page">&nbsp;</div>';
					// echo 'ok'.$page_tournee;
				}
			}
		}
	}
	/********************/

	/***** livraison *****/
	{
		$livraison = (empty($_GET['livraison']))?false:$_GET['livraison'];
		if ($tab_idCalendrier && $livraison != "false") {
			foreach ($liste_tournee as $tournee) {
				$tab_tournee[$tournee] = array();
				foreach ($info_tablesTournee as $table) {
					$requete = new requete();
					$requete->select(array($table => array('id', 'nom')), 'r');
					$requete->select('per_'.substr($table, 0, 3), 'pr');
					$requete->where(array('pr' => array('idCalendrier' => $tab_idCalendrier[0])));
					$requete->group('r', 'id');
					// echo $requete->requete_complete().'<br>';
					$requete->executer_requete();
					$tab_tournee[$tournee][$table] = $requete->resultat;
					unset($requete);
				}
			}
			foreach ($tab_tournee as $tournee => $valeurs) {
				$tab_nbPains[$tournee]['nbPains'] = 0;
				$nbColonnes = 0;
				$requete = new requete();
				$requete->select(array('tournee' => 'nom'), 't');
				$requete->where(array('t' => array('id' => $tournee)));
				$requete->executer_requete();
				$liste = $requete->resultat;
				unset($requete);
				if (!empty($liste)) {
					$nomTournee = $liste[0]['t.nom'];
					unset($liste);
					$page_tournee = '<p>Tournée au '.date("d/m/Y", $timestampJour).' pour la tournée '.$nomTournee.'</p><table><thead><tr><th rowspan="3">Heure de fin</th><th rowspan="3">Pain</th>';
					foreach ($valeurs as $a => $key) {
						if ($key) {
							$page_tournee .= '<th colspan="'.(count($key)*2).'">'.$a.'</th>';
							$nbColonnes += count($key)*2;
						}
					}
					$page_tournee .= '<th rowspan="3">Informations</th></tr><tr>';
					foreach ($valeurs as $key) {
						foreach ($key as $val) {
							$page_tournee .= '<th colspan="2">'.$val['r.nom'].'</th>';
						}
					}
					$page_tournee .= '</tr><tr>';
					for ($i = 0; $i < $nbColonnes; $i++) {
						$page_tournee .= '<th>'.$tab_typeRepas[($i%2)][0].'</th>';
					}
					$page_tournee .= '</tr></thead><tbody>';
					$tableau_tournee = array();
					for ($i = 0; $i < count($tab_idCalendrier); $i++) {
					// foreach ($tab_idCalendrier as $idCalendrier) {
						$idCalendrier = $tab_idCalendrier[$i];
						$requete = new requete();
						$requete->requete_direct('SELECT p.id, p.numPerTou, p.nom, p.prenom, p.pain, p.info, pb.idBoisson, pb.quantite as "pb.quantite", pr.qtePain, pr.quantiteRemp as "pr.quantiteRemp" FROM v2__personne p INNER JOIN v2__per_reg pr ON p.id = pr.idPersonne LEFT JOIN v2__per_boi pb ON p.id = pb.idPersonne AND pr.idCalendrier = pb.idCalendrier WHERE pr.idCalendrier = "'.$idCalendrier.'" AND p.numTournee = "'.$tournee.'" AND p.corbeille = false ORDER BY p.numPerTou');
						// echo $requete->requete_complete().'<br>';
						$requete->executer_requete();
						$liste_repas = $requete->resultat;
						// var_dump($liste_repas);
						unset($requete);
						foreach ($liste_repas as $table) {
							$temp = $table['numPerTou'];
							if (empty($tableau_tournee[$temp])) {
								$tab_nbPains[$tournee]['nbPains'] += $table['pain'];
								$tableau_tournee[$temp] = array();
								$tableau_tournee[$temp]['nom'] = $table['nom'].' '.$table['prenom'];
								$tableau_tournee[$temp]['pain'] = $table['pain'];
								$tableau_tournee[$temp]['info'] = $table['info'];
								$tableau_tournee[$temp]['qtePain'] = 0;
							}
							$tab_nbPains[$tournee]['nbPains'] += $table['qtePain'];
							$tableau_tournee[$temp]['qtePain'] += $table['qtePain'];
							if ($table['idBoisson']) {
								$tableau_tournee[$temp]['boisson'][$table['idBoisson']][$tab_typeRepas[$i]] = $table['pb.quantite']+$table['pr.quantiteRemp'];
							}
						}
						$requete = new requete();
						$requete->requete_direct('SELECT p.numPerTou, quantite FROM v2__personne p INNER JOIN v2__per_sup pr ON p.id = pr.idPersonne WHERE pr.idCalendrier = "'.$idCalendrier.'" AND p.numTournee = "'.$tournee.'" AND p.corbeille = false ORDER BY p.numPerTou');
						// echo $requete->requete_complete().'<br>';
						$requete->executer_requete();
						$liste_repas = $requete->resultat;
						// var_dump($liste_repas);
						unset($requete);
						foreach ($liste_repas as $table) {
							$temp = $table['numPerTou'];
							if (!empty($tableau_tournee[$temp])) {
								$tab_nbPains[$tournee]['nbPains'] += $table['quantite'];
								$tableau_tournee[$temp]['qtePain'] += $table['quantite'];
							}
						}
					}
					
					// var_dump($tableau_tournee);
					ksort($tableau_tournee);
					// echo count($tableau_tournee);
					foreach ($tableau_tournee as $personne) {
						$page_tournee .= '<tr><td>'.$personne['nom'].'</td><td>'.($personne['pain']+$personne['qtePain']).'</td>';
						foreach ($valeurs as $table => $key) {
							foreach ($key as $val) {
								$qteMidi = (isset($personne[$table][$val['r.id']]['MIDI']))?$personne[$table][$val['r.id']]['MIDI']:'-';
								$qteSoir = (isset($personne[$table][$val['r.id']]['SOIR']))?$personne[$table][$val['r.id']]['SOIR']:'-';
								$page_tournee .= '<td>'.$qteMidi.'</td>';
								if (intval($qteSoir) > 0) {
									$page_tournee .= '<td style="background-color:cyan;">'.$qteSoir.'</td>';
								} else {
									$page_tournee .= '<td>'.$qteSoir.'</td>';
								}
							}
						}
						$page_tournee .= '<td>'.$personne['info'].'</td></tr>';
					}
					// var_dump($valeurs);
					$page_tournee .= '</tbody></table>';
					$page[] = $page_tournee.'<div class="page">&nbsp;</div>';
				}
			}
		}
	}
	/***********************/
	
	/***** Eau Et Pain *****/
	{
	$eauEtPain = (empty($_GET['eauEtPain']))?false:$_GET['eauEtPain'];
	if ($tab_idCalendrier && $eauEtPain != "false") {
		$page_eauEtPain = '';
		foreach ($liste_tournee as $tournee) {
			$requete = new requete();
			$requete->select(array('tournee' => 'nom'), 't');
			$requete->where(array('t' => array('id' => $tournee)));
			// echo $requete->requete_complete().'<br>';
			$requete->executer_requete();
			$liste = $requete->resultat;
			unset($requete);
			if (!empty($liste)) {
				$nomTournee = $liste[0]['t.nom'];
				unset($liste);
				$requete = new requete();
				$requete->select(array('calendrier' => 'jour'), 'c');
				$requete->select(array('personne' => array('pain', 'potage')), 'p');
				$requete->select(array('per_reg' => 'qtePain'), 'pr');
				$requete->where(array('c' => array('annee' => $dateAnnee, 'mois' => $dateMois, 'jour' => $dateJour)));
				$requete->where(array('p' => array('numTournee' => $tournee)));
				// echo $requete->requete_complete().'<br>';
				$requete->executer_requete();
				$liste = $requete->resultat;
				unset($requete);
				
				$requete = new requete();
				$requete->select(array('calendrier' => 'jour'), 'c');
				$requete->select(array('boisson' => 'nom'), 'b');
				$requete->select(array('personne' => array('id')), 'p');
				$requete->select(array('SUM' => array('per_boi' => 'quantite')), 'pr');
				$requete->select(array('SUM' => array('per_boi' => 'quantiteRemp')), 'pr');
				$requete->where(array('c' => array('annee' => $dateAnnee, 'mois' => $dateMois, 'jour' => $dateJour)));
				$requete->where(array('p' => array('numTournee' => $tournee)));
				$requete->group('b', 'id');
				// echo $requete->requete_complete().'<br>';
				$requete->executer_requete();
				$liste = $requete->resultat;
				unset($requete);
				
				if (isset($tab_nbPains)) {
					$nbPains = (empty($tab_nbPains[$tournee]['nbPains']))?0:$tab_nbPains[$tournee]['nbPains'];
				} else {
					$nbPains = 0;
					$tableau_tournee = array();
					foreach ($tab_idCalendrier as $idCalendrier) {
						$requete = new requete();
						$requete->requete_direct('SELECT p.numPerTou, p.pain, pr.qtePain FROM v2__personne p INNER JOIN v2__per_reg pr ON p.id = pr.idPersonne LEFT JOIN v2__per_boi pb ON p.id = pb.idPersonne AND pr.idCalendrier = pb.idCalendrier WHERE pr.idCalendrier = "'.$idCalendrier.'" AND p.numTournee = "'.$tournee.'" AND p.corbeille = false ORDER BY p.numPerTou');
						// echo $requete->requete_complete().'<br>';
						$requete->executer_requete();
						$liste_repas = $requete->resultat;
						foreach ($liste_repas as $table) {
							$temp = $table['numPerTou'];
							if (empty($tableau_tournee[$temp])) {
								$nbPains += $table['pain'];
								$tableau_tournee[$temp] = true;
							}
							$nbPains += $table['qtePain'];
						}
						$requete->reset();
						$requete->requete_direct('SELECT p.numPerTou, quantite FROM v2__personne p INNER JOIN v2__per_sup pr ON p.id = pr.idPersonne WHERE pr.idCalendrier = "'.$idCalendrier.'" AND p.numTournee = "'.$tournee.'" AND p.corbeille = false ORDER BY p.numPerTou');
						// echo $requete->requete_complete().'<br>';
						$requete->executer_requete();
						$liste_repas = $requete->resultat;
						// var_dump($liste_repas);
						unset($requete);
						foreach ($liste_repas as $table) {
							$temp = $table['numPerTou'];
							if (!empty($tableau_tournee[$temp])) {
								$nbPains += $table['quantite'];
								$tableau_tournee[$temp]['qtePain'] += $table['quantite'];
							}
						}
					}
				}

				$page_eauEtPain .= '<p>Boissons et Pains au '.date("d/m/Y", $timestampJour).' pour la tournée '.$nomTournee.'</p><table><thead><tr><th>Nom</th><th>Quantité</th></tr></thead><tbody><tr><th>Pains</th><td>'.$nbPains.'</td></tr>';
				foreach ($liste as $val) {
					if ($val['b.nom']) {
						$page_eauEtPain .= '<tr><td>'.$val['b.nom'].'</td><td>'.($val['SUM(pr.quantite)']+$val['SUM(pr.quantiteRemp)']).'</td></tr>';
					}
				}
				$page_eauEtPain .= '</tbody></table>';
			}
		}
		$page[] = $page_eauEtPain.'<div class="page">&nbsp;</div>';
	}
	}
	/***********************/
	
	/***** Recap du Chef *****/
	{
	$recapitulatifChef = (empty($_GET['recapitulatifChef']))?false:$_GET['recapitulatifChef'];
	// echo gettype($recapitulatifChef);
	// $recapitulatifChef = true;
	if ($tab_idCalendrier && $recapitulatifChef != "false") {
		$dateDeuxJours = $timestampJour+86400;
		$page_recapitulatifChef = '<p>R&eacute;capitulatif du chef pour le '.date("d/m/Y", $dateDeuxJours).'</p>';

		/* tab regime */
		foreach ($info_tables as $table) {
			foreach ($tab_typeRepas as $typeRepas) {
				$tab_temp = array();
				foreach ($liste_tournee as $tournee) {
					$requete = new requete();
					$requete->select(array($table => 'nom'), 'r');
					$requete->select(array('personne' => 'id'), 'p');
					$requete->select(array('calendrier' => 'jour'), 'c');
					$requete->select(array('SUM' => array('per_'.substr($table, 0, 3) => 'quantite')), 'pr');
					$requete->where(array('p' => array('numTournee' => $tournee)));
					$requete->where(array('c' => array('annee' => date("Y", $dateDeuxJours), 'mois' => date("m", $dateDeuxJours), 'jour' => date("d", $dateDeuxJours), 'typeCalendrier' => $typeRepas)));
					$requete->group('r', 'id');
					// echo $requete->requete_complete().'<br>';
					$requete->executer_requete();
					$liste = $requete->resultat;
					unset($requete);
					foreach ($liste as $ligne) {
						if (empty($tab_temp[$ligne["r.nom"]]["NORMAL"])) {
							$tab_temp[$ligne["r.nom"]]["NORMAL"] = $ligne["SUM(pr.quantite)"];
						} else {
							$tab_temp[$ligne["r.nom"]]["NORMAL"] += $ligne["SUM(pr.quantite)"];
						}
					}
				}
				foreach ($liste_tournee as $tournee) {
					$requete = new requete();
					$requete->select(array($table => 'nom'), 'r');
					$requete->select(array('personne' => 'id'), 'p');
					$requete->select(array('calendrier' => 'jour'), 'c');
					$requete->select(array('SUM' => array('per_'.substr($table, 0, 3) => 'quantiteRemp')), 'pr');
					$requete->where(array('p' => array('numTournee' => $tournee)));
					$requete->where(array('c' => array('annee' => date("Y", $dateDeuxJours), 'mois' => date("m", $dateDeuxJours), 'jour' => date("d", $dateDeuxJours), 'typeCalendrier' => $typeRepas)));
					$requete->group('r', 'id');
					// echo $requete->requete_complete().'<br>';
					$requete->executer_requete();
					$liste = $requete->resultat;
					unset($requete);
					foreach ($liste as $ligne) {
						if (empty($tab_temp[$ligne["r.nom"]]["REMP"])) {
							$tab_temp[$ligne["r.nom"]]["REMP"] = $ligne["SUM(pr.quantiteRemp)"];
						} else {
							$tab_temp[$ligne["r.nom"]]["REMP"] += $ligne["SUM(pr.quantiteRemp)"];
						}
					}
				}
				// var_dump($tab_temp);
				if ($tab_temp) {
					$page_recapitulatifChef .= '<table class="tableau_Chef tab_'.$typeRepas.'"><thead><tr><th colspan="2">'.$table.' '.$typeRepas.'</th></tr><tr><th>Nom</th><th>Nombre de personnes</th></tr></thead><tbody>';
					foreach ($tab_temp as $cle => $qte) {
						if (intval($qte["NORMAL"]) > 0) {
							$page_recapitulatifChef .= '<tr><td>'.$cle.'</td><td>'.$qte["NORMAL"].'</td></tr>';
						}
						if (intval($qte["REMP"]) > 0) {
							$page_recapitulatifChef .= '<tr><td>REMP '.$cle.'</td><td>'.$qte["REMP"].'</td></tr>';
						}
					}
					$page_recapitulatifChef .= '</tbody></table>';
				}
			}
		}
		/***********************/
		
		/* tab pains et potage */
		{
			$nbPains = 0;
			$nbPotages = 0;
				foreach ($liste_tournee as $tournee) {
					$tableau_tournee = array();
					foreach ($tab_idCalendrier as $idCalendrier) {
						$requete = new requete();
						$requete->requete_direct('SELECT p.numPerTou, p.pain, pr.qtePain FROM v2__personne p INNER JOIN v2__per_reg pr ON p.id = pr.idPersonne LEFT JOIN v2__per_boi pb ON p.id = pb.idPersonne AND pr.idCalendrier = pb.idCalendrier INNER JOIN v2__calendrier c ON c.id = pr.idCalendrier WHERE c.annee = "'.(date("Y", $dateDeuxJours)).'" AND c.mois = "'.(date("m", $dateDeuxJours)).'" AND c.jour = "'.(date("d", $dateDeuxJours)).'" AND p.numTournee = "'.$tournee.'" AND p.corbeille = false ORDER BY p.numPerTou');
						// echo $requete->requete_complete().'<br>';
						$requete->executer_requete();
						$liste_repas = $requete->resultat;
						foreach ($liste_repas as $table) {
							$temp = $table['numPerTou'];
							if (empty($tableau_tournee[$temp])) {
								$nbPains += $table['pain'];
								$tableau_tournee[$temp] = true;
							}
							$nbPains += $table['qtePain'];
						}
						$requete->reset();
						$requete->requete_direct('SELECT p.numPerTou, quantite FROM v2__personne p INNER JOIN v2__per_sup pr ON p.id = pr.idPersonne INNER JOIN v2__calendrier c ON c.id = pr.idCalendrier WHERE c.annee = "'.(date("Y", $dateDeuxJours)).'" AND c.mois = "'.(date("m", $dateDeuxJours)).'" AND c.jour = "'.(date("d", $dateDeuxJours)).'" AND pr.idCalendrier = "'.$idCalendrier.'" AND p.numTournee = "'.$tournee.'" AND p.corbeille = false ORDER BY p.numPerTou');
						// echo $requete->requete_complete().'<br>';
						$requete->executer_requete();
						$liste_repas = $requete->resultat;
						// var_dump($liste_repas);
						unset($requete);
						foreach ($liste_repas as $table) {
							$temp = $table['numPerTou'];
							if (!empty($tableau_tournee[$temp])) {
								$nbPains += $table['quantite'];
								$tableau_tournee[$temp]['qtePain'] += $table['quantite'];
							}
						}
					}
					
					$tableau_tournee = array();
					foreach ($tab_idCalendrier as $typeCalendrier) {
						$requete = new requete();
						$requete->requete_direct('SELECT p.numPerTou, p.potage FROM v2__personne p INNER JOIN v2__per_reg pr ON p.id = pr.idPersonne LEFT JOIN v2__per_boi pb ON p.id = pb.idPersonne AND pr.idCalendrier = pb.idCalendrier LEFT JOIN v2__per_rem pm ON p.id = pm.idPersonne AND pr.idCalendrier = pm.idCalendrier LEFT JOIN v2__per_sup ps ON p.id = ps.idPersonne AND pr.idCalendrier = ps.idCalendrier INNER JOIN v2__calendrier c ON c.id = pr.idCalendrier WHERE c.annee = "'.(date("Y", $dateDeuxJours)).'" AND c.mois = "'.(date("m", $dateDeuxJours)).'" AND c.jour = "'.(date("d", $dateDeuxJours)).'" AND pr.idCalendrier = "'.$typeCalendrier.'" AND p.numTournee = "'.$tournee.'" AND p.corbeille = false ORDER BY p.numPerTou');
						$requete->executer_requete();
						$liste_repas = $requete->resultat;
						unset($requete);
						foreach ($liste_repas as $table) {
							$temp = $table['numPerTou'];
							if (empty($tableau_tournee[$temp])) {
								$nbPotages += $table['potage'];
								$tableau_tournee[$temp] = true;
							}
						}
					}
				}
			$page_recapitulatifChef .= '<table class="tableau_Chef"><thead><tr><th>Nom</th><th>Nombre de personne</th></tr></thead><tbody><tr><th>Pain</th><td>'.$nbPains.'</td></tr><tr><th>Potage</th><td>'.$nbPotages.'</td></tr></tbody></table>';
		}
		/***********************/
		
		// echo $page_recapitulatifChef;
		$page[] = $page_recapitulatifChef;
	}
	}
	/**********************/
	
	echo '<!DOCTYPE html><html xml:lang="fr" lang="fr"><head><meta charset="utf-8"><link rel="stylesheet" type="text/css" href="../../../../css/main.css" media="all" /><link rel="stylesheet" type="text/css" href="../../../../css/print.css" media="all" /><title>Saveurs Maison</title></head><body>'.implode($page, '').'</body></html>';
}
