<?php
/***** IL FAUT GERER l'ID DU JOUR *****/
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$tab_infos = array('regime', 'boisson', 'remplacement', 'supplement');
	$tab_jours = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
	$tab_typeRepas = array('MIDI', 'SOIR');
	$niveau = (empty($_POST['niveau']))?false:$_POST['niveau'];
	$idPersonne = (empty($_POST['idPersonne']))?((empty($_GET['idPersonne']))?0:$_GET['idPersonne']):$_POST['idPersonne'];
	if ($niveau) {
		foreach ($tab_infos as $table) {
			$requete = new requete();
			$requete->delete('tper_'.substr($table, 0, 3), array('tper_'.substr($table, 0, 3) => array('idPersonne' => $idPersonne)));
			// echo $requete->requete_complete().'<br>';
			$requete->executer_requete();
			$erreur = array_merge($erreur, $requete->liste_erreurs);
			unset($requete);
		
			$requete_repas = new requete();
			$requete_repas->select($table, 't');
			// echo $requete_repas->requete_complete();
			$requete_repas->executer_requete();
			$liste_repas = $requete_repas->resultat;
			$erreur = array_merge($erreur, $requete_repas->liste_erreurs);
			unset($requete_repas);
			foreach($liste_repas as $repas) {
				foreach ($tab_jours as $a => $jour) {
					foreach ($tab_typeRepas as $typeRepas) {
						if (!empty($_POST[$table.'_'.$typeRepas.'_'.$jour.'_'.$repas['id']])) {
							$idJour = ($typeRepas == 'SOIR')?$a+8:$a+1;
							$requete = new requete();
							$requete->insert('tper_'.substr($table, 0, 3), array('idPersonne' => $idPersonne, 'idJour' => $idJour, 'id'.ucfirst($table) => $repas['id'], 'quantite' => $_POST[$table.'_'.$typeRepas.'_'.$jour.'_'.$repas['id']]));
							// echo $requete->requete_complete().'<br>';
							$requete->executer_requete();
							$erreur = array_merge($erreur, $requete->liste_erreurs);
							unset($requete);
						}
					}
				}
			}
		}
		echo '<p>Les repas de cette personne ont bien été modifiés.</p>';
	} else {
		echo '<form action="?menu=client&amp;sousmenu=modifierClientRepas" method="post"><input type="hidden" name="niveau" id="niveau" value="2"><input type="hidden" name="idPersonne" id="idPersonne" value="'.$idPersonne.'"><table><tr><th colspan="2" rowspan="2">&nbsp;</th>';
		foreach ($tab_jours as $jour) { echo '<th colspan="2">'.$jour.'</th>'; }
		echo '<th colspan="2" rowspan="2">&nbsp;</th></tr><tr>';
		foreach ($tab_jours as $jour) { echo '<th>MIDI</th><th>SOIR</th>'; }
		echo '</tr>';
		foreach ($tab_infos as $table) {
			$requete = new requete();
			$requete->select('tper_'.substr($table, 0, 3), 't');
			$requete->select(array('personne' => 'id'), 'p');
			$requete->where(array('p' => array('id' => $idPersonne)));
			// echo $requete->requete_complete().'<br>';
			$requete->executer_requete();
			$liste_repasPersonne = $requete->resultat;
			unset($requete);
			$tab_valeur = array();
			foreach ($liste_repasPersonne as $ligne) {
				$tab_valeur[$ligne['id'.ucfirst($table)]][$ligne['idJour']] = $ligne['quantite'];
				// echo '-'.$ligne['id'.ucfirst($table)].' '.$ligne['idJour'].'<br>';
			}
		
			$requete_repas = new requete();
			$requete_repas->select($table, 't');
			// echo $requete_repas->requete_complete();
			$requete_repas->executer_requete();
			$liste_repas = $requete_repas->resultat;
			unset($requete_repas);
			$premier = true;
			foreach($liste_repas as $repas) {
				echo '<tr>';
				if ($premier) {
					echo '<th rowspan="'.count($liste_repas).'">'.$table.'</th>';
					// $premier = false;
				}
				echo '<th>'.$repas['nom'].'</th>';
				for ($i=0;$i<7;$i++) {
					$valMidi = (empty($tab_valeur[$repas['id']][$i+1]))?false:$tab_valeur[$repas['id']][$i+1];
					$valSoir = (empty($tab_valeur[$repas['id']][$i+8]))?false:$tab_valeur[$repas['id']][$i+8];
					if ($valMidi) { echo '<td style="background-color:blue;">';
					} else { echo '<td>'; }
					echo '<input type="number" name="'.$table.'_MIDI_'.$tab_jours[$i].'_'.$repas['id'].'" id="'.$table.'_MIDI_'.$tab_jours[$i].'_'.$repas['id'].'" value="'.$valMidi.'"></td>';
					if ($valSoir) { echo '<td style="background-color:blue;">';
					} else { echo '<td>'; }
					echo '<input type="number" name="'.$table.'_SOIR_'.$tab_jours[$i].'_'.$repas['id'].'" id="'.$table.'_SOIR_'.$tab_jours[$i].'_'.$repas['id'].'" value="'.$valSoir.'"></td>';
				}
				echo '<th>'.$repas['nom'].'</th>';
				if ($premier) {
					echo '<th rowspan="'.count($liste_repas).'">'.$table.'</th>';
					$premier = false;
				}
				echo '</tr>';
			}
		}
		echo '<tr><th colspan="16"><input type="submit" value="Modifier le repas"></th></tr></table></form>';
	}
}