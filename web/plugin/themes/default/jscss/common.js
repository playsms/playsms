<!-- BEGIN #

function CheckUncheckAll(the_form) 
{
        for (var i=0; i < the_form.elements.length; i++) 
        {
                if (the_form.elements[i].type=="checkbox") 
                {
                        the_form.elements[i].checked = !(the_form.elements[i].checked);
                }
        }
}


function PopupSendSms(ta, tg)
{
        var pv = "PV";
        if (ta == pv)
        {
                var url = "menu.php?inc=send_sms&op=sendsmstopv&dst_p_num="+tg;
        }
        else
        {
                var url = "menu.php?inc=send_sms&op=sendsmstogr&dst_gp_code="+tg;
        }
        newwin=window.open("","WinSendSms","scrollbars","resizable=yes")
        newwin.moveTo(20,100)
        newwin.resizeTo(500,500)
        newwin.location=url	    
}

function PopupReplySms(tg, mssg)
{
        var url = "menu.php?inc=send_sms&op=sendsmstopv&dst_p_num="+tg+"&message="+mssg;

        newwin=window.open("","WinSendSms","scrollbars","resizable=yes")
        newwin.moveTo(20,100)
        newwin.resizeTo(500,500)
        newwin.location=url	    
}

function linkto(url)
{
        window.location.href = url;
}

function ConfirmURL(inputText, inputURL)
{ 
        if (confirm(inputText)) document.location=inputURL
}

function SureConfirm()
{
        if (confirm('Are you sure ?')) {
                return true;
        } else {
                return false;
        }
}

function SetSmsTemplate() {
        sellength = document.forms.fm_sendsms.smstemplate.length;
        for ( i=0; i<sellength; i++)
        {
                if (document.forms.fm_sendsms.smstemplate.options[i].selected == true)
                {
                        document.forms.fm_sendsms.message.value = document.forms.fm_sendsms.smstemplate.options[i].value;
                }
        }
}

function SmsTextCounter() {
        var msg = document.fm_sendsms.message;
        var msg_unicode = document.fm_sendsms.msg_unicode;
        var footerlen = parseInt(document.forms.fm_sendsms.footerlen.value);
        var maxChar = document.forms.fm_sendsms.maxchar.value;
        var maxChar_unicode = document.forms.fm_sendsms.maxchar_unicode.value;
        var maxlimit = document.fm_sendsms.hiddcount.value;
        var maxlimit_unicode = document.fm_sendsms.hiddcount_unicode.value;
        var limit;
        var devider;
        var msgcount;
        var result;
        if (msg_unicode.checked) {
                limit = maxlimit_unicode;
                devider = 70;
                if (msg.value.length > 70) {
                	devider = 63;
                }
        } else {
                limit = maxlimit;
                devider = 160;
                if (msg.value.length > 160) {
                	devider = 153;
                }
        }
        if (msg.value.length > limit) {
                msg.value = msg.value.substring(0, limit);
        }
        msgcount = Math.ceil((msg.value.length + footerlen) / devider);
        result = msg.value.length + ' char : ' + msgcount + ' SMS' ;
        return result;
}

function SmsSetCounter() {
        var ilen = SmsTextCounter();
        document.fm_sendsms.txtcount.value  = ilen ;
}

/* ############################
New functions with more abstraction! Don't delete all of the old because some features
like the counter of sendsms form isn't prepared for the abstract functions yet.
############################ */

function SmsSetCounter_Abstract(field, returnField, hiddcountField, hiddcount_unicodeField) {
        var ilen = SmsTextCounter_Abstract(field, hiddcountField, hiddcount_unicodeField);
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
                result = msg.value.length + ' char : ' + messagelenudh + ' SMS' ;
                return result;
        } else {
                // otherwise, update 'characters left' counter
                result = msg.value.length + ' char : 1 SMS' ;
                return result;
        }
}

function SmsCountKeyDown_Abstract(maxChar, formName)
{
		//alert('olá');
        var msg  = document.getElementById(formName).field;
        var left = document.getElementById(formName).charNumberLeftOutput;
        var smsLenLeft = maxChar  - msg.value.length;
		alert(maxChar);
        if (smsLenLeft >= 0) 
        {
                left.value = smsLenLeft;
        } 
        else 
        {
                var msgMaxLen = maxChar;
                left.value = 0; 
                msg.value = msg.value.substring(0, msgMaxLen);
        }
}

function SmsCountKeyUp_Abstract(maxChar,formName, fieldName)
{
		//alert('olá');
        var msg  = document.getElementById(fieldName)
        var left = document.getElementById(formName).charNumberLeftOutput;
        
        var smsLenLeft = maxChar  - msg.value.length;
        if (smsLenLeft >= 0) 
        {
                left.value = smsLenLeft;
        } 
        else 
        {
                var msgMaxLen = maxChar;
                left.value = 0;
                msg.value = msg.value.substring(0, msgMaxLen);
        }
}

// END -->
