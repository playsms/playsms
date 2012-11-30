<?php
defined('_SECURE_') or die('Forbidden');
if (!valid()) { forcenoaccess(); };

switch ($op) {
    case "sms_custom_list":
        if ($err = $_SESSION['error_string']) {
            $content = "<div class=error_string>$err</div>";
        }

        $content .= "
	    <h2>" . _('Manage custom') . "</h2>";

        $content .="<p>
	    <input type=button value=\"" . _('Add SMS custom') . "\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_custom&op=sms_custom_add')\" class=\"button\" />
	    <p>
	";

        if (!isadmin()) {
            $query_user_only = "WHERE uid='$uid'";
        }


        $db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCustom $query_user_only ORDER BY custom_keyword";
        $db_result = dba_query($db_query);

        $content .= "
    <table cellpadding=1 cellspacing=2 border=0 width=100%>
    <tr>
        <td class=box_title width=5>*</td>
        <td class=box_title width=100>" . _('Keyword') . "</td>
        <td class=box_title>" . _('URL') . "</td>
        <td class=box_title width=100>" . _('User') . "</td>	
        <td class=box_title width=75>" . _('Action') . "</td>
    </tr>
	";
        $i = 0;
        $maxlen = 50;
        while ($db_row = dba_fetch_array($db_result)) {
		if ($owner = uid2username($db_row['uid'])) {
            $i++;
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
            $action = "<a href=index.php?app=menu&inc=feature_sms_custom&op=sms_custom_edit&custom_id=" . $db_row['custom_id'] . ">$icon_edit</a>&nbsp;";
            $action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete SMS custom keyword ?') . " (" . _('keyword') . ": " . $db_row['custom_keyword'] . ")','index.php?app=menu&inc=feature_sms_custom&op=sms_custom_del&custom_id=" . $db_row['custom_id'] . "')\">$icon_delete</a>";
            $custom_url = ( (strlen($db_row['custom_url']) > $maxlen) ? substr($db_row['custom_url'], 0, $maxlen) . "..." : $db_row['custom_url'] );
            $content .= "
    <tr>
	<td class=$td_class>&nbsp;$i.</td>
	<td class=$td_class>" . $db_row['custom_keyword'] . "</td>
	<td class=$td_class>" . stripslashes($custom_url) . "</td>
	<td class=$td_class>$owner</td>	
	<td class=$td_class align=center>$action</td>
    </tr>";
		}
        }
        $content .= "</table>";
        echo $content;
        echo "
	    <p>
	    <input type=button value=\"" . _('Add SMS custom') . "\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_custom&op=sms_custom_add')\" class=\"button\" />
	";
        break;
    case "sms_custom_edit":
        $custom_id = $_REQUEST['custom_id'];
        $db_query = "SELECT * FROM " . _DB_PREF_ . "_featureCustom WHERE custom_id='$custom_id'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $edit_custom_keyword = $db_row['custom_keyword'];
        $edit_custom_url = stripslashes($db_row['custom_url']);
        $edit_custom_url = str_replace($feat_custom_path['bin'], '', $edit_custom_url);
        $edit_custom_return_as_reply = ( $db_row['custom_return_as_reply'] == '1' ? 'checked' : '' );
        if ($err = $_SESSION['error_string']) {
            $content = "<div class=error_string>$err</div>";
        }
        $content .= "
	    <h2>" . _('Edit SMS custom') . "</h2>
	    <p>
	    <form action=index.php?app=menu&inc=feature_sms_custom&op=sms_custom_edit_yes method=post>
	    <input type=hidden name=edit_custom_id value=$custom_id>
	    <input type=hidden name=edit_custom_keyword value=$edit_custom_keyword>
	    <p>" . _('SMS custom keyword') . ": <b>$edit_custom_keyword</b>
	    <p>" . _('Pass these parameter to custom URL field') . ":
	    <p><b>{SMSDATETIME}</b> " . _('will be replaced by SMS incoming date/time') . "
	    <p><b>{SMSSENDER}</b> " . _('will be replaced by sender number') . "
	    <p><b>{CUSTOMKEYWORD}</b> " . _('will be replaced by custom keyword') . "
	    <p><b>{CUSTOMPARAM}</b> " . _('will be replaced by custom parameter passed to server from SMS') . "
	    <p><b>{CUSTOMRAW}</b> " . _('will be replaced by SMS raw message') . "
	    <p>" . _('SMS custom URL') . ": <input type=text size=60 name=edit_custom_url value=\"$edit_custom_url\">
            <p>" . _('Make return as reply') . " : <input type=checkbox name=edit_custom_return_as_reply $edit_custom_return_as_reply></p>
	    <p><input type=submit class=button value=\"" . _('Save') . "\">
	    </form>
	";
        echo $content;
        break;
    case "sms_custom_edit_yes":
        $edit_custom_return_as_reply = ( $_POST['edit_custom_return_as_reply'] == 'on' ? '1' : '0' );
        $edit_custom_id = $_POST['edit_custom_id'];
        $edit_custom_keyword = $_POST['edit_custom_keyword'];
        $edit_custom_url = $_POST['edit_custom_url'];
        if ($edit_custom_id && $edit_custom_keyword && $edit_custom_url) {
            $db_query = "UPDATE " . _DB_PREF_ . "_featureCustom SET c_timestamp='" . mktime() . "',custom_url='$edit_custom_url',custom_return_as_reply='$edit_custom_return_as_reply' WHERE custom_keyword='$edit_custom_keyword' AND uid='$uid'";
            echo $db_query;
            if (@dba_affected_rows($db_query)) {
                $_SESSION['error_string'] = _('SMS custom has been saved') . " (" . _('keyword') . " $edit_custom_keyword)";
            } else {
                $_SESSION['error_string'] = _('Fail to save SMS custom') . " (" . _('keyword') . ": $edit_custom_keyword)";
            }
        } else {
            $_SESSION['error_string'] = _('You must fill all fields');
        }
        header("Location: index.php?app=menu&inc=feature_sms_custom&op=sms_custom_edit&custom_id=$edit_custom_id");
        exit();
        break;
    case "sms_custom_del":
        $custom_id = $_REQUEST['custom_id'];
        $db_query = "SELECT custom_keyword FROM " . _DB_PREF_ . "_featureCustom WHERE custom_id='$custom_id'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $keyword_name = $db_row['custom_keyword'];
        if ($keyword_name) {
            $db_query = "DELETE FROM " . _DB_PREF_ . "_featureCustom WHERE custom_keyword='$keyword_name'";
            if (@dba_affected_rows($db_query)) {
                $_SESSION['error_string'] = _('SMS custom has been deleted') . " (" . _('keyword') . " $keyword_name)";
            } else {
                $_SESSION['error_string'] = _('Fail to delete SMS custom') . " (" . _('keyword') . ": $keyword_name)";
            }
        }
        header("Location: index.php?app=menu&inc=feature_sms_custom&op=sms_custom_list");
        exit();
        break;
    case "sms_custom_add":
        if ($err = $_SESSION['error_string']) {
            $content = "<div class=error_string>$err</div>";
        }
        $content .= "
	    <h2>" . _('Add SMS custom') . "</h2>
	    <p>
	    <form action=index.php?app=menu&inc=feature_sms_custom&op=sms_custom_add_yes method=post>
	    <p>" . _('SMS custom keyword') . ": <input type=text size=10 maxlength=10 name=add_custom_keyword value=\"$add_custom_keyword\">
	    <p>" . _('Pass these parameter to custom URL field') . ":
	    <p><b>{SMSDATETIME}</b> " . _('will be replaced by SMS incoming date/time') . "
	    <p><b>{SMSSENDER}</b> " . _('will be replaced by sender number') . "
	    <p><b>{CUSTOMKEYWORD}</b> " . _('will be replaced by custom keyword') . "
	    <p><b>{CUSTOMPARAM}</b> " . _('will be replaced by custom parameter passed to server from SMS') . "
	    <p><b>{CUSTOMRAW}</b> " . _('will be replaced by SMS raw message') . "
	    <p>" . _('SMS custom URL') . ": <input type=text size=60 maxlength=200 name=add_custom_url value=\"$add_custom_url\">
            <p>" . _('Make return as reply') . " : <input type=checkbox name=add_custom_return_as_reply></p>
	    <p><input type=submit class=button value=\"" . _('Add') . "\">
	    </form>
	";
        echo $content;
        break;
    case "sms_custom_add_yes":
        $add_custom_return_as_reply = ( $_POST['add_custom_return_as_reply'] == 'on' ? '1' : '0' );
        $add_custom_keyword = strtoupper($_POST['add_custom_keyword']);
        $add_custom_url = $_POST['add_custom_url'];
        if ($add_custom_keyword && $add_custom_url) {
            if (checkavailablekeyword($add_custom_keyword)) {
                $db_query = "INSERT INTO " . _DB_PREF_ . "_featureCustom (uid,custom_keyword,custom_url,custom_return_as_reply) VALUES ('$uid','$add_custom_keyword','$add_custom_url','$add_custom_return_as_reply')";
                echo $db_query;
                if ($new_uid = @dba_insert_id($db_query)) {
                    $_SESSION['error_string'] = _('SMS custom has been added') . " (" . _('keyword') . ": $add_custom_keyword)";
                } else {
                    $_SESSION['error_string'] = _('Fail to add SMS custom') . " (" . _('keyword') . ": $add_custom_keyword)";
                }
            } else {
                $_SESSION['error_string'] = _('SMS custom already exists, reserved or use by other feature') . " (" . _('keyword') . ": $add_custom_keyword)";
            }
        } else {
            $_SESSION['error_string'] = _('You must fill all fields');
        }
        header("Location: index.php?app=menu&inc=feature_sms_custom&op=sms_custom_add");
        exit();
        break;
}
?>