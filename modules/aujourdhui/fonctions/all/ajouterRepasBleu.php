<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		$tab_tables = array('per_reg', 'per_boi', 'per_rem', 'per_sup');
		$tab_infos = array('regime', 'boisson', 'remplacement', 'supplement');
		$tab_typeCalendrier = array('MIDI', 'SOIR');
		$tab_idCalendrier = array();
		$idPersonne = (empty($_POST['idPersonne']))?0:$_POST['idPersonne'];
		$timestampJour = (empty($_POST['timestampJour']))?0:$_POST['timestampJour'];
		// $idPersonne = 10;
		// $timestampJour = 3005495245;
		
		/* ajout et recherche des calendriers */
		foreach ($tab_typeCalendrier as $typeCalendrier) {
			$requete_calendrier = new requete();
			$requete_calendrier->select('calendrier', 'c');
			$requete_calendrier->where(array('c' => array('annee' => date("Y", $timestampJour), 'mois' => date("m", $timestampJour), 'jour' => date("d", $timestampJour), 'typeCalendrier' => $typeCalendrier)));
			// echo $requete_calendrier->requete_complete();
			$requete_calendrier->executer_requete();
			$liste_calendrier = $requete_calendrier->resultat;
			unset($requete_calendrier);
			if (empty($liste_calendrier)) {
				$requete_calendrier = new requete();
				$requete_calendrier->insert('calendrier', array('annee' => date("Y", $timestampJour), 'mois' => date("m", $timestampJour), 'jour' => date("d", $timestampJour), 'typeCalendrier' => $typeCalendrier, 'timestamp' => $timestampJour));
				// echo $requete_calendrier->requete_complete().'<br><br>';
				$requete_calendrier->executer_requete();
				unset($requete_calendrier);
				/* ajout */
				$requete_calendrier = new requete();
				$requete_calendrier->select('calendrier', 'c');
				$requete_calendrier->where(array('c' => array('annee' => date("Y", $timestampJour), 'mois' => date("m", $timestampJour), 'jour' => date("d", $timestampJour), 'typeCalendrier' => $typeCalendrier)));
				// echo $requete_calendrier->requete_complete().'<br><br>';
				$requete_calendrier->executer_requete();
				$liste_calendrier = $requete_calendrier->resultat;
				unset($requete_calendrier);
				// var_dump($liste_calendrier);
			}
			$tab_idCalendrier[] = $liste_calendrier[0]['id'];
		}
		/**************************************/
		
		foreach ($tab_infos as $table) {
			$requete_repas = new requete();
			$requete_repas->select('tper_'.substr($table, 0, 3), 't');
			$requete_repas->select('jour', 'j');
			$requete_repas->where(array('t' => array('idPersonne' => $idPersonne)));
			$requete_repas->where(array('j' => array('nomAnglais' => date("D", $timestampJour))));
			// echo $requete_repas->requete_complete();
			$requete_repas->executer_requete();
			$liste_repas = $requete_repas->resultat;
			// $erreur = array_merge($erreur, $requete_repas->liste_erreurs);
			unset($requete_repas);
			foreach($liste_repas as $repas) {
				$idCalendrier = ($repas['id'] < 8 ) ? $tab_idCalendrier[0] : $tab_idCalendrier[1];
				$requete_ajoutRepas = new requete();
				if ($table == 'regime') {
					$requete_ajoutRepas->insert('per_'.substr($table, 0, 3), array('idPersonne' => $idPersonne, 'idCalendrier' => $idCalendrier, 'id'.ucfirst($table) => $repas['id'.ucfirst($table)], 'quantite' => $repas['quantite'], 'typeRepas' => 'programmer'));
				} else {
					$requete_ajoutRepas->insert('per_'.substr($table, 0, 3), array('idPersonne' => $idPersonne, 'idCalendrier' => $idCalendrier, 'id'.ucfirst($table) => $repas['id'.ucfirst($table)], 'quantite' => $repas['quantite']));
				}
				// echo $requete_ajoutRepas->requete_complete();
				$requete_ajoutRepas->executer_requete();
				unset($requete_ajoutRepas);
			}
		}
	} else {
		echo 'erreur importation API';
	}
} else {
	echo 'erreur connexion';
}