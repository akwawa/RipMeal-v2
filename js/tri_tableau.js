function triage(th) {
	var sens = th.getAttribute("data-sens");
	if (sens == null) { sens = "ASC"; } else 
	if (sens == "ASC") { sens = "DESC"; } else 
	if (sens == "DESC") { sens = "ASC"; }
	
	var nom = th.getAttribute("data-nom");
	if (nom == null) {
		nom = th.textContent;
		th.setAttribute("data-nom", nom);
	}

	var temp = th;
	while (temp.tagName != "TABLE") { temp = temp.parentNode; }
	
	var table_head = temp.getElementsByTagName("thead");
	var taille_head = table_head.length;
	if (taille_head > 0) {
		var entete = table_head[0].getElementsByTagName("th");
		var nbEntete = entete.length;
		for(var i = 0; i < nbEntete; i++){
			if (entete[i].getAttribute("data-nom") != null) {
				entete[i].textContent = entete[i].getAttribute("data-nom");
			}
		}
	}
	
	var table_body = temp.getElementsByTagName("tbody");
	var taille_body = table_body.length;
	if (taille_body > 0) {
		var lignes = table_body[0].getElementsByTagName("tr");
		var numColonne = 0;
		var temp = th;
		while((temp = temp.previousSibling) != null) numColonne++;

	
		var tab_temp = new Object();
		
		var nbLignes = lignes.length;
		for(var i = 0; i < nbLignes; i++){
			var ligne = lignes[i];
			var nbColonnes = ligne.childNodes.length;
			var valeurColonne = "";
			for(var j = 0; j < nbColonnes; j++){
				if (j == numColonne) {
					valeurColonne = ligne.childNodes[j].textContent;
					tab_temp[valeurColonne+i] = ligne;
					break;
				}
			}
		}
		table_body[0].textContent = "";
		var tab_trie = new Array();
		for(var prop in tab_temp) {
			tab_trie.push(prop);
		}
		tab_trie.sort();
		if (sens == "DESC") { tab_trie.reverse() }
		for(var prop in tab_trie) {
			for(var ligne in tab_temp) {
				if (tab_trie[prop] == ligne) {
					table_body[0].innerHTML += tab_temp[ligne].innerHTML;
					delete ligne;
					break;
				}
			}
		}
	}
	
	th.setAttribute("data-sens", sens);
	th.textContent = nom+" "+sens;
}

function tri_tableau() {
	var tables = document.getElementsByTagName("thead");
	if (tables.length) {
		var lignes = tables[0].getElementsByTagName("th");
		var taille = lignes.length;
		for (var i = 0; i < taille; i++) {
			lignes[i].onclick = function() { triage(this); };
		}
	}
}
window.onload=function() {
	tri_tableau();
	ajout_form_date();
};
