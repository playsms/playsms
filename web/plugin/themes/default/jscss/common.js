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

function ConfirmURL(inputText, inputURL)
{ 
    if (confirm(inputText)) document.location=inputURL
}

function SmsCountKeyUp(maxChar)
{
    var msg  = document.forms.fm_sendsms.message;
    var left = document.forms.fm_sendsms.charNumberLeftOutput;
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

function SmsCountKeyDown(maxChar)
{
    var msg  = document.forms.fm_sendsms.message;
    var left = document.forms.fm_sendsms.charNumberLeftOutput;
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

function linkto(url)
{
    window.location.href = url;
}

function SureConfirm()
{
    if (confirm('Are you sure ?')) {return true;} else {return false;}
}
// END -->