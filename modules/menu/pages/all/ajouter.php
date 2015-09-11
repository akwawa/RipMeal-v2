<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if ($_SESSION['rang'] == 'Administrateur') {
		$type = (empty($_GET['type']))?false:$_GET['type'];
		$nom = (empty($_POST['nom']))?false:$_POST['nom'];

		if ($type) {
			if ($nom) {
				$requete_membre = new requete();
				$requete_membre->insert('menu_'.$type, array('nom' => $nom));
				// echo $requete_membre->requete_complete();
				$requete_membre->executer_requete();
				$erreur = array_merge($erreur, $requete_membre->liste_erreurs);
				unset($requete_membre);
				echo '<p>Le '.$type.' a bien été créé.</p>';
			} else {
				echo '<form action="?menu=menu&amp;sousmenu=ajouter&amp;type='.$type.'" method="post"><p><label for="nom">Nom :</label><input type="text" name="nom" id="nom" value="'.$nom.'"></p></p><p><input type="submit" value="Ajouter"></p></form>';
			}
		} else {
			echo '<p class="erreur">Un problème de type est apparus.</p>';
		}
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}
	include('lister.php');
}