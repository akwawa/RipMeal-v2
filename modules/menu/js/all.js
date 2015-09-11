var textejson='';
var timestampJour=0;
var table = new Object();

function associer_menu(cell) {
	var liste_menu = window.document.getElementById("liste_menu");
	cell.innerHTML = "<select onblur=\"valider_associer_menu(this);\">"+liste_menu.innerHTML+"</select>";
	cell.childNodes[0].focus();
}

function valider_associer_menu(select) {
	var cell = select.parentNode;
	var idMenu = select.value;
	var idRegime = cell.getAttribute("data-idRegime");
	var timestampJour = cell.getAttribute("data-timestampJour");
	var typeCal = cell.getAttribute("data-typeCal");

	var param = {"session":"all", "path":"menu", "fonctions":"associer_menu", "idMenu":idMenu, "idRegime":idRegime, "timestampJour":timestampJour, "typeCal":typeCal};
	var result = ajax_sans_ecrire(param);
	
	var option = select.options[select.selectedIndex].text;
	cell.innerHTML = option.split(';').join('<br>');
}

function loadSemaine(form) {
	if (window.File && window.FileReader && window.FileList && window.Blob) {
		timestamp(form);	
		var fileInput = form.elements["files"];
		if (fileInput.files[0] != undefined) {
			var mydiv = document.createElement('p');
			mydiv.id = 'traitementFichier';
			mydiv.textContent = timestampJour + ' Le fichier est en cours de traitement';
			form.appendChild(mydiv);
			var delimiteur = form.elements["delimiteur"].value;
			var reader = new FileReader();
			reader.readAsText(fileInput.files[0]);
			reader.onload = function() {
				var contents = reader.result;
				createTable(contents, delimiteur, form);
				document.getElementById("traitementFichier").textContent = new Date(timestampJour*1000) + ' Traitement terminé.';
			};
		} else {
			alert('Votre devez selectionner un fichier.');
		}
	} else {
		alert('Votre navigateur ne prend pas en charge cette fonction.');
	}
	return false;
}

function timestamp(form) {
	if (form.elements['dateCalendrier'].value == "") {
		var temp = new Date();
		temp.setHours(0);
		temp.setMinutes(0);
		temp.setSeconds(0);
		var annee = temp.getFullYear();
		var mois = temp.getMonth();
		var jour = temp.getDate();
	} else {
		var temp = form.elements['dateCalendrier'].value;
		var annee = temp.substr(0, 4);
		// var mois = parseInt(temp.substr(5, 2))-1;
		var mois = temp.substr(5, 2);
		var jour = temp.substr(8, 2);
		var temp = new Date();
		temp.setHours(0);
		temp.setMinutes(0);
		temp.setSeconds(0);
		// temp.setFullYear(annee, mois, jour);
		temp.setFullYear(annee, mois-1, jour);
	}
	var tempsJour = 86400;
	var numJour = temp.getDay();
	if (numJour == 0) { numJour = 7; }
	var timeJour = parseInt(temp.getTime()/1000);
	numJour = numJour-1;
	timestampJour = timeJour-(numJour*tempsJour);
	// alert(mois+" "+temp.getMonth()+" "+parseInt(temp.getTime()/1000));
}

function uploadSemaine(form) {
	if (window.File && window.FileReader && window.FileList && window.Blob) {
		var fileInput = form.elements["files"];
		if (fileInput.files[0] != undefined) {
			var mydiv = document.createElement('p');
			mydiv.id = 'traitementFichier';
			mydiv.textContent = 'Le fichier est en cours de traitement';
			form.appendChild(mydiv);
			if (form.elements['dateCalendrier'].value == "") {
				var temp = new Date();
				temp.setHours(0);
				temp.setMinutes(0);
				temp.setSeconds(0);
				var annee = temp.getFullYear();
				var mois = temp.getMonth();
				var jour = temp.getDate();
			} else {
				var temp = form.elements['dateCalendrier'].value;
				var annee = temp.substr(0, 4);
				var mois = temp.substr(5, 2);
				var jour = temp.substr(8, 2);
				var temp = new Date();
				temp.setHours(0);
				temp.setMinutes(0);
				temp.setSeconds(0);
				var annee = temp.setYear(annee);
				var mois = temp.setMonth(mois-1);
				var jour = temp.setDate(jour);
			}
			var tempsJour = 86400;
			var numJour = temp.getDay();
			if (numJour == 0) { numJour = 7; }
			var timeJour = parseInt(temp.getTime()/1000);
			numJour = numJour-1;
			// alert(timestampJour);
			var timestampJour = timeJour-(numJour*tempsJour);
			// alert(timestampJour);
			var delimiteur = form.elements["delimiteur"].value;
			var reader = new FileReader();
			// reader.readAsText(fileInput.files[0], 'UTF-8');
			reader.readAsText(fileInput.files[0]);
			reader.onload = function() {
				var contents = reader.result;
				creerCSV(contents, timestampJour, delimiteur);
				document.getElementById("traitementFichier").textContent = 'Traitement terminé.';
			};
		} else {
			alert('Votre devez selectionner un fichier.');
		}
	} else {
		alert('Votre navigateur ne prend pas en charge cette fonction.');
	}
}

