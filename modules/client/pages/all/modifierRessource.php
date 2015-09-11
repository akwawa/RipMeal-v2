<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	// nom, prenom, sexe, adresse, codePostal, ville, telephone, telephoneSecond 
	$id = (empty($_GET['id']))?(empty($_POST['id']))?0:$_POST['id']:$_GET['id'];
	$niveau = (empty($_POST['niveau']))?false:$_POST['niveau'];
	$trouve = true;
	if ($niveau) {
		$nom = (empty($_POST['nom']))?false:$_POST['nom'];
		$prenom = (empty($_POST['prenom']))?false:$_POST['prenom'];
		$sexe = (empty($_POST['sexe']))?false:$_POST['sexe'];
		$adresse = (empty($_POST['adresse']))?false:$_POST['adresse'];
		$codePostal = (empty($_POST['codePostal']))?false:$_POST['codePostal'];
		$ville = (empty($_POST['ville']))?false:$_POST['ville'];
		$telephone = (empty($_POST['telephone']))?false:$_POST['telephone'];
		$telephoneSecond = (empty($_POST['telephoneSecond']))?'':$_POST['telephoneSecond'];

		if ($nom && $prenom && $sexe && $adresse && $codePostal && $ville && $telephone) {
			$requete = new requete();
			$requete->update('ressource', array('nom' => $nom, 'prenom' => $prenom, 'sexe' => $sexe, 'adresse' => $adresse, 'codePostal' => $codePostal, 'ville' => $ville, 'telephone' => $telephone, 'telephoneSecond' => $telephoneSecond));
			$requete->where(array('ressource' =>array('id' => $id)));
			// echo $requete->requete_complete();
			$requete->executer_requete();
			$erreur = array_merge($erreur, $requete->liste_erreurs);
			unset($requete);		
		
			echo '<p>La ressource a bien été modifiée.</p>';
			include('listerRessources.php');
			$trouve = false;
		}
	} else {
		$requete = new requete();
		$requete->select('ressource', 'r');
		$requete->where(array('r' => array('id' => $id)));
		// echo $requete->requete_complete();
		$requete->executer_requete();
		$info_personne = $requete->resultat;
		$erreur = array_merge($erreur, $requete->liste_erreurs);
		unset($requete);

		$nom = $info_personne[0]['nom'];
		$prenom = $info_personne[0]['prenom'];
		$sexe = $info_personne[0]['sexe'];
		$adresse = $info_personne[0]['adresse'];
		$codePostal = $info_personne[0]['codePostal'];
		$ville = $info_personne[0]['ville'];
		$telephone = $info_personne[0]['telephone'];
		$telephoneSecond = $info_personne[0]['telephoneSecond'];
	}
	if ($trouve) {
		echo '<form action="?menu=client&amp;sousmenu=modifierRessource" method="post"><p><label for="nom">Nom</label><input type="text" name="nom" id="nom" value="'.$nom.'"></p><p><label for="prenom">Prénom</label><input type="text" name="prenom" id="prenom" value="'.$prenom.'"></p><p>Sexe<br><label><input type="radio" name="sexe" value="M"'.(($sexe == 'M')?' checked':'').'>Masculin</label><br><label><input type="radio" name="sexe" value="F"'.(($sexe == 'F')?' checked':'').'>Féminin</label></p><p><label for="adresse">Adresse</label><input type="text" name="adresse" id="adresse" value="'.$adresse.'"></p><p><label for="codePostal">Code postal</label><input type="number" name="codePostal" id="codePostal" value="'.$codePostal.'"></p><p><label for="ville">Ville</label><input type="text" name="ville" id="ville" value="'.$ville.'"></p><p><label for="telephone">Téléphone</label><input type="text" name="telephone" id="telephone" value="'.$telephone.'"></p><p><label for="telephoneSecond">Téléphone secondaire</label><input type="text" name="telephoneSecond" id="telephoneSecond" value="'.$telephoneSecond.'"></p><p><input type="hidden" name="id" id="id" value="'.$id.'"><input type="hidden" name="niveau" id="niveau" value="2"><input type="submit" value="Modifier la ressource"></p></form>';
	}
}