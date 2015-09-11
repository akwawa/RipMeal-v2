var objPersonnes = new Array();
var objTrajets = new Array();
var objDistance = new Array();
var objDuration = new Array();
var nbRequetes = 0;
var maxRequetes = 2000;

/***** RESTE A FAIRE *****/
/* Faire un lien pour modifier l'adresse */
/* Recalculer ensuite */
/*  */
/*************************/

function demarrer(form) {
	var adresse = form.elements["adresse"].value;
	var codePostal = form.elements["codePostal"].value;
	var ville = form.elements["ville"].value;
	var nombre = form.elements["nombre"].value;
	testAdresse(adresse, codePostal, ville);
	listerPersonnes(nombre);
	calculTrajets();
	etablirChemins();
	ecrireTableaux();
	
	return false;
}

function testAdresse(adresse, codePostal, ville) {
	var param = {"session":"all", "path":"trajets", "fonctions":"testAdresse", "adresse":adresse, "codePostal":codePostal, "ville":ville};
	var resultat = ajax(param);
	if (resultat == "false") {
		var origins = adresse+", "+codePostal+" "+ville+", France";
		if (nbRequetes <= maxRequetes) {
			var param = {"chemin":"http://maps.googleapis.com/maps/api/geocode/json?address="+encodeURIComponent(origins).replace(/'/g,"%27").replace(/"/g,"%22")+"&sensor=true"};
			var resultat = JSON.parse(ajax(param));
			nbRequetes++;
			if (resultat["status"] == "OK") {
				if (resultat["results"][0]["geometry"]["location"]) {
					if (resultat["results"][0]["geometry"]["location_type"] == "RANGE_INTERPOLATED" || resultat["results"][0]["geometry"]["location_type"] == "ROOFTOP") {
						var lat = resultat["results"][0]["geometry"]["location"]["lat"];
						var lng = resultat["results"][0]["geometry"]["location"]["lng"];
						adresseDepart = resultat["results"][0]["formatted_address"];
						ajouterCoordonnees(adresse, codePostal, ville, lat, lng, adresseDepart);
					} else {
						alert("Erreur : "+resultat["results"][0]["geometry"]["location_type"]);
					}
				}
			} else {
				alert(resultat["status"]);
			}
		}
	} else {
		resultat = JSON.parse(resultat);
		objPersonnes.push({"nom":"adresse", "prenom":"depart", "adresse":adresse, "codePostal":codePostal, "ville":ville, "c.id":resultat[0]["id"], "lat":resultat[0]["lat"], "lng":resultat[0]["lng"], "formatted_address":resultat[0]["formatted_address"]});
		adresseDepart = resultat[0]["formatted_address"];
	}
}

function modifierAdresse(idPersonne) {
	var param = {"session":"all", "path":"trajets", "fonctions":"modifierAdresse", "id":idPersonne};
	details(param);
}

function validerFormAdresse(form) {
	var adresse = form.elements["adresse"].value;
	var codePostal = form.elements["codePostal"].value;
	var ville = form.elements["ville"].value;
	var idPersonne = form.elements["idPersonne"].value;
	
	var origins = adresse+", "+codePostal+" "+ville+", France";
	var resultat = validerAdresse(origins, idPersonne);
	
	if (resultat["erreur"]) {
		alert(resultat["erreur"]);
	} else if (resultat["noerror"]) {
		if (form.elements["enregistrer"].disabled) {
			form.elements["enregistrer"].disabled = false;
			form.elements["valider"].disabled = true;
		} else {
			var param = {"session":"all", "path":"trajets", "fonctions":"enregistrerAdresse", "adresse":adresse, "codePostal":codePostal, "ville":ville, "idPersonne":idPersonne};
			ajax(param);
			alert('Modification effectuée');
			fermer_details();
		}
	}
	
	return false;
}

function validerAdresse(origins, idPersonne) {
	if (nbRequetes <= maxRequetes) {
		var param = {"chemin":"http://maps.googleapis.com/maps/api/geocode/json?address="+encodeURIComponent(origins).replace(/'/g,"%27").replace(/"/g,"%22")+"&sensor=true"};
		var resultat = JSON.parse(ajax(param));
		nbRequetes++;
		var retour = new Object();
		if (resultat["status"] == "OK") {
			if (resultat["results"][0]["geometry"]["location"]) {
				if (resultat["results"][0]["geometry"]["location_type"] == "RANGE_INTERPOLATED" || resultat["results"][0]["geometry"]["location_type"] == "ROOFTOP") {
					retour["noerror"] = resultat["results"][0];
				} else {
					if (idPersonne) {
						retour["erreur"] = "Erreur : "+resultat["results"][0]["geometry"]["location_type"]+"<input type='button' onclick='modifierAdresse("+idPersonne+");' value=\"Modifier l'adresse\" />";
					} else {
						retour["erreur"] = "Erreur : "+resultat["results"][0]["geometry"]["location_type"];
					}
				}
			} else {
				retour["erreur"] = "erreur geometry location";
			}
		} else {
			retour["erreur"] = resultat["status"];
		}
	} else {
		retour["erreur"] = "MAX_REQUEST";
	}
	return retour;
}

function listerPersonnes(nombre) {
	var param = {"session":"all", "path":"trajets", "fonctions":"listerPersonnes", "nombre":nombre};
	var resultatJSON = ajax(param);
	var resultat = JSON.parse(resultatJSON);
	for (var i=0;i<resultat.length;i++) {
		objPersonnes.push({"id":resultat[i]["p.id"], "nom":resultat[i]["p.nom"], "prenom":resultat[i]["p.prenom"], "adresse":resultat[i]["p.adresse"], "codePostal":resultat[i]["p.codePostal"], "ville":resultat[i]["p.ville"], "c.id":resultat[i]["c.id"], "lat":resultat[i]["c.lat"], "lng":resultat[i]["c.lng"], "formatted_address":resultat[i]["c.formatted_address"]});
	}
	var taille = objPersonnes.length;
	
	var domTable = document.createElement("table");
	domTable.setAttribute("id", "tableTrajets");
	document.getElementById("main_right").appendChild(domTable);
	var domTr = document.createElement("tr");
	domTable.appendChild(domTr);
	var domTh = document.createElement("th");
	domTr.appendChild(domTh);
	for (var i=0;i<taille;i++) {
		var domTh = document.createElement("th");
		domTh.textContent = objPersonnes[i]["nom"]+" "+objPersonnes[i]["prenom"];
		domTr.appendChild(domTh);
	}
	/* écriture adresse */
	var domTr = document.createElement("tr");
	domTable.appendChild(domTr);
	var domTh = document.createElement("th");
	domTr.appendChild(domTh);
	for (var i=0;i<taille;i++) {
		if (objPersonnes[i]["lat"] && objPersonnes[i]["lng"] && objPersonnes[i]["formatted_address"]) {
			var retour = objPersonnes[i]["formatted_address"];
		} else {
			/****/
			var origins = objPersonnes[i]["adresse"]+", "+objPersonnes[i]["codePostal"]+" "+objPersonnes[i]["ville"]+", France";
			var resultat = validerAdresse(origins, objPersonnes[i]["id"]);
			
			if (resultat["erreur"]) {
				var retour = resultat["erreur"];
			} else if (resultat["noerror"]) {
				objPersonnes[i]["lat"] = resultat["noerror"]["geometry"]["location"]["lat"];
				objPersonnes[i]["lng"] = resultat["noerror"]["geometry"]["location"]["lng"];
				objPersonnes[i]["formatted_address"] = resultat["noerror"]["formatted_address"];
				ajouterCoordonnees(objPersonnes[i]["adresse"], objPersonnes[i]["codePostal"], objPersonnes[i]["ville"], objPersonnes[i]["lat"], objPersonnes[i]["lng"], objPersonnes[i]["formatted_address"]);
				var retour = "new | "+objPersonnes[i]["formatted_address"];
			} else {
				alert("Erreur : "+resultat);
			}
			/****/
		}
		var domTh = document.createElement("th");
		domTh.innerHTML = retour;
		domTr.appendChild(domTh);
	}
	
	document.getElementById("action").textContent = "Calculer les temps de trajets";
	document.getElementById("action").setAttribute("onclick", "calculTrajets();");
}

function calculTrajets() {
	var domTable = document.getElementById("tableTrajets");
	var taille = objPersonnes.length;
	for (var i=0;i<taille;i++) {
		var domTr = document.createElement("tr");
		domTable.appendChild(domTr);
		var domTh = document.createElement("th");
		domTh.textContent = objPersonnes[i]["nom"]+" "+objPersonnes[i]["prenom"];
		domTr.appendChild(domTh);
		objTrajets[i] = new Object();
		for (var j=0;j<taille;j++) {
			if (i == j) {
				var retour = "X";
			} else {
				var origin_addresses = objPersonnes[i]["formatted_address"];
				var destination_addresses = objPersonnes[j]["formatted_address"];
				if (origin_addresses && destination_addresses) {
					var param = {"session":"all", "path":"trajets", "fonctions":"listerTrajets", "origin_addresses":origin_addresses, "destination_addresses":destination_addresses};
					var resultat = JSON.parse(ajax(param));
					if (resultat.length == 0) {
						if (nbRequetes <= maxRequetes) {
							var param = {"chemin":"http://maps.googleapis.com/maps/api/distancematrix/json?origins="+encodeURIComponent(origin_addresses).replace(/'/g,"%27").replace(/"/g,"%22")+"&destinations="+encodeURIComponent(destination_addresses).replace(/'/g,"%27").replace(/"/g,"%22")+"&mode=driving&language=fr-FR&sensor=false"};
							var resultat = JSON.parse(ajax(param));
							if (resultat["status"] == "OVER_QUERY_LIMIT") {
								nbRequetes = maxRequetes+1;
							} else {
								nbRequetes++;
								var distance = resultat["rows"][0]["elements"][0]["distance"]["value"];
								var duration = resultat["rows"][0]["elements"][0]["duration"]["value"];
								var retour = "new | distance : "+distance+" | duration "+duration;
								var param = {"session":"all", "path":"trajets", "fonctions":"ajouterTrajets", "origin_addresses":origin_addresses, "destination_addresses":destination_addresses, "distance":distance, "duration":duration};
								ajax(param, true);
							}
						} else {
							var distance = -1;
							var duration = -1;
						}
					} else {
						var distance = resultat[0]["t.distance"];
						var duration = resultat[0]["t.duration"];
						var retour = "distance : "+distance+" | duration "+duration;
					}
					objTrajets[i][j] = new Object();
					objTrajets[i][j]["distance"] = distance;
					objTrajets[i][j]["duration"] = duration;
				} else {
					var retour = "X";
					objTrajets[i][j] = new Object();
					objTrajets[i][j]["distance"] = -1;
					objTrajets[i][j]["duration"] = -1;
				}
			}
			var domTd = document.createElement("td");
			domTd.textContent = retour;
			domTr.appendChild(domTd);
		}
	}
	
	document.getElementById("action").textContent = "Établir les chemins";
	document.getElementById("action").setAttribute("onclick", "etablirChemins();");
}
// v2__trajets -> id, origin_addresses, destination_addresses, distance, duration

function etablirChemins() {
	var taille = objTrajets.length;
	var objDejaPris = new Object();
	var nbPassages = 0;
	var numeroEnCours = 0; // 0 car c'est l'adresse de départ
	while (nbPassages < taille) {
		var miniDistance = 0;
		var miniDuration = 0;
		var cible = 0;
		objDejaPris[numeroEnCours] = true;
		for (var j in objTrajets[numeroEnCours]) {
			if (!objDejaPris[j] && parseInt(objTrajets[numeroEnCours][j]["distance"]) == -1) {
				objDejaPris[j] = true;
				nbPassages++;
			}
			if (!objDejaPris[j]) {
				var distance = parseInt(objTrajets[numeroEnCours][j]["distance"]);
				var duration = parseInt(objTrajets[numeroEnCours][j]["duration"]);
				if (miniDistance == 0 && miniDuration == 0) {
					miniDistance = distance;
					miniDuration = duration;
					cible = j;
				} else if (miniDistance > distance) {
					miniDistance = distance;
					miniDuration = duration;
					cible = j;
				} else if (miniDistance == distance) {
					if (miniDuration > duration) {
						miniDistance = distance;
						miniDuration = duration;
						cible = j;
					}
				}
			}
		}
		nbPassages++;
		if (nbPassages >= taille) {
			miniDistance = parseInt(objTrajets[numeroEnCours][cible]["distance"]);
			miniDuration = parseInt(objTrajets[numeroEnCours][cible]["duration"]);
		}
		objDistance.push(new Array(cible, miniDistance, miniDuration));
		numeroEnCours = cible;
	}
	objDejaPris = new Object();
	var nbPassages = 0;
	var numeroEnCours = 0; // 0 car c'est l'adresse de départ
	while (nbPassages < taille) {
		var miniDistance = 0;
		var miniDuration = 0;
		var cible = 0;
		objDejaPris[numeroEnCours] = true;
		for (var j in objTrajets[numeroEnCours]) {
			if (!objDejaPris[j] && parseInt(objTrajets[numeroEnCours][j]["duration"]) == -1) {
				objDejaPris[j] = true;
				nbPassages++;
			}
			if (!objDejaPris[j]) {
				var distance = parseInt(objTrajets[numeroEnCours][j]["distance"]);
				var duration = parseInt(objTrajets[numeroEnCours][j]["duration"]);
				if (miniDistance == 0 && miniDuration == 0) {
					miniDistance = distance;
					miniDuration = duration;
					cible = j;
				} else if (miniDuration > duration) {
					miniDistance = distance;
					miniDuration = duration;
					cible = j;
				} else if (miniDuration == duration) {
					if (miniDistance > distance) {
						miniDistance = distance;
						miniDuration = duration;
						cible = j;
					}
				}
			}
		}
		nbPassages++;
		if (nbPassages >= taille) {
			miniDistance = parseInt(objTrajets[numeroEnCours][cible]["distance"]);
			miniDuration = parseInt(objTrajets[numeroEnCours][cible]["duration"]);
		}
		objDuration.push(new Array(cible, miniDistance, miniDuration));
		numeroEnCours = cible;
	}
}

function ecrireTableaux() {
	var domTable = document.createElement("table");
	domTable.setAttribute("id", "tableDistance");
	document.getElementById("main_right").appendChild(domTable);
	var domTr = document.createElement("tr");
	domTable.appendChild(domTr);
	var domTh = document.createElement("th");
	domTh.textContent = "Personnes";
	domTr.appendChild(domTh);
	var domTh = document.createElement("th");
	domTh.textContent = "Distance";
	domTr.appendChild(domTh);
	var domTh = document.createElement("th");
	domTh.textContent = "Distance Totale";
	domTr.appendChild(domTh);
	var distanceTotal = 0;
	for (var i=0;i<objDistance.length;i++) {
		var domTr = document.createElement("tr");
		domTable.appendChild(domTr);
		var domTd = document.createElement("td");
		domTd.textContent = objPersonnes[objDistance[i][0]]["nom"]+" "+objPersonnes[objDistance[i][0]]["prenom"];
		domTr.appendChild(domTd);
		var domTd = document.createElement("td");
		domTd.textContent = parseInt(objDistance[i][1]/1000)+"km "+objDistance[i][1]%1000+"m";
		domTr.appendChild(domTd);
		var domTd = document.createElement("td");
		distanceTotal += objDistance[i][1];
		domTd.textContent = parseInt(distanceTotal/1000)+"km "+distanceTotal%1000+"m";
		domTr.appendChild(domTd);
	}
	/*****************************/
	var domTable = document.createElement("table");
	domTable.setAttribute("id", "tableDuration");
	document.getElementById("main_right").appendChild(domTable);
	var domTr = document.createElement("tr");
	domTable.appendChild(domTr);
	var domTh = document.createElement("th");
	domTh.textContent = "Personnes";
	domTr.appendChild(domTh);
	var domTh = document.createElement("th");
	domTh.textContent = "Durée";
	domTr.appendChild(domTh);
	var domTh = document.createElement("th");
	domTh.textContent = "Durée Totale";
	domTr.appendChild(domTh);
	var dureeTotal = 0;
	for (var i=0;i<objDuration.length;i++) {
		var domTr = document.createElement("tr");
		domTable.appendChild(domTr);
		var domTd = document.createElement("td");
		domTd.textContent = objPersonnes[objDuration[i][0]]["nom"]+" "+objPersonnes[objDuration[i][0]]["prenom"];
		domTr.appendChild(domTd);
		var domTd = document.createElement("td");
		domTd.textContent = parseInt(objDuration[i][2]/60/60)+"h "+parseInt((objDuration[i][2]/60)%60)+"m";
		domTr.appendChild(domTd);
		dureeTotal += objDuration[i][2];
		var domTd = document.createElement("td");
		domTd.textContent = parseInt(dureeTotal/60/60)+"h "+parseInt((dureeTotal/60)%60)+"m";
		domTr.appendChild(domTd);
	}
}

//http://maps.googleapis.com/maps/api/geocode/xml?address=3%20rue%20du%20ruisseau%2C%2054610%20eply&sensor=true

// http://maps.googleapis.com/maps/api/distancematrix/json?origins=Eply&destinations=Nancy&mode=driving&language=fr-FR&sensor=false

// window.setTimeout(letsGo,500);

function ajouterCoordonnees(adresse, codePostal, ville, lat, lng, formatted_address) {
	var param = {"session":"all", "path":"trajets", "fonctions":"ajouterCoordonnees", "adresse":adresse, "codePostal":codePostal, "ville":ville, "lat":lat, "lng":lng, "formatted_address":formatted_address};
	ajax(param, true);
}

/*
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
*/