function createTable(file, delimiteur, form) {
	var lines = file.split('\n');
	var nbLignes = lines.length;

	for (var i=0;i<nbLignes;i++) {
		table[i] = new Object();
		var line = lines[i].split(delimiteur);
		var nbCell = line.length;
		for (var j=0;j<nbCell;j++) {
			line[j] = trim(line[j]);
			if (line[j]=="" && (i==0 || j>1)) {line[j] = " ";}
			if (j>2) {line[j]=line[j].replace("*", table[i][2]['textContent']);}
			table[i][j] = {'textContent':line[j], 'colspan':'1','rowspan':'1', 'iParent':i, 'jParent':j};
			if (line[j] == " " && i>0) {table[i][j]['error']=true; }
			if (line[j]=="" && i > 0) {
				var iParent = table[i-1][j]['iParent'];
				var jParent = table[i-1][j]['jParent'];
				table[i][j]['iParent'] = iParent;
				table[i][j]['jParent'] = jParent;
				table[iParent][jParent]['rowspan'] = parseInt(table[iParent][jParent]['rowspan'])+1;
			}
		}
	}
	
	var newTable = document.createElement('table');
	form.appendChild(newTable);
	for (var i=0;i<nbLignes;i++) {
		var newTr = document.createElement('tr');
		if (i==0) {
			var newThead = document.createElement('thead');
			newTable.appendChild(newThead);
			newThead.appendChild(newTr);
		} else {
			newTable.appendChild(newTr);
		}
		var nbCell = Object.size(table[i]);
		for (var j=0;j<nbCell;j++) {
			if (table[i][j]['textContent'] != "") {
				var newTd = document.createElement('td');
				newTd.setAttribute('colspan', table[i][j]['colspan']);
				newTd.setAttribute('rowspan', table[i][j]['rowspan']);
				newTd.textContent = table[i][j]['textContent'];
				if(table[i][j]['error']==true) {
					newTd.setAttribute("style", "background:red;");
				}
				newTr.appendChild(newTd);
			}
		}
	}
	
	var tabCSV = new Object;
	for (var i=0;i<nbLignes;i++) {
		var nbCell = Object.size(table[i]);
		for (var j=0;j<nbCell;j++) {
			// i = ligne ; j = colonne
			var value = trim(table[i][j]['textContent']);
			if (i==0) {
				if (j>1) {
					tabCSV[j-2] = new Object;
					tabCSV[j-2]["nomRegime"] = value;
				}
			} else {
				if (j==0) {
					if (value!="") {jour = value;}
				}
				if ((i%10)>0 && (i%10)<=5) {
					typeCalendrier = "MIDI";
				} else {
					typeCalendrier = "SOIR";
				}
				
				if (j>1) {
					if (!tabCSV[j-2][jour]) {tabCSV[j-2][jour] = new Object();}
					if (!tabCSV[j-2][jour][typeCalendrier]) {tabCSV[j-2][jour][typeCalendrier] = new Object();}
					tabCSV[j-2][jour][typeCalendrier][i%5] = value;
				}
			}
		}
	}
	
	textejson = JSON.stringify(tabCSV);
	// alert(textejson);
	var newButton = document.createElement('input');
	newButton.setAttribute('type', 'button');
	newButton.setAttribute('value', 'Insérer le menu');
	newButton.setAttribute('onclick', 'ajouterTable(this);');
	form.appendChild(newButton);
}

