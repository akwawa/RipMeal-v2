<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	if ($_SESSION['rang'] == 'Administrateur') {
		$entree = (empty($_POST['entree']))?false:$_POST['entree'];
		$viande = (empty($_POST['viande']))?false:$_POST['viande'];
		$legume = (empty($_POST['legume']))?false:$_POST['legume'];
		$fromage = (empty($_POST['fromage']))?false:$_POST['fromage'];
		$dessert = (empty($_POST['dessert']))?false:$_POST['dessert'];
		$supplement = (empty($_POST['supplement']))?false:$_POST['supplement'];

		if ($entree && $viande && $legume && $fromage && $dessert) {
		
			$requete = new requete();
			$requete->select('menu', 'm');
			$requete->where(array('m' => array('idEntree' => $entree, 'idViande' => $viande, 'idLegume' => $legume, 'idFromage' => $fromage, 'idDessert' => $dessert, 'supplement' => $supplement)));
			// echo $requete->requete_complete().'<br><br>';
			$requete->executer_requete();
			$liste = $requete->resultat;
			unset($requete);
	
			if (count($liste) == 0) {
				$requete_membre = new requete();
				$requete_membre->insert('menu', array('idEntree' => $entree, 'idViande' => $viande, 'idLegume' => $legume, 'idFromage' => $fromage, 'idDessert' => $dessert, 'supplement' => $supplement));
				// echo $requete_membre->requete_complete().'<br><br>';
				$requete_membre->executer_requete();
				$erreur = array_merge($erreur, $requete_membre->liste_erreurs);
				unset($requete_membre);
				echo '<p>Le menu a bien été créé.</p>';
			} else {
				echo '<p>Un menu identique existe déjà.</p>';
			}
		} else {
			echo '<form action="?'.$_SERVER['QUERY_STRING'].'" method="post">';

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
			echo '<p><label for="supplement">Supplément :</label><input type="text" size="25" maxlenght="25" name="supplement" id="supplement" value="'.$supplement.'" /></p><p><input type="submit" value="Ajouter le menu"></p></form>';
		}
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}
	include('listerMenu.php');
}