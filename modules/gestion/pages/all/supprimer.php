<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if ($_SESSION['rang'] == 'Administrateur') {
		$id = (empty($_GET['id']))?false:$_GET['id'];
		$type = (empty($_GET['type']))?false:$_GET['type'];
		
		$requete_compte = new requete();
		$requete_compte->delete($type, array($type => array('id' => $id)));
		// echo $requete_compte->requete_complete();
		$requete_compte->executer_requete();
		$erreur = array_merge($erreur, $requete_compte->liste_erreurs);
		unset($requete_compte);
		echo '<p>Le '.$type.' a bien été supprimé.</p>';
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}
	include('lister.php');
}