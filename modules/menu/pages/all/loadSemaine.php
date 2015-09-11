<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if ($_SESSION['rang'] == 'Administrateur') {
		// '.$_SERVER['QUERY_STRING'].'
		echo '<form id="uploadSemaine" action="#" method="post" onsubmit="return loadSemaine(this);"><p><input type="file" id="files" name="files[]" /></p><p><label>Délimiteur :<input type="text" name="delimiteur" id="delimiteur" value=";" /></label></p><p><input type="submit" value="Importer les menus"></p></form><div id="resultat"></div>';
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}
}