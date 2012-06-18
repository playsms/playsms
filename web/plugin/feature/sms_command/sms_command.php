<?php

if (!(defined('_SECURE_'))) {
    die('Intruder alert');
};
if (!valid()) {
    forcenoaccess();
};

switch ($op) {
    case "sms_command_list":
        if ($err) {
            $content = "<div class=error_string>$err</div>";
        }
        $content .= "
	    <h2>" . _('Manage command') . "</h2>";
        $content .= "<h3>" . _('SMS Cummand List') . "</h3><hr>";
        $content .= "<p>
	    <input type=button value=\"" . _('Add SMS command') . "\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_command&op=sms_command_add')\" class=\"button\" />
	    <p>" . _('SMS command exec path') . " : <b>" . $plugin_config['feature']['sms_command']['bin'] . "/</b>
	";
        if (!isadmin()) {
            $query_user_only = "WHERE uid='$uid'";
        }
        $db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCommand $query_user_only ORDER BY command_keyword";
        $db_result = dba_query($db_query);
        $content .= "
    <table cellpadding=1 cellspacing=2 border=0 width=100%>
    <tr>
        <td class=box_title width=5>*</td>
        <td class=box_title width=100>" . _('Keyword') . "</td>
        <td class=box_title>" . _('Exec') . "</td>
        <td class=box_title width=100>" . _('User') . "</td>	
        <td class=box_title width=75>" . _('Action') . "</td>
    </tr>	
	";
        $i = 0;
        $maxlen = 50;
        while ($db_row = dba_fetch_array($db_result)) {
            $i++;
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
            $owner = uid2username($db_row['uid']);
            $action = "<a href=index.php?app=menu&inc=feature_sms_command&op=sms_command_edit&command_id=" . $db_row['command_id'] . ">$icon_edit</a>&nbsp;";
            $action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete SMS command ?') . " (" . _('keyword') . ": `" . $db_row['command_keyword'] . "`)','index.php?app=menu&inc=feature_sms_command&op=sms_command_del&command_id=" . $db_row['command_id'] . "')\">$icon_delete</a>";
            $command_exec = ( (strlen($db_row['command_exec']) > $maxlen) ? substr($db_row['command_exec'], 0, $maxlen) . "..." : $db_row['command_exec'] );
            $content .= "
    <tr>
	<td class=$td_class>&nbsp;$i.</td>
	<td class=$td_class>" . $db_row['command_keyword'] . "</td>
	<td class=$td_class>" . stripslashes($command_exec) . "</td>
	<td class=$td_class>$owner</td>	
	<td class=$td_class align=center>$action</td>
    </tr>";
        }

        $content .= "
    </table>
	";
        echo $content;
        echo "
	    <p>
	    <input type=button value=\"" . _('Add SMS command') . "\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_command&op=sms_command_add')\" class=\"button\" />
	";
        break;
    case "sms_command_edit":
        $command_id = $_REQUEST['command_id'];
        $db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCommand WHERE command_id='$command_id'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $edit_command_keyword = $db_row['command_keyword'];
        $edit_command_exec = stripslashes($db_row['command_exec']);
        $edit_command_exec = str_replace($plugin_config['feature']['sms_command']['bin'] . "/", '', $edit_command_exec);
        $retasrep = $db_row['returnasreply'];
        if ($retasrep == 1) {
            $checked = "checked";
        }
        if ($err) {
            $content = "<div class=error_string>$err</div>";
        }
        $content .= "
	    <h2>" . _('Edit SMS command') . "</h2>
	    <p>
	    <form action=index.php?app=menu&inc=feature_sms_command&op=sms_command_edit_yes method=post>
	    <input type=hidden name=edit_command_id value=$command_id>
	    <input type=hidden name=edit_command_keyword value=$edit_command_keyword>
	    <p>" . _('SMS command keyword') . ": <b>$edit_command_keyword</b>
	    <p>" . _('Pass these parameter to command exec field') . ":
	    <p><b>{SMSDATETIME}</b> " . _('will be replaced by SMS incoming date/time') . "
	    <p><b>{SMSSENDER}</b> " . _('will be replaced by sender number') . "
	    <p><b>{COMMANDKEYWORD}</b> " . _('will be replaced by command keyword') . "
	    <p><b>{COMMANDPARAM}</b> " . _('will be replaced by command parameter passed to server from SMS') . "
	    <p>" . _('SMS command exec path') . ": <b>" . $plugin_config['feature']['sms_command']['bin'] . "</b>
	    <p>" . _('SMS command exec') . ": <input type=text size=60 name=edit_command_exec value=\"$edit_command_exec\">
            <p>" . _('Return As Reply') . " : <input type=checkbox name=retrep $checked></p>
	    <p><input type=submit class=button value=\"" . _('Save') . "\">
	    </form>
	";
        echo $content;
        break;
    case "sms_command_edit_yes":
        $returnasreply = $_POST['retrep'];
        if ($returnasreply == 'on') {
            $reply = 1;
        } else {
            $reply = 0;
        }
        $edit_command_id = $_POST['edit_command_id'];
        $edit_command_keyword = $_POST['edit_command_keyword'];
        $edit_command_exec = $_POST['edit_command_exec'];
        if ($edit_command_id && $edit_command_keyword && $edit_command_exec) {
            $edit_command_exec = str_replace("/", "", $edit_command_exec);
            $edit_command_exec = str_replace("|", "", $edit_command_exec);
            $edit_command_exec = str_replace("\\", "", $edit_command_exec);
            $db_query = "UPDATE " . _DB_PREF_ . "_featureCommand SET c_timestamp='" . mktime() . "',command_exec='$edit_command_exec',returnasreply=$reply WHERE command_keyword='$edit_command_keyword' AND uid='$uid'";
            if (@dba_affected_rows($db_query)) {
                $error_string = _('SMS command has been saved') . " (" . _('keyword') . ": `$edit_command_keyword`)";
            } else {
                $error_string = _('Fail to save SMS command') . " (" . _('keyword') . ": `$edit_command_keyword`)";
            }
        } else {
            $error_string = _('You must fill all fields');
        }
        header("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_edit&command_id=$edit_command_id&err=" . urlencode($error_string));
        break;
    case "sms_command_del":
        $command_id = $_REQUEST['command_id'];
        $db_query = "SELECT command_keyword FROM " . _DB_PREF_ . "_featureCommand WHERE command_id='$command_id'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $keyword_name = $db_row['command_keyword'];
        if ($keyword_name) {
            $db_query = "DELETE FROM " . _DB_PREF_ . "_featureCommand WHERE command_keyword='$keyword_name'";
            if (@dba_affected_rows($db_query)) {
                $error_string = _('SMS command has been deleted') . " (" . _('keyword') . ": `$keyword_name`)";
            } else {
                $error_string = _('Fail to delete SMS command') . " (" . _('keyword') . ": `$keyword_name`)";
            }
        }
        header("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_list&err=" . urlencode($error_string));
        break;
    case "sms_command_add":
        if ($err) {
            $content = "<div class=error_string>$err</div>";
        }
        $content .= "
	    <h2>" . _('Add SMS command') . "</h2>
	    <p>
	    <form action=index.php?app=menu&inc=feature_sms_command&op=sms_command_add_yes method=post>
	    <p>" . _('SMS command keyword') . ": <input type=text size=10 maxlength=10 name=add_command_keyword value=\"$add_command_keyword\">
	    <p>" . _('Pass these parameter to command exec field') . ":
	    <p><b>{SMSDATETIME}</b> " . _('will be replaced by SMS incoming date/time') . "
	    <p><b>{SMSSENDER}</b> " . _('will be replaced by sender number') . "
	    <p><b>{COMMANDKEYWORD}</b> " . _('will be replaced by command keyword') . "
	    <p><b>{COMMANDPARAM}</b> " . _('will be replaced by command parameter passed to server from SMS') . "
	    <p>" . _('SMS command exec path') . ": <b>" . $plugin_config['feature']['sms_command']['bin'] . "</b>
	    <p>" . _('SMS command exec') . ": <input type=text size=60 maxlength=200 name=add_command_exec value=\"$add_command_exec\">
             <p>" . _('Return As Reply') . " : <input type=checkbox name=retrep></p>
	    <p><input type=submit class=button value=\"" . _('Add') . "\">
	    </form>
	";
        echo $content;
        break;
    case "sms_command_add_yes":
        $returnasreply = $_POST['retrep'];
        if ($returnasreply == 'on') {
            $reply = 1;
        } else {
            $reply = 0;
        }
        $add_command_keyword = strtoupper($_POST['add_command_keyword']);
        $add_command_exec = $_POST['add_command_exec'];
        if ($add_command_keyword && $add_command_exec) {
            $add_command_exec = $add_command_exec;
            $add_command_exec = str_replace("/", "", $add_command_exec);
            $add_command_exec = str_replace("|", "", $add_command_exec);
            $add_command_exec = str_replace("\\", "", $add_command_exec);
            if (checkavailablekeyword($add_command_keyword)) {
                $db_query = "INSERT INTO " . _DB_PREF_ . "_featureCommand (uid,command_keyword,command_exec,returnasreply) VALUES ('$uid','$add_command_keyword','$add_command_exec',$reply)";
                if ($new_uid = @dba_insert_id($db_query)) {
                    $error_string = _('SMS command has been added') . " (" . _('keyword') . " `$add_command_keyword`)";
                } else {
                    $error_string = _('Fail to add SMS command') . " (" . _('keyword') . ": `$add_command_keyword`)";
                }
            } else {
                $error_string = _('SMS command already exists, reserved or use by other feature') . " (" . _('keyword') . ": `$add_command_keyword`)";
            }
        } else {
            $error_string = _('You must fill all fields');
        }
        header("Location: index.php?app=menu&inc=feature_sms_command&op=sms_command_add&err=" . urlencode($error_string));
        break;
}
?>