function ajouterTable(form) {
	// alert(timestampJour);
	var nbRegime = Object.size(table[0]);
	var newTable = document.createElement('table');
	form.parentNode.appendChild(newTable);
	var newTr = document.createElement('tr');
	var newThead = document.createElement('thead');
	newTable.appendChild(newThead);
	newThead.appendChild(newTr);
	for (var j=0;j<nbRegime;j++) {
		var newTd = document.createElement('td');
		newTd.textContent = table[0][j]['textContent'];
		newTr.appendChild(newTd);
	}
	var arrayDate = ["LUNDI", "MARDI", "MERCREDI", "JEUDI", "VENDREDI", "SAMEDI", "DIMANCHE"];
	temp_timestampJour = timestampJour-86400;
	// alert(timestampJour+" "+temp_timestampJour);
	for (var i=0;i<14;i++) {
		var newTr = document.createElement('tr');
		newTable.appendChild(newTr);
		for (var j=0;j<nbRegime;j++) {
			var newTd = document.createElement('td');
			if (j==0) {
				newTd.setAttribute("rowspan", "2");
				newTd.textContent = arrayDate[i/2];
			} else if (j==1) {
				if ((i%2)==0) {
					newTd.textContent = "MIDI";
				} else {
					newTd.textContent = "SOIR";
				}
				jour = newTd.textContent;
			} else {
				newTd.textContent = "en cours";
			}
			// alert(timestampJour+" "+temp_timestampJour);
			if (!(j==0 && (i%2)==1)) {
				if (j==0) {temp_timestampJour = temp_timestampJour+86400;}
				if (j>1) { newTd.setAttribute("id", escape(table[0][j]['textContent'])+"_"+jour+"_"+temp_timestampJour); }
				newTr.appendChild(newTd);
			}
			// alert(temp_timestampJour);
		}
	}

	var objet = JSON.parse(textejson);
	for (var cle in objet) {
		for (var cle2 in objet[cle]) {
			if (cle2 == "nomRegime") {
				var nomRegime = objet[cle]["nomRegime"];
			} else {
				var jour = cle2.toUpperCase();
				// alert(jour+" "+timestampJour);
				switch (jour) {
					case "LUNDI" :
						temp_timestampJour = timestampJour;
						break;
					case "MARDI" :
					case "MERCREDI" :
					case "JEUDI" :
					case "VENDREDI" :
					case "SAMEDI" :
					case "DIMANCHE" :
						temp_timestampJour = temp_timestampJour+86400;
						break;
				}
				// alert(jour+" "+temp_timestampJour);
				for (var cle3 in objet[cle][cle2]) {
					var typeCalendrier = cle3;
					// alert(typeCalendrier);
					var entree = objet[cle][cle2][cle3]["1"];
					var viande = objet[cle][cle2][cle3]["2"];
					var legume = objet[cle][cle2][cle3]["3"];
					var fromage = objet[cle][cle2][cle3]["4"];
					var dessert = objet[cle][cle2][cle3]["0"];
					
					if (entree == undefined) { entree = "_"; }
					if (viande == undefined) { viande = "_"; }
					if (legume == undefined) { legume = "_"; }
					if (fromage == undefined) { fromage = "_"; }
					if (dessert == undefined) { dessert = "_"; }

					entree = entree.substr(0, 99);
					viande = viande.substr(0, 99);
					legume = legume.substr(0, 99);
					fromage = fromage.substr(0, 99);
					dessert = dessert.substr(0, 99);
					// alert(nomRegime+" "+jour+" "+typeCalendrier+" "+entree+" "+viande+" "+legume+" "+fromage+" "+dessert+" "+temp_timestampJour);
					ajouterMenu(nomRegime, jour, typeCalendrier, entree, viande, legume, fromage, dessert, temp_timestampJour);
				}
			}
		}
	}
}

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

