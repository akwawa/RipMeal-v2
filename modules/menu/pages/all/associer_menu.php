<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if ($_SESSION['rang'] == 'Administrateur') {
		$idRegime = (empty($_GET['idRegime']))?false:$_GET['idRegime'];
		$timestampJour = (empty($_GET['timestampJour']))?false:$_GET['timestampJour'];
		$typeCal = (empty($_GET['typeCal']))?false:$_GET['typeCal'];
		$idMenu = (empty($_POST['idMenu']))?false:$_POST['idMenu'];

		if ($idRegime && $timestampJour) {
			if ($idMenu) {
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
				// var_dump($tab_idCalendrier);
				$requete = new requete();
				$requete->insert('menu_regime', array('idMenu' => $idMenu, 'idRegime' => $idRegime, 'idCalendrier' => $idCalendrier));
				// echo $requete->requete_complete().'<br><br>';
				$requete->executer_requete();
				unset($requete);
				echo '<p>Le menu a bien été associé.</p>';
				include('calendrier.php');
			} else {
				echo '<form action="?'.$_SERVER['QUERY_STRING'].'" method="post">';

				$requete = new requete();
				$requete->alias = true;
				$requete->select('menu', 'm');
				$requete->select(array('menu_entree' => 'nom'), 'me');
				$requete->select(array('menu_viande' => 'nom'), 'mv');
				$requete->select(array('menu_legume' => 'nom'), 'ml');
				$requete->select(array('menu_fromage' => 'nom'), 'mf');
				$requete->select(array('menu_dessert' => 'nom'), 'md');
				// echo $requete->requete_complete().'<br><br>';
				$requete->executer_requete();
				$liste = $requete->resultat;
				unset($requete);
					
				$nombre = count($liste);
				// var_dump($liste);
				if ($nombre > 0) {
					echo '<p><label for="idMenu">Menu :</label><select name="idMenu" id="idMenu">';
					for ($i=0; $i<$nombre;$i++) {
						echo '<option value="'.$liste[$i]['id'].'">'.$liste[$i]['me.nom'].' | '.$liste[$i]['mv.nom'].' | '.$liste[$i]['ml.nom'].' | '.$liste[$i]['mf.nom'].' | '.$liste[$i]['md.nom'].'</option>';
					}
					echo '</select></p>';
				} else {
					echo '<p>Aucun menu n\'est enregistré.</p>';
				}
				echo '<p><input type="hidden" name="idRegime" id="idRegime" value="'.$idRegime.'" /><input type="hidden" name="timestampJour" id="timestampJour" value="'.$timestampJour.'" /><input type="submit" value="Associer le menu"></p></form>';
			}
		} else {
			echo '<p class="erreur">Un problème est survenu.</p>';
		}
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}
}