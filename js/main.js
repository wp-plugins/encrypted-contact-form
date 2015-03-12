function handleRegenOptions(){

	var e = document.getElementById('nameoptional').checked,
		a = document.getElementById('namerequired').checked,
		t = document.getElementById('emailoptional').checked,
		n = document.getElementById('emailrequired').checked,
		s = document.getElementById('phoneoptional').checked,
		r = document.getElementById('phonerequired').checked,
		i = document.getElementById('messageoptional').checked,
		o = document.getElementById('messagerequired').checked,
		l ='https://i.cx/secureForm.html?';
	
	e&&(l+="id_name=name&element_name=text&"),
	a&&(l+="id_name=name&element_name=text&require_name=true&"),
	t&&(l+="id_email=email&element_email=text&validate_email=email&"),
	n&&(l+="id_email=email&element_email=text&validate_email=email&require_email=true&"),
	s&&(l+="id_phone=phone&element_phone=text&"),
	r&&(l+="id_phone=phone&element_phone=text&require_phone=true&"),
	i&&(l+="id_message=message&element_message=textarea&"),
	o&&(l+="id_message=message&element_message=textarea&require_message=true&");
	
	var c=document.getElementById('cfcdisplayname').value;
	"undefined"==c && (c="Example"),
	l+="toUser="+c;
	
	document.getElementById('cfc-preview').src = l;
	document.getElementById('iframe_url').value = l;
	document.getElementById('cfcpreview').value = '<iframe src="' + l + '" width="600" height="500"></iframe>';

}

function setUsername(){
	e = document.getElementById('cfcdisplayname').value;
	var i = /[^.A-Za-z0-9]/;
	document.getElementById('cfcdisplayparsed').value = e.split(i).join("").toLowerCase();
	handleRegenOptions();
}

function switchPage( v ){
	if ( 'new' == v ){
		document.getElementById('pageinput_text').style.display = '';
		document.getElementById('pageinput_text').required = "required";		
		document.getElementById('pageinput_select').style.display = 'none';
		document.getElementById('cfc_createpage').value = "Create Page";
	} else {
		document.getElementById('pageinput_text').style.display = 'none';
		document.getElementById('pageinput_text').required = false;
		document.getElementById('pageinput_select').style.display = '';	
		document.getElementById('pageinput_text').value = "";				
		document.getElementById('cfc_createpage').value = "Update Page";		
	}
}