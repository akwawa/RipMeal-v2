<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$type = (empty($_GET['type']))?'tournee':$_GET['type'];
	$type2 = $type;
	switch ($type) {
		case 'tournee' : {
			$type2 = 'personne';
			$type3 = 'id';
			break; }
		case 'regime' :
		case 'boisson' :
		case 'remplacement' :
		case 'supplement' :
			$type2 = 'tper_'.substr($type, 0, 3);
			$type3 = 'idPersonne';
			break;
	}
	
	$requete = new requete();
	$requete->select(array('COUNT' => array($type2 => $type3)), 'tpg');
	$requete->select(array($type => array('id', 'nom')), 'r');
	if ($type == 'regime') {
		$requete->select(array('r' => array('nomComplet', 'couleur')));
	}
	$requete->group('r', 'id');
	$requete->join($type, $type2, 'RIGHT');
	// echo $requete->requete_complete().'<br><br>';
	$requete->executer_requete();
	$liste = $requete->resultat;
	$erreur = array_merge($erreur, $requete->liste_erreurs);
	unset($requete);

	if ($liste) {
		if ($type == 'regime') {
			$retour['resultat'] = '<table><thead><tr><th>Nom</th><th>Nom complet</th><th>Couleur</th><th>Nombre de personne</th><th colspan="2">Action</th></tr></thead><tbody>';
			foreach ($liste as $membre) {
				$retour['resultat'] .= '<tr><td>'.$membre['r.nom'].'</td><td>'.$membre['nomComplet'].'</td><td><span style="color:#'.$membre['couleur'].'">'.$membre['couleur'].'</span></td><td>'.$membre['COUNT(tpg.'.$type3.')'].'</td><td><a href="?menu=gestion&amp;sousmenu=modifier&amp;type='.$type.'&amp;id='.$membre['r.id'].'">Modifier</a></td><td><a href="?menu=gestion&amp;sousmenu=supprimer&amp;type='.$type.'&amp;id='.$membre['r.id'].'">Supprimer</a></td></tr>';
			}
		} else {
			$retour['resultat'] = '<table><thead><tr><th>Nom</th><th>Nombre de personne</th><th colspan="2">Action</th></tr></thead><tbody>';
			foreach ($liste as $membre) {
				$retour['resultat'] .= '<tr><td>'.$membre['r.nom'].'</td><td>'.$membre['COUNT(tpg.'.$type3.')'].'</td><td><a href="?menu=gestion&amp;sousmenu=modifier&amp;type='.$type.'&amp;id='.$membre['r.id'].'">Modifier</a></td><td><a href="?menu=gestion&amp;sousmenu=supprimer&amp;type='.$type.'&amp;id='.$membre['r.id'].'">Supprimer</a></td></tr>';
			}
		}
		$retour['resultat'] .= '</tbody></table>';
	} else {
		$retour['resultat'] = '<p class="erreur">Aucun '.$type.' n\'est déclaré.</p>';
	}
	$retour['resultat'] .= '<p><a href="?menu=gestion&amp;sousmenu=ajouter&amp;type='.$type.'">Ajouter un nouveau '.$type.'</a></p>';
	echo $retour['resultat'];
}