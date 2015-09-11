<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		$tab_tables = array('per_reg', 'per_boi', 'per_rem', 'per_sup');
		$idPersonne = $_POST['idPersonne'];
		$timestampJour = $_POST['timestampJour'];

		$requete_calendrier = new requete();
		$requete_calendrier->select('calendrier', 'c');
		$requete_calendrier->where(array('c' => array('annee' => date("Y", $timestampJour), 'mois' => date("m", $timestampJour), 'jour' => date("d", $timestampJour))));
		$requete_calendrier->executer_requete();
		$liste_calendrier = $requete_calendrier->resultat;
		unset($requete_calendrier);
		
		foreach ($liste_calendrier as $calendrier) {
			foreach ($tab_tables as $table) {
				$requete_repas = new requete();
				$requete_repas->delete($table, array($table => array('idPersonne' => $idPersonne, 'idCalendrier' => $calendrier['id'])));
				// echo $requete_repas->requete_complete();
				$requete_repas->executer_requete();
				unset($requete_repas);
			}
		}
	} else {
		echo 'erreur importation API';
	}
} else {
	echo 'erreur connexion';
}