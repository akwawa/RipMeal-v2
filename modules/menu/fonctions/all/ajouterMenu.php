<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if ($_SESSION['rang'] == 'Administrateur') {
		//nomRegime, jour, typeCalendrier, entree, viande, legume, fromage, dessert
		$nomRegime = (empty($_POST['nomRegime']))?false:$_POST['nomRegime'];
		$jour = (empty($_POST['jour']))?false:$_POST['jour'];
		$typeCalendrier = (empty($_POST['typeCalendrier']))?false:$_POST['typeCalendrier'];
		$entree = (empty($_POST['entree']))?false:$_POST['entree'];
		$viande = (empty($_POST['viande']))?false:$_POST['viande'];
		$legume = (empty($_POST['legume']))?false:$_POST['legume'];
		$fromage = (empty($_POST['fromage']))?false:$_POST['fromage'];
		$dessert = (empty($_POST['dessert']))?false:$_POST['dessert'];
		$timestampJour = (empty($_POST['timestampJour']))?false:$_POST['timestampJour'];

		if (file_exists('../../../../fonctions/api.class.php')) {
			require_once('../../../../fonctions/api.class.php');
			if ($nomRegime && $jour && $typeCalendrier && $entree && $viande && $legume && $fromage && $dessert) {
				$requete = new requete();
				$requete->select('regime', 'r');
				$requete->where(array('r' => array('nomComplet' => $nomRegime)));
				$requete->executer_requete();
				$liste = $requete->resultat;
				unset($requete);
				if ($liste) {
					foreach ($liste as $temp) {
					// $tailleListe = count($liste);
					// for ($i=0; $i<=$tailleListe; $i++) {
						if (!empty($temp['id'])) {
						$idRegime = $temp['id'];
						$tab_jour = array('LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE');
						if (in_array($jour, $tab_jour)) {
							{ // calendrier
								$requete = new requete();
								$requete->select('calendrier', 'c');
								$requete->where(array('c' => array('annee' => date("Y", $timestampJour), 'mois' => date("m", $timestampJour), 'jour' => date("d", $timestampJour), 'typeCalendrier' => $typeCalendrier)));
								// $retour['erreur'] = $jour.' '.date("d", $timestampJour).' '.$requete->requete_complete();
								$requete->grand_tableau = false;
								$requete->executer_requete();
								$liste_calendrier = $requete->resultat;
								unset($requete);
								
								if (!$liste_calendrier) {
									$requete_calendrier = new requete();
									$requete_calendrier->insert('calendrier', array('annee' => date("Y", $timestampJour), 'mois' => date("m", $timestampJour), 'jour' => date("d", $timestampJour), 'typeCalendrier' => strtoupper($typeCalendrier), 'timestamp' => $timestampJour));
									$requete_calendrier->executer_requete();
									unset($requete_calendrier);
									/* ajout */
									$requete_calendrier = new requete();
									$requete_calendrier->select('calendrier', 'c');
									$requete_calendrier->where(array('c' => array('annee' => date("Y", $timestampJour), 'mois' => date("m", $timestampJour), 'jour' => date("d", $timestampJour), 'typeCalendrier' => strtoupper($typeCalendrier))));
									$requete_calendrier->grand_tableau = false;
									$requete_calendrier->executer_requete();
									$liste_calendrier = $requete_calendrier->resultat;
									unset($requete_calendrier);
								}
								$idCalendrier = $liste_calendrier['id'];
								// echo $idCalendrier.' '.$timestampJour.' '.date("Y", $timestampJour).' '.date("m", $timestampJour).' '.date("d", $timestampJour);
							}
							{ // entree
								$requete = new requete();
								$requete->select('menu_entree', 'e');
								$requete->where(array('e' => array('nom' => $entree)));
								$requete->grand_tableau = false;
								$requete->executer_requete();
								$liste = $requete->resultat;
								unset($requete);
								if (!$liste) {
									$requete = new requete();
									$requete->insert('menu_entree', array('nom' => $entree));
									$requete->executer_requete();
									unset($requete);
									/* ajout */
									$requete = new requete();
									$requete->select('menu_entree', 'e');
									$requete->where(array('e' => array('nom' => $entree)));
									$requete->grand_tableau = false;
									$requete->executer_requete();
									$liste = $requete->resultat;
									unset($requete);
								}
								$idEntree = $liste['id'];
								unset($liste);
							}
							{ // viande
								$requete = new requete();
								$requete->select('menu_viande', 'e');
								$requete->where(array('e' => array('nom' => $viande)));
								$requete->grand_tableau = false;
								$requete->executer_requete();
								$liste = $requete->resultat;
								unset($requete);
								if (!$liste) {
									$requete = new requete();
									$requete->insert('menu_viande', array('nom' => $viande));
									$requete->executer_requete();
									unset($requete);
									/* ajout */
									$requete = new requete();
									$requete->select('menu_viande', 'e');
									$requete->where(array('e' => array('nom' => $viande)));
									$requete->grand_tableau = false;
									$requete->executer_requete();
									$liste = $requete->resultat;
									unset($requete);
								}
								$idViande = $liste['id'];
								unset($liste);
							}
							{ // legume
								$requete = new requete();
								$requete->select('menu_legume', 'e');
								$requete->where(array('e' => array('nom' => $legume)));
								$requete->grand_tableau = false;
								$requete->executer_requete();
								$liste = $requete->resultat;
								unset($requete);
								if (!$liste) {
									$requete = new requete();
									$requete->insert('menu_legume', array('nom' => $legume));
									$requete->executer_requete();
									unset($requete);
									/* ajout */
									$requete = new requete();
									$requete->select('menu_legume', 'e');
									$requete->where(array('e' => array('nom' => $legume)));
									$requete->grand_tableau = false;
									$requete->executer_requete();
									$liste = $requete->resultat;
									unset($requete);
								}
								$idLegume = $liste['id'];
								unset($liste);
							}
							{ // fromage
								$requete = new requete();
								$requete->select('menu_fromage', 'e');
								$requete->where(array('e' => array('nom' => $fromage)));
								$requete->grand_tableau = false;
								$requete->executer_requete();
								$liste = $requete->resultat;
								unset($requete);
								if (!$liste) {
									$requete = new requete();
									$requete->insert('menu_fromage', array('nom' => $fromage));
									$requete->executer_requete();
									unset($requete);
									/* ajout */
									$requete = new requete();
									$requete->select('menu_fromage', 'e');
									$requete->where(array('e' => array('nom' => $fromage)));
									$requete->grand_tableau = false;
									$requete->executer_requete();
									$liste = $requete->resultat;
									unset($requete);
								}
								$idFromage = $liste['id'];
								unset($liste);
							}
							{ // dessert
								$requete = new requete();
								$requete->select('menu_dessert', 'e');
								$requete->where(array('e' => array('nom' => $dessert)));
								$requete->grand_tableau = false;
								$requete->executer_requete();
								$liste = $requete->resultat;
								unset($requete);
								if (!$liste) {
									$retour['erreur'] = 'ok';
									$requete = new requete();
									$requete->insert('menu_dessert', array('nom' => $dessert));
									$requete->executer_requete();
									unset($requete);
									/* ajout */
									$requete = new requete();
									$requete->select('menu_dessert', 'e');
									$requete->where(array('e' => array('nom' => $dessert)));
									$requete->grand_tableau = false;
									$requete->executer_requete();
									$liste = $requete->resultat;
									unset($requete);
								}
								$idDessert = $liste['id'];
								unset($liste);
							}
							{ // menu
								// id, idEntree, idViande, idLegume, idFromage, idDessert, supplement
								$requete = new requete();
								$requete->select('menu', 'e');
								$requete->where(array('e' => array('idEntree' => $idEntree, 'idViande' => $idViande, 'idLegume' => $idLegume, 'idFromage' => $idFromage, 'idDessert' => $idDessert)));
								$requete->grand_tableau = false;
								$requete->executer_requete();
								$liste = $requete->resultat;
								unset($requete);
								if (!$liste) {
									$requete = new requete();
									$requete->insert('menu', array('idEntree' => $idEntree, 'idViande' => $idViande, 'idLegume' => $idLegume, 'idFromage' => $idFromage, 'idDessert' => $idDessert));
									$requete->executer_requete();
									unset($requete);
									/* ajout */
									$requete = new requete();
									$requete->select('menu', 'e');
									$requete->where(array('e' => array('idEntree' => $idEntree, 'idViande' => $idViande, 'idLegume' => $idLegume, 'idFromage' => $idFromage, 'idDessert' => $idDessert)));
									$requete->grand_tableau = false;
									$requete->executer_requete();
									$liste = $requete->resultat;
									unset($requete);
								}
								$idMenu = $liste['id'];
								unset($liste);
							}
							{ // menu_regime
								// idMenu, idRegime, idCalendrier
								$requete = new requete();
								$requete->select('menu_regime', 'e');
								$requete->where(array('e' => array('idRegime' => $idRegime, 'idCalendrier' => $idCalendrier)));
								$requete->grand_tableau = false;
								$requete->executer_requete();
								$liste = $requete->resultat;
								unset($requete);
								if ($liste) {
									$requete = new requete();
									$requete->delete('menu_regime', array('menu_regime' => array('idRegime' => $idRegime, 'idCalendrier' => $idCalendrier)));
									$requete->executer_requete();
									unset($requete);
								}
								$requete = new requete();
								$requete->insert('menu_regime', array('idMenu' => $idMenu, 'idRegime' => $idRegime, 'idCalendrier' => $idCalendrier));
								$requete->executer_requete();
								// unset($requete);
								$retour['resultat'] = true;
								// $retour['resultat'] = $requete->requete_complete();
							}
						} else {
							$retour['erreur'] = 'Le jour "'.$jour.'" n\'existe pas.';
						}
					}
					}
				} else {
					$retour['erreur'] = 'Le régime "'.$nomRegime.'" n\'existe pas.';
				}
			} else {
				$retour['erreur'] = 'Un paramètre d\'entrée est incorrect est survenu.';
			}
		} else {
			$retour['erreur'] = 'Un problème est survenu avec l\'importation de l\'API.';
		}
	} else {
		$retour['erreur'] = 'Vous n\'avez pas les droits nécessaires pour effectuer cette action.';
	}
	
	echo json_encode($retour);
}