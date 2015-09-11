<?php
if (empty($_SESSION)) { session_start(); }

$retour = array();

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		$tab_infos = array('regime', 'boisson', 'remplacement', 'supplement');
		$tab_idCalendrier = array();
		$idPersonne = (empty($_POST['idPersonne']))?0:$_POST['idPersonne'];
		$timestampJour = (empty($_POST['timestampJour']))?0:$_POST['timestampJour'];
		
		$retour['corps'] = '<form action="#" onsubmit="return ajouter_repas_exceptionnel_validation(this);" method="POST"><input type="hidden" name="idPersonne" id="idPersonne" value="'.$idPersonne.'"><input type="hidden" name="timestampJour" id="timestampJour" value="'.$timestampJour.'"><input type="hidden" name="typeRepas" id="typeRepas" value="modifier"><table><tr><th colspan="2">&nbsp;</th><th>MIDI</th><th>SOIR</th><th>REMP MIDI</th><th>REMP SOIR</th></tr>';
		$liste_repas_prevus = array();
		foreach ($tab_infos as $table) {
			$requete_repas = new requete();
			$requete_repas->select('per_'.substr($table, 0, 3), 'pg');
			$requete_repas->select('calendrier', 'c');
			$requete_repas->where(array('pg' => array('idPersonne' => $idPersonne)));
			$requete_repas->where(array('c' => array('annee' => date("Y", $timestampJour), 'mois' => date("m", $timestampJour), 'jour' => date("d", $timestampJour))));
			// echo $requete_repas->requete_complete().'<br>';
			$requete_repas->executer_requete();
			$resultat = $requete_repas->resultat;
			$liste_repas_prevus[$table] = array();
			foreach ($resultat as $ligne) {
				// echo $ligne['id'.ucfirst($table)];
				// $liste_repas_prevus[$table][$ligne["typeCalendrier"]] = array();
				$liste_repas_prevus[$table][$ligne["typeCalendrier"]][$ligne['id'.ucfirst($table)]]["NORMAL"] = $ligne['quantite'];
				$liste_repas_prevus[$table][$ligne["typeCalendrier"]][$ligne['id'.ucfirst($table)]]["REMP"] = $ligne['quantiteRemp'];
			}
			unset($requete_repas);
		}
		// var_dump($liste_repas_prevus['regime']['SOIR']);
		foreach ($tab_infos as $table) {	
			$requete_repas = new requete();
			$requete_repas->select($table, 't');
			// echo $requete_repas->requete_complete();
			$requete_repas->executer_requete();
			$liste_repas = $requete_repas->resultat;
			unset($requete_repas);
			$premier = true;
			foreach($liste_repas as $repas) {
				$valeurMidi = (isset($liste_repas_prevus[$table]["MIDI"][$repas['id']]["NORMAL"]))?$liste_repas_prevus[$table]["MIDI"][$repas['id']]["NORMAL"]:0;
				$valeurSoir = (isset($liste_repas_prevus[$table]["SOIR"][$repas['id']]["NORMAL"]))?$liste_repas_prevus[$table]["SOIR"][$repas['id']]["NORMAL"]:0;
				$valeurMidiRemp = (isset($liste_repas_prevus[$table]["MIDI"][$repas['id']]["REMP"]))?$liste_repas_prevus[$table]["MIDI"][$repas['id']]["REMP"]:0;
				$valeurSoirRemp = (isset($liste_repas_prevus[$table]["SOIR"][$repas['id']]["REMP"]))?$liste_repas_prevus[$table]["SOIR"][$repas['id']]["REMP"]:0;
			
				$retour['corps'] .= '<tr>';
				if ($premier) {
					$retour['corps'] .= '<th rowspan="'.count($liste_repas).'">'.$table.'</th>';
					$premier = false;
				}
				$retour['corps'] .= '<th>'.$repas['nom'].'</th>';
				
				$retour['corps'] .= ($valeurMidi > 0)?'<td style="background-color:blue;">':'<td>';
				$retour['corps'] .= '<input type="number" data-id="'.$repas['id'].'" data-table="'.$table.'" data-typeJour="MIDI" value="'.$valeurMidi.'" min="0"></td>';
				$retour['corps'] .= ($valeurSoir > 0)?'<td style="background-color:blue;">':'<td>';
				$retour['corps'] .= '<input type="number" data-id="'.$repas['id'].'" data-table="'.$table.'" data-typeJour="SOIR" value="'.$valeurSoir.'" min="0" /></td>';
				$retour['corps'] .= ($valeurMidiRemp > 0)?'<td style="background-color:blue;">':'<td>';
				$retour['corps'] .= '<input type="number" data-remp="true" id="remp_MIDI_'.$repas['id'].'_'.$table.'" data-id="'.$repas['id'].'" data-table="'.$table.'" data-typeJour="MIDI" value="'.$valeurMidiRemp.'" min="0"></td>';
				$retour['corps'] .= ($valeurSoirRemp > 0)?'<td style="background-color:blue;">':'<td>';
				$retour['corps'] .= '<input type="number" data-remp="true" id="remp_SOIR_'.$repas['id'].'_'.$table.'" data-id="'.$repas['id'].'" data-table="'.$table.'" data-typeJour="SOIR" value="'.$valeurSoirRemp.'" min="0" /></td>';
				$retour['corps'] .= '</tr>';
			}
		}
		$retour['corps'] .= '<tr><th colspan="6"><input type="submit" value="Ajouter le repas"></th></tr></table></form>';
	} else {
		$retour['corps'] = 'erreur importation API';
	}
} else {
	$retour['corps'] = 'erreur connexion';
}
// var_dump($retour);
echo json_encode($retour);