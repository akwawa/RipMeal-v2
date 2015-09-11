<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$requete = new requete();
	$requete->requete_direct('SELECT r.id, r.nom, r.nomComplet, count(idPersonne) as "quantite" FROM (SELECT DISTINCT idPersonne, idRegime FROM v2__tper_reg) t INNER JOIN v2__regime r ON t.idRegime = r.id INNER JOIN v2__personne p ON t.idPersonne = p.id INNER JOIN v2__tournee t ON p.numTournee = t.id WHERE p.actif = true AND t.nom <> "PAS DE TOURNEE" GROUP BY idRegime');
	$requete->executer_requete();
	$liste = $requete->resultat;
	unset($requete);
	
	// var_dump($liste);
	
	if ($liste) {
		$nbRegime = count($liste);
		$totalRegime = 0;
		$retour['resultat'] = '<form action="#" method="post" id="impression" onsubmit="menu_visu(this); return false;"><table><thead><tr><th>Nom</th><th>Nom complet</th><th>Quantité</th></tr></thead><tbody>';
		for ($i=0;$i<$nbRegime;$i++) {
			$regime = $liste[$i];
			$retour['resultat'] .= '<tr><td>'.$regime['nom'].'</td><td>'.$regime['nomComplet'].'</td><td><input type="number" name="nombre[]" data-id="reg_'.$regime['id'].'" id="nombre[]" value="'.$regime['quantite'].'" /></td></tr>';
			$totalRegime += $regime['quantite'];
		}
		$retour['resultat'] .= '<tr><th colspan="2">TOTAL</th><th>'.$totalRegime.'</th></tr></tbody></table><p><label for="menuSeul">Menu seulement</label><input type="checkbox" name="menuSeul" id="menuSeul" /></p><input type="submit" value="Imprimer" /></form>';
	} else {
		$retour['resultat'] = '<p class="erreur">Aucun régime n\'est déclaré.</p>';
	}
	
	echo $retour['resultat'];
}