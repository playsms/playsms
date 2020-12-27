function CheckUncheckAll(the_form) {
	for (var i = 0; i < the_form.elements.length; i++) {
		if (the_form.elements[i].type == "checkbox") {
			the_form.elements[i].checked = !(the_form.elements[i].checked);
		}
	}
}

function linkto(url) {
	window.location.href = url;
}

function ConfirmURL(text, goto_url) {
	if (text) {
		BootstrapDialog.show({
			type : BootstrapDialog.TYPE_DANGER,
			title : 'Please confirm',
			message : text,
			buttons : [ {
				label : 'No',
				cssClass : 'btn-primary',
				action : function(dialogItself) {
					dialogItself.close();
				}
			}, {
				label : 'Yes',
				cssClass : 'btn-success',
				action : function() {
					document.location = goto_url;
				}
			} ]
		});
	}
}

function SetSmsTemplate(form_name) {
	sellength = document.getElementById(form_name).smstemplate.length;
	for (i = 0; i < sellength; i++) {
		if (document.getElementById(form_name).smstemplate.options[i].selected == true) {
			document.getElementById(form_name).message.value = document.getElementById(form_name).smstemplate.options[i].value;
		}
	}
	document.getElementById(form_name).message.focus();
}

function isGSMAlphabet(text) {
	var regexp = new RegExp(
			"^[A-Za-z0-9 \\r\\n@£$¥èéùìòÇØøÅå\u0394_\u03A6\u0393\u039B\u03A9\u03A0\u03A8\u03A3\u0398\u039EÆæßÉ!\"#$%&'()*+,\\-./:;<=>?¡ÄÖÑÜ§¿äöñüà^{}\\\\\\[~\\]|\u20AC]*$");
	return regexp.test(text);
}

function SmsTextCounter(form_name) {
	var msg = document.getElementById(form_name).message;
	var msglen = parseInt(msg.value.length, 10);
	var msg_unicode = document.getElementById(form_name).msg_unicode;
	var footerlen = parseInt(document.getElementById(form_name).footerlen.value, 10);
	var maxChar = parseInt(document.getElementById(form_name).maxchar.value, 10);
	var maxChar_unicode = parseInt(document.getElementById(form_name).maxchar_unicode.value, 10);
	var maxlimit = parseInt(document.getElementById(form_name).hiddcount.value, 10);
	var maxlimit_unicode = parseInt(document.getElementById(form_name).hiddcount_unicode.value, 10);
	var chars = parseInt(document.getElementById(form_name).chars.value, 10);
	var SMS = document.getElementById(form_name).SMS.value;
	var limit;
	var devider;
	var msgcount;
	var result;

	if (msg_unicode.checked) {
		limit = maxlimit_unicode;
		devider = 70;
		if (msglen > 70) {
			devider = 67;
		}
	} else {
		limit = maxlimit;
		devider = 160;
		if (msglen > 160) {
			devider = 153;
		}
	}
	if (msglen > limit) {
		msg.value = msg.value.substring(0, limit);
	}
	msgcount = Math.ceil((msglen + footerlen) / parseInt(devider));
	msglen = parseInt(msglen) + parseInt(footerlen);
	result = msgcount + ' / ' + msglen;
	return result;
}

function SmsSetCounter(form_name, span_id) {
	var msg = document.getElementById(form_name).message;
	var ftr = document.getElementById(form_name).msg_footer;
	var msg_unicode = document.getElementById(form_name).msg_unicode;
	var detect = !isGSMAlphabet(msg.value + ftr.value);
	msg_unicode.checked = detect;
	if (ftr.value.length > 0) {
		document.getElementById(form_name).footerlen.value = ftr.value.length + 1;
	} else {
		document.getElementById(form_name).footerlen.value = 0;
	}
	var ilen = SmsTextCounter(form_name);
	document.getElementById(form_name).querySelector('#' + span_id).textContent = ilen;
}

/*
 * ############################ New functions with more abstraction! Don't
 * delete all of the old because some features like the counter of sendsms form
 * isn't prepared for the abstract functions yet. ############################
 */

function SmsSetCounter_Abstract(field, returnField, hiddcountField,
		hiddcount_unicodeField) {
	var ilen = SmsTextCounter_Abstract(field, hiddcountField,
			hiddcount_unicodeField);
	document.getElementById(returnField).value = ilen;
}

function SmsTextCounter_Abstract(field, hiddcountField, hiddcount_unicodeField) {
	var msg = document.getElementById(field);
	var maxlimit = document.getElementById(hiddcountField).value;
	var maxlimit_unicode = document.getElementById(hiddcount_unicodeField).value;
	var limit = maxlimit;
	var devider = 160;
	var messagelenudh;
	var result;

	if (msg.value.length > limit) {
		msg.value = msg.value.substring(0, limit);
	}
	if (msg.value.length > devider) {
		messagelenudh = Math.ceil(msg.value.length / (devider - 7));
		result = msg.value.length + ' char : ' + messagelenudh + ' SMS';
		return result;
	} else {
		// otherwise, update 'characters left' counter
		result = msg.value.length + ' char : 1 SMS';
		return result;
	}
}

function SmsCountKeyDown_Abstract(maxChar, formName) {
	// alert('olá');
	var msg = document.getElementById(formName).field;
	var left = document.getElementById(formName).charNumberLeftOutput;
	var smsLenLeft = maxChar - msg.value.length;
	alert(maxChar);
	if (smsLenLeft >= 0) {
		left.value = smsLenLeft;
	} else {
		var msgMaxLen = maxChar;
		left.value = 0;
		msg.value = msg.value.substring(0, msgMaxLen);
	}
}

function SmsCountKeyUp_Abstract(maxChar, formName, fieldName) {
	// alert('olá');
	var msg = document.getElementById(fieldName)
	var left = document.getElementById(formName).charNumberLeftOutput;

	var smsLenLeft = maxChar - msg.value.length;
	if (smsLenLeft >= 0) {
		left.value = smsLenLeft;
	} else {
		var msgMaxLen = maxChar;
		left.value = 0;
		msg.value = msg.value.substring(0, msgMaxLen);
	}
}
