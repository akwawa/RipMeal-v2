<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		$tab_tables = array('per_reg', 'per_boi', 'per_rem', 'per_sup');
		$tab_infos = array('regime', 'boisson', 'remplacement', 'supplement');
		$tab_typeCalendrier = array('MIDI', 'SOIR');
		$tab_idCalendrier = array();
		$idPersonne = $_POST['idPersonne'];
		$timestampJour = $_POST['timestampJour'];
		$table = $_POST['table'];
		$id = $_POST['id'];
		$typeJour = $_POST['typeJour'];
		$quantite = (empty($_POST['quantite']))?0:$_POST['quantite'];
		$quantiteRemp = (empty($_POST['quantiteRemp']))?0:$_POST['quantiteRemp'];
		$typeRepas = $_POST['typeRepas'];
		
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
		
		if (in_array($table, $tab_infos) && in_array($typeJour, $tab_typeCalendrier)) {
			$idCalendrier = ($typeJour == "MIDI" ) ? $tab_idCalendrier[0] : $tab_idCalendrier[1];
			$requete_ajoutRepas = new requete();
			if ($table == "regime") {
				$requete_ajoutRepas->insert('per_'.substr($table, 0, 3), array('idPersonne' => $idPersonne, 'idCalendrier' => $idCalendrier, 'id'.ucfirst($table) => $id, 'quantite' => $quantite, 'quantiteRemp' => $quantiteRemp, 'typeRepas' => $typeRepas));
			} else {
				$requete_ajoutRepas->insert('per_'.substr($table, 0, 3), array('idPersonne' => $idPersonne, 'idCalendrier' => $idCalendrier, 'id'.ucfirst($table) => $id, 'quantite' => $quantite, 'quantiteRemp' => $quantiteRemp));
			}
			// echo $requete_ajoutRepas->requete_complete();
			$requete_ajoutRepas->executer_requete();
			unset($requete_ajoutRepas);
		}
	} else {
		echo 'erreur importation API';
	}
} else {
	echo 'erreur connexion';
}