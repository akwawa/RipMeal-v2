<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$idPersonne = (is_numeric($_GET['idPersonne']))?$_GET['idPersonne']:0;
	$idRessource = (is_numeric($_GET['idRessource']))?$_GET['idRessource']:0;
	$requete = new requete();
	$requete->delete('personne_ressource', array('personne_ressource' => array('idPersonne' => $idPersonne, 'idRessource' => $idRessource)));
	// echo $requete->requete_complete();
	$requete->executer_requete();
	$erreur = array_merge($erreur, $requete->liste_erreurs);
	unset($requete);
	
	echo '<p>La relation a été supprimée.</p>';
	
	include('listerRelation.php');
}