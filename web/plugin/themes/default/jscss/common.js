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

function SmsTextCounter(field, maxlimit) {
        var messagelen=1;
        var mesagelenudh;
        var messagelenudh1;
        var result1;
        var result2;
        var hamm;
        if (field.value.length > maxlimit) {
                if(maxlimit == 153) {
                        messagelen = Math.ceil(field.value.length/maxlimit)*7;
                } else {
                        messagelen = Math.ceil(field.value.length/maxlimit)*3;
                }
                messagelenudh1 = messagelen + field.value.length;
                messagelenudh = Math.ceil(messagelenudh1/maxlimit);
                hamm = 'Sms(es)';
                result1 = field.value.length + ' char : ' + messagelenudh + ' Sms' ;
                //return messagelenudh; 
                return result1;
        } else {
                // otherwise, update 'characters left' counter
                result2 = field.value.length + ' char : 1 Sms' ;
                return result2;
        //return  field.value.length;
        }
}

function SmsSetCounter() {
        var ilen = SmsTextCounter(document.fm_sendsms.message,document.fm_sendsms.hiddcount.value );
        document.fm_sendsms.txtcount.value  = ilen ;
}

// END -->
