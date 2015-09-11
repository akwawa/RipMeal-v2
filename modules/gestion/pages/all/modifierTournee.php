<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	
	if ($_SESSION['rang'] == 'Administrateur') {
		$id = (empty($_GET['id']))?false:$_GET['id'];
		if ($id) {
			$niveau = (empty($_POST['niveau']))?false:$_POST['niveau'];
			if ($niveau == 1) {
				echo 'suivant';
			} elseif ($niveau == 2) {

			} else {
				$requete = new requete();
				$requete->select(array('personne' => array('id', 'nom', 'prenom', 'numPerTou')), 'p');
				$requete->where(array('p' => array('numTournee' => $id, 'corbeille' => false)));
				$requete->order('p.numPerTou');
				// echo $requete->requete_complete().'<br><br>';
				$requete->executer_requete();
				$liste = $requete->resultat;
				unset($requete);
				
				if ($liste) {
					$tableau = '<table><thead><tr><th>Nom</th><th>Numéro</th><th colspan="2">Action</th></tr></thead><tbody>';
					$nbPersonnes = count($liste);
					$ordreErreur = false;
					for ($i=0;$i<$nbPersonnes;$i++) {
						if (!$ordreErreur && $i <> $liste[$i]['p.numPerTou']) {
							echo $i.' '.$liste[$i]['p.numPerTou'].' '.$liste[$i]['p.nom'].' '.$liste[$i]['p.prenom'];
							$ordreErreur = true;
						}
						$tableau .= '<tr><td>'.$liste[$i]['p.nom'].' '.$liste[$i]['p.prenom'].'</td><td>'.$liste[$i]['p.numPerTou'].'</td><td onclick="modifOrder(this, 1, '.$liste[$i]['p.id'].');"><img class="fleche" src="modules/gestion/img/fleche_haut.jpeg"></td><td onclick="modifOrder(this, 2, '.$liste[$i]['p.id'].');"><img class="fleche" src="modules/gestion/img/fleche_bas.jpeg"></td></tr>';
					}
					$tableau .= '</tbody></table>';
					if ($ordreErreur) {
						echo '<p class="erreur">Les numéros de tournées ne sont pas bons.</p>
						<p><a onclick="reinitOrdre('.$id.');">Mettre à jours les numéros</a></p>';
					}
					echo $tableau;
				} else {
					echo '<p class="erreur">Aucune personne n\'est déclarée dans cette tournée.</p>';
				}
				}
			// if ($nom) {
				// $requete_membre = new requete();
				// $requete_membre->update($type, array('nom' => $nom));
				// $requete_membre->where(array($type => array('id' => $id)));
				// echo $requete_membre->requete_complete();
				// $requete_membre->executer_requete();
				// $erreur = array_merge($erreur, $requete_membre->liste_erreurs);
				// unset($requete_membre);
				// echo '<p>Le '.$type.' a bien été modifié.</p>';
			// } else {
				// echo '<p class="erreur">Erreur</p>';
			// }
		} else {
			$requete = new requete();
			$requete->select(array('COUNT' => array('personne' => 'id')), 'tpg');
			$requete->select(array('tournee' => array('id', 'nom')), 'r');
			$requete->group('r', 'id');
			$requete->join('personne', 'tournee', 'RIGHT');
			// echo $requete->requete_complete().'<br><br>';
			$requete->executer_requete();
			$liste = $requete->resultat;
			$erreur = array_merge($erreur, $requete->liste_erreurs);
			unset($requete);
			if ($liste) {
				echo '<table><thead><tr><th>Nom</th><th>Nombre de personne</th><th>Action</th></tr></thead><tbody>';
				foreach ($liste as $membre) {
					echo '<tr><td>'.$membre['r.nom'].'</td><td>'.$membre['COUNT(tpg.id)'].'</td><td><a href="?menu=gestion&amp;sousmenu=modifierTournee&amp;id='.$membre['r.id'].'">Modifier</a></td></tr>';
				}
				echo '</tbody></table>';
			} else {
				echo '<p class="erreur">Aucune tournée n\'est déclarée.</p>';
			}
		}
	} else {
		echo '<p class="erreur">Vous n\'avez pas les droits nécessaires pour effectuer cette action.</p>';
	}
}