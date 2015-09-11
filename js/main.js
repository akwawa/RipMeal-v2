function details(param) {
	// var param = {"path":"client", "fonctions":"detailsClient", "session":"all", "id":id};
	
	var nouveauDiv = null;
	nouveauDiv = document.createElement("div");
	nouveauDiv.id = "fond_detail";
	nouveauDiv.onclick = function() { fermer_details(); }
	var taille_page = getDocumentSize();
	var hauteurActuelle = getScrollPosition();
	nouveauDiv.style.width = taille_page[0]+'px';
	nouveauDiv.style.height = taille_page[1]+'px';
	document.body.appendChild(nouveauDiv);

	var nouveauDiv = null;
	nouveauDiv = document.createElement("article");
	nouveauDiv.id = "apercu_details";
	nouveauDiv.innerHTML = "<section>Détails</section><div id='corps'></div><footer id='pied'></footer><a onclick='fermer_details();'>Fermer la fiche</a>";
	nouveauDiv.style.top = hauteurActuelle[1]+'px';
	document.body.appendChild(nouveauDiv);

	ecrire(ajax(param));
	
	return false;
}

function fermer_details() {
	var d = document.body; 
	d.removeChild(document.getElementById("fond_detail"));
	d.removeChild(document.getElementById("apercu_details"));
	
	return false;
}

function supprimer_article(id) {
	var reponse = confirm('Étes-vous sûr de vouloir supprimer cet article ?');
	
	if (reponse) {
		var param = {"session":"admin", "path":"editions", "fonctions":"supprimer_article", "id":id};
		ajax(param);
		alert('L\'article a bien été supprimé.');
		document.getElementById('article_'+id).style.display = 'none';
	}

	return false;
}

function getDocumentSize() {
	return new Array((document.documentElement && document.documentElement.scrollWidth) ? document.documentElement.scrollWidth : (document.body.scrollWidth > document.body.offsetWidth) ? document.body.scrollWidth : document.body.offsetWidth,(document.documentElement && document.documentElement.scrollHeight) ? document.documentElement.scrollHeight : (document.body.scrollHeight > document.body.offsetHeight) ? document.body.scrollHeight : document.body.offsetHeight);
}

function getScrollPosition() {
	return Array((document.documentElement && document.documentElement.scrollLeft) || window.pageXOffset || self.pageXOffset || document.body.scrollLeft,(document.documentElement && document.documentElement.scrollTop) || window.pageYOffset || self.pageYOffset || document.body.scrollTop);
}

function ajout_form_date() {
	var main_right = document.getElementById("main_right");
	if (main_right) {
		var form = document.getElementsByTagName("form");
		var taille = form.length;
		if (taille) {
			for (var i = 0; i < taille; i++) {
				idForm = form[i].getAttribute("id");
			}
		}
		if ((typeof(idForm) == "undefined") || (typeof(idForm) == "object" && idForm == null)) {
			var nouveauDiv = null;
			nouveauDiv = document.createElement("form");
			nouveauDiv.setAttribute('id', 'form_changeDate');
			nouveauDiv.setAttribute('method', 'post');
			nouveauDiv.setAttribute('action', document.location.href);
			nouveauDiv.setAttribute('name', 'form_changeDate');
			main_right.appendChild(nouveauDiv);
			idForm = nouveauDiv.getAttribute('id');
		}
		var nouveauDiv = null;
		nouveauDiv = document.createElement("label");
		nouveauDiv.setAttribute('for', 'dateCalendrier');
		nouveauDiv.innerHTML = 'Nouvelle date :';
		document.getElementById(idForm).appendChild(nouveauDiv);

		var nouveauDiv = null;
		nouveauDiv = document.createElement("input");
		nouveauDiv.setAttribute('type', 'date');
		nouveauDiv.setAttribute('id', 'dateCalendrier');
		nouveauDiv.setAttribute('name', 'dateCalendrier');
		document.getElementById(idForm).appendChild(nouveauDiv);

		if (!taille) {
			var nouveauDiv = null;
			nouveauDiv = document.createElement("input");
			nouveauDiv.setAttribute('type', 'submit');
			nouveauDiv.setAttribute('value', 'Changer la date');
			document.getElementById(idForm).appendChild(nouveauDiv);
		}
	}
}

