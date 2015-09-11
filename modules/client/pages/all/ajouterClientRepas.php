<?php
/***** IL FAUT GERER l'ID DU JOUR *****/
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$tab_infos = array('regime', 'boisson', 'remplacement', 'supplement');
	$niveau = (empty($_POST['niveau']))?false:$_POST['niveau'];
	$idPersonne = (empty($_POST['idPersonne']))?$info_personne[0]['p.id']:$_POST['idPersonne'];
	if ($niveau) {
		$jour = (empty($_POST['jour']))?false:$_POST['jour'];
		if ($jour) {
			// var_dump($jour);
			foreach ($tab_infos as $table) {
				$requete_repas = new requete();
				$requete_repas->select($table, 't');
				// echo $requete_repas->requete_complete();
				$requete_repas->executer_requete();
				$liste_repas = $requete_repas->resultat;
				$erreur = array_merge($erreur, $requete_repas->liste_erreurs);
				unset($requete_repas);
				foreach($liste_repas as $repas) {
					if (!empty($_POST[$table.'_MIDI_'.$repas['id']])) {
						foreach ($jour as $idJour) {
							$requete = new requete();
							$requete->insert('tper_'.substr($table, 0, 3), array('idPersonne' => $idPersonne, 'idJour' => $idJour, 'id'.ucfirst($table) => $repas['id'], 'quantite' => $_POST[$table.'_MIDI_'.$repas['id']]));
							// echo $requete->requete_complete().'<br>';
							$requete->executer_requete();
							$erreur = array_merge($erreur, $requete->liste_erreurs);
							unset($requete);
						}
					}
					if (!empty($_POST[$table.'_SOIR_'.$repas['id']])) {
						foreach ($jour as $idJour) {
							$requete = new requete();
							$requete->insert('tper_'.substr($table, 0, 3), array('idPersonne' => $idPersonne, 'idJour' => $idJour+7, 'id'.ucfirst($table) => $repas['id'], 'quantite' => $_POST[$table.'_SOIR_'.$repas['id']]));
							// echo $requete->requete_complete().'<br>';
							$requete->executer_requete();
							$erreur = array_merge($erreur, $requete->liste_erreurs);
							unset($requete);
						}
					}
				}
			}
			echo '<p>Les repas de cette personne ont bien été enregistrés.</p>';
		}
	} else {
		echo '<form action="?menu=client&amp;sousmenu=ajouterClientRepas" method="post"><input type="hidden" name="niveau" id="niveau" value="2"><input type="hidden" name="idPersonne" id="idPersonne" value="'.$idPersonne.'"><p><label><input type="checkbox" name="jour[]" value="1">Lundi</label><br><label><input type="checkbox" name="jour[]" value="2">Mardi</label><br><label><input type="checkbox" name="jour[]" value="3">Mercredi</label><br><label><input type="checkbox" name="jour[]" value="4">Jeudi</label><br><label><input type="checkbox" name="jour[]" value="5">Vendredi</label><br><label><input type="checkbox" name="jour[]" value="6">Samedi</label><br><label><input type="checkbox" name="jour[]" value="7">Dimanche</label></p><table><tr><th colspan="2">&nbsp;</th><th>MIDI</th><th>SOIR</th></tr>';
		foreach ($tab_infos as $table) {
			$requete_repas = new requete();
			$requete_repas->select($table, 't');
			// echo $requete_repas->requete_complete();
			$requete_repas->executer_requete();
			$liste_repas = $requete_repas->resultat;
			$erreur = array_merge($erreur, $requete_repas->liste_erreurs);
			unset($requete_repas);
			$premier = true;
			foreach($liste_repas as $repas) {
				echo '<tr>';
				if ($premier) {
					echo '<th rowspan="'.count($liste_repas).'">'.$table.'</th>';
					$premier = false;
				}
				echo '<th>'.$repas['nom'].'</th><td><input type="number" name="'.$table.'_MIDI_'.$repas['id'].'" id="'.$table.'_MIDI_'.$repas['id'].'"></td><td><input type="number" name="'.$table.'_SOIR_'.$repas['id'].'" id="'.$table.'_SOIR_'.$repas['id'].'"></td></tr>';
			}
		}
		echo '<tr><th colspan="4"><input type="submit" value="Ajouter le repas"></th></tr></table></form>';
	}
}