function miseAJour(bouton) {
	var fichier = bouton.getAttribute("data-fichier");
	var version = bouton.getAttribute("data-version");
	var urlFichier = bouton.getAttribute("data-urlFichier");
	var erreur = false;
	var retour = false;
	
	var ul = document.createElement("ul");
	bouton.parentNode.appendChild(ul);
	bouton.parentNode.removeChild(bouton);
	
	{ /*** Téléchargement ***/
		var li = document.createElement("li");
		ul.appendChild(li);
		li.textContent = 'Téléchargement : en cours...';
		var param = {"session":"all", "path":"maj", "fonctions":"download", "version":version, "urlFichier":encodeURI(urlFichier)};
		var result = ajax(param);
		var retour = JSON.parse(result);
		if (!erreur && retour['resultat']) {
			li.style.color = "green";
			li.textContent = "Téléchargement : ";
			li.innerHTML = li.textContent+retour['resultat'];
		} else {
			erreur = true;
			li.style.color = "red";
			li.textContent = "Téléchargement : ERREUR - "+retour['erreur']+".";
		}
	}
	
	{ /*** SAV base de données ***/
		if (!erreur) {
			var li = document.createElement("li");
			ul.appendChild(li);
			li.textContent = 'Sauvegarde de la base de données : en cours...';
			var param = {"session":"all", "path":"maj", "fonctions":"sauvegardeBase"};
			var result = ajax_sans_ecrire(param);
			var retour = JSON.parse(result);
			if (!erreur && retour['resultat']) {
				li.style.color = "green";
				li.textContent = "Sauvegarde de la base de données : terminée avec succès.";
			} else {
				erreur = true;
				li.style.color = "red";
				li.textContent = "Sauvegarde de la base de données : ERREUR - "+retour['erreur']+".";
			}
		}
	}
	
	{ /*** ZIP répertoire ***/
		if (!erreur) {
			var li = document.createElement("li");
			ul.appendChild(li);
			li.textContent = 'Sauvegarde du logiciel : en cours...';
			var param = {"session":"all", "path":"maj", "fonctions":"zipLogiciel"};
			var retour = JSON.parse(ajax_sans_ecrire(param));
			if (!erreur && retour['resultat']) {
				li.style.color = "green";
				li.textContent = "Sauvegarde du logiciel : terminée avec succès.";
			} else {
				erreur = true;
				li.style.color = "red";
				li.textContent = "Sauvegarde du logiciel : ERREUR - "+retour['erreur']+".";
			}
		}
	}
	
	{ /*** Mise à jour ***/
		if (!erreur) {
			var li = document.createElement("li");
			ul.appendChild(li);
			li.textContent = 'Mise à jour : en cours...';
			var param = {"session":"all", "path":"maj", "fonctions":"miseAJour", "archive":fichier};
			var result = ajax(param);
			var retour = JSON.parse(result);
			if (!erreur && retour['resultat']) {
				li.style.color = "green";
				li.textContent = "Mise à jour : ";
				li.innerHTML = li.textContent+retour['resultat'];
			} else {
				erreur = true;
				li.style.color = "red";
				li.textContent = "Mise à jour : ERREUR - "+retour['erreur']+".";
			}
		}
	}
	
	{ /*** Changement de version ***/
		if (!erreur) {
			var li = document.createElement("li");
			ul.appendChild(li);
			li.textContent = 'Changement de version : en cours...';
			var param = {"session":"all", "path":"maj", "fonctions":"changementVersion", "version":version};
			var result = ajax(param);
			var retour = JSON.parse(result);
			if (!erreur && retour['resultat']) {
				li.style.color = "green";
				li.textContent = "Changement de version : ";
				li.innerHTML = li.textContent+retour['resultat'];
			} else {
				erreur = true;
				li.style.color = "red";
				li.textContent = "Changement de version : ERREUR - "+retour['erreur']+".";
			}
		}
	}

	if (erreur == false) {
		var li = document.createElement("li");
		ul.appendChild(li);
		var a = document.createElement("a");
		a.setAttribute('href', 'logout.php');
		a.textContent = 'Le logiciel devrait être relancé. Cliquez ici pour être déconnecté.';
		li.appendChild(a);
	} else {
		var li = document.createElement("li");
		li.style.color = "red";
		li.textContent = 'La mise à jour à échoué veuillez contacter le support.';
		ul.appendChild(li);
	}
}

function ajax_maj(param) {
	var retour = false;
	var data = '';
	
	var req = createInstance();
	req.onreadystatechange = function() {
		if(req.readyState == 4) {
			if(req.status == 200) {
				retour=req.responseText;
			} else {
				alert("Error: returned status code " + req.status + " " + req.statusText + " : "+chemin);
			}
		}
	};
	var chemin = 'modules/maj/fonctions/all/'+param['fichier']+'.php';
	req.open("POST", chemin, false);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.send(data);
	return retour;
}