<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if (file_exists(dirname(__FILE__).'\tableauRecapitulatif.php')) {
		include(dirname(__FILE__).'\tableauRecapitulatif.php');
	}
}