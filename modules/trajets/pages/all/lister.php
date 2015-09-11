<?php
if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$requete = new requete();
	$requete->select('param');
	$requete->executer_requete();
	// echo $requete->requete_complete();
	$liste = $requete->resultat;
	unset($requete);

	if ($liste) {
		$taille = count($liste);
		$adresse = "";
		$codePostal = "";
		$ville = "";
		$nombre = 10;
		for ($i=0;$i<$taille;$i++) {
			if ($liste[$i]["intitule"] == "adresse") {
				$adresse = $liste[$i]["texte"];
			} elseif ($liste[$i]["intitule"] == "codePostal") {
				$codePostal = $liste[$i]["texte"];
			} elseif ($liste[$i]["intitule"] == "ville") {
				$ville = $liste[$i]["texte"];
			} elseif ($liste[$i]["intitule"] == "nombre") {
				$nombre = $liste[$i]["texte"];
			}
		}
	}
	echo '<form method="POST" onsubmit="demarrer(this); return false;"><p><label for="adresse">Adresse de départ : </label><input type="text" size="100" id="adresse" value="'.$adresse.'" required="required" /></p><p><label for="codePostal">Code postal : </label><input type="number" size="8" id="codePostal" value="'.$codePostal.'" required="required" /></p><p><label for="ville">Ville : </label><input type="text" size="100" id="ville" value="'.$ville.'" required="required" /></p><p><label for="nombre">Nombre : </label><input type="number" id="nombre" value="'.$nombre.'" required="required" /></p><p><input type="submit" id="action" value="Lister les personnes" /></p></form>';
}