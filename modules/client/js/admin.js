function reinit() {
	var id = document.getElementById("etablissement").value;
	var password = document.getElementById("password").value;
	
	var param = {'path':'admin', 'module':'etablissement', 'fonctions':'reinit', 'id':id, 'password':password};
	ajax(param);
}

function supprimer(id) {
	var param = {'path':'admin', 'module':'etablissement', 'fonctions':'supprimer', 'id':id}
	ajax(param);
	document.getElementById("tr_"+id).style.display = 'none';
	
}

function modifier(id) {
	if (document.getElementById("niveau") === null) {
		var param = {'path':'admin', 'module':'etablissement', 'fonctions':'modifier', 'id':id};
		ajax(param);
	} else {
		var id = document.getElementById("id").value;
		var niveau = document.getElementById("niveau").value;
		var etablissement = document.getElementById("etablissement").value;
		var lieu_depart = document.getElementById("lieu_depart").value;
		var adresse = document.getElementById("adresse").value;
		var ville = document.getElementById("ville").value;
		var contact = document.getElementById("contact").value;
		var contact_tel = document.getElementById("contact_tel").value;
		var contact_mail = document.getElementById("contact_mail").value;
		
		if (etablissement != "" && lieu_depart != "" && adresse != "" && ville != "" && contact != "" && contact_tel != "" && contact_mail != "" ) {
			var param = {'path':'admin', 'module':'etablissement', 'fonctions':'modifier', 'id':id, 'etablissement':etablissement, 'lieu_depart':lieu_depart, 'adresse':adresse, 'ville':ville, 'contact':contact, 'contact_tel':contact_tel, 'contact_mail':contact_mail};
			ajax(param);
		} else {
			document.getElementById("erreur").innerHTML = "Tous les champs doivent être remplis";
			return false;
		}
	}
}

function ajouter() {
	var etablissement = document.getElementById("etablissement").value;
	var password = document.getElementById("password").value;
	var lieu_depart = document.getElementById("lieu_depart").value;
	var adresse = document.getElementById("adresse").value;
	var ville = document.getElementById("ville").value;
	var contact = document.getElementById("contact").value;
	var contact_tel = document.getElementById("contact_tel").value;
	var contact_mail = document.getElementById("contact_mail").value;
	
	if (etablissement != "" && password != "" && lieu_depart != "" && adresse != "" && ville != "" && contact != "" && contact_tel != "" && contact_mail != "" ) {
		var param = {'path':'admin', 'module':'etablissement', 'fonctions':'ajouter', 'etablissement':etablissement, 'password':password, 'lieu_depart':lieu_depart, 'adresse':adresse, 'ville':ville, 'contact':contact, 'contact_tel':contact_tel, 'contact_mail':contact_mail};
		ajax(param);
	} else {
		document.getElementById("erreur").innerHTML = "Tous les champs doivent être remplis";
		return false;
	}
}