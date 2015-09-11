<?php
$requete_membres = new requete();
$requete_membres->select('personne');
$requete_membres->executer_requete();
$liste = $requete_membres->resultat;
$erreur = array_merge($erreur, $requete_membres->liste_erreurs);
unset($requete_membres);

if ($liste) {
	$retour['resultat'] = '<table><thead><tr><th>Nom</th><th>Prénom</th><th>Sexe</th><th>Adresse</th><th>Code postal</th><th>Ville</th><th>Téléphone</th><th>Téléphone secondaire</th><th>Tournée</th><th>Numéro tournée</th><th>Pain</th><th>Potage</th><th>Actif</th><th>Sac</th><th>Informations</th><th colspan="2">Action</th></tr></thead><tbody>';
	foreach ($liste as $personne) {
		$retour['resultat'] .= '<tr><td>'.$personne['nom'].'</td><td>'.$personne['prenom'].'</td><td>'.$personne['sexe'].'</td><td>'.$personne['adresse'].'</td><td>'.$personne['codePostal'].'</td><td>'.$personne['ville'].'</td><td>'.$personne['telephone'].'</td><td>'.$personne['telephoneSecond'].'</td><td>'.$personne['numTournee'].'</td><td>'.$personne['numPerTou'].'</td><td>'.$personne['pain'].'</td><td>'.$personne['potage'].'</td><td>'.$personne['actif'].'</td><td>'.$personne['sacPorte'].'</td><td>'.$personne['info'].'</td><td><a href="">Modifier</a></td><td><a href="">Supprimer</a></td></tr>';
	}
} else {
	$retour['resultat'] = '<p class="erreur">Aucune personne n\'est déclarée.</p>';
}
$retour['resultat'] .= '</tbody></table><p><a href="">Ajouter une personne</a></p>';
echo $retour['resultat'];