<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()) {forcenoaccess();};

switch ($op) {
	case "sms_subscribe_list" :
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage subscribe')."</h2>
			<p>"._button('index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_add', _('Add SMS subscribe'));
		if (!isadmin()) {
			$query_user_only = "WHERE uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe ".$query_user_only." ORDER BY subscribe_id";
		$db_result = dba_query($db_query);
		$content .= "
			<table cellpadding=1 cellspacing=2 border=0 width=100% class=sortable>
			<thead><tr>";
		if (isadmin()) {
			$content .= "
				<th width=5>*</th>
				<th width=20%>"._('Keyword')."</th>
				<th width=20%>"._('User')."</th>
				<th width=20%>"._('Members')."</th>
				<th width=20%>"._('Messages')."</th>
				<th width=10%>"._('Status')."</th>
				<th width=10%>"._('Action')."</th>";
		} else {
			$content .= "
				<th width=5>*</th>
				<th width=20%>"._('Keyword')."</th>
				<th width=30%>"._('Members')."</th>
				<th width=30%>"._('Messages')."</th>
				<th width=10%>"._('Status')."</th>
				<th width=10%>"._('Action')."</th>";
		}
		$content .= "
			</tr></thead>
			<tbody>";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			if ($owner = uid2username($db_row['uid'])) {
				if (! isadmin()) {
					$query_user_only = "AND uid='$uid'";
				}
				$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id = '".$db_row['subscribe_id']."' ".$query_user_only;
				$members = @dba_num_rows($db_query);
				if (!$members) { $members = 0; }
				$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id = '".$db_row['subscribe_id']."' ".$query_user_only;
				$messages = @dba_num_rows($db_query);
				if (!$messages) { $messages = 0; }
				$i++;
				$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
				$subscribe_status = "<a href=\"index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_status&subscribe_id=".$db_row['subscribe_id']."&ps=1\"><font color=red>"._('disabled')."</font></a>";
				if ($db_row['subscribe_enable']) {
					$subscribe_status = "<a href=\"index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_status&subscribe_id=".$db_row['subscribe_id']."&ps=0\"><font color=green>"._('enabled')."</font></a>";
				}
				$action = "<a href=index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_edit&subscribe_id=".$db_row['subscribe_id'].">$icon_edit</a>&nbsp;";
				$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS subscribe ?')." ("._('keyword').": ".$db_row['subscribe_keyword'].")','index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_del&subscribe_id=".$db_row['subscribe_id']."')\">$icon_delete</a>";
				if (isadmin()) {
					$option_owner = "<td class=$td_class>$owner</td>";
				}
				$content .= "
					<tr>
						<td class=$td_class>&nbsp;$i.</td>
						<td class=$td_class>".$db_row['subscribe_keyword']."</td>
						".$option_owner."
						<td class=$td_class align=center><a href=index.php?app=menu&inc=feature_sms_subscribe&op=msg_list&subscribe_id=".$db_row['subscribe_id'].">".$members."</a></td>
						<td class=$td_class align=center><a href=index.php?app=menu&inc=feature_sms_subscribe&op=mbr_list&subscribe_id=".$db_row['subscribe_id'].">".$messages."</a></td>
						<td class=$td_class align=center>$subscribe_status</td>
						<td class=$td_class align=center>$action</td>
					</tr>";
			}
		}
		$content .= "</tbody>
			</table>
			<p>"._button('index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_add', _('Add SMS subscribe'));
		echo $content;
		break;
	case "sms_subscribe_status" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$ps = $_REQUEST['ps'];
		if (! isadmin()) {
			$query_user_only = "AND uid='$uid'";
		}
		$db_query = "UPDATE " . _DB_PREF_ . "_featureSubscribe SET c_timestamp='" . mktime() . "',subscribe_enable='$ps' WHERE subscribe_id='$subscribe_id' ".$query_user_only;
		$db_result = @ dba_affected_rows($db_query);
		if ($db_result > 0) {
			$_SESSION['error_string'] = _('SMS subscribe status has been changed');
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_list");
		exit();
		break;
	case "sms_subscribe_add" :
		$max_length = $core_config['main']['max_sms_length'];
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$add_forward_param = 'BC';
		$content .= "
			<h2>"._('Manage subscribe')."</h2>
			<h3>"._('Add SMS subscribe')."</h3>
			<p>
			<form name=\"form_subscribe_add\" id=\"form_subscribe_add\" action=index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_add_yes method=post>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td width=150>"._('SMS subscribe keyword')."</td><td width=5>:</td><td><input type=text size=10 maxlength=10 name=add_subscribe_keyword value=\"$add_subscribe_keyword\"></td>
			</tr>
			<tr>
				<td width=150>"._('SMS subscribe parameter')."</td>
				<td width=5>:</td>
				<td>
					<input type=text size=10 maxlength=20 name=add_subscribe_param value=\"$add_subscribe_param\">
				</td>
			</tr>
			<tr>
				<td style=\"vertical-align:top;\">"._('SMS subscribe reply')."</td>
				<td style=\"vertical-align:top;\">:</td>
				<td>
					<textarea maxlength=\"200\" name=\"add_subscribe_msg\" id=\"add_subscribe_msg\" value=\"\" cols=\"35\" rows=\"3\" 
						onClick=\"SmsSetCounter_Abstract('add_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\" 
						onkeypress=\"SmsSetCounter_Abstract('add_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\" 
						onblur=\"SmsSetCounter_Abstract('add_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\" 
						onKeyUp=\"SmsSetCounter_Abstract('add_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\"	
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_add', 'add_subscribe_msg');\" 
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_add');\"></textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount\" id=\"txtcount\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_add.add_subscribe_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"".$core_config['main']['max_sms_length'] ."\" name=\"hiddcount\" id=\"hiddcount\"> 
					<input type=\"hidden\" value=\"".$core_config['main']['max_sms_length_unicode']."\" name=\"hiddcount_unicode\" id=\"hiddcount_unicode\"> 
				</td>
			</tr>
			<tr>
				<td width=150>"._('SMS unsubscribe parameter')."</td>
				<td width=5>:</td>
				<td>
					<input type=text size=10 maxlength=20 name=add_unsubscribe_param value=\"$add_unsubscribe_param\">
				</td>
			</tr>
			<tr>
				<td style=\"vertical-align:top;\">"._('SMS unsubscribe reply')."</td>
				<td style=\"vertical-align:top;\">:</td>
				<td>
					<textarea maxlength=\"200\" name=\"add_unsubscribe_msg\" id=\"add_unsubscribe_msg\" value=\"\" cols=\"35\" rows=\"3\" 
						onClick=\"SmsSetCounter_Abstract('add_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\" 
						onkeypress=\"SmsSetCounter_Abstract('add_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\" 
						onblur=\"SmsSetCounter_Abstract('add_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\" 
						onKeyUp=\"SmsSetCounter_Abstract('add_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\"	
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_add', 'add_unsubscribe_msg');\" 
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_add');\"></textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount_un\" id=\"txtcount_un\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_add.add_unsubscribe_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"".$core_config['main']['max_sms_length'] ."\" name=\"hiddcount_un\" id=\"hiddcount_un\"> 
					<input type=\"hidden\" value=\"".$core_config['main']['max_sms_length_unicode']."\" name=\"hiddcount_unicode_un\" id=\"hiddcount_unicode_un\"> 
				</td>
			</tr>
			<tr>
				<td width=150>"._('SMS forward parameter')."</td>
				<td width=5>:</td>
				<td>
					<input type=text size=10 maxlength=20 name=add_forward_param value=\"$add_forward_param\">
				</td>
			</tr>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			<p>"._b('index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_list');
		echo $content;
		break;
	case "sms_subscribe_add_yes" :
		$add_subscribe_keyword = strtoupper($_POST['add_subscribe_keyword']);
		$add_subscribe_msg = $_POST['add_subscribe_msg'];
		$add_unsubscribe_msg = $_POST['add_unsubscribe_msg'];
		$add_subscribe_param = strtoupper($_POST['add_subscribe_param']);
		$add_unsubscribe_param = strtoupper($_POST['add_unsubscribe_param']);
		$add_forward_param = strtoupper($_POST['add_forward_param']);
		if (! $add_forward_param) { $add_forward_param = 'BC'; };
		if ($add_subscribe_keyword && $add_subscribe_msg && $add_unsubscribe_msg && $add_forward_param) {
			if (checkavailablekeyword($add_subscribe_keyword)) {
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_featureSubscribe (uid,subscribe_keyword,subscribe_msg,unsubscribe_msg, subscribe_param, unsubscribe_param, forward_param)
					VALUES ('$uid','$add_subscribe_keyword','$add_subscribe_msg','$add_unsubscribe_msg','$add_subscribe_param','$add_unsubscribe_param','$add_forward_param')";
				if ($new_uid = @ dba_insert_id($db_query)) {
					$_SESSION['error_string'] = _('SMS subscribe has been added')." ("._('keyword').": $add_subscribe_keyword)";
				} else {
					$_SESSION['error_string'] = _('Fail to add SMS subscribe')." ("._('keyword').": $add_subscribe_keyword)";
				}
			} else {
				$_SESSION['error_string'] = _('SMS subscribe already exists, reserved or use by other feature')." ("._('keyword').": $add_subscribe_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_add");
		exit();
		break;
	case "sms_subscribe_edit" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_subscribe_keyword = $db_row['subscribe_keyword'];
		$edit_subscribe_msg = $db_row['subscribe_msg'];
		$edit_unsubscribe_msg = $db_row['unsubscribe_msg'];
		$edit_subscribe_param = $db_row['subscribe_param'];
		$edit_unsubscribe_param = $db_row['unsubscribe_param'];
		$edit_forward_param = $db_row['forward_param'];
		$max_length = $core_config['main']['max_sms_length'];
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage subscribe')."</h2>
			<h3>"._('Edit SMS subscribe')."</h3>
			<p>
			<form name=\"form_subscribe_edit\" id=\"form_subscribe_edit\" action=index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_edit_yes method=post>
			<input type=hidden name=edit_subscribe_id value=\"$subscribe_id\">
			<input type=hidden name=edit_subscribe_keyword value=\"$edit_subscribe_keyword\">
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td width=150>"._('SMS subscribe keyword')."</td><td width=5>:</td><td><b>$edit_subscribe_keyword</b></td>
			</tr>
				<tr>
					<td width=150>"._('SMS subscribe parameter')."</td>
					<td width=5>:</td>
					<td>
						<input type=text size=10 maxlength=20 name=edit_subscribe_param value=\"$edit_subscribe_param\">
					</td>
				</tr>
			<tr>
				<td style=\"vertical-align:top;\">"._('SMS subscribe reply')."</td>
				<td style=\"vertical-align:top;\">:</td>
				<td>
					<textarea maxlength=\"200\" name=\"edit_subscribe_msg\" id=\"edit_subscribe_msg\" value=\"\" cols=\"35\" rows=\"3\" 
						onClick=\"SmsSetCounter_Abstract('edit_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\" 
						onkeypress=\"SmsSetCounter_Abstract('edit_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\" 
						onblur=\"SmsSetCounter_Abstract('edit_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\" 
						onKeyUp=\"SmsSetCounter_Abstract('edit_subscribe_msg','txtcount','hiddcount','hiddcount_unicode');\"	
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_edit', 'edit_subscribe_msg');\" 
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_edit');\">$edit_subscribe_msg</textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount\" id=\"txtcount\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_edit.edit_subscribe_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"".$core_config['main']['max_sms_length'] ."\" name=\"hiddcount\" id=\"hiddcount\"> 
					<input type=\"hidden\" value=\"".$core_config['main']['max_sms_length_unicode']."\" name=\"hiddcount_unicode\" id=\"hiddcount_unicode\"> 
				</td>
			</tr>
			<tr>
				<td width=150>"._('SMS unsubscribe parameter')."</td>
				<td width=5>:</td>
				<td>
					<input type=text size=10 maxlength=20 name=edit_unsubscribe_param value=\"$edit_unsubscribe_param\">
				</td>
			</tr>
			<tr>
				<td style=\"vertical-align:top;\">"._('SMS unsubscribe reply')."</td>
				<td style=\"vertical-align:top;\">:</td>
				<td>
					<textarea maxlength=\"200\" name=\"edit_unsubscribe_msg\" id=\"edit_unsubscribe_msg\" value=\"\" cols=\"35\" rows=\"3\" 
						onClick=\"SmsSetCounter_Abstract('edit_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\" 
						onkeypress=\"SmsSetCounter_Abstract('edit_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\" 
						onblur=\"SmsSetCounter_Abstract('edit_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\" 
						onKeyUp=\"SmsSetCounter_Abstract('edit_unsubscribe_msg','txtcount_un','hiddcount_un','hiddcount_unicode_un');\"	
						onKeyUp=\"SmsCountKeyUp_Abstract($max_length, 'form_subscribe_edit', 'edit_unsubscribe_msg');\" 
						onKeyDown=\"SmsCountKeyDown_Abstract($max_length, 'form_subscribe_edit');\">$edit_unsubscribe_msg</textarea>
					<br>
					<input type=\"text\"  style=\"font-weight:bold;\" name=\"txtcount_un\" id=\"txtcount_un\" value=\"0 char : 0 SMS\" size=\"17\" onFocus=\"document.form_subscribe_edit.edit_unsubscribe_msg.focus();\" readonly>
					<input type=\"hidden\" value=\"".$core_config['main']['max_sms_length'] ."\" name=\"hiddcount_un\" id=\"hiddcount_un\"> 
					<input type=\"hidden\" value=\"".$core_config['main']['max_sms_length_unicode']."\" name=\"hiddcount_unicode_un\" id=\"hiddcount_unicode_un\"> 
				</td>
			</tr>
			<tr>
				<td width=150>"._('SMS forward parameter')."</td>
				<td width=5>:</td>
				<td>
					<input type=text size=10 maxlength=20 name=edit_forward_param value=\"$edit_forward_param\">
				</td>
			</tr>
		</table>
		<p><input type=submit class=button value=\""._('Save')."\">
		</form>
		<p>"._b('index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_list');
		echo $content;
		break;
	case "sms_subscribe_edit_yes" :
		$edit_subscribe_id = $_POST['edit_subscribe_id'];
		$edit_subscribe_keyword = strtoupper($_POST['edit_subscribe_keyword']);
		$edit_subscribe_msg = $_POST['edit_subscribe_msg'];
		$edit_unsubscribe_msg = $_POST['edit_unsubscribe_msg'];
		$edit_subscribe_param = strtoupper($_POST['edit_subscribe_param']);
		$edit_unsubscribe_param = strtoupper($_POST['edit_unsubscribe_param']);
		$edit_forward_param = strtoupper($_POST['edit_forward_param']);
		if (! $edit_forward_param) { $edit_forward_param = 'BC'; };
		if ($edit_subscribe_id && $edit_subscribe_keyword && $edit_subscribe_msg && $edit_unsubscribe_msg && $edit_forward_param) {
			if (! isadmin()) {
				$query_user_only = "AND uid='$uid'";
			}
			$db_query = "
				UPDATE " . _DB_PREF_ . "_featureSubscribe
				SET c_timestamp='" . mktime() . "',subscribe_keyword='$edit_subscribe_keyword',subscribe_msg='$edit_subscribe_msg',unsubscribe_msg='$edit_unsubscribe_msg', subscribe_param='$edit_subscribe_param', unsubscribe_param='$edit_unsubscribe_param', forward_param='$edit_forward_param'
				WHERE subscribe_id='$edit_subscribe_id' ".$query_user_only;
			if (@ dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS subscribe has been saved')." ("._('keyword').": $edit_subscribe_keyword)";
			} else {
				$_SESSION['error_string'] = _('Fail to edit SMS subscribe')." ("._('keyword').": $edit_subscribe_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_edit&subscribe_id=$edit_subscribe_id");
		exit();
		break;
	case "sms_subscribe_del" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		if (! isadmin()) {
			$query_user_only = "AND uid='$uid'";
		}
		$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id' ".$query_user_only;
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_keyword = $db_row['subscribe_keyword'];
		if ($subscribe_keyword) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id' ".$query_user_only;
			if (@ dba_affected_rows($db_query)) {
				$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id='$subscribe_id'";
				$del_msg = dba_affected_rows($db_query);
				$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id='$subscribe_id'";
				$del_member = dba_affected_rows($db_query);
				$_SESSION['error_string'] = _('SMS subscribe with all its messages and members has been deleted')." ("._('keyword').": $subscribe_keyword)";
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_list");
		exit();
		break;
	case "mbr_list" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		if (! isadmin()) {
			$query_user_only = "AND uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id = '$subscribe_id' ".$query_user_only;
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($subscribe_name = $db_row['subscribe_keyword']) {
			$subscribe_id = $db_row['subscribe_id'];
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id = '$subscribe_id' ORDER BY member_since DESC";
		$db_result = dba_query($db_query);
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage subscribe')."</h2>
			<h3>"._('Member list for keyword')." $subscribe_name</h3>
			<p>
			<table cellpadding=1 cellspacing=2 border=0 width=100% class=sortable>
			<thead><tr>
				<th width=4>*</th>
				<th width=50%>"._('Phone number')."</th>
				<th width=40%>"._('Member join datetime')."</th>
				<th width=10%>"._('Action')."</th>
			</tr></thead>
			<tbody>";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$action = "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete this member ?')."','index.php?app=menu&inc=feature_sms_subscribe&op=mbr_del&subscribe_id=$subscribe_id&mbr_id=".$db_row['member_id']."')\">$icon_delete</a>";
			$content .= "
				<tr>
					<td class=$td_class>&nbsp;$i.</td>
					<td class=$td_class align=center>".$db_row['member_number']."</td>
					<td class=$td_class align=center>".$db_row['member_since']."</td>
					<td class=$td_class align=center>$action</td>
					</tr>";
		}
		$content .= "</tbody>
			</table>
			<p>"._b('index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_list');
		echo $content;
		break;
	case "mbr_del" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$continue = false;
		if (isadmin()) {
			$continue = true;
		} else {
			$list = dba_search(_DB_PREF_.'_featureSubscribe', 'uid', array('uid' => $uid));
			if ($subscribe_id==$list[0]['subscribe_id']) {
				$continue = true;
			}
		}
		$mbr_id = $_REQUEST['mbr_id'];
		if ($mbr_id && $continue) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE member_id='$mbr_id'";
			if (@ dba_affected_rows($db_query)) {
				$_SESSION['error_string'] =_('Member has been deleted');
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=mbr_list&subscribe_id=$subscribe_id");
		exit();
		break;
	case "msg_list" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		if (! isadmin()) {
			$query_user_only = "AND uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id = '$subscribe_id' ".$query_user_only;
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($subscribe_name = $db_row['subscribe_keyword']) {
			$subscribe_id = $db_row['subscribe_id'];
		}
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage subscribe')."</h2>
			<h3>"._('SMS messages list for keyword')." $subscribe_name</h3>
			<p>"._button('index.php?app=menu&inc=feature_sms_subscribe&op=msg_add&&subscribe_id='.$subscribe_id, _('Add message'))."
			<table cellpadding=1 cellspacing=2 border=0 width=100% class=sortable>
			<thead><tr>
				<th width=4>*</th>
				<th width=40%>"._('Message')."</th>
				<th width=20%>"._('Created')."</th>
				<th width=20%>"._('Last update')."</th>
				<th width=10%>"._('Sent')."</th>
				<th width=10%>"._('Action')."</th>
			</tr></thead>
			<tbody>";
		$i = 0;
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id = '$subscribe_id'";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$action = "<a href=index.php?app=menu&inc=feature_sms_subscribe&op=msg_view&subscribe_id=".$db_row['subscribe_id']."&msg_id=".$db_row['msg_id'].">$icon_view</a>&nbsp;";
			$action .= "<a href=index.php?app=menu&inc=feature_sms_subscribe&op=msg_edit&subscribe_id=$subscribe_id&msg_id=".$db_row['msg_id'].">$icon_edit</a>&nbsp;";
			$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete this message?')."','index.php?app=menu&inc=feature_sms_subscribe&op=msg_del&subscribe_id=$subscribe_id&msg_id=".$db_row['msg_id']."')\">$icon_delete</a>";
			$content .= "
				<tr>
					<td class=$td_class>&nbsp;$i.</td>
					<td class=$td_class>".$db_row['msg']."</td>
					<td class=$td_class align=center>".core_display_datetime($db_row['create_datetime'])."</td>
					<td class=$td_class align=center>".core_display_datetime($db_row['update_datetime'])."</td>
					<td class=$td_class align=center>".$db_row['counter']."</td>
					<td class=$td_class align=center>$action</td>
					</tr>";
		}
		$content .= "</tbody>
			</table>
			<p>"._button('index.php?app=menu&inc=feature_sms_subscribe&op=msg_add&&subscribe_id='.$subscribe_id, _('Add message'))."
			<p>"._b('index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_list');
		echo $content;
		break;
	case "msg_edit" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		if (! isadmin()) {
			$query_user_only = "AND uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id' ".$query_user_only;
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($subscribe_name = $db_row['subscribe_keyword']) {
			$subscribe_id = $db_row['subscribe_id'];
		}
		$msg_id = $_REQUEST['msg_id'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id='$subscribe_id' AND msg_id = '$msg_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_mbr_msg = $db_row['msg'];
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage subscribe')."</h2>
			<h3>"._('Edit message')."</h3>
			<form action=index.php?app=menu&inc=feature_sms_subscribe&op=msg_edit_yes method=post>
			<input type=hidden value=$subscribe_id name=subscribe_id>
			<input type=hidden value=$msg_id name=msg_id>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td width=150>"._('SMS subscribe keyword')."</td><td width=5>:</td><td><b>$subscribe_name</b></td>
			</tr>
			<tr>
				<td colspan=3>
					"._('Message body').":
					<p><textarea name=edit_mbr_msg rows=5 cols=60>$edit_mbr_msg</textarea>
				</td>
			</tr>
			</table>
			<input type=submit class=button value=\""._('Save')."\">
			</form>
			<p>"._b('index.php?app=menu&inc=feature_sms_subscribe&op=msg_list&subscribe_id='.$subscribe_id);
		echo $content;
		break;
	case "msg_edit_yes" :
		$subscribe_id = $_POST['subscribe_id'];
		$edit_mbr_msg = $_POST['edit_mbr_msg'];
		$msg_id = $_POST['msg_id'];
		if (! isadmin()) {
			$query_user_only = "AND uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id' ".$query_user_only;
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_id = $db_row['subscribe_id'];
		if ($subscribe_id && $edit_mbr_msg && $msg_id) {
			$db_query = "
				UPDATE " . _DB_PREF_ . "_featureSubscribe_msg set c_timestamp='" . mktime() . "', msg='$edit_mbr_msg',update_datetime='".$core_config['datetime']['now']."'
				WHERE subscribe_id='$subscribe_id' AND msg_id ='$msg_id'";
			if (@ dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Message has been edited');
			} else {
				$_SESSION['error_string'] = _('Fail to edit message');
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=msg_edit&subscribe_id=$subscribe_id&msg_id=$msg_id");
		exit();
		break;
	case "msg_add" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe where subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_name = $db_row['subscribe_keyword'];
		$content .= "
			<h2>"._('Manage subscribe')."</h2>
			<h3>"._('Add message')."</h3>
			<form action=index.php?app=menu&inc=feature_sms_subscribe&op=msg_add_yes method=post>
			<input type=hidden value=$subscribe_id name=subscribe_id>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td width=150>"._('SMS subscribe keyword')."</td><td width=5>:</td><td><b>$subscribe_name</b></td>
			</tr>
			<tr>
				<td colspan=3>
					"._('Message body').":
					<p><textarea name=add_mbr_message rows=5 cols=60></textarea>
				</td>
			</tr>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			<p>"._b('index.php?app=menu&inc=feature_sms_subscribe&op=msg_list&subscribe_id='.$subscribe_id);
		echo $content;
		break;
	case "msg_add_yes" :
		$subscribe_id = $_POST['subscribe_id'];
		$add_mbr_message = $_POST['add_mbr_message'];
		if (! isadmin()) {
			$query_user_only = "AND uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id' ".$query_user_only;
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_id = $db_row['subscribe_id'];
		if ($subscribe_id && $add_mbr_message) {
			$datetime_now = $core_config['datetime']['now'];
			$db_query = "
				INSERT INTO " . _DB_PREF_ . "_featureSubscribe_msg (subscribe_id,msg,create_datetime,update_datetime)
				VALUES ('$subscribe_id','$add_mbr_message','$datetime_now','$datetime_now')";
			if ($new_uid = @ dba_insert_id($db_query)) {
				$_SESSION['error_string'] = _('Message has been added');
			} else {
				$_SESSION['error_string'] = _('Fail to add message');
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=msg_add&subscribe_id=$subscribe_id");
		exit();
		break;
	case "msg_del" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$msg_id = $_REQUEST['msg_id'];
		if (! isadmin()) {
			$query_user_only = "AND uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id = '$subscribe_id' ".$query_user_only;
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_id = $db_row['subscribe_id'];
		if ($msg_id) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id='$subscribe_id' AND msg_id='$msg_id'";
			if (@ dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Message has been deleted');
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=msg_list&subscribe_id=".$subscribe_id);
		exit();
		break;
	case "msg_view" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$msg_id = $_REQUEST['msg_id'];
		if (! isadmin()) {
			$query_user_only = "AND uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id = '$subscribe_id' ".$query_user_only;
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($subscribe_name = $db_row['subscribe_keyword']) {
			$subscribe_id = $db_row['subscribe_id'];
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id='$subscribe_id' AND msg_id = '$msg_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$msg_id = $db_row['msg_id'];
		$message = $db_row['msg'];
		$counter = $db_row['counter'];
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage subscribe')."</h2>
			<h3>"._('Message detail')."</h3>
			<form action=index.php?app=menu&inc=feature_sms_subscribe&op=msg_send method=post>
			<input type=hidden value=$message name=msg>
			<input type=hidden value=$subscribe_id name=subscribe_id>
			<input type=hidden value=$msg_id name=msg_id>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tr><td width=150>"._('SMS subscribe keyword')."</td><td>:</td><td><b>$subscribe_name</b></td></tr>
			<tr><td>"._('Message ID')."</td><td>:</td><td>".$msg_id."</td></tr>
			<tr><td>"._('Message')."</td><td>:</td><td>".$message."</td></tr>
			<tr><td>"._('Sent')."</td><td>:</td><td>".$counter."</td></tr>
			</table>
			<p>"._('Send this message to all members')."</p>
			<input type=submit value=\""._('Send')."\" class=\"button\" />
			</form>
			<p>"._b('index.php?app=menu&inc=feature_sms_subscribe&op=msg_list&subscribe_id='.$subscribe_id);
		echo $content;
		break;
	case "msg_send" :
		$msg_id = $_POST['msg_id'];
		$subscribe_id = $_POST['subscribe_id'];
		if (! isadmin()) {
			$query_user_only = "AND uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id = '$subscribe_id' ".$query_user_only;
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$c_uid = $db_row['uid'];
		$username = uid2username($c_uid);
		$subscribe_id = $db_row['subscribe_id'];
		$db_query = "SELECT msg FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id='$subscribe_id' AND msg_id='$msg_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$message = $db_row['msg'];
		$counter = $db_row['counter'];
		$db_query = "SELECT member_number FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$sms_to = '';
		if ($message && $subscribe_id) {
			while ($db_row = dba_fetch_array($db_result)) {
				if ($member_number = $db_row['member_number']) {
					$sms_to[] = $member_number;
				}
			}
			if ($sms_to[0]) {
				$unicode = core_detect_unicode($message);
				list($ok, $to, $smslog_id, $queue) = sendsms($username, $sms_to, $message, 'text', $unicode);
				if ($ok[0]) {
					$counter++;
					dba_update(_DB_PREF_.'_featureSubscribe_msg', array('counter' => $counter), array('subscribe_id' => $subscribe_id, 'msg_id' => $msg_id));
					$_SESSION['error_string'] .= _('Your SMS has been delivered to queue')."<br>";
				} else {
					$_SESSION['error_string'] .= _('Fail to send SMS')."<br>";
				}
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=msg_view&msg_id=$msg_id&subscribe_id=$subscribe_id");
		exit();
		break;


}
?>