<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$requete_membres = new requete();
	$requete_membres->select('membre');
	$requete_membres->executer_requete();
	$liste_membres = $requete_membres->resultat;
	$erreur = array_merge($erreur, $requete_membres->liste_erreurs);
	unset($requete_membres);

	if ($liste_membres) {
		$retour['resultat'] = '<table><thead><tr><th>Nom</th><th>Rang</th><th colspan="2">Action</th></tr></thead><tbody>';
		foreach ($liste_membres as $membre) {
			$retour['resultat'] .= '<tr><td>'.$membre['nom'].'</td><td>'.$rang[$membre['rang']].'</td><td><a href="?menu=compte&amp;sousmenu=modifierCompte&amp;id='.$membre['id'].'">Modifier</a></td><td><a href="?menu=compte&amp;sousmenu=supprimerCompte&amp;id='.$membre['id'].'">Supprimer</a></td></tr>';
		}
		$retour['resultat'] .= '</tbody></table><p><a href="?menu=compte&amp;sousmenu=ajouterCompte">Ajouter un nouveau compte</a></p>';
	} else {
		$retour['resultat'] = '<h2>Aucun membre n\'est déclarée.</h2>';
	}

	echo $retour['resultat'];
}