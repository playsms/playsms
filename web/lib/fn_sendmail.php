<?php
function sendmail($mail_from,$mail_to,$mail_subject="",$mail_body="")
{
    global $core_config;
    for ($c=0;$c<count($core_config['toolslist']);$c++)
    {
	if (x_hook($core_config['toolslist']['$c'],'sendmail',array($keyword))) {
	    $ok = false;
	    break;
	}
    }
}

?>