function sellfromblogForm(wpajax) {

	// zawartość pola tekstowego
	var kod = document.getElementById("sellfromblog_kod").value;
	var email = document.getElementById("sellfromblog_email").value;
	
	// tu wpiszemy wynik
	var sellfromblogdiv = document.getElementById("sellfromblogdiv");
	
	sellfromblogdiv.innerHTML = '<div class="sellfromblog_wait"><img src="' + wpajax.plugindir + 'wait.gif" /></div>';
	
	new Ajax.Updater('sellfromblogdiv', wpajax.ajaxurl + "?action=sellfromblog&kod=" + kod + "&email=" + email);
}