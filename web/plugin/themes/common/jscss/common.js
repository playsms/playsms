function CheckUncheckAll(the_form) {
	for (var i = 0; i < the_form.elements.length; i++) {
		if (the_form.elements[i].type == "checkbox") {
			the_form.elements[i].checked = !(the_form.elements[i].checked);
		}
	}
}

function PopupSendSms(sms_to, sms_message, dialog_title, return_url) {
	BootstrapDialog.show({
		type : BootstrapDialog.TYPE_PRIMARY,
		title : dialog_title,
		closable : false,
		closeByBackdrop : false,
		closeByKeyboard : false,
		draggable: true,
		message : function(dialog) {
			var $message = $('<div></div>');
			var pageToLoad = dialog.getData('pageToLoad');
			$message.load(pageToLoad);

			return $message;
		},
		data : {
			'pageToLoad' : 'index.php?app=main&inc=core_sendsms&op=sendsms&to='
					+ sms_to + '&message=' + sms_message
					+ '&popup=1&_themes_layout_=contentonly' + '&return_url='
					+ return_url
		}
	});
}

function PopupReplySms(sms_to, sms_message, dialog_title, return_url) {
	PopupSendSms(sms_to, sms_message, dialog_title, return_url);
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

function SureConfirm() {
	BootstrapDialog.show({
		type : BootstrapDialog.TYPE_DANGER,
		title : 'Are you sure ?',
		message : text,
		buttons : [ {
			label : 'No',
			cssClass : 'btn-primary',
			action : function(dialogItself) {
				return false;
			}
		}, {
			label : 'Yes',
			cssClass : 'btn-success',
			action : function() {
				return true;
			}
		} ]
	});
}

function SubmitConfirm(text, form_name) {
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
					document.getElementById(form_name).submit();
				}
			} ]
		});
	}
}

function SetSmsTemplate() {
	sellength = document.forms.fm_sendsms.smstemplate.length;
	for (i = 0; i < sellength; i++) {
		if (document.forms.fm_sendsms.smstemplate.options[i].selected == true) {
			document.forms.fm_sendsms.message.value = document.forms.fm_sendsms.smstemplate.options[i].value;
		}
	}
}

function SmsTextCounter() {
	var msg = document.fm_sendsms.message;
	var msg_len = parseInt(msg.value.length);
	var msg_unicode = document.fm_sendsms.msg_unicode;
	var footerlen = parseInt(document.forms.fm_sendsms.footerlen.value);
	var maxChar = document.forms.fm_sendsms.maxchar.value;
	var maxChar_unicode = document.forms.fm_sendsms.maxchar_unicode.value;
	var maxlimit = document.fm_sendsms.hiddcount.value;
	var maxlimit_unicode = document.fm_sendsms.hiddcount_unicode.value;
	var chars = document.fm_sendsms.chars.value;
	var SMS = document.fm_sendsms.SMS.value;
	var limit;
	var devider;
	var msgcount;
	var result;

	if (msg_unicode.checked) {
		limit = maxlimit_unicode;
		devider = 70;
		if (msg_len > 70) {
			devider = 63;
		}
	} else {
		limit = maxlimit;
		devider = 160;
		if (msg_len > 160) {
			devider = 153;
		}
	}
	if (msg_len > limit) {
		msg.value = msg.value.substring(0, limit);
	}
	msgcount = Math.ceil((msg_len + footerlen) / devider);
	result = msg_len + footerlen + ' ' + chars + ' : ' + msgcount + ' ' + SMS;
	return result;
}

function isGSMAlphabet(text) {
	var regexp = new RegExp(
			"^[A-Za-z0-9 \\r\\n@£$¥èéùìòÇØøÅå\u0394_\u03A6\u0393\u039B\u03A9\u03A0\u03A8\u03A3\u0398\u039EÆæßÉ!\"#$%&'()*+,\\-./:;<=>?¡ÄÖÑÜ§¿äöñüà^{}\\\\\\[~\\]|\u20AC]*$");
	return regexp.test(text);
}

function SmsSetCounter() {
	var msg = document.fm_sendsms.message;
	var ftr = document.fm_sendsms.msg_footer;
	var msg_unicode = document.fm_sendsms.msg_unicode;
	var detect = !isGSMAlphabet(msg.value + ftr.value);
	msg_unicode.checked = detect;
	if (ftr.value.length > 0) {
		document.forms.fm_sendsms.footerlen.value = ftr.value.length + 1;
	} else {
		document.forms.fm_sendsms.footerlen.value = 0;
	}
	var ilen = SmsTextCounter();
	document.fm_sendsms.txtcount.value = ilen;
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
