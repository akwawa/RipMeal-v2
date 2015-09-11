<?php
setlocale(LC_TIME, 'fr_FR.utf8','fra');

class requete {
	/***** VARIABLES *****/
	var $fichier_connexion = null;
	
	var $type_base = 'mysql'; // à changer suivant le type de base
	var $jeux_de_caracteres = 'utf8'; // à changer suivant l'encodage de la page
	/*********************/
	
	/***** PARAMETRES *****/
	var $afficher_erreurs = true;
	var $casse_compte = true;
	/**********************/

	/***** NE PAS TOUCHER *****/
	var $dbh = NULL;
	var $sel = '';
	var $prefixe = '';
	var $alias = false;
	var $requete = '';
	var $requete_complete = '';
	var $type_requete = 'SELECT';
	var $grand_tableau = true;
	var $update = array();
	var $delete = array();
	var $param = array();
	var $group = array();
	var $order = array();
	var $limit = false;
	var $tab_limit = array(0, 10);
	var $resultat = null;
	var $liste_erreurs = array();
	var $liste_tables = array();
	var $tableau_speciaux_requete = array('DISTINCT' => false);
	var $tableau_speciaux_champs = array('SUM', 'COUNT');
	/**************************/
	
	function __construct($connexion = NULL) {
		$this->fichier_connexion = (empty($connexion))?dirname(__FILE__).'\connectBDD.php':$connexion;
		if (file_exists($this->fichier_connexion)) {
			include($this->fichier_connexion);
			$this->sel = (empty($sel))?'':$sel;
			$this->prefixe = (empty($prefixe))?'':$prefixe;

			try {
				$temp = new PDO($this->type_base.':host='.$PARAM_hote.';dbname='.$PARAM_nom_bd,
								$PARAM_utilisateur, $PARAM_mot_passe,
								array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.(($this->jeux_de_caracteres)?$this->jeux_de_caracteres:'utf8')));
				$this->dbh = $temp;
			} catch(Exception $e) {
				$this->liste_erreurs['connexion'][] = $e->getMessage().'<br />N° : '.$e->getCode();
			}
		} else {
			$this->liste_erreurs['connexion'][] = 'Impossible de trouver le fichier de connexion';
		}
	}
	
	function __destruct() {
		// $this->afficher_requete();
		// $this->construire_requete();
		// $this->liste_erreurs['fin'][] =  'fin';
		// $this->gestion_des_erreurs();
		$this->dbh = null;
	}
	
	function reset() {
		$this->alias = false;
		$this->requete = '';
		$this->requete_complete = '';
		$this->type_requete = 'SELECT';
		$this->grand_tableau = true;
		$this->update = array();
		$this->delete = array();
		$this->param = array();
		$this->group = array();
		$this->order = array();
		$this->limit = array(0, 10);
		$this->resultat = null;
		$this->liste_erreurs = array();
		$this->liste_tables = array();
    }
	
	function gestion_des_erreurs() {
		if ($this->afficher_erreurs) {
			foreach ($this->liste_erreurs as $categorie_erreur => $tab_erreurs) {
				foreach ($tab_erreurs as $erreur) {
					echo $erreur.'<br>';
				}
			// switch (gettype($erreur)) {
			// type : boolean, integer, float, double, string, array, object, resource, NULL, callable
			}
		}
	}

	function majuscule($nom_table) {
		if (!$this->casse_compte) {
			if (gettype($nom_table) == 'array') {
				$nom_table = array_change_key_case($nom_table, CASE_UPPER);
				foreach ($nom_table as $key => $val) {
					if (gettype($val) == 'string') {
						$nom_table[$key] = strtoupper($val);
					} elseif (gettype($val) == 'array') {
						// $nom_table[$key] = array_map('strtoupper', $val);
						$nom_table[$key] = $this->majuscule($val);
					}
				}
			} elseif(gettype($nom_table) == 'string') {
				$nom_table = strtoupper($nom_table);
			}
		}
		return $nom_table;
	}
	
	function select($nom_table = NULL, $nom_raccourcis = NULL, $afficher = true) {
		$nom_table = $this->majuscule($nom_table);
		$nom_raccourcis = $this->majuscule($nom_raccourcis);

		if (gettype($nom_table) == 'string') {
			if (array_key_exists($nom_table, $this->tableau_speciaux_requete)) {
				$this->tableau_speciaux_requete[$nom_table] = true;
			} else {
				$nom_raccourcis = ($nom_raccourcis)?$nom_raccourcis:$nom_table;
				if (isset($this->liste_tables[$nom_raccourcis])) {
					$this->liste_erreurs['select'][] =  'Vous avez appelez plusieurs fois la table dont le nom raccourcis est "'.$nom_raccourcis.'".';
				}
				$this->liste_tables[$nom_raccourcis] = array('table' => $nom_table, 'champs' => array('*'));
			}
		} elseif (gettype($nom_table) == 'array') {
			foreach ($nom_table as $table => $champs) {
				$nom_raccourcis = ($nom_raccourcis)?$nom_raccourcis:$table;
				if (gettype($table) == 'integer') {
					$this->liste_tables[$champs] = array('table' => $champs, 'champs' => array('*'));
				} elseif(gettype($table) == 'string') {
					if (in_array($table, $this->tableau_speciaux_champs)) {
						if (gettype($champs) == 'array') {
							foreach ($champs as $temp_table => $temp_champs) {
								if (gettype($temp_champs) == 'string') {
									$temp_champs = array($temp_champs);
								}
								if (gettype($temp_champs) == 'array') {
									foreach ($temp_champs as $key) {
										if (!isset($this->liste_tables[$nom_raccourcis]['champs'][$table]) || !in_array($key, $this->liste_tables[$nom_raccourcis]['champs'][$table])) {
											$this->liste_tables[$nom_raccourcis]['table'] = $temp_table;
											$this->liste_tables[$nom_raccourcis]['champs'][$table][] = $key;
											// $this->liste_tables[$nom_raccourcis]['champs'][$table][$key]['alias'] = "ok";
										}
									}
								} else {
									$this->liste_erreurs['select'][] =  'Les paramètres pour les fonctions d\'agrégats doivent être passé par des tableaux ou une chaine pour la définition de(s) table(s) qui les concernent. Il sont : "'.gettype($temp_champs).'" pour la ligne "'.$temp_table.'"';
								}
							}
						} else {
							$this->liste_erreurs['select'][] =  'Les paramètres pour les fonctions d\'agrégats doivent être passé par des tableaux. Il sont : "'.gettype($champs).'" pour la ligne "'.$table.'"';
						}
					} else {
						if (gettype($champs) == 'string') { $champs = array($champs); }
						if (gettype($champs) == 'array') {
							////////
							if (isset($this->liste_tables[$nom_raccourcis]['champs'])) {
								if (in_array('*', $this->liste_tables[$nom_raccourcis]['champs']) || in_array('*', $champs)) {
									$this->liste_tables[$nom_raccourcis]['champs'] = array('*');
								} else {
									foreach ($champs as $key) {
										// if ($this->alias) {
											// if (!empty($key) && !in_array($key, $this->liste_tables[$nom_raccourcis]['champs'])) {
												// $this->liste_tables[$nom_raccourcis]['champs'][] = array($key, $nom_raccourcis.$key);
												// $this->liste_tables[$nom_raccourcis]['champs'][] = array('nom' => $val, 'alias' => $val);
											// }
										// } else {
											if (!empty($key) && !in_array($key, $this->liste_tables[$nom_raccourcis]['champs'])) {
												$this->liste_tables[$nom_raccourcis]['champs'][] = $key;
											}
										// }
									}
								}
							} else {
								foreach ($champs as $key => $val) {
									$this->liste_tables[$nom_raccourcis]['table'] = $table;
									if (gettype($key) == 'string') {
										$this->liste_tables[$nom_raccourcis]['champs'][] = array('nom' => $key, 'alias' => $val);
									} else {
										$this->liste_tables[$nom_raccourcis]['champs'][] = array('nom' => $val, 'alias' => $nom_raccourcis.'.'.$val);
									}
									
								}
							}
						} else {
							$this->liste_erreurs['select'][] =  'Les paramètres passées en champs ne correspondent pas aux attentes pour la table : "'.$table.'". Paramètre en tant que : "'.gettype($champs).'"';
						}
					}
				} else {
					$this->liste_erreurs['select'][] =  'Les paramètres passées en table ne correspondent pas aux attentes pour la table. Son type est dis : "'.gettype($table).'".';
				}
			}
		} else {
			$this->liste_erreurs['select'][] =  'Les paramètres passées en table ne correspondent pas aux attentes pour la table. Son type est dis : "'.gettype($nom_table).'".';
		}
	}

	function recherche_clef_etrangere($table) {
		// echo $table.'<br>';
		$retour = false;
		$sth = $this->dbh->prepare('SELECT table_name, column_name, referenced_table_name, referenced_column_name FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE referenced_table_name IS NOT NULL AND table_name = :table');
		$sth->bindParam(':table', $table);
		if ($sth->execute()) {
			$retour = $this->majuscule($sth->fetchAll());
			$sth->closeCursor();
		} else {
			$this->liste_erreurs['recherche_clef_etrangere'][] =  'Un problème concernant l\'execution de cette requete a été détecté.';
		}
		// echo count($retour).' '.$table.'<br>';
		return $retour;
	}

	/*
		INNER JOIN <table> ON <condition>
		LEFT | RIGHT | FULL OUTER JOIN <table> ON <condition>
		NATURAL JOIN <table> [USING <nom_colonne>]
		CROSS JOIN
		UNION JOIN
	*/
	function jointure($tableau_tables) { // raccourcis, table
		$tab_temp = Array();
		$tab_tab = Array();
		$premier = true;
		foreach ($tableau_tables as $a => $table) {
			// var_dump($table);
			$test = false;
			$clef = $this->recherche_clef_etrangere($table[0]);
			// var_dump($clef);
			if (is_array($clef) && count($clef) > 0) {
				// var_dump($clef);
				
				foreach ($clef as $i) {
					foreach ($tableau_tables as $b => $c) {
						if ($i['referenced_table_name'] == $c[0]) {
							if (in_array($i['referenced_table_name'], $tab_tab)) {
								$test = array($i['referenced_table_name'], $table[0]);
							} else {
								$tab_tab[] = $i['referenced_table_name'];
							}
							if ($premier) { $premier = false; $tab_tab[] = $table[0]; }
							$c[1][0] = (empty($c[1][0]))?'INNER':$c[1][0];
							$tab_temp[$table[0]]['table'] = $a;
							$tab_temp[$table[0]][$c[1][0]][$i['referenced_table_name']] = Array($a, $i['column_name'], $b, $i['referenced_column_name']);
						}
					}
				}
			}
			if (!empty($test)) {
				if (isset($tab_temp[$test[1]]) && $tab_temp[$test[0]]) {
					$tab_tab[] = $test[0];
					if (empty($tab_temp[$test[1]]['INNER'])) {
						$tab_temp[$test[1]]['INNER'] = array();
					}
					if (empty($tab_temp[$test[1]]['OUTER'])) {
						$tab_temp[$test[1]]['OUTER'] = array();
					}
					if (empty($tab_temp[$test[1]]['LEFT'])) {
						$tab_temp[$test[1]]['LEFT'] = array();
					}
					if (empty($tab_temp[$test[1]]['RIGHT'])) {
						$tab_temp[$test[1]]['RIGHT'] = array();
					}
					if (isset($tab_temp[$test[0]]['INNER'])) {
						$tab_temp[$test[1]]['INNER'] = array_merge($tab_temp[$test[1]]['INNER'], $tab_temp[$test[0]]['INNER']);
					}
					if (isset($tab_temp[$test[0]]['OUTER'])) {
						$tab_temp[$test[1]]['OUTER'] = array_merge($tab_temp[$test[1]]['OUTER'], $tab_temp[$test[0]]['OUTER']);
					}
					if (isset($tab_temp[$test[0]]['LEFT'])) {
						$tab_temp[$test[1]]['LEFT'] = array_merge($tab_temp[$test[1]]['LEFT'], $tab_temp[$test[0]]['LEFT']);
					}
					if (isset($tab_temp[$test[0]]['RIGHT'])) {
						$tab_temp[$test[1]]['RIGHT'] = array_merge($tab_temp[$test[1]]['RIGHT'], $tab_temp[$test[0]]['RIGHT']);
					}
				}
				unset($tab_temp[$test[0]]);
			}
		}
		// echo count($tab_tab).'<br>';
		// var_dump($tab_tab);
		foreach ($tableau_tables as $a => $table) {
			if (!in_array($table[0], $tab_tab)) {
				$tab_temp[$table[0]]['table'] = $a;
				$tab_temp[$table[0]]['CROSS'][] = $a;
			}
		}
		// echo count($tab_temp).'<br>';
		// if (count($tab_temp) > 1) {
			// var_dump($tab_temp);
		// }
		return $tab_temp;
	}

	function recherche_condition_existe($nom_colonne, $tab) {
		$condition = ':'.$nom_colonne;
		if (is_array($tab)) {
			$niveau = 0;
			do {
				$trouve = false;
				foreach($tab as $val) {
					if (isset($val['nom']) && $val['nom'] == $condition) {
						$trouve = true;
						$condition = ':'.$nom_colonne.$niveau;
						$niveau++;
						unset($val);
						break;
					}
				}
			} while ($trouve);
		}
		// echo $condition.'<br>';
		return $condition;
	}
	
	function where($tab_table = NULL) {
		if (is_array($tab_table)) {
			$tab_table = $this->majuscule($tab_table);
			foreach($tab_table as $nom_table => $tab_conditions) {
				if (empty($this->liste_tables[$nom_table])) {
					$this->liste_tables[$nom_table] = array('table' => $nom_table, 'champs' => array('*'));
				}
				foreach($tab_conditions as $nom_colonne => $condition) {
					if (is_array($condition)) {
						$salage = (empty($condition['SALAGE']))?false:$condition['SALAGE'];
						if ($salage) {
							$valeur = (empty($condition['VALEUR']))?($condition[0].$this->sel):($condition['VALEUR'].$this->sel);

							$typeHash = (empty($condition['TYPEHASH']))?'sha1':$condition['TYPEHASH'];
							switch ($typeHash) {
								case 'sha1' :
									$valeur = (empty($condition['VALEUR']))?sha1($condition[0].$this->sel):sha1($condition['VALEUR'].$this->sel);
									break;
								case 'md5' :
									$valeur = (empty($condition['VALEUR']))?md5($condition[0].$this->sel):md5($condition['VALEUR'].$this->sel);
									break;
							}
						} else {
							$valeur = (empty($condition['VALEUR']))?$condition[0]:$condition['VALEUR'];
						}
						$operateur = (empty($condition['OPERATEUR']))?'=':$condition['OPERATEUR'];
						// switch($operateur) {
							// case '>':
							// case '<':
							// case '=':
							// case 'LIKE':
						$type = (empty($condition['TYPE']))?gettype($valeur):$condition['TYPE'];
						$conjonction = (empty($condition['CONJONCTION']))?'AND':$condition['CONJONCTION'];
								// break;
						// }
					} else {
						$valeur = $condition;
						$type = gettype($valeur);
						$operateur = '=';
						$conjonction = 'AND';
					}
					$nom_condition = $this->recherche_condition_existe($nom_colonne, $this->param);
					$temp = Array(
						'table' => $nom_table,
						'colonne' => $nom_colonne,
						'nom' => $nom_condition,
						'valeur' => $valeur,
						'type' => $type,
						'operateur' => $operateur,
						'conjonction' => $conjonction
						);
					// var_dump($temp);
					$this->param[] = $temp;
				}
			}
		} else {
			$this->liste_erreurs['where'][] =  'Le premier paramètre de la fonction "where" dois être un tableau. Vous avez passé un "'.gettype($tab_table).'".';
		}
	}	
	
	function group($table = NULL, $colonne = NULL) {
		if (is_string($table)) {
			if (is_string($colonne)) {
				$this->group[] = $this->majuscule($table.'.'.$colonne);
			} else {
				$this->liste_erreurs['limit'][] =  'Le deuxième paramètre de la fonction "group" dois être une chaine de caractères. Vous avez passé un "'.gettype($colonne).'".';
			}
		} else {
			$this->liste_erreurs['limit'][] =  'Le premier paramètre de la fonction "group" dois être une chaine de caractères. Vous avez passé un "'.gettype($table).'".';
		}
	}
	
	function order($colonnes = NULL) {
		if (is_array($colonnes)) {
			$colonnes = $this->majuscule($colonnes);
			foreach ($colonnes as $nom => $valeur) {
				if (gettype($valeur) == 'string') {
					$this->order[] = $nom.'.'.$valeur.' ASC';
				} elseif (gettype($valeur) == 'array') {
					foreach ($valeur as $colonne => $tri) {
						if (gettype($colonne) == 'integer') {
							$this->order[] = $nom.'.'.$tri.' ASC';
						} else {
							$this->order[] = $nom.'.'.$colonne.' '.$tri;
						}
					}
				}
			}
		} elseif (gettype($colonnes) == 'string') {
			$this->order[] = $colonnes.' ASC';
		} else {
			$this->liste_erreurs['order'][] =  'Le paramètre de la fonction "order" dois être soit une chaine de caractère soit un tableau. Vous avez passé un "'.gettype($colonnes).'".';
		}
	}
	
	function limit($nombre = NULL, $debut = 0) {
		if (is_numeric($nombre) && $nombre > 0) {
			if (is_numeric($debut) && $debut > -1) {
				$this->limit=true;
				$this->tab_limit[0] = intval($debut);
				$this->tab_limit[1] = intval($nombre);
			} else {
				$this->liste_erreurs['limit'][] =  'Le deuxième paramètre de la fonction "limit" dois être un entier supérieur ou égal à 0. Vous avez passé un "'.gettype($debut).'".';
			}
		} else {
			$this->liste_erreurs['limit'][] =  'Le premier paramètre de la fonction "limit" dois être un entier supérieur à 0. Vous avez passé un "'.gettype($nombre).'".';
		}
	}
	
	function join($table_1 = NULL, $table_2 = NULL, $typeJointure = NULL) {
		if ($table_1 && $table_2) {
			$table_1 = $this->majuscule($table_1);
			$table_2 = $this->majuscule($table_2);
			foreach ($this->liste_tables as $raccourcis_table => $info_table) {
				if ($info_table['table'] == $table_1) {
					$this->liste_tables[$raccourcis_table]['join'] = array($typeJointure, $table_2);
				} elseif ($info_table['table'] == $table_2) {
					$this->liste_tables[$raccourcis_table]['join'] = array($typeJointure, $table_1);
				}
				// $tab_tables[$raccourcis_table] = $this->prefixe.$info_table['table'];
			}
		} else {
			$this->liste_erreurs['join'][] =  'Les paramètres de la fonction "join" doivent être des chaines de caractères. Vous avez passé un "'.gettype($table_1).'" et "'.gettype($table_1).'".';
		}
	}
	
	function construire_requete() {
		$tab_select = array();
		$tab_tables = array();
		$tab_from = array();
		$requete = '';
		// var_dump($this->liste_tables);

		switch ($this->type_requete) {
			case 'SELECT' : {
				foreach ($this->liste_tables as $raccourcis_table => $info_table) {
					$info_table['join'] = (empty($info_table['join']))?'':$info_table['join'];
					$tab_tables[$raccourcis_table] = array($this->prefixe.$info_table['table'], $info_table['join']);
					// var_dump($info_table['champs']);
					foreach ($info_table['champs'] as $cle => $val) {
						if (is_array($val)) {
							if (in_array($cle, $this->tableau_speciaux_champs, true)) {
								foreach ($val as $cle2 => $val2) {
									$tab_select[] = $cle.'('.$raccourcis_table.'.'.$val2.')';
								}
							} elseif ($val['alias']) {
								$tab_select[] = $raccourcis_table.'.'.$val['nom'].' AS "'.$val['alias'].'"';
							} else {
								echo 'erreur construire requete';
							}
						} elseif(!empty($val)) {
							// echo $cle.' '.$val.'<br>';
							if ($this->alias && $val != '*') {
								$tab_select[] = $raccourcis_table.'.'.$val.' AS "'.$raccourcis_table.'.'.$val.'"';
							} else {
								$tab_select[] = $raccourcis_table.'.'.$val;
							}
						}
					}
				}
				$requete = 'SELECT ';
				foreach($this->tableau_speciaux_requete as $key => $val) {
					if ($val) {
						$requete .= $key.' ';
					}
				}
				$requete .= implode($tab_select, ', ').' FROM ';
				$premier = false;
				$tab_dejaVu = array();
				foreach ($this->jointure($tab_tables) as $raccourcis_table => $info_table) {
					if ($premier) {
						if (!in_array($info_table['table'], $tab_dejaVu)) {
							$requete .= ', '; 
						}
					} else {
						$requete .= $raccourcis_table.' '.$info_table['table'];
					}
					$tab_dejaVu[] = $info_table['table'];
					foreach ($info_table as $a => $b) {
						switch($a) {
							case 'RIGHT' :
							case 'LEFT' :
							case 'INNER' : {
								foreach ($b as $table_jointure => $valeur_jointure) {
									$tab_dejaVu[] = $valeur_jointure[2];
									$requete .= ' '.$a.' JOIN '.$table_jointure.' '.$valeur_jointure[2].' ON '.$valeur_jointure[0].'.'.$valeur_jointure[1].' = '.$valeur_jointure[2].'.'.$valeur_jointure[3];
								}
								break; }
							case 'CROSS' : {
								// echo $info_table['table'].' '.$raccourcis_table;
								break; }
						}
					}
					$premier = true;
				}
				
				$taille = count($this->param);
				if ($taille > 0) {
					$requete .= ' WHERE';
					$tab_conditions = $this->param;
					for ($i=0; $i<$taille; $i++) {
						if ($i > 0) {
							$requete .= ' '.$tab_conditions[$i]['conjonction'];
						}
						$requete .= ' '.$tab_conditions[$i]['table'].'.'.$tab_conditions[$i]['colonne'].' '.$tab_conditions[$i]['operateur'].' '.$tab_conditions[$i]['nom'];
					}
					// 'table', 'colonne', 'nom', 'valeur', 'type', 'operateur', 'conjonction'
				}
				
				if (count($this->group) > 0) {
					$requete .= ' GROUP BY '.implode($this->group, ', ');
				}
				
				if (count($this->order) > 0) {
					$requete .= ' ORDER BY '.implode($this->order, ', ');
				}

				if ($this->limit) {
					$requete .= ' LIMIT '.$this->tab_limit[0].', '.$this->tab_limit[1];
				}
				break; }
			
			case 'INSERT': {
				$requete = $this->requete;
				break; }
			
			case 'UPDATE': {
				// UPDATE articles SET titre = "test titre 1", test = "titre" WHERE articles.id = 1;
				$tab_update = $this->update;
				$taille_update = count($tab_update['champs']);
				$tab_conditions = $this->param;
				$taille_conditions = count($tab_conditions);
				// array($i => array(table =>, colonne => nom =>, valeur =>, type =>, operateur =>, conjonction =>))
				
				$tab_champs = array();
				$requete = 'UPDATE '.$this->prefixe.$tab_update['nom_table'].' SET ';
				for ($i=0; $i<$taille_update; $i++) {
					// nom, valeur, type
					$this->param[] = array('table' => $tab_update['nom_table'], 'colonne' => $tab_update['champs'][$i]['nom'], 'operateur' => 'AND', 'nom' => ':'.$tab_update['champs'][$i]['nom'], 'valeur' => $tab_update['champs'][$i]['valeur'], 'type' => $tab_update['champs'][$i]['type']);
					if ($i > 0 && $i < $taille_update) { $requete .= ', '; }
					$requete .= $tab_update['champs'][$i]['nom'].' = :'.$tab_update['champs'][$i]['nom'];
				}
				if ($taille_conditions > 0) {
					$requete .= ' WHERE';
					for ($i=0; $i<$taille_conditions; $i++) {
						if ($i > 0) {
							$tab_conditions[$i]['conjonction'] = (empty($tab_conditions[$i]['conjonction']))?'AND':$tab_conditions[$i]['conjonction'];
							$requete .= ' '.$tab_conditions[$i]['conjonction'];
						}
						$requete .= ' '.$this->prefixe.$tab_conditions[$i]['table'].'.'.$tab_conditions[$i]['colonne'].' '.$tab_conditions[$i]['operateur'].' '.$tab_conditions[$i]['nom'];
					}
					// 'table', 'colonne', 'nom', 'valeur', 'type', 'operateur', 'conjonction'
				}
				break; }
				
			case 'DELETE': {
				$tab_typeRequete = $this->delete;
				$tab_conditions = $this->param;
				$taille_conditions = count($tab_conditions);
				
				$tab_champs = array();
				$requete = 'DELETE FROM '.$this->prefixe.$tab_typeRequete['nom_table'];
				if ($taille_conditions > 0) {
					$requete .= ' WHERE';
					for ($i=0; $i<$taille_conditions; $i++) {
						if ($i > 0) {
							$requete .= ' '.$tab_conditions[$i]['conjonction'];
						}
						$requete .= ' '.$this->prefixe.$tab_conditions[$i]['table'].'.'.$tab_conditions[$i]['colonne'].' '.$tab_conditions[$i]['operateur'].' '.$tab_conditions[$i]['nom'];
					}
				}
				break; }
				
			case 'DIRECT': {
				$requete = $this->requete;
				break; }
		}
		
		$this->requete = $requete;
	}
	
	function requete_complete() {
		$this->construire_requete();
		$retour = $this->requete;
		foreach($this->param as $conditions) {
			switch($conditions['type']) {
				case 'integer':
				case 'double':
					$retour = str_replace($conditions['nom'], $conditions['valeur'], $retour);
					break;
				case 'boolean':
					$temp_val = ($conditions['valeur'])?1:0;
					$retour = str_replace($conditions['nom'], $temp_val, $retour);
					unset($temp_val);
					break;
				default:
					$retour = str_replace($conditions['nom'], '"'.$conditions['valeur'].'"', $retour);
			}
		}
		$this->requete_complete = $retour;
		return $retour;
	}
	
	function executer_requete() {
		$retour = false;
		$this->construire_requete();
		$requete = $this->requete;
		$sth = $this->dbh->prepare($requete);
	
		foreach($this->param as $conditions) {
			switch($conditions['type']) {
				case 'integer':
				case 'double':
					$sth->bindParam($conditions['nom'], $conditions['valeur'], PDO::PARAM_INT);
					break;
				case 'string':
				case 'boolean':
					$sth->bindParam($conditions['nom'], $conditions['valeur']);
					break;
				default:
					echo '|--'.$conditions['nom'].' | '.$conditions['type'].'--|<br>';
			}
		}
		if ($sth->execute()) {
			if ($this->grand_tableau) {
				$retour = $sth->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$retour = $sth->fetch(PDO::FETCH_ASSOC);
			}
			$sth->closeCursor();
		} else {
			/*$sth = $this->dbh->prepare('SHOW TABLES'); */
			$this->liste_erreurs['executer_requete'][] =  'Un problème concernant l\'execution de cette requete a été détecté.';
		}
		$this->resultat = $retour;
		return $retour;
	}
	
	function requete_direct($requete = NULL) {
		$this->type_requete = 'DIRECT';
		$this->requete = $requete;
	}
	
	/////////////////
	function insert($nom_table = NULL, $tab_champs = NULL) {
		if (gettype($nom_table) == 'string') {
			if (gettype($tab_champs) == 'array' && count($tab_champs) > 0) {
				$this->type_requete = 'INSERT';
				$tab_temp = array();
				foreach ($tab_champs as $nom_champs => $valeur_champs) {
					if (gettype($valeur_champs) == 'array') {
						$typeHash = (empty($valeur_champs['TYPEHASH']))?'sha1':$valeur_champs['TYPEHASH'];
						switch ($typeHash) {
							case 'sha1' :
								$valeur = (empty($valeur_champs['VALEUR']))?sha1($valeur_champs[0].$this->sel):sha1($valeur_champs['VALEUR'].$this->sel);
								break;
							case 'md5' :
								$valeur = (empty($valeur_champs['VALEUR']))?md5($valeur_champs[0].$this->sel):md5($valeur_champs['VALEUR'].$this->sel);
								break;
						}
						// echo $valeur.'<br>'.$valeur_champs['VALEUR'].'<br>'.$this->sel.'<br>';
						$tab_temp[] = $nom_champs;
						$this->param[] = Array(
							'nom' => ':'.$nom_champs,
							'valeur' => $valeur,
							'type' => gettype($valeur),
							);

					} else {
						$tab_temp[] = $nom_champs;
						$this->param[] = Array(
							'nom' => ':'.$nom_champs,
							'valeur' => $valeur_champs,
							'type' => gettype($valeur_champs),
							);
					}
				}
				$temp = 'INSERT INTO '.$this->prefixe.$nom_table.' ('.implode($tab_temp, ', ').') VALUES (:'.implode($tab_temp, ', :').')';
				$this->requete = $temp;
			} else {
				$this->liste_erreurs['insert'][] =  'Vous devez passer un tableau associatif non-vide de type : nomDuChamps => valeur. Vous avez passé "'.gettype($tab_champs).'" à la place d\'un tableau associatif.';
			}
		} else {
			$this->liste_erreurs['insert'][] =  'Vous devez passer le nom de la table en premier paramètre. Vous avez passé "'.gettype($nom_table).'" à la place d\'une chaine de caractère.';
		}
	}

	/////////////////////
	function update($nom_table = NULL, $tab_champs = NULL) {
		if (gettype($nom_table) == 'string') {
			if (gettype($tab_champs) == 'array' && count($tab_champs) > 0) {
				$this->type_requete = 'UPDATE';
				$tab_temp = array('nom_table' => $nom_table);
				foreach ($tab_champs as $nom_champs => $valeur_champs) {
					$tab_temp['champs'][] = Array(
						'nom' => $nom_champs,
						'valeur' => $valeur_champs,
						'type' => gettype($valeur_champs),
						);
				}
				$this->update = $tab_temp;
			} else {
				$this->liste_erreurs['update'][] =  'Vous devez passer un tableau associatif non-vide de type : nomDuChamps => valeur. Vous avez passé "'.gettype($tab_champs).'" à la place d\'un tableau associatif.';
			}
		} else {
			$this->liste_erreurs['update'][] =  'Vous devez passer le nom de la table en premier paramètre. Vous avez passé "'.gettype($nom_table).'" à la place d\'une chaine de caractère.';
		}
	}

	/////////////////
	function delete($nom_table = NULL, $tab_champs = NULL) {
		if (gettype($nom_table) == 'string') {
			if (gettype($tab_champs) == 'array' && count($tab_champs) > 0) {
				$this->type_requete = 'DELETE';
				$tab_temp = array('nom_table' => $nom_table);
				$this->where($tab_champs);
				// foreach ($tab_champs as $nom_champs => $valeur_champs) {
					// $this->param[] = Array(
						// 'nom' => ':'.$nom_champs,
						// 'valeur' => $valeur_champs,
						// 'type' => gettype($valeur_champs),
						// );
				// }
				$this->delete = $tab_temp;
			} else {
				$this->liste_erreurs['delete'][] =  'Vous devez passer un tableau associatif non-vide de type : nomDuChamps => valeur. Vous avez passé "'.gettype($tab_champs).'" à la place d\'un tableau associatif.';
			}
		} else {
			$this->liste_erreurs['delete'][] =  'Vous devez passer le nom de la table en premier paramètre. Vous avez passé "'.gettype($nom_table).'" à la place d\'une chaine de caractère.';
		}
	}
}

