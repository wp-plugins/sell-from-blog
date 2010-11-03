function sellfromblogForm(wpajax) {

	// zawartość pola tekstowego
	var kod = document.getElementById("sellfromblog_kod").value;
	var email = document.getElementById("sellfromblog_email").value;
	if (document.getElementById("sellfromblog_agree")) {
		var agree_checked = document.getElementById("sellfromblog_agree").checked;
	} else {
		var agree_checked = false;
	}
	
	if (agree_checked == true) {
		var agree = "on";
	} else {
		var agree = "";
	}
	
	// tu wpiszemy wynik
	var sellfromblogdiv = document.getElementById("sellfromblogdiv");
	
	sellfromblogdiv.innerHTML = '<div class="sellfromblog_wait"><img src="' + wpajax.plugindir + 'wait.gif" /></div>';
	
	new Ajax.Updater('sellfromblogdiv', wpajax.ajaxurl + "?action=sellfromblog&kod=" + kod + "&email=" + email + "&agree=" + agree);
}