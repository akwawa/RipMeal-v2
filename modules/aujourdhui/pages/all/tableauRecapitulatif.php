<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$debutTimestamp = time();
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
	/*********************/
	
	$nbRequete = 0;

	$requete = new requete();
	$requete->select('tournee', 't');
	$requete->where(array('t' => array('nom' => array('OPERATEUR' => '<>', 'VALEUR' => 'PAS DE TOURNEE'))));
	$requete->executer_requete();
	$liste_tournees = $requete->resultat;
	$requete->reset();

	if ($liste_tournees) {
		$valeur = array(0 => "regime", 1 => "boisson", 2 => "remplacement", 3 => "supplement");
		$repasJournee = array(0 => "MIDI", 1 => "SOIR");
		$premiereTournee = false;

		foreach ($liste_tournees as $tournee) {
			echo '<table class="tableauRecapitulatif"><caption>'.$tournee['nom'].' - R&eacute;capitulatif du mois '.$dateMois.'/'.$dateAnnee.'</caption><tr><th rowspan="3" colspan="2">&nbsp;</th><th colspan="'.$nbJour.'">JOURS</th><th rowspan="3">&nbsp;</th></tr><tr>';
			for ($i=1; $i<=$nbJour; $i++) {
				echo '<th>'.($i==date("d")+1?'<span class="jourEnCours">'.$i.'</span>':$i).'</th>';
			}
			echo '</tr><tr>';
			for ($i=1; $i<=$nbJour; $i++) {
				$timestampJour = mktime(0, 0, 0, $dateMois, $i, $dateAnnee);
				echo '<th><input type="checkbox" onclick="selectAll(this);" data-timestampJour="'.$timestampJour.'"></th>';
			}
			echo '</tr>';
			$requete = new requete();
			$requete->select(array('personne' => array('id', 'nom', 'prenom', 'actif', 'dateActif')), 'p');
			$requete->where(array('p' => array('numTournee' => $tournee['id'], 'corbeille' =>false)));
			$requete->order('numPerTou');
			$requete->executer_requete();
			$liste_personnes = $requete->resultat;
			// echo $requete->requete_complete().'<br>';
			$requete->reset();
			$nbLignes = 0;
			// var_dump($liste_personnes);
			foreach ($liste_personnes as $personne) {
				if ($nbLignes == 15 && count($liste_personnes) > 20) {
					echo '<tr><th colspan="2">&nbsp;</th>';
					for ($i=1; $i<=$nbJour; $i++) {
						echo '<th>'.($i==date("d")+1?'<span class="jourEnCours">'.$i.'</span>':$i).'</th>';
					}
					echo '<th>&nbsp;</th></tr>';
					$nbLignes = 0;
				}
				echo '<tr><th><a onclick="details({\'path\':\'client\', \'fonctions\':\'detailsClient\', \'session\':\'all\', \'id\':'.$personne['p.id'].'});">'.$personne['p.nom'].' '.$personne['p.prenom'].'</a></th><th><input type="checkbox" onclick="selectLigne(this);" data-idPersonne="'.$personne['p.id'].'"></th>';
				/* RECUPERATION DES COMMENTAIRES */
				$requete = new requete();
				$requete->select(array('commentaire' => 'commentaire'), 'co');
				$requete->select(array('calendrier' => 'jour'), 'c');
				$requete->where(array('co' => array('idPersonne' => $personne['p.id'])));
				$requete->where(array('c' => array('annee' => $dateAnnee, 'mois' => $dateMois)));
				$requete->executer_requete();
				$liste_commentaires = $requete->resultat;
				$requete->reset();
				$nbRequete++;
				$tab_repasCommentaire = array();
				foreach ($liste_commentaires as $commentaire) {
					$tab_repasCommentaire[$commentaire['c.jour']] = $commentaire['co.commentaire'];
				}
				/* VERIFICATION SI EXISTE DEJA */
				$requete = new requete();
				// $requete->requete_direct('SELECT DISTINCT pg.typeRepas AS "pg.typeRepas", c.jour AS "c.jour", co.commentaire AS "co.commentaire" FROM v2__per_reg pg INNER JOIN v2__calendrier c ON pg.idCalendrier = c.id LEFT JOIN v2__commentaire co ON co.idCalendrier = c.id WHERE pg.idPersonne = "'.$personne['p.id'].'" AND c.annee = "'.$dateAnnee.'" AND c.mois = "'.$dateMois.'"');
				$requete->select(array('per_reg' => 'typeRepas'), 'pg');
				$requete->select(array('calendrier' => 'jour'), 'c');
				// $requete->select(array('commentaire' => 'commentaire'), 'co');
				// $requete->join('calendrier', 'commentaire', 'RIGHT');
				$requete->where(array('pg' => array('idPersonne' => $personne['p.id'])));
				$requete->where(array('c' => array('annee' => $dateAnnee, 'mois' => $dateMois)));
				// echo $requete->requete_complete().'<br>';
				$requete->executer_requete();
				$liste_repas = $requete->resultat;
				$requete->reset();
				$nbRequete++;
				$tab_repasPersonne = array();
				foreach ($liste_repas as $repas) {
					$tab_repasPersonne[$repas['c.jour']] = $repas['pg.typeRepas'];
				}
				if ($personne['p.actif'] == false) {
					/* personne inactive */
					for ($i=1; $i<=$nbJour; $i++) {
						$commentaire=(empty($tab_repasCommentaire[$i]))?'':' title="'.$tab_repasCommentaire[$i].'" style="color:red;" ';
						if (isset($tab_repasPersonne[$i])) {
							$timestampJour = mktime(0, 0, 0, $dateMois, $i, $dateAnnee);
							if ($i > date("d")) {
								echo '<td style="background-color:red;"><input '.$commentaire.' type="checkbox" checked="checked" onclick="ajouter_repas(this);" data-idPersonne="'.$personne['p.id'].'" data-timestampJour="'.$timestampJour.'" /></td>';
							} else {
								echo '<td><input '.$commentaire.' type="checkbox" checked="checked" onclick="ajouter_repas(this);" data-idPersonne="'.$personne['p.id'].'" data-timestampJour="'.$timestampJour.'" /></td>';
							}
						} else {
							echo '<td><input '.$commentaire.' type="checkbox" disabled="disabled" /></td>';
						}
					}
				} else {
					/* VERIFICATION SI PROGRAMMÉ */
					$tab_repasPersonneProgrammer = array();
					$requete = new requete();
					$requete->select(array('tper_reg' => 'quantite'), 'tpg');
					$requete->select(array('jour' => 'nomAnglais'), 'j');
					$requete->where(array('tpg' => array('idPersonne' => $personne['p.id'])));
					$requete->executer_requete();
					$nbRequete++;
					$liste_repas = $requete->resultat;
					// echo $requete->requete_complete().'<br>';
					$requete->reset();
					foreach ($liste_repas as $repas) {
						$tab_repasPersonneProgrammer[$repas['j.nomAnglais']] = $repas['tpg.quantite'];
					}
					for ($i=1; $i<=$nbJour; $i++) {
						$commentaire=(empty($tab_repasCommentaire[$i]))?'':' title="'.$tab_repasCommentaire[$i].'" style="color:red;" ';
						$timestampJour = mktime(0, 0, 0, $dateMois, $i, $dateAnnee);
						if (empty($tab_repasPersonne[$i])) {
							$nomAnglais = date("D", $timestampJour);
							if (empty($tab_repasPersonneProgrammer[$nomAnglais])) {
								/* pas programmé et pas coché */
								echo '<td><input '.$commentaire.' type="checkbox" onclick="ajouter_repas_exceptionnel(this);" data-idPersonne="'.$personne['p.id'].'" data-timestampJour="'.$timestampJour.'" /></td>';
							} else {
								/* programmé et pas coché */
								echo '<td class="etat_3"><input '.$commentaire.' type="checkbox" onclick="ajouter_repas(this);" data-typeRepas="3" data-idPersonne="'.$personne['p.id'].'" data-timestampJour="'.$timestampJour.'" /></td>';
							}
						} else {
							switch ($tab_repasPersonne[$i]) {
								case 'programmer' : {
									/* programmé et coché */
									echo '<td class="etat_0"><input '.$commentaire.' type="checkbox" onclick="ajouter_repas(this);" data-idPersonne="'.$personne['p.id'].'" data-timestampJour="'.$timestampJour.'" checked /></td>';
									break; }
								case 'modifier' : {
									/* programmé, cocher puis modifié  par rapport au programmé */
									echo '<td class="etat_1"><input '.$commentaire.' type="checkbox" onclick="ajouter_repas(this);" data-idPersonne="'.$personne['p.id'].'" data-timestampJour="'.$timestampJour.'" checked /></td>';
									break; }
								case 'exceptionnel' : {
									/* pas programmé mais coché */
									echo '<td class="etat_2"><input '.$commentaire.' type="checkbox" onclick="ajouter_repas_exceptionnel(this);" data-idPersonne="'.$personne['p.id'].'" data-timestampJour="'.$timestampJour.'" checked /></td>';
									break; }
								default :
									echo '#### PROBLEME TABLEAU RECAPITULATIF ####';
							}
						}
					}
				}
				echo '<th><a onclick="details({\'path\':\'client\', \'fonctions\':\'detailsClient\', \'session\':\'all\', \'id\':'.$personne['p.id'].'});">'.$personne['p.nom'].' '.$personne['p.prenom'].'</a></th></tr>';
				$nbLignes++;
			}
			echo '</table>';
		}
	} else {
		echo '<h2>Aucune tournée n\'est déclarée.</h2>';
	}
	echo $nbRequete.' requetes en '.date('s', time()-$debutTimestamp).' secondes';
}