<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$id = (is_numeric($_GET['id']))?$_GET['id']:0;
	$requete = new requete();
	$requete->delete('ressource', array('ressource' => array('id' => $id)));
	// echo $requete->requete_complete();
	$requete->executer_requete();
	$erreur = array_merge($erreur, $requete->liste_erreurs);
	unset($requete);
	
	echo '<p>La ressource a été supprimée.</p>';
	
	include('listerRessources.php');
}