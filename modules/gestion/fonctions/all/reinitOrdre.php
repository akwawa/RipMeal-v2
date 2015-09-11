<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		$idTournee = (empty($_POST['idTournee']))?0:$_POST['idTournee'];
		
		$requete = new requete();
		$requete->select(array('personne' => array('id', 'numPerTou')), 'p');
		$requete->where(array('p' => array('numTournee' => $idTournee, 'corbeille' => false)));
		$requete->order('p.numPerTou');
		// echo $requete->requete_complete().'<br><br>';
		$requete->executer_requete();
		$liste = $requete->resultat;
		unset($requete);
		
		if ($liste) {
			$nbPersonnes = count($liste);
			$tab_final = array();
			for ($i=0;$i<$nbPersonnes;$i++) {
				$requete = new requete();
				$requete->update('personne', array('numPerTou' => $i));
				$requete->where(array('personne' => array('id' => $liste[$i]['p.id'])));
				// echo $requete->requete_complete().'<br>';
				$requete->executer_requete();
				unset($requete);
			}
			$retour['resultat'] = true;
		}
	} else {
		$retour['resultat'] = 'erreur importation API';
	}
	echo json_encode($retour['resultat']);
}