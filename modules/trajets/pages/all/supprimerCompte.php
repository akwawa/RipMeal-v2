<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if ($_SESSION['rang'] == 'Administrateur') {
		$id = (empty($_GET['id']))?false:$_GET['id'];
		if ($id == $_SESSION['id']) {
			echo '<p class="erreur">Vous ne pouvez pas supprimer votre compte.</p>';
		} else {
			$requete_compte = new requete();
			$requete_compte->delete('membre', array('membre' => array('id' => $id)));
			// echo $requete_compte->requete_complete();
			$requete_compte->executer_requete();
			$erreur = array_merge($erreur, $requete_compte->liste_erreurs);
			unset($requete_compte);
			echo '<p>Le compte a bien été supprimé.</p>';
		}
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}include_once('listerComptes.php');
}