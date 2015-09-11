function ecrire(textejson) {
	// alert(textejson);
	try {
		var objet = JSON.parse(textejson);
		var rien = false;
		for (var cle in objet) {
			if (objet[cle] === null) {
				rien = rien && true;
			} else if (typeof(objet[cle]) == 'object') {
				var objet2 = objet[cle];
				for (var cle2 in objet2) {
					// alert(cle2+" | "+objet2[cle2]+' | '+typeof(objet2[cle2])+' | '+objet2[cle2].length);
					if (objet2[cle2].length == 0) {
						// alert(cle2+" | "+objet2[cle2]);
					} else if (typeof(objet2[cle2]) == 'string') {
						if (document.getElementById(cle2)) {
							document.getElementById(cle2).innerHTML = objet2[cle2];
						}
					} else {
						alert('bj2 et cle2 '+cle2+" | "+objet2[cle2]);
					}
				}
			} else {
				if (document.getElementById(cle)) {
					document.getElementById(cle).innerHTML = objet[cle];
				}
			}
		}
	} catch (e) {
		// document.getElementById("erreur").innerHTML = textejson;
	}
}

/******** AJAX version 2 ******/
function ajax(param, attente) {
	var retour = false;
	var premier = true;
	var data = '';
	if (!attente) { attente = false; }
	for (var name in param) {
		if (name == 'chemin') {
			var chemin = param[name];
		} else if (name == 'session') {
			var session = param[name];
		} else if (name == 'path') {
			var path = param[name];
		} else if (name == 'pages') {
			var nom_page = param[name];
			var type = name;
		} else if (name == 'fonctions') {
			var nom_page = param[name];
			var type = name;
		} else if (typeof(param[name]) == 'string' || typeof(param[name]) == 'number') {
			if (premier) {
				data += name+'='+param[name];
				premier=false;
			} else {
				data += '&'+name+'='+param[name];
			}
		} else if (typeof(param[name]) == 'object') {
			var temp = param[name];
			for (var cle in temp) {
				if (typeof(temp[cle]) == 'string' && temp[cle] != "") {
					alert(cle+" "+temp[cle]);
				}
			}
		} else {
			alert(name+" "+typeof(param[name]));
		}
	}
	var req = createInstance();
	req.onreadystatechange = function() {
		if(req.readyState == 4) {
			if(req.status == 200) {
				// var textejson = req.responseText;
				// alert(typeof(textejson)+" "+textejson);
				// ecrire(textejson);
				retour = req.responseText;
			} else {
				alert("Error: returned status code " + req.status + " " + req.statusText + " : " + type + " " + nom_page+" "+chemin);
			}
		}
	};
	if (!chemin) {
		if (session) {
			var chemin = 'modules/'+encodeURIComponent(path)+'/'+encodeURIComponent(type)+'/'+session+'/'+encodeURIComponent(nom_page)+'.php';
		} else if(type) {
			var chemin = 'modules/'+encodeURIComponent(path)+'/'+encodeURIComponent(type)+'/'+encodeURIComponent(nom_page)+'.php';
		} else {
			var chemin = 'modules/'+encodeURIComponent(path)+'/'+encodeURIComponent(nom_page)+'.php';
		}
	}
	req.open("POST", chemin, attente);
	// alert(chemin+" | "+attente);
	
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.send(data);
	// alert(data);
	return retour;
}

function ajax_sans_ecrire(param) {
	return ajax(param, false);
}
/******************************/

/*
function ajax(param) {
	var premier = true;
	var data = '';
	for (var name in param) {
		if (name == 'session') {
			var session = param[name];
		} else if (name == 'path') {
			var path = param[name];
		} else if (name == 'pages') {
			var nom_page = param[name];
			var type = name;
		} else if (name == 'fonctions') {
			var nom_page = param[name];
			var type = name;
		} else if (typeof(param[name]) == 'string' || typeof(param[name]) == 'number') {
			if (premier) {
				data += name+'='+param[name];
				premier=false;
			} else {
				data += '&'+name+'='+param[name];
			}
		} else if (typeof(param[name]) == 'object') {
			var temp = param[name];
			for (var cle in temp) {
				if (typeof(temp[cle]) == 'string' && temp[cle] != "") {
					// alert(cle+" "+temp[cle]);
				}
			}
			alert(temp['name']+" "+temp['error']+" "+temp["tmp_name"]);
		} else {
			alert(name+" "+typeof(param[name]));
		}
	}
	var req = createInstance();
	req.onreadystatechange = function() {
		if(req.readyState == 4) {
			if(req.status == 200) {
				var textejson = req.responseText;
				// alert(typeof(textejson)+" "+textejson);
				ecrire(textejson);
				return true;
			} else {
				alert("Error: returned status code " + req.status + " " + req.statusText + " : " + type + " " + nom_page);
			}
		}
	};
	if (session) {
		var chemin = 'modules/'+encodeURIComponent(path)+'/'+encodeURIComponent(type)+'/'+session+'/'+encodeURIComponent(nom_page)+'.php';
	} else {
		var chemin = 'modules/'+encodeURIComponent(path)+'/'+encodeURIComponent(type)+'/'+encodeURIComponent(nom_page)+'.php';
	}
	req.open("POST", chemin, true);
	// alert(chemin);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.send(data);
	// alert(data);
	return false;
}

function ajax_sans_ecrire(param) {
	var retour = false;
	var premier = true;
	var data = '';
	for (var name in param) {
		if (name == 'session') {
			var session = param[name];
		} else if (name == 'path') {
			var path = param[name];
		} else if (name == 'pages') {
			var nom_page = param[name];
			var type = name;
		} else if (name == 'fonctions') {
			var nom_page = param[name];
			var type = name;
		} else if (typeof(param[name]) == 'string' || typeof(param[name]) == 'number') {
			if (premier) {
				data += name+'='+param[name];
				premier=false;
			} else {
				data += '&'+name+'='+param[name];
			}
		} else {
			alert(name+" "+typeof(param[name]));
		}
	}
	var req = createInstance();
	req.onreadystatechange = function() {
		if(req.readyState == 4) {
			if(req.status == 200) {
				retour=req.responseText;
				// alert(retour);
			} else {
				alert("Error: returned status code " + req.status + " " + req.statusText + " : " + type + " " + nom_page+" "+chemin);
			}
		}
	};
	if (session) {
		var chemin = 'modules/'+encodeURIComponent(path)+'/'+encodeURIComponent(type)+'/'+session+'/'+encodeURIComponent(nom_page)+'.php';
	} else if(type) {
		var chemin = 'modules/'+encodeURIComponent(path)+'/'+encodeURIComponent(type)+'/'+encodeURIComponent(nom_page)+'.php';
	} else {
		var chemin = 'modules/'+encodeURIComponent(path)+'/'+encodeURIComponent(nom_page)+'.php';
	}
	// alert(chemin);
	req.open("POST", chemin, false);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.send(data);
	return retour;
}
*/

function createInstance() {
	var req = null;
	if(window.XMLHttpRequest) {
		req = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		try {
			req = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				req = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
				alert("XHR non créé");
			}
		}
	}
	return req;
}