<?php

if (!$_SESSION) session_start();

if ($_SESSION) {
	$id = (empty($_GET['id']))?false:$_GET['id'];
	$type = (empty($_GET['type']))?false:$_GET['type'];
	if ($_SESSION['id'] == $id || $_SESSION['rang'] == 'Administrateur') {
		$niveau = (empty($_POST['niveau']))?false:$_POST['niveau'];
		$entree = (empty($_POST['entree']))?false:$_POST['entree'];
		$viande = (empty($_POST['viande']))?false:$_POST['viande'];
		$legume = (empty($_POST['legume']))?false:$_POST['legume'];
		$fromage = (empty($_POST['fromage']))?false:$_POST['fromage'];
		$dessert = (empty($_POST['dessert']))?false:$_POST['dessert'];
		$supplement = (empty($_POST['supplement']))?false:$_POST['supplement'];
		if (!$niveau) {
			echo '<form action="?'.$_SERVER['QUERY_STRING'].'&amp;id='.$id.'" method="post">';
			$type = array('entree', 'viande', 'legume', 'fromage', 'dessert');
			foreach ($type as $val) {
				$requete = new requete();
				$requete->select('menu_'.$val, 'm');
				$requete->order('nom');
				// echo $requete->requete_complete().'<br><br>';
				$requete->executer_requete();
				$liste = $requete->resultat;
				unset($requete);
				
				$nombre = count($liste);
				
				if ($nombre > 0) {
					echo '<p><label for="'.$val.'">'.$val.' :</label><select name="'.$val.'" id="'.$val.'">';
					for ($i=0; $i<$nombre;$i++) {
						echo '<option value="'.$liste[$i]['id'].'">'.$liste[$i]['nom'].'</option>';
					}
					echo '</select></p>';
				} else {
					echo '<p>Aucun(e) '.$val.' n\'est enregistré(e).</p>';
				}
			}
			echo '<p><label for="supplement">Supplément :</label><input type="text" size="25" maxlenght="25" name="supplement" id="supplement" value="'.$supplement.'" /></p><p><input type="hidden" name="niveau" id="niveau" value="1"><input type="submit" value="Modifier le menu"></p></form>';
		} else {
			$requete = new requete();
			$requete->update('menu', array('idEntree' => $entree, 'idViande' => $viande, 'idLegume' => $legume, 'idFromage' => $fromage, 'idDessert' => $dessert, 'supplement' => $supplement));
			$requete->where(array('menu' => array('id' => $id)));
			// echo $requete->requete_complete();
			$requete->executer_requete();
			// $erreur = array_merge($erreur, $requete->liste_erreurs);
			unset($requete);
			echo '<p>Le menu a bien été modifié.</p>';
		}
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}
	include('listerMenu.php');
}