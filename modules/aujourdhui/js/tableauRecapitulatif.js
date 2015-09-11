window.onload=function() {
	
	var td = document.getElementsByTagName("td");
	for (var i = 0; i < td.length; i++) {
		td[i].oncontextmenu = function() { ajouter_commentaire(this); return false; };
	}
};

function ajouter_commentaire(cellule) {
	var timestampJour = cellule.childNodes[0].getAttribute("data-timestampJour");
	var idPersonne = cellule.childNodes[0].getAttribute("data-idPersonne");

	if (confirm('Voulez-vous ajouter un commentaire à ce repas ?')){
		var param = {"session":"all", "path":"aujourdhui", "fonctions":"ajouterCommentaireRepas", "idPersonne":idPersonne, "timestampJour":timestampJour};
		details(param);
		cellule.childNodes[0].style.color='red';
	}
}

function ajouter_commentaire_validation(form) {
	var idPersonne = form.elements["idPersonne"].value;
	var timestampJour = form.elements["timestampJour"].value;
	var commentaire = form.elements["commentaire"].value;
	
	var param = {"session":"all", "path":"aujourdhui", "fonctions":"ajouterCommentaireRepas", "idPersonne":idPersonne, "timestampJour":timestampJour, "typeAction":"suppression"};
	ajax_sans_ecrire(param);

	if (commentaire) {
		var param = {"session":"all", "path":"aujourdhui", "fonctions":"ajouterCommentaireRepas", "idPersonne":idPersonne, "timestampJour":timestampJour, "commentaire":commentaire, "typeAction":"ajout"};
		ajax_sans_ecrire(param);
	}

	fermer_details();
	return false;
}

function ajouter_repas(cellule) {
	var timestampJour = cellule.getAttribute("data-timestampJour");
	var idPersonne = cellule.getAttribute("data-idPersonne");

	/*
	var tab_jour=new Array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
	var ladate=new Date();
	ladate.setTime(timestampJour*1000);
	var nomJouAng = tab_jour[ladate.getDay()];
	*/
	if (cellule.checked) {
		var param = {"session":"all", "path":"aujourdhui", "fonctions":"ajouterRepasBleu", "idPersonne":idPersonne, "timestampJour":timestampJour};
		ajax_sans_ecrire(param);
		cellule.parentNode.className='etat_0';
	} else {
		if (confirm('Voulez-vous modifier ce repas ?')){
			var param = {"session":"all", "path":"aujourdhui", "fonctions":"modifierRepasBleu", "idPersonne":idPersonne, "timestampJour":timestampJour};
			details(param);
			cellule.parentNode.className='etat_1';
			cellule.checked = true;
		} else {
			if (confirm('Voulez-vous supprimer ce repas ?')){
				var param = {"session":"all", "path":"aujourdhui", "fonctions":"supprimerRepas", "idPersonne":idPersonne, "timestampJour":timestampJour};
				ajax_sans_ecrire(param);
				cellule.parentNode.className='etat_3';
			} else {
				cellule.checked = true;
			}
		}
	}
}

function selectLigne(cellule) {
	table = cellule.parentNode.parentNode.parentNode.parentNode;
	var coche = cellule.getAttribute("data-coche");
	if (!coche) { coche = "false"; }
	var idPersonne = cellule.getAttribute("data-idPersonne");

	if (coche == "false") {
		cellule.setAttribute("data-coche", "true");
		var cells = table.getElementsByTagName("input"); 
		for (var i = 0; i < cells.length; i++) {
			if (cells[i].type == "checkbox" && cells[i].parentNode.nodeName == "TD") {
				var status = cells[i].getAttribute("data-idPersonne");
				var typeRepas = cells[i].getAttribute("data-typeRepas");
				if (status == idPersonne && typeRepas == "3") {
					cells[i].checked=true;
					ajouter_repas(cells[i]);
				}
			}
		}
	} else {
		cellule.setAttribute("data-coche", "false");
		var cells = table.getElementsByTagName("input"); 
		for (var i = 0; i < cells.length; i++) {
			if (cells[i].type == "checkbox" && cells[i].parentNode.nodeName == "TD") {
				var status = cells[i].getAttribute("data-idPersonne");
				var timestampJour = cells[i].getAttribute("data-timestampJour");
				var etat = cells[i].parentNode.getAttribute("class");
				if (status == idPersonne && etat == "etat_0") {
					cells[i].checked = false;
					var param = {"session":"all", "path":"aujourdhui", "fonctions":"supprimerRepas", "idPersonne":idPersonne, "timestampJour":timestampJour};
					ajax_sans_ecrire(param);
					cells[i].parentNode.className='etat_3';
				}
			}
		}
	}
}

