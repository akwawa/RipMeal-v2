<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	$type = (empty($_GET['type']))?'entree':$_GET['type'];
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

	$requete = new requete();
	$requete->select('menu_'.$type, 'm');
	$requete->order('nom');
	// echo $requete->requete_complete().'<br><br>';
	$requete->executer_requete();
	$liste = $requete->resultat;
	// $erreur = array_merge($erreur, $requete->liste_erreurs);
	unset($requete);

	if ($liste) {
		$retour['resultat'] = '<table><thead><tr><th>Nom</th><th>Nombre d\'utilisation</th><th colspan="2">Action</th></tr></thead><tbody>';
		foreach ($liste as $ligne) {
			$requete = new requete();
			// $requete->select(array('COUNT' => array('menu' => 'id')), 'menu');
			$requete->select(array('COUNT' => array('menu' => 'id')), 'menu');
			$requete->where(array('menu' => array('id'.ucfirst($type) => $ligne['id'])));
			// echo $requete->requete_complete().'<br><br>';
			$requete->grand_tableau = false;
			$requete->executer_requete();
			$result = $requete->resultat;
			// var_dump($result);
			unset($requete);
			$retour['resultat'] .= '<tr><td>'.$ligne['nom'].'</td><td>'.$result['COUNT(menu.id)'].'</td><td><a href="?menu=menu&amp;sousmenu=modifier&amp;type='.$type.'&amp;id='.$ligne['id'].'">Modifier</a></td><td><a href="?menu=menu&amp;sousmenu=supprimer&amp;type='.$type.'&amp;id='.$ligne['id'].'">Supprimer</a></td></tr>';
		}
		$retour['resultat'] .= '</tbody></table>';
	} else {
		$retour['resultat'] = '<p class="erreur">Aucun '.$type.' n\'est déclaré(e).</p>';
	}
	$retour['resultat'] .= '<p><a href="?menu=menu&amp;sousmenu=ajouter&amp;type='.$type.'">Ajouter un nouveau '.$type.'</a></p>';
	echo $retour['resultat'];
}