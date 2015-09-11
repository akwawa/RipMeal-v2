<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$requete = new requete();
	$requete->select('ressource');
	$requete->executer_requete();
	$liste_personnes = $requete->resultat;
	$erreur = array_merge($erreur, $requete->liste_erreurs);
	unset($requete);

	if ($liste_personnes) {
		$retour['resultat'] = '<table><thead><tr><th>Nom</th><th>Prénom</th><th>Ville</th><th>Téléphone</th><th colspan="2">Action</th></tr></thead><tbody>';
		foreach ($liste_personnes as $personne) {
			$retour['resultat'] .= '<tr><td><a onclick="details({\'path\':\'client\', \'fonctions\':\'detailsRessource\', \'session\':\'all\', \'id\':'.$personne['id'].'});">'.$personne['nom'].'</a></td><td>'.$personne['prenom'].'</td><td>'.$personne['ville'].'</td><td>'.$personne['telephone'].'</td><td><a href="?menu=client&amp;sousmenu=modifierRessource&amp;id='.$personne['id'].'">Modifier</a></td><td><a href="?menu=client&amp;sousmenu=supprimerRessource&amp;id='.$personne['id'].'">Supprimer</a></td></tr>';
		}
		$retour['resultat'] .= '</tbody></table>';
	} else {
		$retour['resultat'] = '<p class="erreur">Il n\'y a aucune ressource enregistrée.</p>';
	}
	$retour['resultat'] .= '<p><a href="?menu=client&amp;sousmenu=ajouterRessource">Ajouter une nouvelle ressource</a></p>';
	echo $retour['resultat'];
}