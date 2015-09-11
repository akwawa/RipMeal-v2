<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$id = (is_numeric($_GET['id']))?$_GET['id']:0;
	$actif = false;
	$corbeille = true;
	

	/**************************/
	$requete = new requete();
	$requete->select(array('personne' => array('numTournee', 'numPerTou')), 'p');
	$requete->where(array('p' =>array('id' => $id)));
	$requete->executer_requete();
	$liste = $requete->resultat;
	unset($requete);
	$numTournee = $liste[0]['p.numTournee'];
	$numPerTou = $liste[0]['p.numPerTou'];


	/**************************/
	$requete = new requete();
	$requete->requete_direct('UPDATE v2__personne SET numPerTou = numPerTou-1 WHERE v2__personne.numTournee = '.$numTournee.' AND numPerTou > '.$numPerTou);
	// echo $requete->requete_complete();
	$requete->executer_requete();
	unset($requete);


	/**************************/
	$requete = new requete();
	$requete->update('personne', array('actif' => $actif, 'corbeille' => $corbeille, 'numTournee' => 1, 'numPerTou' => -1));
	$requete->where(array('personne' =>array('id' => $id)));
	// echo $requete->requete_complete();
	$requete->executer_requete();
	$erreur = array_merge($erreur, $requete->liste_erreurs);
	unset($requete);


	/**************************/
	echo '<p>Le client a été supprimée.</p>';
	
	include('listerClients.php');
}