function selectAll(cellule) {
	table = cellule.parentNode.parentNode.parentNode.parentNode;
	var coche = cellule.getAttribute("data-coche");
	if (!coche) { coche = "false"; }
	var timestampJour = cellule.getAttribute("data-timestampJour");

	if (coche == "false") {
		cellule.setAttribute("data-coche", "true");
		var cells = table.getElementsByTagName("input"); 
		for (var i = 0; i < cells.length; i++) {
			if (cells[i].type == "checkbox" && cells[i].parentNode.nodeName == "TD") {
				var status = cells[i].getAttribute("data-timestampJour");
				var typeRepas = cells[i].getAttribute("data-typeRepas");
				if (status == timestampJour && typeRepas == "3") {
					cells[i].checked=true;
					ajouter_repas(cells[i]);
				}
			}
		}
	} else {
		cellule.setAttribute("data-coche", "false");
		var cells = table.getElementsByTagName("input"); 
		for (var i = 0; i < cells.length; i++) {
			if (cells[i].type == "checkbox" && cells[i].parentNode.nodeName == "TD") {
				var status = cells[i].getAttribute("data-timestampJour");
				var idPersonne = cells[i].getAttribute("data-idpersonne");
				var etat = cells[i].parentNode.getAttribute("class");
				if (status == timestampJour && etat == "etat_0") {
					cells[i].checked = false;
					var param = {"session":"all", "path":"aujourdhui", "fonctions":"supprimerRepas", "idPersonne":idPersonne, "timestampJour":timestampJour};
					ajax_sans_ecrire(param);
					cells[i].parentNode.className='etat_3';
				}
			}
		}
	}
}

function ajouter_repas_exceptionnel(cellule) {
	var timestampJour = cellule.getAttribute("data-timestampJour");
	var idPersonne = cellule.getAttribute("data-idPersonne");

	var tab_jour=new Array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
	var ladate=new Date();
	ladate.setTime(timestampJour*1000);
	var nomJouAng = tab_jour[ladate.getDay()];
	
	if (cellule.checked) {
		var param = {"session":"all", "path":"aujourdhui", "fonctions":"ajouterRepasExceptionnel", "idPersonne":idPersonne, "timestampJour":timestampJour};
		details(param);
		cellule.parentNode.className='etat_2';
	} else {
		var param = {"session":"all", "path":"aujourdhui", "fonctions":"supprimerRepas", "idPersonne":idPersonne, "timestampJour":timestampJour};
		ajax_sans_ecrire(param);
		cellule.parentNode.className='';
	}	
}

function ajouter_repas_exceptionnel_validation(form) {
	var idPersonne = form.elements["idPersonne"].value;
	var timestampJour = form.elements["timestampJour"].value;
	var typeRepas = form.elements["typeRepas"].value;
	
	if (typeRepas == "modifier") {
		var param = {"session":"all", "path":"aujourdhui", "fonctions":"supprimerRepas", "idPersonne":idPersonne, "timestampJour":timestampJour};
		ajax_sans_ecrire(param);
	}

	var cells = form.getElementsByTagName("input"); 
	for (var i = 0; i < cells.length; i++) {
		if (cells[i].type == "number") {
			if (cells[i].getAttribute("data-remp") != "true") {
				var id = cells[i].getAttribute("data-id");
				var table = cells[i].getAttribute("data-table");
				var typeJour = cells[i].getAttribute("data-typeJour");
				var quantite = cells[i].value;
				var quantiteRemp = document.getElementById("remp_"+typeJour+"_"+id+"_"+table).value;
				if ((parseInt(quantite)+parseInt(quantiteRemp)) > 0) {
					var param = {"session":"all", "path":"aujourdhui", "fonctions":"ajouterRepasExceptionnelValidation", "idPersonne":idPersonne, "timestampJour":timestampJour, "table":table, "id":id, "typeJour":typeJour, "quantite":quantite, "quantiteRemp":quantiteRemp, "typeRepas":typeRepas};
					ajax_sans_ecrire(param);
				}
			}
		}
	}
	fermer_details();
	return false;
}


