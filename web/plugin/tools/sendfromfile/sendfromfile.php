<?php if(!(defined('_SECURE_'))){die('Intruder alert');}; ?>
<?php
if(!valid()){forcenoaccess();};

if (($route = $_REQUEST['route']) && ($route == 'user')) {
	include $core_config['apps_path']['plug'].'/tools/sendfromfile/sendfromfile_user.php';
	exit();
}

switch ($op) {
	case 'list':
		$content = '<h2>'._('Send from file').'</h2><p />';
		// error messages
		$error_content = '';
		if ($errid) {
			$err = logger_get_error_string($errid);
		}
		if ($err) {
			$error_content = "<div class=error_string>$err</div>";
		}
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= "
			<form action=\"index.php?app=menu&inc=tools_sendfromfile&op=upload_confirm\" enctype=\"multipart/form-data\" method=\"post\">
		    		"._('Please select CSV file')." ("._('format : destination number, message, username').")<br>
		    		<p><input type=\"file\" name=\"fncsv\">
		    		<p><input type=\"submit\" value=\""._('Upload file')."\" class=\"button\">
			</form>";
		echo $content;
		break;
	case 'upload_confirm':
		$filename = $_FILES['fncsv']['name'];		
		$fn = $_FILES['fncsv']['tmp_name'];		
		$fs = $_FILES['fncsv']['size'];
		$row = 0;		
		$valid = 0;
		$invalid = 0;
		if (($fs == filesize($fn)) && file_exists($fn)) {
			if (($fd = fopen($fn, 'r')) !== FALSE) {
				$sid = md5($fn);
				while (($data = fgetcsv($fd, $fs, ',')) !== FALSE) {
					$row++;
					$sms_to = $data[0];
					$sms_msg = $data[1];
					$sms_username = $data[2];
					if ($sms_to && $sms_msg) {
						if ($uid = username2uid($sms_username)) {
							$db_query = "INSERT INTO "._DB_PREF_."_toolsSendfromfile (uid,sid,sms_datetime,sms_to,sms_msg,sms_username) ";
							$db_query .= "VALUES ('$uid','$sid','".$core_config['datetime']['now']."','$sms_to','$sms_msg','$sms_username')";
							if ($db_result = dba_insert_id($db_query)) {
								$valid++;
								$item_valid[$valid-1] = $data;
							}
						} else {
							$invalid++;
							$item_invalid[$invalid-1] = $data;
						}
					}
				}
			}
		} else {
                        $error_string = _('Invalid CSV file');
                        $errid = logger_set_error_string($error_string);
                        header("Location: index.php?app=menu&inc=tools_sendfromfile&op=list&errid=".$errid);
                        break;
                }
		$content = '<h2>'._('Send from file').'</h2><p />';
		$content .= '<h3>'._('Confirmation').'</h3><p />';
		$content .= _('Uploaded file').': '.$filename.'<p />';
		$content .= '<p /><br />';
		if ($valid) {
			$content .= _('Found valid entries in uploaded file').' ('._('valid entries').': '.$valid.')<p />';
			$content .= '<h3>'._('Valid entries').'</h3><p />';
			$content .= "
				<table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
					<tr>
						<td class=box_title width=4>*</td>
						<td class=box_title width='150'>Destination number</td>
						<td class=box_title>Message</td>
						<td class=box_title width='100' align=right>Username</td>
					</tr>
			";
			for ($i=0;$i<count($item_valid);$i++) {
				$tr_class = ($i % 2) ? "box_text_odd" : "box_text_even";
				$content .= "
					<tr class='".$tr_class."'>
						<td>".($i+1).".</td>
						<td>".$item_valid[$i][0]."</td>
						<td>".$item_valid[$i][1]."</td>
						<td>".$item_valid[$i][2]."</td>
					</tr>
				";
			}
			$content .= "</table>";
		}
		$content .= '<p /><br />';
		if ($invalid) {
			$content .= _('Found invalid entries in uploaded file').' ('._('invalid entries').': '.$invalid.')<p />';
			$content .= '<h3>'._('Invalid entries').'</h3><p />';
			$content .= "
				<table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
					<tr>
						<td class=box_title width=4>*</td>
						<td class=box_title width='150'>Destination number</td>
						<td class=box_title>Message</td>
						<td class=box_title width='100' align=right>Username</td>
					</tr>
			";
			for ($i=0;$i<count($item_invalid);$i++) {
				$tr_class = ($i % 2) ? "box_text_odd" : "box_text_even";
				$content .= "
					<tr class='".$tr_class."'>
						<td>".($i+1).".</td>
						<td>".$item_invalid[$i][0]."</td>
						<td>".$item_invalid[$i][1]."</td>
						<td>".$item_invalid[$i][2]."</td>
					</tr>
				";
			}
			$content .= "</table>";
		}
		$content .= '<p /><br />';
		$content .= '<h3>'._('Your choice').': </h3><p />';
		$content .= "<form action=\"index.php?app=menu&inc=tools_sendfromfile&op=upload_cancel\" method=\"post\">";
		$content .= "<input type=hidden name=sid value='".$sid."'>";
		$content .= "<input type=\"submit\" value=\""._('Cancel send from file')."\" class=\"button\"></p>";
		$content .= "</form>";
		$content .= "<form action=\"index.php?app=menu&inc=tools_sendfromfile&op=upload_process\" method=\"post\">";
		$content .= "<input type=hidden name=sid value='".$sid."'>";
		$content .= "<input type=\"submit\" value=\""._('Send SMS to valid entries')."\" class=\"button\"></p>";
		$content .= "</form>";
		echo $content;
		break;
	case 'upload_cancel':
		if ($sid = $_REQUEST['sid']) {
			$db_query = "DELETE FROM "._DB_PREF_."_toolsSendfromfile WHERE sid='$sid'";
			if ($db_result = dba_affected_rows($db_query)) {
				$error_string = _('Send from file has been cancelled');
			} else {
				$error_string = _('Fail to remove cancelled entries from database');
			}
		} else {
			$error_string = _('Invalid session ID');
		}
		$errid = logger_set_error_string($error_string);
		header("Location: index.php?app=menu&inc=tools_sendfromfile&op=list&errid=".$errid);
		break;
	case 'upload_process':
		if ($sid = $_REQUEST['sid']) {
			$db_query = "SELECT * FROM "._DB_PREF_."_toolsSendfromfile WHERE sid='$sid'";
			$db_result = dba_query($db_query);
			while ($db_row = dba_fetch_array($db_result)) {
				$c_sms_to = $db_row['sms_to'];
				$c_sms_msg = $db_row['sms_msg'];
				$c_username = $db_row['sms_username'];
				if ($c_sms_to && $c_sms_msg && $c_username) {
					$type = 'text';
					$unicode = '0';
					list($ok,$to,$smslog_id,$queue) = sendsms_pv($c_username,$c_sms_to,$c_sms_msg,$type,$unicode);
				}
			}
			$db_query = "DELETE FROM "._DB_PREF_."_toolsSendfromfile WHERE sid='$sid'";
			$db_result = dba_affected_rows($db_query);
			$error_string = _('SMS has been set to destination in uploaded file');
		} else {
			$error_string = _('Invalid session ID');
		}
		$errid = logger_set_error_string($error_string);
		header("Location: index.php?app=menu&inc=tools_sendfromfile&op=list&errid=".$errid);
		break;
}

?>