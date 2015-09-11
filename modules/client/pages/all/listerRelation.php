<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$requete_personnes = new requete();
	$requete_personnes->alias = true;
	$requete_personnes->select('personne_ressource', 'pr');
	$requete_personnes->select(array('personne' => array('id', 'nom', 'prenom')), 'p');
	$requete_personnes->select(array('ressource' => array('id', 'nom', 'prenom')), 'r');
	$requete_personnes->where(array('p' => array('corbeille' => false)));
	// echo $requete_personnes->requete_complete();
	$requete_personnes->executer_requete();
	$liste_personnes = $requete_personnes->resultat;
	$erreur = array_merge($erreur, $requete_personnes->liste_erreurs);
	unset($requete_personnes);

	if ($liste_personnes) {
		$retour['resultat'] = '<table><thead><tr><th>Client</th><th>Personne ressource</th><th>Lien</th><th colspan="2">Action</th></tr></thead><tbody>';
		foreach ($liste_personnes as $personne) {
			$retour['resultat'] .= '<tr><td><a onclick="details({\'path\':\'client\', \'fonctions\':\'detailsClient\', \'session\':\'all\', \'id\':'.$personne['p.id'].'});">'.$personne['p.nom'].' '.$personne['p.prenom'].'</a></td><td><a onclick="details({\'path\':\'client\', \'fonctions\':\'detailsRessource\', \'session\':\'all\', \'id\':'.$personne['r.id'].'});">'.$personne['r.nom'].' '.$personne['r.prenom'].'</a></td><td>'.$personne['lien'].'</td><td><a href="?menu=client&amp;sousmenu=modifierRelation&amp;idPersonne='.$personne['p.id'].'&amp;idRessource='.$personne['r.id'].'">Modifier</a></td><td><a href="?menu=client&amp;sousmenu=supprimerRelation&amp;idPersonne='.$personne['p.id'].'&amp;idRessource='.$personne['r.id'].'">Supprimer</a></td></tr>';
		}
		$retour['resultat'] .= '</tbody></table>';
	} else {
		$retour['resultat'] = '<p class="erreur">Il n\'y a aucune relation enregistrée.</p>';
	}
	$retour['resultat'] .= '<p><a href="?menu=client&amp;sousmenu=ajouterRelation">Ajouter une nouvelle relation</a></p>';
	echo $retour['resultat'];
}