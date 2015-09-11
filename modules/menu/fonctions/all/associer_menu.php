<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if ($_SESSION['rang'] == 'Administrateur') {
		$idRegime = (empty($_POST['idRegime']))?false:$_POST['idRegime'];
		$timestampJour = (empty($_POST['timestampJour']))?false:$_POST['timestampJour'];
		$typeCal = (empty($_POST['typeCal']))?false:$_POST['typeCal'];
		$idMenu = (empty($_POST['idMenu']))?false:$_POST['idMenu'];

		if (file_exists('../../../../fonctions/api.class.php')) {
			require_once('../../../../fonctions/api.class.php');
			if ($idRegime && $timestampJour && $typeCal && $idMenu) {
				$tab_typeCalendrier = array('MIDI', 'SOIR');
				/* ajout et recherche des calendriers */
				foreach ($tab_typeCalendrier as $typeCalendrier) {
					$requete_calendrier = new requete();
					$requete_calendrier->select('calendrier', 'c');
					$requete_calendrier->where(array('c' => array('annee' => date("Y", $timestampJour), 'mois' => date("m", $timestampJour), 'jour' => date("d", $timestampJour), 'typeCalendrier' => $typeCalendrier)));
					$requete_calendrier->executer_requete();
					$liste_calendrier = $requete_calendrier->resultat;
					unset($requete_calendrier);
					if (!$liste_calendrier) {
						$requete_calendrier = new requete();
						$requete_calendrier->insert('calendrier', array('annee' => date("Y", $timestampJour), 'mois' => date("m", $timestampJour), 'jour' => date("d", $timestampJour), 'typeCalendrier' => $typeCalendrier, 'timestamp' => $timestampJour));
						$requete_calendrier->executer_requete();
						unset($requete_calendrier);
						/* ajout */
						$requete_calendrier = new requete();
						$requete_calendrier->select('calendrier', 'c');
						$requete_calendrier->where(array('c' => array('annee' => date("Y", $timestampJour), 'mois' => date("m", $timestampJour), 'jour' => date("d", $timestampJour), 'typeCalendrier' => $typeCalendrier)));
						$requete_calendrier->executer_requete();
						$liste_calendrier = $requete_calendrier->resultat;
						unset($requete_calendrier);
					}
					$tab_idCalendrier[] = $liste_calendrier[0]['id'];
				}
				/**************************************/
				if ($typeCal == "MIDI") {
					$idCalendrier = $tab_idCalendrier[0];
				} elseif ($typeCal == "SOIR") {
					$idCalendrier = $tab_idCalendrier[1];
				} else {
					$idCalendrier = 0;
				}
				$requete_repas = new requete();
				$requete_repas->delete('menu_regime', array('menu_regime' => array('idMenu' => $idMenu, 'idRegime' => $idRegime, 'idCalendrier' => $idCalendrier)));
				$requete_repas->executer_requete();
				unset($requete_repas);
				
				$requete = new requete();
				$requete->insert('menu_regime', array('idMenu' => $idMenu, 'idRegime' => $idRegime, 'idCalendrier' => $idCalendrier));
				// echo $requete->requete_complete().'<br><br>';
				$requete->executer_requete();
				unset($requete);
				$retour['resultat'] = '<p>Le menu a bien été associé.</p>';
			} else {
				$retour['erreur'] = '<p class="erreur">Un problème est survenu.</p>';
			}
		} else {
			$retour['erreur'] = '<p class="erreur">Un problème est survenu avec l\'importation de l\'API.</p>';
		}
	} else {
		$retour['erreur'] = '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}
	
	echo json_encode($retour);
}