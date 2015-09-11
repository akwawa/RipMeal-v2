<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$requete_personnes = new requete();
	$requete_personnes->select('personne');
	$requete_personnes->where(array('personne' => array('corbeille' => false)));
	// echo $requete_personnes->requete_complete();
	$requete_personnes->executer_requete();
	$liste_personnes = $requete_personnes->resultat;
	$erreur = array_merge($erreur, $requete_personnes->liste_erreurs);
	unset($requete_personnes);

	if ($liste_personnes) {
		$retour['resultat'] = '<table><thead><tr><th>Nom</th><th>Prénom</th><th>Adresse</th><th>Ville</th><th>Téléphone</th><th>Actif</th><th colspan="3">Action</th></tr></thead><tbody>';
		foreach ($liste_personnes as $personne) {
			$retour['resultat'] .= '<tr><td><a onclick="details({\'path\':\'client\', \'fonctions\':\'detailsClient\', \'session\':\'all\', \'id\':'.$personne['id'].'});">'.$personne['nom'].'</a></td><td>'.$personne['prenom'].'</td><td>'.$personne['adresse'].'</td><td>'.$personne['ville'].'</td><td>'.$personne['telephone'].'</td><td>'.(($personne['actif'])?'<span style="color:green;">oui</span>':'<span style="color:red;">non</span>').'</td><td><a href="?menu=client&amp;sousmenu=modifierClient&amp;id='.$personne['id'].'">Modifier</a></td><td><a href="?menu=client&amp;sousmenu=changerActifClient&amp;id='.$personne['id'].'">'.($personne['actif']?'Passer inactif':'Passer actif').'</a></td><td><a href="?menu=client&amp;sousmenu=supprimerClient&amp;id='.$personne['id'].'">Supprimer</a></td></tr>';
		}
		$retour['resultat'] .= '</tbody></table>';
	} else {
		$retour['resultat'] = '<p class="erreur">Il n\'y a aucune personne enregistrée.</p>';
	}
	$retour['resultat'] .= '<p><a href="?menu=client&amp;sousmenu=ajouterClient">Ajouter une nouvelle personne</a></p>';
	echo $retour['resultat'];
}