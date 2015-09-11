<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	// nom, prenom, sexe, adresse, codePostal, ville, telephone, telephoneSecond 
	{
	$nom = (empty($_POST['nom']))?false:$_POST['nom'];
	$prenom = (empty($_POST['prenom']))?false:$_POST['prenom'];
	$sexe = (empty($_POST['sexe']))?false:$_POST['sexe'];
	$adresse = (empty($_POST['adresse']))?false:$_POST['adresse'];
	$codePostal = (empty($_POST['codePostal']))?false:$_POST['codePostal'];
	$ville = (empty($_POST['ville']))?false:$_POST['ville'];
	$telephone = (empty($_POST['telephone']))?false:$_POST['telephone'];
	$telephoneSecond = (empty($_POST['telephoneSecond']))?'':$_POST['telephoneSecond'];
	}

	if ($nom && $prenom && $sexe && $adresse && $codePostal && $ville && $telephone) {		
		$requete = new requete();
		$requete->insert('ressource', array('nom' => $nom, 'prenom' => $prenom, 'sexe' => $sexe, 'adresse' => $adresse, 'codePostal' => $codePostal, 'ville' => $ville, 'telephone' => $telephone, 'telephoneSecond' => $telephoneSecond));
		// echo $requete->requete_complete();
		$requete->executer_requete();
		$erreur = array_merge($erreur, $requete->liste_erreurs);
		unset($requete);
		
		echo '<p>La ressource a bien été ajoutée.</p>';
		include('listerRessources.php');
	} else {
		echo '<form action="?menu=client&amp;sousmenu=ajouterRessource" method="post"><p><label for="nom">Nom</label><input type="text" name="nom" id="nom" value="'.$nom.'"></p><p><label for="prenom">Prénom</label><input type="text" name="prenom" id="prenom" value="'.$prenom.'"></p><p>Sexe<br><label><input type="radio" name="sexe" value="M">Masculin</label><br><label><input type="radio" name="sexe" value="F">Féminin</label></p><p><label for="adresse">Adresse</label><input type="text" name="adresse" id="adresse" value="'.$adresse.'"></p><p><label for="codePostal">Code postal</label><input type="number" name="codePostal" id="codePostal" value="'.$codePostal.'"></p><p><label for="ville">Ville</label><input type="text" name="ville" id="ville" value="'.$ville.'"></p><p><label for="telephone">Téléphone</label><input type="text" name="telephone" id="telephone" value="'.$telephone.'"></p><p><label for="telephoneSecond">Téléphone secondaire</label><input type="text" name="telephoneSecond" id="telephoneSecond" value="'.$telephoneSecond.'"></p><p><input type="submit" value="Ajouter la ressource"></p></form>';
	}
}