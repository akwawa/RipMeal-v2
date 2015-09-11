<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	/* GESTION DES DATES */
	$dateCalendrier = (empty($_POST['dateCalendrier']))?false:$_POST['dateCalendrier'];
	if ($dateCalendrier) {
		list($dateAnnee, $dateMois, $dateJour) = explode('-', $dateCalendrier);
		$nbJour = date("t", mktime(0, 0, 0, $dateMois, 1, $dateAnnee));
	} else {
		$dateAnnee = date("Y");
		$dateMois = date("m");
		$dateJour = date("d");
		$nbJour = date("t", mktime(0, 0, 0, $dateMois, 1, $dateAnnee));
		if ($dateJour == $nbJour) { $dateJour = 1; if ($dateMois == 12) { $dateMois = 1; $dateAnnee++; } else { $dateMois++; } } else { $dateJour++; }
	}
	$temps = mktime(0, 0, 0, $dateMois, $dateJour, $dateAnnee);
	/*********************/


	// SELECT COUNT( idMenu ) AS "NbMenu" FROM v2__menu_regime mr RIGHT JOIN v2__menu m ON mr.idMenu = m.id GROUP BY m.id
	
	$requete = new requete();
	$requete->alias = true;
	$requete->select(array('menu' => array('id', 'supplement')), 'm');
	$requete->select(array('menu_entree' => 'nom'), 'me');
	$requete->select(array('menu_viande' => 'nom'), 'mv');
	$requete->select(array('menu_legume' => 'nom'), 'ml');
	$requete->select(array('menu_fromage' => 'nom'), 'mf');
	$requete->select(array('menu_dessert' => 'nom'), 'md');
	$requete->select(array('COUNT' => array('menu_regime' => 'idMenu')), 'mr');
	$requete->join('menu_regime', 'menu', 'RIGHT');
	// $requete->join($type, $type2, 'RIGHT');
	$requete->group('m', 'id');
	$requete->order('me.nom');
	$requete->order('mv.nom');
	$requete->order('ml.nom');
	$requete->order('mf.nom');
	$requete->order('md.nom');
	$requete->order('m.supplement');
	// echo $requete->requete_complete().'<br><br>';
	$requete->executer_requete();
	$liste = $requete->resultat;
	$erreur = array_merge($erreur, $requete->liste_erreurs);
	unset($requete);

	if ($liste) {
		$retour['resultat'] = '<p><a href="?menu=menu&amp;sousmenu=ajouterMenu">Ajouter un nouveau menu</a></p><table><caption>Menus - '.count($liste).' différents</caption><thead><tr><th>Nombre</th><th>Entree</th><th>Viande</th><th>Légume</th><th>Fromage</th><th>Dessert</th><th>Supplément</th><th colspan="2">Action</th></tr></thead><tbody>';
		foreach ($liste as $membre) {
			$retour['resultat'] .= '<tr><td>'.$membre['COUNT(mr.idMenu)'].'</td><td>'.$membre['me.nom'].'</td><td>'.$membre['mv.nom'].'</td><td>'.$membre['ml.nom'].'</td><td>'.$membre['mf.nom'].'</td><td>'.$membre['md.nom'].'</td><td>'.$membre['m.supplement'].'</td><td><a href="?menu=menu&amp;sousmenu=modifierMenu&amp;id='.$membre['m.id'].'">Modifier</a></td><td><a href="?menu=menu&amp;sousmenu=supprimerMenu&amp;id='.$membre['m.id'].'">Supprimer</a></td></tr>';
		}
		$retour['resultat'] .= '</tbody></table>';
	} else {
		$retour['resultat'] = '<p class="erreur">Aucun régime n\'est déclaré.</p>';
	}
	$retour['resultat'] .= '<p><a href="?menu=menu&amp;sousmenu=ajouterMenu">Ajouter un nouveau menu</a></p>';
	echo $retour['resultat'];
}