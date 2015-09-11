<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$id = (is_numeric($_GET['id']))?$_GET['id']:0;
	
	$requete = new requete();
	$requete->select('personne');
	$requete->where(array('personne' => array('id' => $id)));
	$requete->executer_requete();
	$liste = $requete->resultat;
	unset($requete);
	
	if ($liste) {
		$actif = ($liste[0]['actif'])?false:true;
		$requete = new requete();
		$requete->update('personne', array('actif' => $actif, 'corbeille' => false));
		$requete->where(array('personne' => array('id' => $id)));
		$requete->executer_requete();
		unset($requete);
	}
	
	if ($actif) {
		echo '<p>Le client a été passé en inactif.</p>';
	} else {
		echo '<p>Le client a été passé en actif.</p>';
	}

	include('listerClients.php');
}