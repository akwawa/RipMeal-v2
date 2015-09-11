<?php

setlocale(LC_TIME, 'fr_FR.utf8','fra'); 

class modules {
	var $menu = '';
	var $sousmenu;
	var $tableau_modules;
	var $droit;
	var $page = 'all';

    function liste_menu($menuEnCours) {
		$this->liste_modules();
		$tab_modules = $this->tableau_modules;
		foreach ($tab_modules as $cle => $valeur) {
			if (isset($valeur[$this->page]['lien'])) {
				if (isset($valeur[$this->page]['conditions'])) {
					$conditions = $valeur[$this->page]['conditions'];
					$tab_conditions = explode(';',trim($conditions));
					foreach ($tab_conditions as $key) {
						if ($key) {
							list($condition, $val) = explode(':',trim($key));
							if (isset($_SESSION[$condition]) && $_SESSION[$condition] == $val) {
								$this->menu .= '<ul class="box_title"><li><a href="?menu='.$valeur[$this->page]['lien'].'&amp;sousmenu='.$valeur[$this->page]['default'].'">'.$valeur[$this->page]['menu'].'</a></li><li><ul>';
								// echo $valeur[$this->page]['lien'];
								$this->menu .= $this->liste_sousmenu($valeur[$this->page]['lien']);
								$this->menu .= '</ul></li></ul>';
							}
						}
					}
				} else {
					$this->menu .= '<ul class="box_title"><li><a href="?menu='.$valeur[$this->page]['lien'].'&amp;sousmenu='.$valeur[$this->page]['default'].'">'.$valeur[$this->page]['menu'].'</a></li><li><ul>';
					$this->menu .= $this->liste_sousmenu($valeur[$this->page]['lien']);
					$this->menu .= '</ul></li></ul>';
				}
			}
		}
		$this->menu .= '<div class="box_title"><a href="logout.php" onclick="logout(); return false;">Deconnexion</a></div>';
    }
	
	function liste_sousmenu($menuEnCours) {
		$fichier = 'modules/'.$menuEnCours.'/pages/'.$this->page.'/sousmenu.htm';
		$temp = '';
		$temp2 = '';
		if(file_exists($fichier)) {
			$fp = fopen($fichier, 'r');
			while (!feof($fp)) {
				$temp = trim(fgets($fp));
				if (strlen($temp)) {
					$temp2 .= '<li class="sousmenu">'.$temp.'</li>';
				}
			}
			fclose($fp);
			$this->sousmenu = $temp2;
		}
		return $temp2;
	}
	
	function liste_modules() {
		$tab_fichier = array();
		$chemin = 'modules';
		$repertoire = opendir($chemin) or die('Erreur pour lister les modules');
		while ($entree = readdir($repertoire)) {
			if ($entree != '.' || $entree != '..') {
				$tab_fichier[] = $entree;
			}
		}
		
		foreach ($tab_fichier as $fichier) {
			$array=array();
			$fichier = 'modules/'.$fichier.'/info.ini';
			if(file_exists($fichier) && $fichier_lecture=file($fichier)) {
				foreach($fichier_lecture as $ligne) {
					if(preg_match("#^\[(.*)\]\s+$#",$ligne,$matches)) {
						$groupe=$matches[1];
						$array[$groupe]=array();
					} elseif ($ligne[0] != ';' && trim($ligne) != '') {
						list($item, $valeur) = explode('=',trim($ligne),2);
						if(empty($valeur)) { $valeur=''; }
						$array[$groupe][$item]=$valeur;
						if(!empty($valeur)) { $array[$groupe][$item]=$valeur; }
					}
				}
			}
			if (isset($array['informations générales']['nom'])) {
				$tab_modules[$array['informations générales']['nom']] = $array;
			}
		}
		$this->tableau_modules = $tab_modules;
	}
}
?>