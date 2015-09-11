<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$id = (empty($_GET['id']))?false:$_GET['id'];
	$type = (empty($_GET['type']))?false:$_GET['type'];

	if ($_SESSION['id'] == $id || $_SESSION['rang'] == 'Administrateur') {
		$niveau = (empty($_POST['niveau']))?false:$_POST['niveau'];
		if (!$niveau) {
			$requete_membres = new requete();
			$requete_membres->select($type);
			$requete_membres->where(array($type => array('id' => $id)));
			$requete_membres->grand_tableau = false;
			// echo $requete_membres->requete_complete();
			$requete_membres->executer_requete();
			$liste_membres = $requete_membres->resultat;
			$erreur = array_merge($erreur, $requete_membres->liste_erreurs);
			unset($requete_membres);
			$nom = $liste_membres['nom'];
			$nomComplet = $liste_membres['nomComplet'];
			$couleur = $liste_membres['couleur'];
			
			echo '<form action="?menu=gestion&amp;sousmenu=modifier&amp;type='.$type.'&amp;id='.$id.'" method="post"><p><label for="nom">Nom :</label><input type="text" name="nom" id="nom" value="'.$nom.'" required/></p>';
			if ($type == 'regime') {
				echo '<p><label>Nom complet :<input type="text" name="nomComplet" id="nomComplet" value="'.$nomComplet.'" maxlength="100" /></p>';
				echo '<p><label>Couleur :<input type="color" name="couleur" id="couleur" value="#'.$couleur.'" /></p>';
			}
			echo '<p><input type="hidden" name="niveau" id="niveau" value="1"><input type="submit" value="Modifier"></p></form>';
		} else {
			$nom = (empty($_POST['nom']))?false:$_POST['nom'];
			$nomComplet = (empty($_POST['nomComplet']))?$nom:$_POST['nomComplet'];
			$couleur = (empty($_POST['couleur']))?'000':substr($_POST['couleur'], 1);
			if ($nom) {
				$requete_membre = new requete();
				if ($type == 'regime') {
					$requete_membre->update($type, array('nom' => $nom, 'nomComplet' => $nomComplet, 'couleur' => $couleur));
				} else {
					$requete_membre->update($type, array('nom' => $nom));
				}
				$requete_membre->where(array($type => array('id' => $id)));
				// echo $requete_membre->requete_complete();
				$requete_membre->executer_requete();
				$erreur = array_merge($erreur, $requete_membre->liste_erreurs);
				unset($requete_membre);
				echo '<p>Le '.$type.' a bien été modifié.</p>';
			} else {
				echo '<p class="erreur">Erreur</p>';
			}
		}
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}
	include('lister.php');
}