function fichesRepas_visu(form) {
	var recapitulatifChef = form.elements['recapitulatifChef'].checked;
	var tournee = '';
	var livraison = form.elements['livraison'].checked;
	var preparation = form.elements['preparation'].checked;
	var eauEtPain = form.elements['eauEtPain'].checked;
	var dateCalendrier = form.elements['dateCalendrier'].value;

	for (i=0;i<form.elements["tournee[]"].length ;i++) {
		if(form.elements["tournee[]"][i].checked) {
			if (tournee) {
				tournee += ','+form.elements["tournee[]"][i].value;
			} else {
				tournee += form.elements["tournee[]"][i].value;
			}
		}
	}
	var src = "modules/impression/pages/all/fichesRepas_visu.php?recapitulatifChef="+recapitulatifChef+"&tournee="+tournee+"&livraison="+livraison+"&preparation="+preparation+"&eauEtPain="+eauEtPain+"&dateCalendrier="+dateCalendrier;
	window.open (src, "impression", config='height=100, width=400, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no');
}

function menu_visu(form) {
	var nombre
	var dateCalendrier = form.elements['dateCalendrier'].value
	if (dateCalendrier) {nombre = "dateCalendrier="+dateCalendrier}

	for (i=0;i<form.elements["nombre[]"].length ;i++) {
		id = form.elements["nombre[]"][i].getAttribute("data-id")
		value = form.elements["nombre[]"][i].value
		if (value > 0) {
			if (nombre) {
				nombre += '&'+id+"="+value
			} else {
				nombre = id+"="+value
			}
		}
	}

	if (form.elements['menuSeul'].checked) {nombre += "&menuSeul=true"}
	
	// alert(nombre);
	
	var src = "modules/impression/pages/all/menu_visu.php?"+nombre;
	window.open (src, "impression", config='height=100, width=400, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no');
}

function etiquettes_visu(form) {
	var dateCalendrier = form.elements['dateCalendrier'].value;
	var src = "modules/impression/pages/all/etiquettes_visu.php?dateCalendrier="+dateCalendrier;
	window.open (src, "impression", config='height=100, width=400, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no');
}