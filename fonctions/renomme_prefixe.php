<?php
/*************************************/
/* Script qui permet de changer le   */
/* prefixe des tables d'une BDD      */
/*                                   */
/* Création : 23/01/2012 19:19       */
/* Auteur : Rémi PERETTE             */
/* Utilise : PHP + PDO               */
/*************************************/

/*************************************/
/* VARIABLES A MODIFIER              */
$prefix_old = 'v1__';
$prefix_new = 'v2__';

$PARAM_hote='localhost';
$PARAM_port='3306';
$PARAM_nom_bd='peretterepasv2';
$PARAM_utilisateur='peretterepas';
$PARAM_mot_passe='repas54SM';
$PARAM_type_base='mysql';
$jeux_de_caracteres='utf8';
/*************************************/

$dbh = null;
try {
	$dbh = new PDO($PARAM_type_base.':host='.$PARAM_hote.';dbname='.$PARAM_nom_bd,
					$PARAM_utilisateur, $PARAM_mot_passe,
					array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$jeux_de_caracteres));

	$sth = $dbh->prepare('SHOW TABLES LIKE :prefix_old');
	$prefix_old .= "%";
	$sth->bindParam(':prefix_old', $prefix_old);
	// $sth->bindParam($conditions['nom'], $conditions['valeur'], PDO::PARAM_INT);
	if ($sth->execute()) {
		$liste_tables = $sth->fetchAll(PDO::FETCH_ASSOC);
		$sth->closeCursor();
		foreach ($liste_tables as $table) {
			$nomTable = $table['Tables_in_'.$PARAM_nom_bd.' ('.$prefix_old.')'];
			$nouveau_nomTable = $prefix_new.substr($nomTable, strlen($prefix_old)-1-strlen($nomTable));
			$sth = $dbh->prepare('ALTER TABLE `'.$nomTable.'` RENAME AS `'.$nouveau_nomTable.'`');
			if ($sth->execute()) {
				echo '<span style="color:green;">Eok : '.$nomTable.' | '.$nouveau_nomTable.'</span><br>';
			} else {
				echo '<span style="color:red;">Erreur : '.$nomTable.' | '.$nouveau_nomTable.'</span><br>';
			}
			$sth->closeCursor();
		}
	} else {
		echo 'erreur';
	}
	$dbh = null;
} catch(Exception $e) {
	echo $e->getMessage().'<br />N° : '.$e->getCode();
	die();
}

// 'SHOW TABLES LIKE "'.$prefix_old.'%"'
// 'RENAME TABLE "'.$table.'"  TO CONCAT("'.$prefix_new.'", RIGHT("'.$table.'", LENGTH("'.$table.'")-LENGTH("'.$prefix_old.'")))'