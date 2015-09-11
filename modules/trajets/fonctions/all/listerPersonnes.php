<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists('../../../../fonctions/api.class.php')) {
		require_once('../../../../fonctions/api.class.php');
		$nombre = (empty($_POST['nombre']))?5:$_POST['nombre'];
		$requete = new requete();
		$requete->requete_direct('SELECT p.id AS "p.id", p.nom AS "p.nom", p.prenom AS "p.prenom", p.adresse AS "p.adresse", p.codePostal AS "p.codePostal", p.ville AS "p.ville", c.id as "c.id", c.lat AS "c.lat", c.lng AS "c.lng", c.formatted_address as "c.formatted_address" FROM v2__personne p LEFT JOIN v2__coordonnees c ON p.adresse = c.adresse AND p.codePostal = c.codePostal AND p.ville = c.ville LIMIT 0, '.$nombre);
		$requete->executer_requete();
		$retour['resultat'] = $requete->resultat;
		unset($requete);
		
		// $retour['resultat'] = '[{"p.nom":"BECKER","p.prenom":"Yvette","p.adresse":"26 rue Abb\u00e9 Devaux","p.codePostal":"54140","p.ville":"Jarville-la-Malgrange","c.id":"7","c.lat":"48.6712774","c.lng":"6.2031164","c.formatted_address":"26 Rue Abb\u00e9 Devaux, 54140 Jarville-la-Malgrange, France"},{"p.nom":"ARDUINI","p.prenom":"Mme","p.adresse":"4 rue de la madine","p.codePostal":"54520","p.ville":"Laxou","c.id":"8","c.lat":"48.6992925","c.lng":"6.127093899999999","c.formatted_address":"4 Rue de la Madine, 54520 Laxou, France"},{"p.nom":"LAUMONT","p.prenom":"yvette","p.adresse":"15 allee de l\'aire","p.codePostal":"54520","p.ville":"laxou","c.id":"9","c.lat":"48.6967211","c.lng":"6.1241283","c.formatted_address":"15 All\u00e9e de l\'Aire, 54520 Laxou, France"}]';
	
	} else {
		$retour['resultat'] = 'erreur importation API';
	}
	echo json_encode($retour['resultat']);
	// echo $retour['resultat'];
}