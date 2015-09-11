<?php
if (empty($_SESSION)) { session_start(); }
if ($_SESSION) {
	$id = (empty($_GET['id']))?false:$_GET['id'];
	if ($_SESSION['id'] == $id || $_SESSION['rang'] == 'Administrateur') {
		$niveau = (empty($_POST['niveau']))?false:$_POST['niveau'];
		
		if (!$niveau) {
			$requete_membres = new requete();
			$requete_membres->select('membre');
			$requete_membres->where(array('membre' => array('id' => $id)));
			$requete_membres->executer_requete();
			$liste_membres = $requete_membres->resultat;
			$erreur = array_merge($erreur, $requete_membres->liste_erreurs);
			unset($requete_membres);
			$login = $liste_membres[0]['nom'];
			$rangInput = $liste_membres[0]['rang'];
			echo '<form action="?menu=compte&amp;sousmenu=modifierCompte&amp;id='.$id.'" method="post"><p><label for="login">Login :</label><input type="text" name="login" id="login" value="'.$login.'"></p><p><label for="ancienPassword">Ancien mot de passe :</label><input type="password" name="ancienPassword" id="ancienPassword" required></p><p><label for="nouveauPassword">Nouveau mot de passe :</label><input type="password" name="nouveauPassword" id="nouveauPassword"></p><p><label>Poste :</label>';
			foreach($rang as $cle => $valeur) {
				echo '<input type="radio" name="rang" value="'.$cle.'" '.(($cle == $rangInput)?'checked':'').'>'.$valeur.'&nbsp;';
			}
			echo '</p><p><input type="hidden" name="niveau" id="niveau" value="1"><input type="submit" value="Modifier le compte"></p></form>';
		} else {
			$login = (empty($_POST['login']))?false:$_POST['login'];
			$ancienMdp = (empty($_POST['ancienPassword']))?false:$_POST['ancienPassword'];
			$nouveauMdp = (empty($_POST['nouveauPassword']))?$ancienMdp:$_POST['nouveauPassword'];
			$rangInput = (empty($_POST['rang']))?false:$_POST['rang'];
			if ($login && $ancienMdp && $rangInput) {
				$requete_membre = new requete();
				$requete_membre->update('membre', array('nom' => $login, 'mdp' => array('VALEUR' => $nouveauMdp, 'SALAGE' => true), 'dateCreation' => time(), 'rang' => $rangInput));
				$requete_membre->where(array('membre' => array('id' => $id, 'mdp' => array('VALEUR' => $ancienMdp, 'SALAGE' => true))));
				// echo $requete_membre->requete_complete();
				$requete_membre->executer_requete();
				$erreur = array_merge($erreur, $requete_membre->liste_erreurs);
				unset($requete_membre);
				echo '<p>Le compte a bien été modifié.</p>';
			} else {
				echo '<p class="erreur"></p>';
			}
		}
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}include_once('listerComptes.php');
}