<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		$idPersonne = (empty($_POST['idPersonne']))?0:$_POST['idPersonne'];
		$ordre = (empty($_POST['ordre']))?0:$_POST['ordre'];
		// 1 la personne monte, 2 la personne descend
		
		$requete = new requete();
		$requete->select(array('personne' => array('numTournee', 'numPerTou')), 'p');
		$requete->where(array('p' => array('id' => $idPersonne)));
		$requete->grand_tableau = false;
		// echo $requete->requete_complete().'<br><br>';
		$requete->executer_requete();
		$liste = $requete->resultat;
		unset($requete);
		
		if ($liste) {
			if ($ordre == 1) {
				$futurNum = $liste['p.numPerTou']-1;
			} elseif ($ordre == 2) {
				$futurNum = $liste['p.numPerTou']+1;
			}
			$requete = new requete();
			$requete->requete_direct('UPDATE v2__personne SET numPerTou = '.$liste['p.numPerTou'].' WHERE v2__personne.numTournee = '.$liste['p.numTournee'].' AND numPerTou = '.$futurNum);
			// echo $requete->requete_complete().'<br>';
			$requete->executer_requete();
			unset($requete);
			$requete = new requete();
			$requete->requete_direct('UPDATE v2__personne SET numPerTou = '.$futurNum.' WHERE v2__personne.id = '.$idPersonne);
			// echo $requete->requete_complete().'<br>';
			$requete->executer_requete();
			unset($requete);
			$retour['resultat'] = true;
		}
	} else {
		$retour['resultat'] = 'erreur importation API';
	}
	echo json_encode($retour['resultat']);
}