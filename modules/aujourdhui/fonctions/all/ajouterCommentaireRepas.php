<?php
if (empty($_SESSION)) { session_start(); }

$retour = array();

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		$idPersonne = (empty($_POST['idPersonne']))?0:$_POST['idPersonne'];
		$timestampJour = (empty($_POST['timestampJour']))?0:$_POST['timestampJour'];
		$commentaire = (empty($_POST['commentaire']))?"":$_POST['commentaire'];
		$typeAction = (empty($_POST['typeAction']))?false:$_POST['typeAction'];
		$table='commentaire';
		$typeCalendrier='MIDI';

		/* ajout et recherche des calendriers */
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
		$idCalendrier = $tab_idCalendrier[0];

		if ($typeAction=="suppression") {			
			$requete_repas = new requete();
			$requete_repas->delete($table, array($table => array('idPersonne' => $idPersonne, 'idCalendrier' => $idCalendrier)));
			// echo $requete_repas->requete_complete();
			$requete_repas->executer_requete();
			unset($requete_repas);
		}elseif ($typeAction=="ajout") {
			$requete_repas = new requete();
			$requete_repas->insert($table, array('idPersonne' => $idPersonne, 'idCalendrier' => $idCalendrier, 'commentaire' => $commentaire));
			$requete_repas->executer_requete();
			unset($requete_repas);
		}else{
			$requete = new requete();
			$requete->select('commentaire', 'c');
			$requete->where(array('c' => array('idPersonne' => $idPersonne, 'idCalendrier' => $idCalendrier)));
			// echo $requete_repas->requete_complete();
			$requete->executer_requete();
			$liste_commentaire = $requete->resultat;
			$commentaire=$liste_commentaire[0]["commentaire"];
			unset($requete);

			$retour['corps'] = '<h3>Ajouter un commentaire</h3><form action="#" onsubmit="return ajouter_commentaire_validation(this);" method="POST"><input type="hidden" name="idPersonne" id="idPersonne" value="'.$idPersonne.'" /><input type="hidden" name="timestampJour" id="timestampJour" value="'.$timestampJour.'" /><textarea name="commentaire" id="commentaire">'.$commentaire.'</textarea><input type="submit" size="22" value="Ajouter le commentaire"></form>';
		}
	} else {
		$retour['corps'] = 'erreur importation API';
	}
} else {
	$retour['corps'] = 'erreur connexion';
}
echo json_encode($retour);