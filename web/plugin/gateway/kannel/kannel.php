<?php

if (!(defined('_SECURE_'))) {
    die('Intruder alert');
};
if (!isadmin()) {
    forcenoaccess();
};

include $apps_path['plug'] . "/gateway/kannel/config.php";

if ($gateway_module == $kannel_param['name']) {
    $status_active = "(<b><font color=green>" . _('Active') . "</font></b>)";
} else {
    $status_active = "(<b><font color=red>" . _('Inactive') . "</font></b>) (<a href=\"index.php?app=menu&inc=gateway_kannel&op=manage_activate\">" . _('click here to activate') . "</a>)";
}

switch ($op) {
    case "manage":
        if ($err) {
            $content = "<div class=error_string>$err</div>";
        }
        //Fixme Edward, Browse /etc/kannel.conf to Show on web Page
        $kanelconffile = "/etc/kannel/kannel.conf";
        $readconf = fopen($kanelconffile, 'r');
        $conf = fread($readconf, filesize($kanelconffile));
        fclose($readconf);
        //End Of Fixme Edward, Browse /etc/kannel.conf to Show on web Page
        $content .= "
	    <h2>" . _('Manage kannel') . "</h2>
	    <p>
	    <form action=index.php?app=menu&inc=gateway_kannel&op=manage_save method=post>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>" . _('Gateway name') . "</td><td width=5>:</td><td><b>kannel</b> $status_active</td>
	    </tr>
	    <tr>
		<td>" . _('Username') . "</td><td>:</td><td><input type=text size=30 maxlength=30 name=up_username value=\"" . $kannel_param['username'] . "\"></td>
	    </tr>	    
	    <tr>
		<td>" . _('Password') . "</td><td>:</td><td><input type=password size=30 maxlength=30 name=up_password value=\"\"> (" . _('Fill to change the password') . ")</td>
	    </tr>
	    <tr>
		<td>"._('Module sender ID')."</td><td>:</td><td><input type=text size=16 maxlength=16 name=up_global_sender value=\"".$kannel_param['global_sender']."\"> ("._('Max. 16 numeric or 11 alphanumeric char. empty to disable').")</td>
	    </tr>	    
	    <tr>
		<td>" . _('Module timezone') . "</td><td>:</td><td><input type=text size=5 maxlength=5 name=up_global_timezone value=\"" . $kannel_param['datetime_timezone'] . "\"> (" . _('Eg: +0700 for Jakarta/Bangkok timezone') . ")</td>
	    </tr>
	    <tr>
		<td>" . _('Bearerbox hostname or IP') . "</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_bearerbox_host value=\"" . $kannel_param['bearerbox_host'] . "\"> (" . _('Kannel specific') . ")</td>
	    </tr>	    
	    <tr>
		<td>" . _('Send SMS port') . "</td><td>:</td><td><input type=text size=10 maxlength=10 name=up_sendsms_port value=\"" . $kannel_param['sendsms_port'] . "\"> (" . _('Kannel specific') . ")</td>
	    </tr>	    
	    <tr>
		<td>" . _('Additional URL parameter') . "</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_additional_param value=\"" . $kannel_param['additional_param'] . "\"></td>
	    </tr>
	    <tr>
		<td>" . _('playSMS web URL') . "</td><td>:</td><td><input type=text size=30 maxlength=250 name=up_playsms_web value=\"" . $kannel_param['playsms_web'] . "\"> (" . _('URL to playSMS, empty it to set it to base URL') . ")</td>
	    </tr>
            <!-- Fixme Edward Added Kanel HTTP Admin Parameter-->
             <tr>
		<td>" . _('Kannel Admin Url') . "</td><td>:</td><td><input type=text size=30 maxlength=250 name=kannel_admin_url value=\"" . $kannel_param['admin_url'] . "\"> (" . _('URL to Kannel HTTP Administration') . ")</td>
	    </tr>
            <tr>
		<td>" . _('Kannel Admin Password') . "</td><td>:</td><td><input type=text size=30 maxlength=250 name=kannel_admin_pwd value=\"" . $kannel_param['admin_pwd'] . "\"> (" . _('Password of Http Kannel Admin') . ")</td>
	    </tr>
            <tr>
		<td>" . _('Kannel Admin Port') . "</td><td>:</td><td><input type=text size=30 maxlength=250 name=kannel_admin_port value=\"" . $kannel_param['admin_port'] . "\"> (" . _('Port Of Http Kannel Admin') . ")</td>
	    </tr>
            <tr>
            <td>" . _('Kannel Configurations files') . "</td><td>:</td><td><textarea name='kannelconf' rows='40' style='width: 100%; border: 1px solid #333; padding: 4px; '>$conf</textarea></td>
            </tr>
                <!-- End Of Fixme Edward Added Kanel HTTP Admin Parameter--> 
	</table>	    
	    <!--
	    <p>" . _('Note') . ":</br>
	    <p><input type=checkbox name=up_trn $checked> " . _('Send SMS message without footer banner') . "
	    -->
	    <p><input type=submit class=button value=\"" . _('Save') . "\">
	    </form>
            <!-- Fixme Edward Added Button Restart Kannel, To Restart Kannel Services-->
            <p><input type='button' value='Restart Services' class='button' onClick=\"parent.location.href='index.php?app=menu&inc=gateway_kannel&op=restart-kannel'\"></p>
            <!-- End Of Fixme Edward Added Button Restart Kannel, To Restart Kannel Services-->
	";
        echo $content;
        break;
    case "manage_save":
        $up_username = $_POST['up_username'];
        $up_password = $_POST['up_password'];
        $up_global_sender = $_POST['up_global_sender'];
        $up_global_timezone = $_POST['up_global_timezone'];
        $up_bearerbox_host = $_POST['up_bearerbox_host'];
        $up_sendsms_port = $_POST['up_sendsms_port'];
        $up_playsms_web = ( $_POST['up_playsms_web'] ? $_POST['up_playsms_web'] : $http_path['base'] );
        $up_additional_param = ( $_POST['up_additional_param'] ? $_POST['up_additional_param'] : "smsc=default" );
        $error_string = _('No changes has been made');
        if ($up_username && $up_bearerbox_host && $up_sendsms_port) {
            if ($up_password) {
                $password_change = "cfg_password='$up_password',";
            }
            //Fixme Edward, Added Kannel HTTP Admin Parameter
            /*             * $db_query = "
              UPDATE " . _DB_PREF_ . "_gatewayKannel_config
              SET c_timestamp='" . mktime() . "',
              cfg_username='$up_username',
              " . $password_change . "
              cfg_global_sender='$up_global_sender',
              cfg_datetime_timezone='$up_global_timezone',
              cfg_bearerbox_host='$up_bearerbox_host',
              cfg_sendsms_port='$up_sendsms_port',
              cfg_playsms_web='$up_playsms_web',
              cfg_additional_param='$up_additional_param'
              ";* */
            $db_query = "
		UPDATE " . _DB_PREF_ . "_gatewayKannel_config 
		SET c_timestamp='" . mktime() . "',
		    cfg_username='$up_username',
		    " . $password_change . "
		    cfg_global_sender='$up_global_sender',
		    cfg_datetime_timezone='$up_global_timezone',
		    cfg_bearerbox_host='$up_bearerbox_host',
		    cfg_sendsms_port='$up_sendsms_port',
		    cfg_playsms_web='$up_playsms_web',
		    cfg_additional_param='$up_additional_param',
                    cfg_adminhost='$up_adminkannelurl',
                    cfg_adminpwd='$up_adminkannelpwd',
                    cfg_adminport='$up_adminkannelport'
	    ";
            //End Of Fixme Edward, Added Kannel HTTP Admin Parameter
            if (@dba_affected_rows($db_query)) {
                $error_string = _('Gateway module configurations has been saved');
            }
            //Fixme Edward, Handle Editing Kannel.conf Via Web
            $kanelconffile = "/etc/kannel/kannel.conf";
            $readconf = fopen($kanelconffile, "r");
            $conf = fread($readconf, filesize($kanelconffile));

            $confstring = $_POST['kannelconf'];
            //print_r($confstring);
            $fhandle = fopen($kanelconffile, "w");
            fwrite($fhandle, $confstring);
            fclose($fhandle);
            //End Of Edward, Handle Editing Kannel.conf Via Web
        }
        header("Location: index.php?app=menu&inc=gateway_kannel&op=manage&err=" . urlencode($error_string));
        break;
    case "manage_activate":
        $db_query = "UPDATE " . _DB_PREF_ . "_tblConfig_main SET c_timestamp='" . mktime() . "',cfg_gateway_module='kannel'";
        $db_result = dba_query($db_query);
        $error_string = _('Gateway has been activated');
        header("Location: index.php?app=menu&inc=gateway_kannel&op=manage&err=" . urlencode($error_string));
        break;
    
    //Fixme Edward, Adding New Case To Handle Button Restart Kannel Services
     case "restart-kannel":
        $kanneladminurl = $kannel_param['admin_url'];
        $kanneladminpwd = $kannel_param['admin_pwd'];
        $kanneladminport = $kannel_param['admin_port'];
        $url = "$kanneladminurl$kanneladminport/restart?password=$kanneladminpwd";
        $restart = file_get_contents($url);
        $error_string   = _('Kannel Service Has Been Restart');
        header("Location: index.php?app=menu&inc=gateway_kannel&op=manage&err=" . urlencode($error_string));
        break;
    //end Of Fixme Edward, Adding New Case To Handle Button Restart Kannel Services
}
?>
