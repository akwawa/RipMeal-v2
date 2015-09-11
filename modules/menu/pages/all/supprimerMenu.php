<?php

if (!$_SESSION) session_start();

if ($_SESSION) {
	if ($_SESSION['rang'] == 'Administrateur') {
		$id = (empty($_GET['id']))?false:$_GET['id'];
		
		$requete_compte = new requete();
		$requete_compte->delete('menu', array('menu' => array('id' => $id)));
		$requete_compte->executer_requete();
		unset($requete_compte);
		echo '<p>Le menu a bien été supprimé.</p>';
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}
	include('listerMenu.php');
}