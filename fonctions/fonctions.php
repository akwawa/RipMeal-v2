<?php

/*
function chemin_racine() {
	if(defined('RUN_MODE_PRODUCTION') && RUN_MODE_PRODUCTION === true) {
		return getenv('DOCUMENT_ROOT').'/repas2/';
		// return getenv('DOCUMENT_ROOT').'/InfoMage/RipMeal/v3.00/';
	}
	return getenv('DOCUMENT_ROOT').'/repas2/';
	// return getenv('DOCUMENT_ROOT').'/InfoMage/RipMeal/v3.00/';
}
*/

function chemin_racine() {
	$dirname = dirname(__FILE__);
	$pos = strrpos($dirname, '\\');
	return substr($dirname, 0, $pos+1);
}

function ScanDirectory($Directory){
	$MyDirectory = opendir($Directory) or die('Erreur');
	while($Entry = @readdir($MyDirectory)) {
		if(is_dir($Directory.'/'.$Entry)&& $Entry != '.' && $Entry != '..') {
			echo '<ul>'.$Directory;
			ScanDirectory($Directory.'/'.$Entry);
			echo '</ul>';
		} else {
			echo '<li>'.$Entry.'</li>';
		}
	}
	closedir($MyDirectory);
}

function construire_page($page) {
	$retour = '';
	$retour .= (empty($page['doctype']))?'<!DOCTYPE html>':$page['doctype'];
	$retour .= (empty($page['html']))?'<html>':$page['html'];
	if (!empty($page['head'])) {
		$retour .= '<head>';
		foreach($page['head']['meta'] as $cle => $valeur) {
			if ($cle == 'Content-type') {
				$retour .= '<meta http-equiv="Content-type" content="'.$page['head']['meta']['Content-type'].'" />';
			} else {
				$retour .= '<meta '.$cle.'="'.$valeur.'">';
			}
		}
		foreach($page['head']['css'] as $cle) {
			if (is_array($cle)) {
				$retour .= '<link rel="stylesheet" type="text/css" href="'.$cle[0].'" media="'.$cle[1].'" />';
			} else {
				$retour .= '<link rel="stylesheet" type="text/css" href="'.$cle.'" />';
			}
		}
		foreach($page['head']['js'] as $cle) {
			$retour .= '<script type="text/javascript" src="'.$cle.'"></script>';
		}
		if (!empty($page['head']['title'])) {
			$retour .= '<title>'.$page['head']['title'].'</title>';
		}
		$retour .= '</head>';
	} else {
		$retour .= '<head></head>';
	}
	return $retour;
}