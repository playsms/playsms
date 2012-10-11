<?php
defined('_SECURE_') or die('Forbidden');
if (!valid()) {
	forcenoaccess();
};

switch ($op) {
	case "sms_subscribe_list" :
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
				<h2>"._('Manage subscribe')."</h2>
				<p>
				<input type=button value=\""._('Add SMS subscribe')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_add')\" class=\"button\" />
				<p>
			";
		if (!isadmin()) {
			$query_user_only = "WHERE uid='$uid'";
		}
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe $query_user_only ORDER BY subscribe_id";
		$db_result = dba_query($db_query);
		$content .= "
					<table cellpadding=1 cellspacing=2 border=0 width=100%>
			<tr>
			    <td class=box_title width=5>*</td>
			    <td class=box_title width=30%>"._('Keyword')."</td>
				<td class=box_title	width=30%>"._('Total members')."</td>
			   	<td class=box_title width=20%>"._('User')."</td>	
			    <td class=box_title width=20%>"._('Status')."</td>
			    <td class=box_title>"._('Action')."</td>
			</tr>		
			";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id = '".$db_row['subscribe_id']."'";
			$num_rows = dba_num_rows($db_query);
			if (!$num_rows) {
				$num_rows = "0";
			}
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$owner = uid2username($db_row['uid']);
			$subscribe_status = "<font color=red>"._('Disabled')."</font>";
			if ($db_row['subscribe_enable']) {
				$subscribe_status = "<font color=green>"._('Enabled')."</font>";
			}
			$action = "<a href=index.php?app=menu&inc=feature_sms_subscribe&op=mbr_list&subscribe_id=".$db_row['subscribe_id'].">$subscribe_icon_view_members</a>&nbsp;";
			$action .= "<a href=index.php?app=menu&inc=feature_sms_subscribe&op=msg_list&subscribe_id=".$db_row['subscribe_id'].">$subscribe_icon_view_messages</a>&nbsp;";
			$action .= "<a href=index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_mbr_msg_add&subscribe_id=".$db_row['subscribe_id'].">$subscribe_icon_add_message</a>&nbsp;";
			$action .= "<a href=index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_edit&subscribe_id=".$db_row['subscribe_id'].">$icon_edit</a>&nbsp;";
			$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS subscribe ?')." ("._('keyword').": ".$db_row['subscribe_keyword'].")','index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_del&subscribe_id=".$db_row['subscribe_id']."')\">$icon_delete</a>";
			$content .= "
					<tr>
						<td class=$td_class>&nbsp;$i.</td>
						<td class=$td_class>".$db_row['subscribe_keyword']."</td>
						<td class=$td_class>$num_rows</td>
						<td class=$td_class>$owner</td>
						<td class=$td_class>$subscribe_status</td>		
						<td class=$td_class align=center>$action</td>
					</tr>";
		}
		$content .= "</table>";
		echo $content;
		echo "
				<p>
				<input type=button value=\""._('Add SMS subscribe')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_add')\" class=\"button\" />
				</p>
				";
		break;

	case "msg_list" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id = '$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_name = $db_row['subscribe_keyword'];

		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE subscribe_id = '$subscribe_id'";
		$db_result = dba_query($db_query);
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
		    <h2>"._('SMS messages list for keyword')." $subscribe_name</h2>	    
			";
		$content .= "
				<p>
				<input type=button value=\""._('Add Message')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_mbr_msg_add&&subscribe_id=$subscribe_id')\" class=\"button\" />
				</p>
				";
		$content .= "
	    	<table cellpadding=1 cellspacing=2 border=0 width=100%>
				<tr>
			    <td class=box_title width=4>*</td>
				<td class=box_title width=100%>"._('Message')."</td>
				<td class=box_title>"._('Action')."</td>
				</tr>
			";
		$i = 0;
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
					<td class=$td_class>$action</td>	
					</tr>";
		}
		$content .= "</table>";
		echo $content;
		echo "
				<p>
				<input type=button value=\""._('Add Message')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_mbr_msg_add&&subscribe_id=$subscribe_id')\" class=\"button\" />
				</p>
				";
		break;

	case "msg_view" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$msg_id = $_REQUEST['msg_id'];

		$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id = '$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_name = $db_row['subscribe_keyword'];

		$db_query = "SELECT msg FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE msg_id = '$msg_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$message = $db_row['msg'];

		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			    <h2>"._('Message detail')."</h2>
				<form action=index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_msg_send method=post>
				<input type=hidden value=$message name=msg>
				<input type=hidden value=$subscribe_id name=subscribe_id>
				<input type=hidden value=$msg_id name=msg_id>  		
				<table border=0 width=100%> 
				<tr>
					<td width=150>"._('SMS subscribe keyword')."</td><td>:</td><td><b>$subscribe_name</b></td>
				</tr>
				<tr>
					<td>"._('Message')."</td><td>:</td><td>$message</td>
				</tr>
				</table>
				<p>"._('Send this message to all members')."</p>
				<input type=submit value=\""._('Send')."\" class=\"button\" />
				</form>
				";
		echo $content;
		break;

	case "msg_edit" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$msg_id = $_REQUEST['msg_id'];

		$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id = '$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_name = $db_row['subscribe_keyword'];

		$db_query = "SELECT msg FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE msg_id = '$msg_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_mbr_msg = $db_row['msg'];

		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
				<h2>Edit message </h2>
				<table width=100% border=0>
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
			<br><textarea name=edit_mbr_msg rows=5 cols=60>$edit_mbr_msg</textarea>		
			</td>
		    </tr>			
			</table>
			<p>
			<input type=submit class=button value=\""._('Edit')."\">
			</form>
			";
		echo $content;
		break;

	case "msg_edit_yes" :
		$subscribe_id = $_POST['subscribe_id'];
		$msg_id = $_POST['msg_id'];
		$edit_mbr_msg = $_POST['edit_mbr_msg'];
		if ($subscribe_id && $edit_mbr_msg && $msg_id) {
			$db_query = "
						UPDATE " . _DB_PREF_ . "_featureSubscribe_msg set c_timestamp='" . mktime() . "', msg='$edit_mbr_msg'
						WHERE msg_id ='$msg_id'";
			if (@ dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Message has been edited');
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=msg_edit&subscribe_id=$subscribe_id&msg_id=$msg_id");
		exit();
		break;

	case "sms_subscribe_msg_send" :
		$msg_id = $_POST['msg_id'];
		$subscribe_id = $_POST['subscribe_id'];

		$db_query = "SELECT msg FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE msg_id='$msg_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$message = $db_row['msg'];

		$db_query = "SELECT uid FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$c_uid = $db_row['uid'];
		$username = uid2username($c_uid);

		$db_query = "SELECT member_number FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id = '$subscribe_id'";
		$db_result = dba_query($db_query);
		if ($message && $subscribe_id) {
			while ($db_row = dba_fetch_array($db_result)) {

				$sms_to = $db_row['member_number'];

				for ($i = 0; $i < count($sms_to); $i++) {
					//list($ok,$to,$smslog_id,$queue) = sendsms_pv($username, $sms_to, $message);
					//$ok = $ok[0];
					$unicode = 0;
					if (function_exists('mb_detect_encoding')) {
						$encoding = mb_detect_encoding($message, 'auto');
						if ($encoding != 'ASCII') {
							$unicode = 1;
						}
					}
					list($ok, $to, $smslog_id, $queue) = sendsms_pv($username, $sms_to, $message, 'text', $unicode);
					if ($ok[0]) {
						$_SESSION['error_string'] .= _('Your SMS has been delivered to queue')." ("._('to').": ".$sms_to.")<br>";
					} else {
						$_SESSION['error_string'] .= _('Fail to send SMS')." ("._('to').": " . $sms_to . ")<br>";
					}
				}
			}

		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=msg_view&msg_id=$msg_id&subscribe_id=$subscribe_id");
		exit();
		break;

	case "mbr_list" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id = '$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_name = $db_row['subscribe_keyword'];

		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id = '$subscribe_id' ORDER BY member_since DESC";
		$db_result = dba_query($db_query);

		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
		    <h2>"._('Member list for keyword')." $subscribe_name</h2>
			";

		$content .= "
	    	<table cellpadding=1 cellspacing=2 border=0 width=100%>
	    	<tr>
	        	<td class=box_title width=4>*</td>
				<td class=box_title width=50%>"._('Phone number')."</td>
				<td class=box_title width=50%>"._('Member join datetime')."</td>
				<td class=box_title>"._('Action')."</td>
	    	</tr>
			";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";

			$action = "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete this member ?')."','index.php?app=menu&inc=feature_sms_subscribe&op=mbr_del&subscribe_id=$subscribe_id&mbr_id=".$db_row['member_id']."')\">$icon_delete</a>";

			$content .= "
		    		<tr>
					<td class=$td_class>&nbsp;$i.</td>
					<td class=$td_class>".$db_row['member_number']."</td>
					<td class=$td_class>".$db_row['member_since']."</td>
					<td class=$td_class>$action</td>	
					</tr>";
		}
		$content .= "</table>";
		echo $content;
		break;

	case "mbr_del" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$mbr_id = $_REQUEST['mbr_id'];
		if ($mbr_id) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE member_id='$mbr_id'";
			if (@ dba_affected_rows($db_query)) {
				$_SESSION['error_string'] =_('"Member has been deleted');
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=mbr_list&subscribe_id=$subscribe_id");
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
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
		    <h2>"._('Edit SMS subscribe')."</h2>
		    <p>
		    <form action=index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_edit_yes method=post>
		    <input type=hidden name=edit_subscribe_id value=\"$subscribe_id\">
		    <input type=hidden name=edit_subscribe_keyword value=\"$edit_subscribe_keyword\">
			<table width=100% cellpadding=1 cellspacing=2 border=0>
		    	<tr>
				<td width=150>"._('SMS subscribe keyword')."</td><td width=5>:</td><td><b>$edit_subscribe_keyword</b></td>
		    	</tr>
				<tr>
				<td>"._('SMS subscribe reply')."</td><td>:</td><td><input type=text size=50 maxlength=200 name=edit_subscribe_msg value=\"$edit_subscribe_msg\"></td>
		   		</tr>
				<tr>
				<td>"._('SMS unsubscribe reply')."</td><td>:</td><td><input type=text size=50 maxlength=200 name=edit_unsubscribe_msg value=\"$edit_unsubscribe_msg\"></td>
		   		</tr>		
			</table>	    
		    <p><input type=submit class=button value=\""._('Save')."\">
		    </form>
		    <br>
			";
		echo $content;

		$db_query = "SELECT subscribe_enable FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_status = "<b><font color=red>"._('Disabled')."</font></b>";
		if ($db_row['subscribe_enable']) {
			$subscribe_status = "<b><font color=green>"._('Enabled')."</font></b>";
		}
		$content = "
				<h2>"._('Enable or disable this subscribe')."</h2>
				<p>
				<p>"._('Current status').": $subscribe_status
				<p>"._('What do you want to do ?')."
				<p>- <a href=\"index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_status&subscribe_id=$subscribe_id&ps=1\">"._('I want to enable this subscribe')."</a>
				<p>- <a href=\"index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_status&subscribe_id=$subscribe_id&ps=0\">"._('I want to disable this subscribe')."</a>
				<br>
				";
		echo $content;
		break;

	case "sms_subscribe_edit_yes" :
		$edit_subscribe_id = $_POST['edit_subscribe_id'];
		$edit_subscribe_keyword = $_POST['edit_subscribe_keyword'];
		$edit_subscribe_msg = $_POST['edit_subscribe_msg'];
		$edit_unsubscribe_msg = $_POST['edit_unsubscribe_msg'];
		if ($edit_subscribe_id && $edit_subscribe_keyword && $edit_subscribe_msg && $edit_unsubscribe_msg) {
			$db_query = "
			        UPDATE " . _DB_PREF_ . "_featureSubscribe
			        SET c_timestamp='" . mktime() . "',subscribe_keyword='$edit_subscribe_keyword',subscribe_msg='$edit_subscribe_msg',unsubscribe_msg='$edit_unsubscribe_msg'
					WHERE subscribe_id='$edit_subscribe_id' AND uid='$uid'
			    	";
			if (@ dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('SMS subscribe has been saved')." ("._('keyword').": $edit_subscribe_keyword)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_edit&subscribe_id=$edit_subscribe_id");
		exit();
		break;

	case "sms_subscribe_status" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$ps = $_REQUEST['ps'];
		$db_query = "UPDATE " . _DB_PREF_ . "_featureSubscribe SET c_timestamp='" . mktime() . "',subscribe_enable='$ps' WHERE subscribe_id='$subscribe_id'";
		$db_result = @ dba_affected_rows($db_query);
		if ($db_result > 0) {
			$_SESSION['error_string'] = _('SMS subscribe status has been changed');
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_edit&subscribe_id=$subscribe_id");
		exit();
		break;

	case "sms_subscribe_del" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_keyword = $db_row['subscribe_keyword'];
		if ($subscribe_keyword) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_keyword='$subscribe_keyword'";
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

	case "msg_del" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$msg_id = $_REQUEST['msg_id'];
		if ($msg_id) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE msg_id='$msg_id'";
			if (@ dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Message has been deleted');
			}
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=msg_view&subscribe_id=$subscribe_id");
		exit();
		break;

	case "sms_subscribe_add" :
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
				<h2>"._('Add SMS subscribe')."</h2>
		    <p>
		    <form action=index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_add_yes method=post>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
				<tr>
				<td width=150>"._('SMS subscribe keyword')."</td><td width=5>:</td><td><input type=text size=8 maxlength=10 name=add_subscribe_keyword value=\"$add_subscribe_keyword\"></td>
				</tr>
				<tr>
				<td>"._('SMS subscribe reply')."</td><td>:</td><td><input type=text size=50 maxlength=200 name=add_subscribe_msg value=\"$add_subscribe_msg\"></td>
				</tr>
				<tr>
				<td>"._('SMS unsubscribe reply')."</td><td>:</td><td><input type=text size=50 maxlength=200 name=add_unsubscribe_msg value=\"$add_unsubscribe_msg\"></td>
				</tr>
			</table>
				<p><input type=submit class=button value=\""._('Add')."\">
				</form>
			";
		echo $content;
		break;

	case "sms_subscribe_add_yes" :
		$add_subscribe_keyword = strtoupper($_POST['add_subscribe_keyword']);
		$add_subscribe_msg = $_POST['add_subscribe_msg'];
		$add_unsubscribe_msg = $_POST['add_unsubscribe_msg'];
		if ($add_subscribe_keyword && $add_subscribe_msg && $add_unsubscribe_msg) {
			if (checkavailablekeyword($add_subscribe_keyword)) {
				$db_query = "
							INSERT INTO " . _DB_PREF_ . "_featureSubscribe (uid,subscribe_keyword,subscribe_msg,unsubscribe_msg)
							VALUES ('$uid','$add_subscribe_keyword','$add_subscribe_msg','$add_unsubscribe_msg')
							";
				if ($new_uid = @ dba_insert_id($db_query)) {
					$_SESSION['error_string'] = _('SMS subscribe has been added')." ("._('keyword').": $add_subscribe_keyword)";
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

	case "sms_subscribe_mbr_msg_add" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe where subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_name = $db_row['subscribe_keyword'];
		$content .= "
				<h2>Add Message</h2>
				<table width=100% cellpadding=1 cellspacing=2 border=0>	
				<tr>
				<td width=150>"._('SMS subscribe keyword')."</td><td width=5>:</td><td><b>$subscribe_name</b></td>
				</tr>
				<form action=index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_mbr_msg_add_yes method=post>
				<input type=hidden value=$subscribe_id name=subscribe_id>
				
				<tr>
				<td colspan=3>
				"._('Message body').":
				<br><textarea name=add_mbr_message rows=5 cols=60></textarea>		
				</td>
				</tr>					
			</table>
				<p><input type=submit class=button value=\""._('Add')."\">
				</form>
			";
		echo $content;
		break;

	case "sms_subscribe_mbr_msg_add_yes" :
		$subscribe_id = $_POST['subscribe_id'];
		$add_mbr_message = $_POST['add_mbr_message'];
		if ($subscribe_id && $add_mbr_message) {
			$db_query = "
						INSERT INTO " . _DB_PREF_ . "_featureSubscribe_msg (subscribe_id,msg)
						VALUES ('$subscribe_id','$add_mbr_message')
					";
			if ($new_uid = @ dba_insert_id($db_query)) {
				$_SESSION['error_string'] = _('Member message has been added');
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=feature_sms_subscribe&op=sms_subscribe_mbr_msg_add&subscribe_id=$subscribe_id");
		exit();
		break;
}
?>
