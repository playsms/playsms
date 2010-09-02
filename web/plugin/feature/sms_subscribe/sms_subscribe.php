<?php
if (!valid()) {
	forcenoaccess();
};

switch ($op) {
	case "sms_subscribe_list" :
		if ($err) {
			$content = "<p><font color=red>$err</font><p>";
		}
		$content .= "
				<h2>Manage subscribe</h2>
				<p>
				<input type=button value=\"Add SMS subscribe\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_subscribe&op=sms_subscribe_add')\" class=\"button\" />
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
			    <td class=box_title>*</td>
			    <td class=box_title width=30%>Keyword</td>
				<td class=box_title	width=30%>Total Members</td>
			   	<td class=box_title width=20%>User</td>	
			    <td class=box_title width=20%>Status</td>
			    <td class=box_title>Action</td>
			</tr>		
			";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id = '".$db_row['subscribe_id']."'";
			$num_rows = dba_num_rows($db_query);
			if (!$num_rows) {
				$num_rows = "No";
			}
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$owner = uid2username($db_row['uid']);
			$subscribe_status = "<font color=red>Disable</font>";
			if ($db_row['subscribe_enable']) {
				$subscribe_status = "<font color=green>Enable</font>";
			}
			$action = "<a href=menu.php?inc=feature_sms_subscribe&op=mbr_list&subscribe_id=".$db_row['subscribe_id'].">$icon_view</a>&nbsp;";
			$action .= "<a href=menu.php?inc=feature_sms_subscribe&op=msg_list&subscribe_id=".$db_row['subscribe_id'].">$icon_view</a>&nbsp;";
			$action .= "<a href=menu.php?inc=feature_sms_subscribe&op=sms_subscribe_mbr_msg_add&subscribe_id=".$db_row['subscribe_id'].">$icon_edit</a>&nbsp;";
			$action .= "<a href=menu.php?inc=feature_sms_subscribe&op=sms_subscribe_edit&subscribe_id=".$db_row['subscribe_id'].">$icon_edit</a>&nbsp;";
			$action .= "<a href=\"javascript: ConfirmURL('Are you sure you want to delete SMS subscribe keyword `".$db_row['subscribe_keyword']."`?','menu.php?inc=feature_sms_subscribe&op=sms_subscribe_del&subscribe_id=".$db_row['subscribe_id']."')\">$icon_delete</a>";
			$content .= "
					<tr>
						<td class=$td_class>&nbsp;$i.</td>
						<td class=$td_class>".$db_row['subscribe_keyword']."</td>
						<td class=$td_class>$num_rows members</td>
						<td class=$td_class>$owner</td>
						<td class=$td_class>$subscribe_status</td>		
						<td class=$td_class align=center>$action</td>
					</tr>";
		}
		$content .= "</table>";
		echo $content;
		echo "
				<p>
				<input type=button value=\"Add SMS subscribe\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_subscribe&op=sms_subscribe_add')\" class=\"button\" />
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
		if ($err) {
			$content = "<p><font color=red>$err</font><p>";
		}
		$content .= "
		    <h2>Message List for [ $subscribe_name ]</h2>	    
			";
		$content .= "		
				<p>
				<input type=button value=\"Add Message\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_subscribe&op=sms_subscribe_mbr_msg_add&&subscribe_id=$subscribe_id')\" class=\"button\" />
				</p>
				";
		$content .= "
	    	<table cellpadding=1 cellspacing=2 border=0 width=100%>
				<tr>
			    <td class=box_title width=4>*</td>
				<td class=box_title width=100%>Message</td>
				<td class=box_title>Action</td>      
				</tr>
			";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";

			$action = "<a href=menu.php?inc=feature_sms_subscribe&op=msg_view&subscribe_id=".$db_row['subscribe_id']."&msg_id=".$db_row['msg_id'].">$icon_view</a>&nbsp;";
			$action .= "<a href=menu.php?inc=feature_sms_subscribe&op=msg_edit&subscribe_id=$subscribe_id&msg_id=".$db_row['msg_id'].">$icon_edit</a>&nbsp;";
			$action .= "<a href=\"javascript: ConfirmURL('Are you sure you want to delete this message?','menu.php?inc=feature_sms_subscribe&op=msg_del&subscribe_id=$subscribe_id&msg_id=".$db_row['msg_id']."')\">$icon_delete</a>";
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
				<input type=button value=\"Add Message\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_subscribe&op=sms_subscribe_mbr_msg_add&&subscribe_id=$subscribe_id')\" class=\"button\" />
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

		if ($err) {
			$content = "<p><font color=red>$err</font><p>";
		}
		$content .= "
			    <h2>Message Detail</h2>
				<form action=menu.php?inc=feature_sms_subscribe&op=sms_subscribe_msg_send method=post>
				<input type=hidden value=$message name=msg>
				<input type=hidden value=$subscribe_id name=subscribe_id>
				<input type=hidden value=$msg_id name=msg_id>  		
				<table border=0 width=100%> 
				<tr>
					<td width=150>Subscribe Keyword</td><td>:</td><td><b>$subscribe_name</b></td>
				</tr>
				<tr>
					<td>Message</td><td>:</td><td>$message</td>
				</tr>
				</table>
				<p>Send this message to all Members</p>
				<input type=submit value=\"Send\" class=\"button\" />
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

		if ($err) {
			$content = "<p><font color=red>$err</font><p>";
		}
		$content .= "
				<h2>Edit message </h2>
				<table width=100% border=0>
				<form action=menu.php?inc=feature_sms_subscribe&op=msg_edit_yes method=post>
				<input type=hidden value=$subscribe_id name=subscribe_id>
				<input type=hidden value=$msg_id name=msg_id>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
				<tr>
				<td width=150>Subscribe Keyword</td><td width=5>:</td><td><b>$subscribe_name</b></td>
				</tr>		
				<tr>
			<td colspan=3>
			Message Body:
			<br><textarea name=edit_mbr_msg rows=5 cols=60>$edit_mbr_msg</textarea>		
			</td>
		    </tr>			
			</table>
			<p>
			<input type=submit class=button value=Update>
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
				$error_string = "Message has been updated";
			}
		} else {
			$error_string = "You must fill all fields!";
		}
		header("Location: menu.php?inc=feature_sms_subscribe&op=msg_edit&subscribe_id=$subscribe_id&msg_id=$msg_id&err=" . urlencode($error_string));
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
		$sms_sender = username2sender($username);
		$mobile_sender = username2mobile($username);

		$db_query = "SELECT member_number FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id = '$subscribe_id'";
		$db_result = dba_query($db_query);
		if ($message && $subscribe_id) {
			while ($db_row = dba_fetch_array($db_result)) {

				$sms_to = $db_row['member_number'];

				for ($i = 0; $i < count($sms_to); $i++) {
					$send = sendsms($mobile_sender, $sms_sender, $sms_to, $message, $c_uid);
					if ($send) {
						$error_string .= "Your SMS for `" . $sms_to . "` has been delivered to queue<br>";
					} else {
						$error_string .= "Fail to sent SMS to `" . $sms_to . "`<br>";
					}
				}
			}

		} else {
			$error_string = "Fail to send!";
		}
		header("Location: menu.php?inc=feature_sms_subscribe&op=msg_view&msg_id=$msg_id&subscribe_id=$subscribe_id&err=" . urlencode($error_string));
		break;

	case "mbr_list" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id = '$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_name = $db_row['subscribe_keyword'];

		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe_member WHERE subscribe_id = '$subscribe_id' ORDER BY member_since DESC";
		$db_result = dba_query($db_query);

		if ($err) {
			$content = "<p><font color=red>$err</font><p>";
		}
		$content .= "
		    <h2>Member List for [ $subscribe_name ]</h2>	    
			";

		$content .= "
	    	<table cellpadding=1 cellspacing=2 border=0 width=100%>
	    	<tr>
	        	<td class=box_title width=4>*</td>
				<td class=box_title width=50%>Phone Number</td>
				<td class=box_title width=50%>Member From</td>
				<td class=box_title>Action</td>      
	    	</tr>
			";
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";

			$action = "<a href=\"javascript: ConfirmURL('Are you sure you want to delete this member?','menu.php?inc=feature_sms_subscribe&op=mbr_del&subscribe_id=$subscribe_id&mbr_id=".$db_row['member_id']."')\">$icon_delete</a>";

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
				$error_string = "Member has been deleted!";
			}
		}
		header("Location: menu.php?inc=feature_sms_subscribe&op=mbr_list&subscribe_id=$subscribe_id&err=" . urlencode($error_string));
		break;

	case "sms_subscribe_edit" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$edit_subscribe_keyword = $db_row['subscribe_keyword'];
		$edit_subscribe_msg = $db_row['subscribe_msg'];
		$edit_unsubscribe_msg = $db_row['unsubscribe_msg'];
		if ($err) {
			$content = "<p><font color=red>$err</font><p>";
		}
		$content .= "
		    <h2>Edit SMS subscribe</h2>
		    <p>
		    <form action=menu.php?inc=feature_sms_subscribe&op=sms_subscribe_edit_yes method=post>
		    <input type=hidden name=edit_subscribe_id value=\"$subscribe_id\">
		    <input type=hidden name=edit_subscribe_keyword value=\"$edit_subscribe_keyword\">
			<table width=100% cellpadding=1 cellspacing=2 border=0>
		    	<tr>
				<td width=150>SMS subscribe</td><td width=5>:</td><td><b>$edit_subscribe_keyword</b></td>
		    	</tr>
				<tr>
				<td>SMS subscribe reply</td><td>:</td><td><input type=text size=50 maxlength=200 name=edit_subscribe_msg value=\"$edit_subscribe_msg\"></td>
		   		</tr>
				<tr>
				<td>SMS unsubscribe reply</td><td>:</td><td><input type=text size=50 maxlength=200 name=edit_unsubscribe_msg value=\"$edit_unsubscribe_msg\"></td>
		   		</tr>		
			</table>	    
		    <p><input type=submit class=button value=\"Save Subscribe\">
		    </form>
		    <br>
			";
		echo $content;

		$db_query = "SELECT subscribe_enable FROM " . _DB_PREF_ . "_featureSubscribe WHERE subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_status = "<font color=red><b>Disable</b></font>";
		if ($db_row['subscribe_enable']) {
			$subscribe_status = "<font color=green><b>Enable</b></font>";
		}
		$content = "
				<h2>Enable or disable this subscribe</h2>
				<p>
				<p>Current status: $subscribe_status
				<p>What do you want to do ?
				<p>- <a href=\"menu.php?inc=feature_sms_subscribe&op=sms_subscribe_status&subscribe_id=$subscribe_id&ps=1\">I want to <b>enable</b> this subscribe</a>
				<p>- <a href=\"menu.php?inc=feature_sms_subscribe&op=sms_subscribe_status&subscribe_id=$subscribe_id&ps=0\">I want to <b>disable</b> this subscribe</a>
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
				$error_string = "SMS subscribe with keyword `$edit_subscribe_keyword` has been saved";
			}
		} else {
			$error_string = "You must fill all fields!";
		}
		header("Location: menu.php?inc=feature_sms_subscribe&op=sms_subscribe_edit&subscribe_id=$edit_subscribe_id&err=" . urlencode($error_string));
		break;

	case "sms_subscribe_status" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$ps = $_REQUEST['ps'];
		$db_query = "UPDATE " . _DB_PREF_ . "_featureSubscribe SET c_timestamp='" . mktime() . "',subscribe_enable='$ps' WHERE subscribe_id='$subscribe_id'";
		$db_result = @ dba_affected_rows($db_query);
		if ($db_result > 0) {
			$error_string = "This subscribe status has been changed!";
		}
		header("Location: menu.php?inc=feature_sms_subscribe&op=sms_subscribe_edit&subscribe_id=$subscribe_id&err=" . urlencode($error_string));
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
				if ($del_msg && $del_member) {
					$error_string = "SMS subscribe `$subscribe_keyword` with all its messages and members has been deleted!";
				}
			}
		}
		header("Location: menu.php?inc=feature_sms_subscribe&op=sms_subscribe_list&err=" . urlencode($error_string));
		break;

	case "msg_del" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		$msg_id = $_REQUEST['msg_id'];
		if ($msg_id) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSubscribe_msg WHERE msg_id='$msg_id'";
			if (@ dba_affected_rows($db_query)) {
				$error_string = "Message has been deleted!";
			}
		}
		header("Location: menu.php?inc=feature_sms_subscribe&op=msg_view&subscribe_id=$subscribe_id&err=" . urlencode($error_string));
		break;

	case "sms_subscribe_add" :
		if ($err) {
			$content = "<p><font color=red>$err</font><p>";
		}
		$content .= "
				<h2>Add SMS subscribe</h2>
		    <p>
		    <form action=menu.php?inc=feature_sms_subscribe&op=sms_subscribe_add_yes method=post>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
				<tr>
				<td width=150>SMS subscribe keyword</td><td width=5>:</td><td><input type=text size=8 maxlength=10 name=add_subscribe_keyword value=\"$add_subscribe_keyword\"></td>
				</tr>
				<tr>
				<td>SMS subscribe reply</td><td>:</td><td><input type=text size=50 maxlength=200 name=add_subscribe_msg value=\"$add_subscribe_msg\"></td>
				</tr>
				<tr>
				<td>SMS unsubscribe reply</td><td>:</td><td><input type=text size=50 maxlength=200 name=add_unsubscribe_msg value=\"$add_unsubscribe_msg\"></td>
				</tr>
			</table>
				<p><input type=submit class=button value=Add>
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
					$error_string = "SMS subscribe with keyword `$add_subscribe_keyword` has been added";
				}
			} else {
				$error_string = "SMS keyword `$add_subscribe_keyword` already exists, reserved or use by other feature!";
			}
		} else {
			$error_string = "You must fill all fields!";
		}
		header("Location: menu.php?inc=feature_sms_subscribe&op=sms_subscribe_add&err=" . urlencode($error_string));
		break;

	case "sms_subscribe_mbr_msg_add" :
		$subscribe_id = $_REQUEST['subscribe_id'];
		if ($err) {
			$content = "<p><font color=red>$err</font><p>";
		}
		$db_query = "SELECT subscribe_keyword FROM " . _DB_PREF_ . "_featureSubscribe where subscribe_id='$subscribe_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$subscribe_name = $db_row['subscribe_keyword'];
		$content .= "
				<h2>Add Message</h2>
				<table width=100% cellpadding=1 cellspacing=2 border=0>	
				<tr>
				<td width=150>Subscribe name</td><td width=5>:</td><td><b>$subscribe_name</b></td>
				</tr>
				<form action=menu.php?inc=feature_sms_subscribe&op=sms_subscribe_mbr_msg_add_yes method=post>
				<input type=hidden value=$subscribe_id name=subscribe_id>
				
				<tr>
				<td colspan=3>
				Message Body:
				<br><textarea name=add_mbr_message rows=5 cols=60></textarea>		
				</td>
				</tr>					
			</table>
				<p><input type=submit class=button value=Add>
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
				$error_string = "Member message has been added";
			}
		} else {
			$error_string = "You must fill all fields!";
		}
		header("Location: menu.php?inc=feature_sms_subscribe&op=sms_subscribe_mbr_msg_add&subscribe_id=$subscribe_id&err=" . urlencode($error_string));
		break;
}
?>
