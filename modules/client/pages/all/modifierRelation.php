<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$idPersonne = (empty($_GET['idPersonne']))?(empty($_POST['idPersonne']))?0:$_POST['idPersonne']:$_GET['idPersonne'];
	$idRessource = (empty($_POST['idRessource']))?false:$_POST['idRessource'];
	$niveau = (empty($_POST['niveau']))?false:$_POST['niveau'];
	$trouve = true;
	if ($niveau) {
		$lien = (empty($_POST['lien']))?false:$_POST['lien'];
		$ancienIdRessource = (empty($_POST['ancienIdRessource']))?false:$_POST['ancienIdRessource'];
		if ($lien) {
			$requete = new requete();
			$requete->delete('personne_ressource', array('personne_ressource' => array('idPersonne' => $idPersonne, 'idRessource' => $ancienIdRessource)));
			// echo $requete->requete_complete();
			$requete->executer_requete();
			$erreur = array_merge($erreur, $requete->liste_erreurs);
			unset($requete);
			
			$requete = new requete();
			$requete->insert('personne_ressource', array('idPersonne' => $idPersonne, 'idRessource' => $idRessource, 'lien' => $lien));
			// echo $requete->requete_complete();
			$requete->executer_requete();
			$erreur = array_merge($erreur, $requete->liste_erreurs);
			unset($requete);
			
			echo '<p>La relation a bien été modifiée.</p>';
			include('listerRelation.php');
			$trouve = false;
		}
	} else {
		$ancienIdRessource = (empty($_GET['idRessource']))?false:$_GET['idRessource'];
		$requete_personnes = new requete();
		$requete_personnes->select(array('personne_ressource' => 'lien'), 'pr');
		$requete_personnes->where(array('pr' => array('idPersonne' => $idPersonne, 'idRessource' => $ancienIdRessource)));
		// echo $requete_personnes->requete_complete();
		$requete_personnes->executer_requete();
		$liste = $requete_personnes->resultat;
		$erreur = array_merge($erreur, $requete_personnes->liste_erreurs);
		unset($requete_personnes);
		$lien = $liste[0]['lien'];
		$idRessource = $ancienIdRessource;
	}
	if ($trouve) {
		$requete = new requete();
		$requete->select('personne', 'p');
		$requete->where(array('p' => array('id' => $idPersonne)));
		// echo $requete->requete_complete();
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
	
		echo '<form action="?menu=client&amp;sousmenu=modifierRelation" method="post"><p>Client<select name="idPersonne" id="idPersonne">';
		echo '<option value="'.$liste_client[0]['id'].'" selected>'.$liste_client[0]['nom'].' '.$liste_client[0]['prenom'].' ('.$liste_client[0]['adresse'].' à '.$liste_client[0]['ville'].')</option>';
		echo '</select></p><p>Ressource<select name="idRessource" id="idRessource">';
		foreach ($liste_ressource as $ressource) {
			if ($ressource['id'] == $idRessource) {
				echo '<option value="'.$ressource['id'].'" selected>'.$ressource['nom'].' '.$ressource['prenom'].' ('.$ressource['adresse'].' à '.$ressource['ville'].')</option>';
			} else {
				echo '<option value="'.$ressource['id'].'">'.$ressource['nom'].' '.$ressource['prenom'].' ('.$ressource['adresse'].' à '.$ressource['ville'].')</option>';
			}
		}
		echo '</select></p><p><label for="lien">Lien</label><input type="text" name="lien" id="lien" value="'.$lien.'"></p><p><input type="hidden" name="ancienIdRessource" id="ancienIdRessource" value="'.$ancienIdRessource.'"><input type="hidden" name="niveau" id="niveau" value="2"><input type="submit" value="Modifier la relation"></p></form>';
	}
}