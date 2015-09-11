<?php

if (empty($_SESSION)) { session_start(); }
if ($_SESSION) {
	if ($_SESSION['rang'] == 'Administrateur') {
		$type = (empty($_GET['type']))?'regime':$_GET['type'];
		$nom = (empty($_POST['nom']))?false:$_POST['nom'];
		$nomComplet = (empty($_POST['nomComplet']))?$nom:$_POST['nomComplet'];
		$couleur = (empty($_POST['couleur']))?'000':substr($_POST['couleur'], 1);

		if ($nom) {
			$requete_membre = new requete();
			if ($type == 'regime') {
				$requete_membre->insert($type, array('nom' => $nom, 'nomComplet' => $nomComplet, 'couleur' => $couleur));
			} else {
				$requete_membre->insert($type, array('nom' => $nom));
			}
			// echo $requete_membre->requete_complete();
			$requete_membre->executer_requete();
			$erreur = array_merge($erreur, $requete_membre->liste_erreurs);
			unset($requete_membre);
			echo '<p>Le '.$type.' a bien été créé.</p>';
		} else {
			echo '<form action="?menu=gestion&amp;sousmenu=ajouter&amp;type='.$type.'" method="post"><p><label for="nom">Nom :</label><input type="text" name="nom" id="nom" value="'.$nom.'" required></p>';
			if ($type == 'regime') {
				echo '<p><label>Nom complet :<input type="text" name="nomComplet" id="nomComplet" value="'.$nomComplet.'" maxlength="100" /></p>';
				echo '<p><label>Couleur :<input type="color" name="couleur" id="couleur" value="#'.$couleur.'" required /></p>';
			}
			echo '<p><input type="submit" value="Ajouter"></p></form>';
		}
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}
	include('lister.php');
}