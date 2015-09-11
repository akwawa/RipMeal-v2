<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if ($_SESSION['rang'] == 'Administrateur') {
		$login = (empty($_POST['login']))?false:$_POST['login'];
		$mdp = (empty($_POST['password']))?false:$_POST['password'];
		$rangInput = (empty($_POST['rang']))?false:$_POST['rang'];

		if ($login && $mdp && $rangInput) {
			$requete_membre = new requete();
			$requete_membre->insert('membre', array('nom' => $login, 'mdp' => array('VALEUR' => $mdp, 'SALAGE' => true), 'dateCreation' => time(), 'rang' => $rangInput));
			// echo $requete_membre->requete_complete();
			$requete_membre->executer_requete();
			$erreur = array_merge($erreur, $requete_membre->liste_erreurs);
			unset($requete_membre);
			echo '<p>Le compte a bien été créé.</p>';
		} else {
			echo '<form action="?menu=compte&amp;sousmenu=ajouterCompte" method="post"><p><label for="login">Login :</label><input type="text" name="login" id="login" value="'.$login.'"></p><p><label for="password">Mot de passe :</label><input type="password" name="password" id="password" value="'.$mdp.'"></p><p><label>Poste :</label>';
			foreach($rang as $cle => $valeur) {
				echo '<input type="radio" name="rang" value="'.$cle.'" '.(($cle == $rangInput)?'checked':'').'>'.$valeur.'&nbsp;';
			}
			echo '</p><p><input type="submit" value="Ajouter le compte"></p></form>';
		}
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}
	include_once('listerComptes.php');
}