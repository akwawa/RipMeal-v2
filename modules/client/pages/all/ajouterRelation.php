<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	// nom, prenom, sexe, adresse, codePostal, ville, telephone, telephoneSecond 
	{
	$idPersonne = (empty($_POST['idPersonne']))?false:$_POST['idPersonne'];
	$idRessource = (empty($_POST['idRessource']))?false:$_POST['idRessource'];
	$lien = (empty($_POST['lien']))?false:$_POST['lien'];
	}

	if ($idPersonne && $idRessource && $lien) {		
		$requete = new requete();
		$requete->insert('personne_ressource', array('idPersonne' => $idPersonne, 'idRessource' => $idRessource, 'lien' => $lien));
		echo $requete->requete_complete();
		$requete->executer_requete();
		$erreur = array_merge($erreur, $requete->liste_erreurs);
		unset($requete);
		
		echo '<p>La relation a bien été ajoutée.</p>';
		include('listerRelation.php');
	} else {
		$requete = new requete();
		$requete->select('personne');
		$requete->executer_requete();
		$liste_client = $requete->resultat;
		$erreur = array_merge($erreur, $requete->liste_erreurs);
		unset($requete);
		
		$requete = new requete();
		$requete->select('ressource');
		$requete->executer_requete();
		$liste_ressource = $requete->resultat;
		$erreur = array_merge($erreur, $requete->liste_erreurs);
		unset($requete);
	
		echo '<form action="?menu=client&amp;sousmenu=ajouterRelation" method="post"><p>Client<select name="idPersonne" id="idPersonne">';
		foreach ($liste_client as $client) {
			echo '<option value="'.$client['id'].'">'.$client['nom'].' '.$client['prenom'].' ('.$client['adresse'].' à '.$client['ville'].')</option>';
		}
		echo '</select></p><p>Ressource<select name="idRessource" id="idRessource">';
		foreach ($liste_ressource as $ressource) {
			echo '<option value="'.$ressource['id'].'">'.$ressource['nom'].' '.$ressource['prenom'].' ('.$ressource['adresse'].' à '.$ressource['ville'].')</option>';
		}
		echo '</select></p><p><label for="lien">Lien</label><input type="text" name="lien" id="lien" value="'.$lien.'"></p><p><input type="submit" value="Ajouter la relation"></p></form>';
	}
}