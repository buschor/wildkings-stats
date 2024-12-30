<!--

function checkAddSgSession() {
  
  var errmsg = "";
  
  frm = document.getElementById('sg_sessadd');
  
  if (!checkDate(frm.sgdate.value))
    errmsg += "Ungültiges Datum!\n";
  
  if (frm.sglocation.value == "")
    errmsg += "Ungültige Location!\n";
    
  if (isNaN(frm.sgbuyin.value))       
    errmsg += "Ungültiger Buy In!\n";    
  
  for (i = 0; i < 32; i++) {
    if (document.getElementsByName("sgname[" + i + "]")[0].selectedIndex > 0) {
      
      if (isNaN(document.getElementsByName("sgpoints[" + i + "]")[0].value) ||
        (document.getElementsByName("sgpoints[" + i + "]")[0].value == ""))       
        errmsg += "Ungültige Punktzahl (Zeile " + (i+1) + ")\n";
      if (isNaN(document.getElementsByName("sgsaldo[" + i + "]")[0].value))       
        errmsg += "Ungültiges Preisgeld (Zeile " + (i+1) + ")\n";
    }  
  }
  
  if (errmsg == "")
    return true;
  else {
    alert(errmsg);
    return false;
  }  
  
}

function checkAddMhSession() {
  
  var errmsg = "";
  
  frm = document.getElementById('mh_sessadd');
  
  if (!checkDate(frm.mhdate.value))
    errmsg += "Ungültiges Datum!\n";
  
  if (frm.mhlocation.value == "")
    errmsg += "Ungültige Location!\n";
  
  for (i = 0; i < 20; i++) {
    if (document.getElementsByName("mhname[" + i + "]")[0].selectedIndex > 0) {
      
      if (isNaN(document.getElementsByName("mhsaldo[" + i + "]")[0].value))        
        errmsg += "Ungültiges Saldo (Zeile " + (i+1) + ")\n";
      if (isNaN(document.getElementsByName("mhhwons[" + i + "]")[0].value))       
        errmsg += "Ungültige Hands Won (Zeile " + (i+1) + ")\n";
      if (isNaN(document.getElementsByName("mhbuyin[" + i + "]")[0].value))       
        errmsg += "Ungültige Buy Ins (Zeile " + (i+1) + ")\n";
      if (isNaN(document.getElementsByName("mhgamep[" + i + "]")[0].value))       
        errmsg += "Ungültige Games Played (Zeile " + (i+1) + ")\n";
    }  
  }
  
  if (errmsg == "")
    return true;
  else {
    alert(errmsg);
    return false;
  }  
  
}

function checkDate(datum) {
	//(Schritt 1) Fehlerbehandlung
  if (!datum)
    return false;

  datum = datum.toString();
	
  if (!datum.match("^[0-3][0-9]\.(0|1)[0-9]\.(1|2)[0-9]{3}$"))
    return false;
	
  //(Schritt 2) Aufspaltung des Datums
  datum = datum.split(".");
  if (datum.length != 3) 
    return false;	
	
  //(Schritt 3) Entfernung der fuehrenden Nullen und Anpassung des Monats	
  datum[0] = parseInt(datum[0], 10);
  datum[1] = parseInt(datum[1], 10) - 1;	
	
  //(Schritt 5) Erzeugung eines neuen Dateobjektes
  var kontrolldatum = new Date(datum[2], datum[1], datum[0]);
	
  //(Schritt 6) Vergleich, ob das eingegebene Datum gleich dem JS-Datum ist
  if (kontrolldatum.getDate()==datum[0] && kontrolldatum.getMonth()==datum[1] && kontrolldatum.getFullYear()==datum[2])
    return true; 
  else 
    return false;
}


//-->