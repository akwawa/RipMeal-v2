<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	// nom, prenom, sexe, adresse, codePostal, ville, telephone, telephoneSecond, numTournee, numPerTou, pain, potage, dateActif, sacPorte, info
	// actif
	// modifTou, debutHibernation, finHibernation, 
	{
	$nom = (empty($_POST['nom']))?false:$_POST['nom'];
	$prenom = (empty($_POST['prenom']))?false:$_POST['prenom'];
	$sexe = (empty($_POST['sexe']))?false:$_POST['sexe'];
	$adresse = (empty($_POST['adresse']))?false:$_POST['adresse'];
	$codePostal = (empty($_POST['codePostal']))?false:$_POST['codePostal'];
	$ville = (empty($_POST['ville']))?false:$_POST['ville'];
	$telephone = (empty($_POST['telephone']))?false:$_POST['telephone'];
	$telephoneSecond = (empty($_POST['telephoneSecond']))?'':$_POST['telephoneSecond'];
	$numTournee = (empty($_POST['numTournee']))?false:$_POST['numTournee'];
	$numPerTou = (empty($_POST['numPerTou']))?(-1):$_POST['numPerTou'];
	$pain = (empty($_POST['pain']))?0:$_POST['pain'];
	$potage = (empty($_POST['potage']))?0:$_POST['potage'];
	$dateActif = (empty($_POST['dateActif']))?false:$_POST['dateActif'];
	$sacPorte = (empty($_POST['sacPorte']))?false:$_POST['sacPorte'];
	$info = (empty($_POST['info']))?'':$_POST['info'];
	}

	if ($nom && $prenom && $sexe && $adresse && $codePostal && $ville && $telephone && $numTournee) {
	{
		$dateActif = ($dateActif)?strtotime($dateActif):time();
		// $actif = ($dateActif >= time())?false:true;
		$actif = true;
		$numTournee = (is_numeric($numTournee))?$numTournee:0;
		$numPerTou = (is_numeric($numPerTou) && $numPerTou != "-1")?$numPerTou:0;

		$requete_membre = new requete();
		$requete_membre->requete_direct('UPDATE v2__personne SET numPerTou = numPerTou+1 WHERE v2__personne.numTournee = '.$numTournee.' AND numPerTou >= '.$numPerTou);
		// echo $requete_membre->requete_complete();
		$requete_membre->executer_requete();
		$erreur = array_merge($erreur, $requete_membre->liste_erreurs);
		unset($requete_membre);
		
		$requete = new requete();
		$requete->insert('personne', array('nom' => $nom, 'prenom' => $prenom, 'sexe' => $sexe, 'adresse' => $adresse, 'codePostal' => $codePostal, 'ville' => $ville, 'telephone' => $telephone, 'telephoneSecond' => $telephoneSecond, 'numTournee' => $numTournee, 'numPerTou' => $numPerTou, 'pain' => $pain, 'potage' => $potage, 'dateActif' => $dateActif, 'sacPorte' => $sacPorte, 'actif' => $actif, 'info' => $info));
		// echo $requete->requete_complete();
		$requete->executer_requete();
		$erreur = array_merge($erreur, $requete->liste_erreurs);
		unset($requete);
		
		$requete = new requete();
		$requete->select(array('personne' => 'id'), 'p');
		$requete->where(array('p' => array('nom' => $nom, 'prenom' => $prenom, 'sexe' => $sexe, 'adresse' => $adresse, 'codePostal' => $codePostal, 'ville' => $ville, 'telephone' => $telephone, 'telephoneSecond' => $telephoneSecond, 'numTournee' => $numTournee, 'numPerTou' => $numPerTou, 'pain' => $pain, 'potage' => $potage, 'dateActif' => $dateActif, 'sacPorte' => $sacPorte, 'actif' => $actif, 'info' => $info)));
		// echo $requete->requete_complete();
		$requete->executer_requete();
		$info_personne = $requete->resultat;
		$erreur = array_merge($erreur, $requete->liste_erreurs);
		unset($requete);
		
		include('ajouterClientRepas.php');
	}
	} else {
		echo '<form action="?menu=client&amp;sousmenu=ajouterClient" method="post"><p><label for="nom">Nom</label><input type="text" name="nom" id="nom" value="'.$nom.'" required/></p><p><label for="prenom">Prénom</label><input type="text" name="prenom" id="prenom" value="'.$prenom.'" required/></p><p>Sexe<br><label><input type="radio" name="sexe" value="M" required />Masculin</label><br><label><input type="radio" name="sexe" value="F" />Féminin</label></p><p><label for="adresse">Adresse</label><input type="text" name="adresse" id="adresse" value="'.$adresse.'" required/></p><p><label for="codePostal">Code postal</label><input type="number" name="codePostal" id="codePostal" value="'.$codePostal.'" required/></p><p><label for="ville">Ville</label><input type="text" name="ville" id="ville" value="'.$ville.'" required/></p><p><label for="telephone">Téléphone</label><input type="text" name="telephone" id="telephone" value="'.$telephone.'" required/></p><p><label for="telephoneSecond">Téléphone secondaire</label><input type="text" name="telephoneSecond" id="telephoneSecond" value="'.$telephoneSecond.'" required/></p><p><label for="numTournee">Tournée</label>';

		$requete = new requete();
		$requete->select('tournee');
		$requete->executer_requete();
		$liste = $requete->resultat;
		$erreur = array_merge($erreur, $requete->liste_erreurs);
		unset($requete);
		echo '<select name="numTournee" size="'.count($liste).'" required>';
		foreach ($liste as $tournee) {
			echo '<option value="'.$tournee['id'].'" onclick="lister_numPerTou(this);">'.$tournee['nom'].'</option>';
		}
		echo '</select></p><p><label for="numPerTou">Inséré après</label><select id="numPerTou" name="numPerTou" required></select></p><p><label for="pain">Pain</label><input type="number" name="pain" id="pain" value="'.$pain.'" required/></p><p><label for="potage">Potage</label><input type="number" name="potage" id="potage" value="'.$potage.'" required/></p><p><label for="dateActif">Date actif</label><input type="Date" name="dateActif" id="dateActif" value="'.$dateActif.'"></p><p>Sac à la porte<br><label><input type="radio" name="sacPorte" value="'.(true).'">Oui</label><br><label><input type="radio" name="sacPorte" value="'.(false).'">Non</label></p><p>Informations complémentaires<textarea name="info" id="info"></textarea></p><p><input type="submit" value="Ajouter le client"></p></form>';
	}
}