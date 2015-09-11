function modifOrder(cell, ordre, idPersonne) {
	var param = {"session":"all", "path":"gestion", "fonctions":"modifOrdre", "idPersonne":idPersonne, "ordre":ordre};
	var resultat = ajax_sans_ecrire(param);
	if (resultat != "true") {
		alert(resultat);
	} else {
		var ligne = cell.parentNode;
		if (ordre == 1) {
			var precedent = ligne;
			var suivant = ligne.previousSibling;
		} else if (ordre == 2) {
			var precedent = ligne.nextSibling;
			var suivant = ligne;
		}
		ligne.parentNode.insertBefore(precedent, suivant);
		precedent.childNodes[1].textContent = precedent.childNodes[1].textContent-1;
		suivant.childNodes[1].textContent = suivant.childNodes[1].textContent-1+2;
	}
}

function reinitOrdre(idTournee) {
	var param = {"session":"all", "path":"gestion", "fonctions":"reinitOrdre", "idTournee":idTournee};
	var resultat = ajax_sans_ecrire(param);
	if (resultat == "true") {
		location.href="?menu=gestion&sousmenu=modifierTournee&id="+idTournee;
	}
}