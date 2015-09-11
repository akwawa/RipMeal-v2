function lister_numPerTou(choix) {
	var Node = document.getElementById("numPerTou");
	var NodeListe = Node.getElementsByTagName("option");
	while (Node.firstChild) {
		Node.removeChild(Node.firstChild);
	}
	var anOption = document.createElement("option");
	document.getElementById("numPerTou").options.add(anOption);
	anOption.innerText = "en premier";
	anOption.value = "-1";

	var param = {"session":"all", "path":"client", "fonctions":"listerPersonneTournee", "idTournee":choix.value};
	var resultat = ajax_sans_ecrire(param);
	var objet = JSON.parse(resultat);
	for (var cle in objet) {
		var anOption = document.createElement("option");
		document.getElementById("numPerTou").options.add(anOption);
		anOption.innerText = objet[cle]["p.nom"]+" "+objet[cle]["p.prenom"];
		anOption.value = objet[cle]["p.numPerTou"];
	}
}