function creerCSV(fichier, timestampJour, delimiteur) {
	var tabCSV = new Object;

	var lines = fichier.split('\n');
	var nbLignes = lines.length;
	var numLigne = 0;
	for (var i=0;i<nbLignes;i++) {
		var contenu = lines[i].split(delimiteur);
		var nbContenu = contenu.length;
		for (var j=0;j<nbContenu;j++) {
			if (contenu[j] != "") {
				// alert(j+" "+contenu[j]);
				if (i == 0) {
					tabCSV[j] = new Object;
					tabCSV[j]["nomRegime"] = trim(contenu[j]);
				} else {
					if (j == 0) {
						var jour = trim(contenu[j].toUpperCase());
					} else if (j == 1) {
						var typeCalendrier = trim(contenu[j].toUpperCase());
					} else {
						if (typeof(tabCSV[j][jour]) == "undefined") { tabCSV[j][jour] = new Object; }
						if (typeof(tabCSV[j][jour][typeCalendrier]) == "undefined") { tabCSV[j][jour][typeCalendrier] = new Object; }
						if (j == 2) {
							var plat = trim(contenu[j]);
							var platTemp = plat;
						} else {
							var platTemp = trim(contenu[j].replace("*", plat));
						}
						if (numLigne == 6) { numLigne = 1; }
						if (numLigne == 1) { // ENTREE
							tabCSV[j][jour][typeCalendrier]["entree"] = platTemp;
						} else if (numLigne == 2) { // VIANDE
							tabCSV[j][jour][typeCalendrier]["viande"] = platTemp;
						} else if (numLigne == 3) { // LEGUMES
							tabCSV[j][jour][typeCalendrier]["legume"] = platTemp;
						} else if (numLigne == 4) { // FROMAGE
							tabCSV[j][jour][typeCalendrier]["fromage"] = platTemp;
						} else if (numLigne == 5) { // DESSERT
							// alert(platTemp);
							tabCSV[j][jour][typeCalendrier]["dessert"] = platTemp;
						}
					}
				}
			} else {
				// alert("erreur "+j+" "+contenu[j]);
			}
		}
		numLigne++;
	}

	var textejson = JSON.stringify(tabCSV);
	// alert(textejson);
	var objet = JSON.parse(textejson);
	for (var cle in objet) {
		for (var cle2 in objet[cle]) {
			if (cle2 == "nomRegime") {
				var nomRegime = objet[cle]["nomRegime"];
			} else {
				var jour = cle2.toUpperCase();
				// alert(jour);
				switch (jour) {
					case "LUNDI" :
						temp_timestampJour = timestampJour;
						break;
					case "MARDI" :
					case "MERCREDI" :
					case "JEUDI" :
					case "VENDREDI" :
					case "SAMEDI" :
					case "DIMANCHE" :
						temp_timestampJour = temp_timestampJour+86400;
						break;
				}
				for (var cle3 in objet[cle][cle2]) {
					var typeCalendrier = cle3;
					var entree = objet[cle][cle2][cle3]["entree"];
					var viande = objet[cle][cle2][cle3]["viande"];
					var legume = objet[cle][cle2][cle3]["legume"];
					var fromage = objet[cle][cle2][cle3]["fromage"];
					var dessert = objet[cle][cle2][cle3]["dessert"];
					
					if (entree == undefined) { entree = "_"; }
					if (viande == undefined) { viande = "_"; }
					if (legume == undefined) { legume = "_"; }
					if (fromage == undefined) { fromage = "_"; }
					if (dessert == undefined) { dessert = "_"; }

					// timestampJour = timestampJour+43200;
					// alert(timestampJour);
					
					/* test */
					entree = entree.substr(0, 99);
					viande = viande.substr(0, 99);
					legume = legume.substr(0, 99);
					fromage = fromage.substr(0, 99);
					dessert = dessert.substr(0, 99);
					/********/
					
					ajouterMenu(nomRegime, jour, typeCalendrier, entree, viande, legume, fromage, dessert, temp_timestampJour);
				}
			}
		}
	}
}

function ajouterMenu(nomRegime, jour, typeCalendrier, entree, viande, legume, fromage, dessert, timestampJour) {
	var param = {"session":"all", "path":"menu", "fonctions":"ajouterMenu", "nomRegime":nomRegime, "jour":jour, "typeCalendrier":typeCalendrier, "entree":entree, "viande":viande, "legume":legume, "fromage":fromage, "dessert":dessert, "timestampJour":timestampJour};
	// alert(nomRegime+" "+jour+" "+typeCalendrier+" "+entree+" "+viande+" "+legume+" "+fromage+" "+dessert+" "+timestampJour);
	var retour = ajax_sans_ecrire(param);
	// alert(retour);
	var result = JSON.parse(retour);
	var mydiv = document.createElement('div');
	// if (result["test"]) { alert(result["test"]); }
	var date = new Date(timestampJour*1000);
	var day = date.getDate();
	var month = date.getMonth();
	var year = date.getYear();
	var id = escape(nomRegime)+"_"+typeCalendrier+"_"+timestampJour;
	if (result["resultat"]) {
		document.getElementById(id).textContent = "ok";
		// mydiv.innerHTML = result["resultat"]+" "+nomRegime+" "+jour+" "+typeCalendrier+" "+day+"/"+month+"/"+year;
	} else {
		document.getElementById(id).textContent = result["erreur"];
		// mydiv.innerHTML = result["erreur"]+" "+nomRegime+" "+jour+" "+typeCalendrier+" "+day+"/"+month+"/"+year;
	}
	document.getElementById("resultat").appendChild(mydiv);
}

function trim(myString) {
	return myString.replace(/^\s+/g,'').replace(/\s+$/g,'')
} 

























