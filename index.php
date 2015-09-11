<?php
if (empty($_SESSION)) { session_start(); }
setlocale(LC_TIME, 'fr_FR.utf8','fra'); 
header('Content-Type: text/html; charset=utf-8');
include_once('fonctions/fonctions.php');

/* DEBUT */
$erreur = array();
$rang = array(1 => 'Administrateur', 2 => 'Chef', 3 => 'Préparateur', 4 => 'Livreur', 5 => 'Invité');

$api = 'fonctions/api.class.php';
if (file_exists($api)) { require_once($api); } else { $erreur['gestion'][] = 'erreur avec l\'importation du fichier d\'API'; }

/* RECUPERATION DES VARIABLES */
$login = (empty($_POST['login']))?false:$_POST['login'];
$password = (empty($_POST['password']))?false:$_POST['password'];
/******************************/

$page['doctype'] = '<!DOCTYPE html>';
$page['html'] = '<html xml:lang="fr" lang="fr">';
$page['head']['title'] = 'RipMeal - Gestion des livraisons de repas à domicile - SAVEURS MAISON 54';
$page['head']['js'][] = 'js/main.js';
$page['head']['js'][] = 'js/ajax.js';
$page['head']['js'][] = 'js/tri_tableau.js';
$page['head']['css'][] = 'css/main.css';
$page['head']['css'][] = 'css/admin.css';
$page['head']['css'][] = array('css/print.css', 'print');
$page['head']['meta']['charset'] = 'utf-8';

if (isset($_SESSION['id'])) {
	include_once('fonctions/modules.class.php');
	$menuEnCours = (empty($_REQUEST['menu']))?'aujourdhui':$_REQUEST['menu'];
	$sousMenuEnCours = (empty($_REQUEST['sousmenu']))?'index':$_REQUEST['sousmenu'];

	$class = new modules();
	$class->liste_menu($menuEnCours);
	
	if (!empty($class->tableau_modules[$menuEnCours]['informations générales']['css'])) {
		$page['head']['css'][] = 'modules/'.$menuEnCours.'/css/'.$class->tableau_modules[$menuEnCours]['informations générales']['css'];
	}
	if (!empty($class->tableau_modules[$menuEnCours][$_SESSION['type']]['css'])) {
		$page['head']['css'][] = 'modules/'.$menuEnCours.'/css/'.$class->tableau_modules[$menuEnCours][$_SESSION['type']]['css'];
	}
	if (!empty($class->tableau_modules[$menuEnCours]['informations générales']['js'])) {
		$page['head']['js'][] = 'modules/'.$menuEnCours.'/js/'.$class->tableau_modules[$menuEnCours]['informations générales']['js'];
	}
	if (!empty($class->tableau_modules[$menuEnCours][$_SESSION['type']]['js'])) {
		$page['head']['js'][] = 'modules/'.$menuEnCours.'/js/'.$class->tableau_modules[$menuEnCours][$_SESSION['type']]['js'];
	}
	echo construire_page($page);
	echo '<body><div id="window">';
	if (sizeof($erreur) > 0) {
		echo '<p class="erreur">Erreur suivante signalées : <br>';
		foreach ($erreur as $typeErreur => $listeErreurs) { foreach ($listeErreurs as $erreur) { echo ' - '.$typeErreur.' : '.$erreur.'<br>'; } }
		echo '</p>';
	}
	echo '<div class="left" id="main_left"><div id="main_left_content">';
	echo $class->menu;
	echo '</div></div><div class="right" id="main_right">';

	include_once('modules/'.$menuEnCours.'/pages/all/'.$sousMenuEnCours.'.php');

} else {
	if ($login && $password) {
		$requete_login = new requete();
		$requete_login->select('membre', 'm');
		$requete_login->where(array('m' => array('nom' => $_REQUEST['login'], 'mdp' => array('VALEUR' => $_REQUEST['password'], 'SALAGE' => true))));
		$requete_login->grand_tableau = false;
		// echo $requete_login->requete_complete();
		$requete_login->executer_requete();
		$liste_login = $requete_login->resultat;
		$erreur = array_merge($erreur, $requete_login->liste_erreurs);
		unset($requete_login);
		if ($liste_login) {
			session_regenerate_id();
			$_SESSION['id'] = $liste_login['id'];
			$_SESSION['nom'] = $liste_login['nom'];
			$_SESSION['rang'] = $rang[$liste_login['rang']];
			$_SESSION['type'] = 'all';
			
			/* LOGS */
			$requete_logs = new requete();
			$requete_logs->insert('logs', array('idMembre' => $liste_login['id'], 'dateConnexion' => time(), 'ipOrdi' => $_SERVER["REMOTE_ADDR"]));
			$requete_logs->executer_requete();
			$erreur = array_merge($erreur, $requete_logs->liste_erreurs);
			unset($requete_logs);
			/********/

			header("Location: index.php?menu=maj");
		}
	}
	echo construire_page($page);
	echo '<body><div id="window"><div>';
	if (sizeof($erreur) > 0) {
		echo '<p class="erreur">Erreur suivante signalées : <br>';
		foreach ($erreur as $typeErreur => $listeErreurs) { foreach ($listeErreurs as $erreur) { echo ' - '.$typeErreur.' : '.$erreur.'<br>'; } }
		echo '</p>';
	}
	echo '<section id="section_form">
			<h1>Connexion</h1>
			<form action="index.php" method="POST">
				<div class="wrapChamp">
					<input id="login" name="login" type="text" placeholder="Login" value="" autofocus="" required=""/>
				</div>
				<div class="wrapChamp">
					<input id="password" name="password" type="password" placeholder="Password" value="" required=""/>
				</div>
					<input type="submit" id="submit" class="bouton" value="Se connecter"/>
					<input type="hidden" name="niveau" id="niveau" value="true"/>
			</form>
		</section>';
}
echo '</div></div></body></html>';