<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

if (($route = $_REQUEST['route']) && ($route == 'user')) {
	include $core_config['apps_path']['plug'].'/tools/sendfromfile/sendfromfile_user.php';
	exit();
}

switch ($op) {
	case 'list':
		$content = '<h2>'._('Send from file').'</h2><p />';
		if (isadmin()) {
			$content .= "
				<form action=\"index.php?app=menu&inc=tools_sendfromfile&op=upload_confirm\" enctype=\"multipart/form-data\" method=\"post\">
					"._('Please select CSV file')." ("._('format : destination number, message, username').")<br>
					<p><input type=\"file\" name=\"fncsv\">
					<p><input type=\"submit\" value=\""._('Upload file')."\" class=\"button\">
				</form>";
		} else {
			$content .= "
				<form action=\"index.php?app=menu&inc=tools_sendfromfile&op=upload_confirm\" enctype=\"multipart/form-data\" method=\"post\">
					"._('Please select CSV file')." ("._('format : destination number, message').")<br>
					<p><input type=\"file\" name=\"fncsv\">
					<p><input type=\"submit\" value=\""._('Upload file')."\" class=\"button\">
				</form>";
		}
		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
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
				$sid = uniqid('SID', true);
				$continue = true;
				while ((($data = fgetcsv($fd, $fs, ',')) !== FALSE) && $continue) {
					$row++;
					$sms_to = trim($data[0]);
					$sms_msg = trim($data[1]);
					if (isadmin()) {
						$sms_username = trim($data[2]);
						$uid = username2uid($sms_username);
					} else {
						$sms_username = $core_config['user']['username'];
						$uid = $core_config['user']['uid'];
						$data[2] = $sms_username;
					}
					if ($sms_to && $sms_msg && $uid) {
						$db_query = "INSERT INTO "._DB_PREF_."_toolsSendfromfile (uid,sid,sms_datetime,sms_to,sms_msg,sms_username) ";
						$db_query .= "VALUES ('$uid','$sid','".core_get_datetime()."','$sms_to','".addslashes($sms_msg)."','$sms_username')";
						if ($db_result = dba_insert_id($db_query)) {
							$valid++;
							$item_valid[$valid-1] = $data;
						} else {
							$invalid++;
							$item_invalid[$invalid-1] = $data;
						}
					} else if ($sms_to || $sms_msg) {
						$invalid++;
						$item_invalid[$invalid-1] = $data;
					}
					$num_of_rows = $valid + $invalid;
					if ($num_of_rows >= $sendfromfile_row_limit) {
						$continue = false;
					}
				}
			}
		} else {
			$_SESSION['error_string'] = _('Invalid CSV file');
			header("Location: index.php?app=menu&inc=tools_sendfromfile&op=list");
			exit();
			break;
		}
		$content = '<h2>'._('Send from file').'</h2><p />';
		$content .= '<h3>'._('Confirmation').'</h3><p />';
		$content .= _('Uploaded file').': '.$filename.'<p />';
		if ($valid) {
			$content .= _('Found valid entries in uploaded file').' ('._('valid entries').': '.$valid.' '._('of').' '.$num_of_rows.')<p />';
			$content .= '<h3>'._('Valid entries').'</h3><p />';
			$content .= "
				<table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
				<thead><tr>
					<th width=4>*</th>
					<th width='20%'>"._('Destination number')."</th>
					<th width='60%'>"._('Message')."</th>
					<th width='20%'>"._('Username')."</th>
				</tr></thead>
				<tbody>";
			$j = 0;
			for ($i=0;$i<count($item_valid);$i++) {
				if ($item_valid[$i][0] && $item_valid[$i][1] && $item_valid[$i][2]) {
					$j++;
					$tr_class = ($j % 2) ? "box_text_odd" : "box_text_even";
					$content .= "
						<tr class='".$tr_class."'>
							<td>&nbsp;".$j.".</td>
							<td align='center'>".$item_valid[$i][0]."</td>
							<td>".$item_valid[$i][1]."</td>
							<td align='center'>".$item_valid[$i][2]."</td>
						</tr>";
				}
			}
			$content .= "</tbody></table>";
		}
		if ($invalid) {
			$content .= '<p /><br />';
			$content .= _('Found invalid entries in uploaded file').' ('._('invalid entries').': '.$invalid.' '._('of').' '.$num_of_rows.')<p />';
			$content .= '<h3>'._('Invalid entries').'</h3><p />';
			$content .= "
				<table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
				<thead><tr>
					<th width=4>*</th>
					<th width='20%'>"._('Destination number')."</th>
					<th width='60%'>"._('Message')."</th>
					<th width='20%'>"._('Username')."</th>
				</tr></thead>";
			$j = 0;
			for ($i=0;$i<count($item_invalid);$i++) {
				if ($item_invalid[$i][0] || $item_invalid[$i][1] || $item_invalid[$i][2]) {
					$j++;
					$tr_class = ($j % 2) ? "box_text_odd" : "box_text_even";
					$content .= "
						<tr class='".$tr_class."'>
							<td>&nbsp;".$j.".</td>
							<td align='center'>".$item_invalid[$i][0]."</td>
							<td>".$item_invalid[$i][1]."</td>
							<td align='center'>".$item_invalid[$i][2]."</td>
						</tr>";
				}
			}
			$content .= "</tbody></table>";
		}
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
				$_SESSION['error_string'] = _('Send from file has been cancelled');
			} else {
				$_SESSION['error_string'] = _('Fail to remove cancelled entries from database');
			}
		} else {
			$_SESSION['error_string'] = _('Invalid session ID');
		}
		header("Location: index.php?app=menu&inc=tools_sendfromfile&op=list");
		exit();
		break;
	case 'upload_process':
		set_time_limit(600);
		if ($sid = $_REQUEST['sid']) {
			$db_query = "SELECT * FROM "._DB_PREF_."_toolsSendfromfile WHERE sid='$sid'";
			$db_result = dba_query($db_query);
			while ($db_row = dba_fetch_array($db_result)) {
				$c_sms_to = $db_row['sms_to'];
				$c_sms_msg = addslashes($db_row['sms_msg']);
				$c_username = $db_row['sms_username'];
				if ($c_sms_to && $c_sms_msg && $c_username) {
					$type = 'text';
					$unicode = '0';
					list($ok,$to,$smslog_id,$queue) = sendsms($c_username,$c_sms_to,$c_sms_msg,$type,$unicode);
				}
			}
			$db_query = "DELETE FROM "._DB_PREF_."_toolsSendfromfile WHERE sid='$sid'";
			$db_result = dba_affected_rows($db_query);
			$_SESSION['error_string'] = _('SMS has been sent to valid numbers in uploaded file');
		} else {
			$_SESSION['error_string'] = _('Invalid session ID');
		}
		header("Location: index.php?app=menu&inc=tools_sendfromfile&op=list");
		exit();
		break;
}

?>