<?php

if (empty($_SESSION)) { session_start(); }

if ($_SESSION) {
	// nom, prenom, sexe, adresse, codePostal, ville, telephone, telephoneSecond, numTournee, numPerTou, pain, potage, dateActif, sacPorte, info
	// actif
	// modifTou, debutHibernation, finHibernation, 
	$id = (empty($_GET['id']))?(empty($_POST['id']))?0:$_POST['id']:$_GET['id'];
	$niveau = (empty($_POST['niveau']))?false:$_POST['niveau'];
	$ancienNumPerTou = (empty($_POST['ancienNumPerTou']))?(-1):$_POST['ancienNumPerTou'];
	$ancienNumTournee = (empty($_POST['ancienNumTournee']))?(-1):$_POST['ancienNumTournee'];
	$trouve = true;
	if ($niveau) {
		$nom = (empty($_POST['nom']))?false:$_POST['nom'];
		$prenom = (empty($_POST['prenom']))?false:$_POST['prenom'];
		$sexe = (empty($_POST['sexe']))?false:$_POST['sexe'];
		$adresse = (empty($_POST['adresse']))?false:$_POST['adresse'];
		$codePostal = (empty($_POST['codePostal']))?false:$_POST['codePostal'];
		$ville = (empty($_POST['ville']))?false:$_POST['ville'];
		$telephone = (empty($_POST['telephone']))?false:$_POST['telephone'];
		$telephoneSecond = (empty($_POST['telephoneSecond']))?'':$_POST['telephoneSecond'];
		$numTournee = (empty($_POST['numTournee']))?false:$_POST['numTournee'];
		$numPerTou = (empty($_POST['numPerTou']))?(-1):$_POST['numPerTou'];
		$pain = (empty($_POST['pain']))?0:$_POST['pain'];
		$potage = (empty($_POST['potage']))?0:$_POST['potage'];
		$dateActif = (empty($_POST['dateActif']))?false:$_POST['dateActif'];
		$sacPorte = (empty($_POST['sacPorte']))?false:$_POST['sacPorte'];
		$info = (empty($_POST['info']))?'':$_POST['info'];
		$AlimentInterdit = (empty($_POST['AlimentInterdit']))?'':$_POST['AlimentInterdit'];

		if ($nom && $prenom && $sexe && $adresse && $codePostal && $ville && $telephone && $numTournee) {
			$dateActif = ($dateActif)?strtotime($dateActif):time();
			$actif = ($dateActif > time())?false:true;
			$numTournee = (is_numeric($numTournee))?$numTournee:0;
			$numPerTou = (is_numeric($numPerTou) && $numPerTou != "-1")?$numPerTou:0;

			$requete_membre = new requete();
			$requete_membre->requete_direct('UPDATE v2__personne SET numPerTou = numPerTou-1 WHERE v2__personne.numTournee = '.$ancienNumTournee.' AND numPerTou > '.$ancienNumPerTou);
			// echo $requete_membre->requete_complete();
			$requete_membre->executer_requete();
			$erreur = array_merge($erreur, $requete_membre->liste_erreurs);
			unset($requete_membre);
			
			$requete_membre = new requete();
			$requete_membre->requete_direct('UPDATE v2__personne SET numPerTou = numPerTou+1 WHERE v2__personne.numTournee = '.$numTournee.' AND numPerTou >= '.$numPerTou);
			// echo $requete_membre->requete_complete();
			$requete_membre->executer_requete();
			$erreur = array_merge($erreur, $requete_membre->liste_erreurs);
			unset($requete_membre);
			
			$requete = new requete();
			$requete->update('personne', array('nom' => $nom, 'prenom' => $prenom, 'sexe' => $sexe, 'adresse' => $adresse, 'codePostal' => $codePostal, 'ville' => $ville, 'telephone' => $telephone, 'telephoneSecond' => $telephoneSecond, 'numTournee' => $numTournee, 'numPerTou' => $numPerTou, 'pain' => $pain, 'potage' => $potage, 'dateActif' => $dateActif, 'sacPorte' => $sacPorte, 'actif' => $actif, 'info' => $info, 'AlimentInterdit' => $AlimentInterdit));
			$requete->where(array('personne' =>array('id' => $id)));
			// echo $requete->requete_complete();
			$requete->executer_requete();
			$erreur = array_merge($erreur, $requete->liste_erreurs);
			unset($requete);		
		
			echo '<p>Le client a bien été modifié.</p>';
			include('listerClients.php');
			$trouve = false;
		}
	} else {
		$requete = new requete();
		$requete->select('personne', 'p');
		$requete->where(array('p' => array('id' => $id)));
		// echo $requete->requete_complete();
		$requete->grand_tableau = false;
		$requete->executer_requete();
		$info_personne = $requete->resultat;
		$erreur = array_merge($erreur, $requete->liste_erreurs);
		unset($requete);

		// var_dump($info_personne);
		$nom = $info_personne['nom'];
		$prenom = $info_personne['prenom'];
		$sexe = $info_personne['sexe'];
		$adresse = $info_personne['adresse'];
		$codePostal = $info_personne['codePostal'];
		$ville = $info_personne['ville'];
		$telephone = $info_personne['telephone'];
		$telephoneSecond = $info_personne['telephoneSecond'];
		$numTournee = $info_personne['numTournee'];
		$numPerTou = $info_personne['numPerTou'];
		$pain = $info_personne['pain'];
		$potage = $info_personne['potage'];
		$dateActif = $info_personne['dateActif'];
		$sacPorte = $info_personne['sacPorte'];
		$info = $info_personne['info'];
		$AlimentInterdit = $info_personne['AlimentInterdit'];
		$ancienNumPerTou = $numPerTou;
		$ancienNumTournee = $numTournee;
	}
	
	if ($trouve) {
		echo '<a href="?menu=client&amp;sousmenu=modifierClientRepas&amp;idPersonne='.$id.'">Modifier les repas</a><form action="?menu=client&amp;sousmenu=modifierClient&amp;id='.$id.'" method="post"><p><label for="nom">Nom</label><input type="text" name="nom" id="nom" value="'.$nom.'" required/></p><p><label for="prenom">Prénom</label><input type="text" name="prenom" id="prenom" value="'.$prenom.'" required/></p><p>Sexe<br><label><input type="radio" name="sexe" value="M"'.(($sexe == 'M')?' checked':'').' required/>Masculin</label><br><label><input type="radio" name="sexe" value="F"'.(($sexe == 'F')?' checked':'').' />Féminin</label></p><p><label for="adresse">Adresse</label><input type="text" name="adresse" id="adresse" value="'.$adresse.'" required/></p><p><label for="codePostal">Code postal</label><input type="number" name="codePostal" id="codePostal" value="'.$codePostal.'" required/></p><p><label for="ville">Ville</label><input type="text" name="ville" id="ville" value="'.$ville.'" required/></p><p><label for="telephone">Téléphone</label><input type="text" name="telephone" id="telephone" value="'.$telephone.'" required/></p><p><label for="telephoneSecond">Téléphone secondaire</label><input type="text" name="telephoneSecond" id="telephoneSecond" value="'.$telephoneSecond.'" required/></p><p><label for="numTournee">Tournée</label>';

		$requete = new requete();
		$requete->select('tournee');
		$requete->executer_requete();
		$liste = $requete->resultat;
		$erreur = array_merge($erreur, $requete->liste_erreurs);
		unset($requete);
		echo '<select name="numTournee" size="'.count($liste).'" required>';
		foreach ($liste as $tournee) {
			if ($tournee['id'] == $numTournee) {
				echo '<option value="'.$tournee['id'].'" onclick="lister_numPerTou(this);" selected>'.$tournee['nom'].'</option>';
			} else {
				echo '<option value="'.$tournee['id'].'" onclick="lister_numPerTou(this);">'.$tournee['nom'].'</option>';
			}
		}
		echo '</select></p><p><label for="numPerTou">Inséré après</label><select id="numPerTou" name="numPerTou" required>';
		$requete = new requete();
		$requete->select(array('personne' => array('nom', 'prenom', 'numPerTou')), 'p');
		$requete->where(array('p' => array('numTournee' => $numTournee, 'corbeille' => false)));
		// echo $requete->requete_complete();
		$requete->order('p.numPerTou');
		$requete->executer_requete();
		$liste = $requete->resultat;
		unset($requete);
		$temp = array();
		$trouve = true;
		foreach ($liste as $tournee) {
			if ($tournee['p.numPerTou']+1 == $numPerTou) {
				$temp[] = '<option value="'.$tournee['p.numPerTou'].'" selected>'.$tournee['p.nom'].' '.$tournee['p.prenom'].'</option>';
				$trouve = false;
			} else {
				$temp[] = '<option value="'.$tournee['p.numPerTou'].'">'.$tournee['p.nom'].' '.$tournee['p.prenom'].'</option>';
			}
		}
		if ($trouve) {
			echo '<option value="-1" selected>en premier</option>'.implode($temp, '');
		} else {
			echo '<option value="-1">en premier</option>'.implode($temp, '');
		}
		echo '</select></p><p><label for="pain">Pain</label><input type="number" name="pain" id="pain" value="'.$pain.'" required/></p><p><label for="potage">Potage</label><input type="number" name="potage" id="potage" value="'.$potage.'" required/></p><p><label for="dateActif">Date actif</label><input type="Date" name="dateActif" id="dateActif" value="'.date("Y-m-d",$dateActif).'"></p><p>Sac à la porte<br><label><input type="radio" name="sacPorte" value="'.true.'"'.(($sacPorte)?' checked':'').'>Oui</label><br><label><input type="radio" name="sacPorte" value="'.false.'"'.(($sacPorte)?'':' checked').'>Non</label></p><p>Informations complémentaires<textarea name="info" id="info">'.$info.'</textarea></p><p>Aliments interdit :<textarea name="AlimentInterdit" id="AlimentInterdit">'.$AlimentInterdit.'</textarea></p><p><input type="hidden" name="niveau" id="niveau" value="2"><input type="hidden" name="ancienNumTournee" id="ancienNumTournee" value="'.$ancienNumTournee.'"><input type="hidden" name="ancienNumPerTou" id="ancienNumPerTou" value="'.$ancienNumPerTou.'"><input type="submit" value="Modifier le client"></p></form>';
	}
}