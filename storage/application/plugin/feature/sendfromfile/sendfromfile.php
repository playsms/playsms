<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

if (!auth_isvalid()) {
	auth_block();
}

// fixme anton - perhaps we just need to check $_SESSION['status'] == 2
$sendfromfile_isadmin = FALSE;
if (auth_isadmin()) {
	$sendfromfile_isadmin = TRUE;
}

switch (_OP_) {
	case 'list':
		$content = _dialog() . '<h2 class=page-header-title>' . _('Send from file') . '</h2><p />';
		if ($sendfromfile_isadmin) {
			$info_format = _('destination number, message, username');
		} else {
			$info_format = _('destination number, message');
		}
		$content .= "
			<table class=ps_table>
				<tbody>
					<tr>
						<td>
							<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_confirm\" enctype=\"multipart/form-data\" method=\"post\">
							" . _CSRF_FORM_ . "
							<p>" . _('Please select CSV file') . "</p>
							<p><input type=\"file\" name=\"fncsv\"></p>
							<p class=help-block>" . _('CSV file format') . " : " . $info_format . "</p>
							<p><input type=\"submit\" value=\"" . _('Upload file') . "\" class=\"button\"></p>
							</form>
						</td>
					</tr>
				</tbody>
			</table>";
		_p($content);
		break;
	case 'upload_confirm':
	
		// fixme anton - https://www.exploit-database.net/?id=92843
		$filename = core_sanitize_filename($_FILES['fncsv']['name']);		
		if ($filename == $_FILES['fncsv']['name']) {
			$continue = TRUE;
		} else {
			$continue = FALSE;
		}
		
		$fn = $_FILES['fncsv']['tmp_name'];
		$fs = (int) $_FILES['fncsv']['size'];
		$all_numbers = array();
		$valid = 0;
		$invalid = 0;
		$item_valid = array();
		$item_invalid = array();
		
		if ($continue && ($fs == filesize($fn)) && file_exists($fn)) {
			if (($fd = fopen($fn, 'r')) !== FALSE) {
				$sid = md5(uniqid('SID', true));
				$continue = true;
				while ((($data = fgetcsv($fd, $fs, ',')) !== FALSE) && $continue) {
					$dup = false;
					$data[0] = core_sanitize_sender($data[0]);
					$data[1] = core_sanitize_string($data[1]);
					
					$skip = FALSE;
					if ($sendfromfile_isadmin) {
						if ($sms_username = core_sanitize_username($data[2])) {
							// if supplied username is clean  and it exists
							if (($sms_username == $data[2]) && ($uid = user_username2uid($sms_username))) {
								// user found
								$data[2] = $sms_username;
							} else {
								// username filled but user not found
								$skip = TRUE;
							}
						} else {
							// username not defined or empty
							$sms_username = $user_config['username'];
							$uid = $user_config['uid'];
							$data[2] = $sms_username;
						}
					} else {
						// not an admin
						$sms_username = $user_config['username'];
						$uid = $user_config['uid'];
						$data[2] = $sms_username;
					}
					
					// check dups
					$dup = ( in_array($data[0], $all_numbers) ? TRUE : FALSE );
					
					if ($data[0] && $data[1] && $uid && !$skip && !$dup) {
						$all_numbers[] = $data[0];
						$db_query = "INSERT INTO " . _DB_PREF_ . "_featureSendfromfile (uid,sid,sms_datetime,sms_to,sms_msg,sms_username) ";
						$db_query .= "VALUES ('$uid','$sid','" . core_get_datetime() . "','$data[0]','" . addslashes($data[1]) . "','$sms_username')";
						if ($db_result = dba_insert_id($db_query)) {
							$item_valid[$valid] = $data;
							$valid++;
						} else {
							$item_invalid[$invalid] = $data;
							$invalid++;
						}
					} else if (($data[0] || $data[1]) && !$dup) {
						$item_invalid[$invalid] = $data;
						$invalid++;
					}
					$num_of_rows = $valid + $invalid;
					if ($num_of_rows >= $sendfromfile_row_limit) {
						$continue = false;
					}
				}
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('Invalid CSV file');
			header("Location: " . _u('index.php?app=main&inc=feature_sendfromfile&op=list'));
			exit();
			break;
		}
		
		$content = '<h2 class=page-header-title>' . _('Send from file') . '</h2>';
		$content .= '<p class=lead>' . _('Confirmation') . '</p>';	
		$content .= '<p>' . _('Uploaded file') . ': ' . $filename . '</p>';
		$content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_cancel\" method=\"post\">";
		$content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sid . "'>";
		$content .= "<input type=\"submit\" value=\"" . _('Cancel send from file') . "\" class=\"button\"></p>";
		$content .= "</form>";
		
		if ($valid) {
			$content .= _('Found valid entries in uploaded file') . ' (' . _('valid entries') . ': ' . $valid . ' ' . _('of') . ' ' . $num_of_rows . ')<p />';
			$content .= '<p class=lead><span class="playsms-icon fa fas fa-thumbs-up" alt="' . _('Valid entries') . '"></span>' . _('Valid entries') . '</p>';
			$content .= "
				<div class=table-responsive>
					<table id=playsms-table-list class=playsms-table-list>
					<thead>";
			if ($sendfromfile_isadmin) {
				$content .= "
					<tr>
						<th width=20%>" . _('Destination number') . "</th>
						<th width=60%>" . _('Message') . "</th>
						<th width=20%>" . _('Username') . "</th>
					</tr>";
			} else {
				$content .= "
					<tr>
						<th width=20%>" . _('Destination number') . "</th>
						<th width=80%>" . _('Message') . "</th>
					</tr>";
			}
			$content .= "
					</thead>
					<tbody>";
			$j = 0;
			foreach ($item_valid as $item) {
				if ($sendfromfile_isadmin) {
					$content .= "
						<tr>
							<td>" . $item[0] . "</td>
							<td>" . $item[1] . "</td>
							<td>" . $item[2] . "</td>
						</tr>";
				} else {
					$content .= "
						<tr>
							<td>" . $item[0] . "</td>
							<td>" . $item[1] . "</td>
						</tr>";
				}
			}
			$content .= "
					</tbody>
					<tfoot>
						<tr>
							<td id='playsms-table-pager' class='playsms-table-pager' colspan=3>
							<div class='form-inline pull-right'>
								<div class='btn-group btn-group-sm mx-1' role='group'>
									<button type='button' class='btn btn-secondary first'>&#8676;</button>
									<button type='button' class='btn btn-secondary prev'>&larr;</button>
									<span class='pagedisplay'></span>
								</div>
								<div class='btn-group btn-group-sm mx-1' role='group'>
									<button type='button' class='btn btn-secondary next' title='next'>&rarr;</button>
									<button type='button' class='btn btn-secondary last' title='last'>&#8677;</button>
								</div>
								<select class='form-control-sm custom-select px-1 pagesize' title='" . _('Select page size') . "'>
									<option selected='selected' value='10'>10</option>
									<option value='20'>20</option>
									<option value='50'>50</option>
									<option value='100'>100</option>
								</select>
							</div>
							</td>
						</tr>
					</tfoot>
					</table>
				</div>
				<script type='text/javascript'>
					$(document).ready(function() { 
						$('#playsms-table-list').tablesorterPager({container: $('#playsms-table-pager')}); 
					});
				</script>";
		}
		
		if ($invalid) {
			$content .= '<p /><br />';
			$content .= _('Found invalid entries in uploaded file') . ' (' . _('invalid entries') . ': ' . $invalid . ' ' . _('of') . ' ' . $num_of_rows . ')<p />';
			$content .= '<p class=lead><span class="playsms-icon fa fas fa-thumbs-down" alt="' . _('Invalid entries') . '"></span>' . _('Invalid entries') . '</p>';
			$content .= "
				<div class=table-responsive>
					<table id=table-invalid-entries class=playsms-table-list>
					<thead>";
			if ($sendfromfile_isadmin) {
				$content .= "
					<tr>
						<th width=20%>" . _('Destination number') . "</th>
						<th width=60%>" . _('Message') . "</th>
						<th width=20%>" . _('Username') . "</th>
					</tr>";
			} else {
				$content .= "
					<tr>
						<th width=20%>" . _('Destination number') . "</th>
						<th width=80%>" . _('Message') . "</th>
					</tr>";
			}
			$content .= "
					</thead>
					<tbody>";
			$j = 0;
			foreach ($item_invalid as $item) {
				if ($sendfromfile_isadmin) {
					$content .= "
						<tr>
							<td>" . $item[0] . "</td>
							<td>" . $item[1] . "</td>
							<td>" . $item[2] . "</td>
						</tr>";
				} else {
					$content .= "
						<tr>
							<td>" . $item[0] . "</td>
							<td>" . $item[1] . "</td>
						</tr>";
				}
			}
			$content .= "
					</tbody>
					<tfoot>
						<tr>
							<td id='table-invalid-pager' colspan=3>
							<div class='form-inline pull-right'>
								<div class='btn-group btn-group-sm mx-1' role='group'>
									<button type='button' class='btn btn-secondary first'>&#8676;</button>
									<button type='button' class='btn btn-secondary prev'>&larr;</button>
									<span class='pagedisplay'></span>
								</div>
								<div class='btn-group btn-group-sm mx-1' role='group'>
									<button type='button' class='btn btn-secondary next' title='next'>&rarr;</button>
									<button type='button' class='btn btn-secondary last' title='last'>&#8677;</button>
								</div>
								<select class='form-control-sm custom-select px-1 pagesize' title='" . _('Select page size') . "'>
									<option selected='selected' value='10'>10</option>
									<option value='20'>20</option>
									<option value='50'>50</option>
									<option value='100'>100</option>
								</select>
							</div>
							</td>
						</tr>
					</tfoot>
					</table>
				</div>
				<script type='text/javascript'>
					$(document).ready(function() { 
						$('#table-invalid-entries').tablesorterPager({container: $('#table-invalid-pager')}); 
					});
				</script>";
		}
		
		$content .= '<p class=lead>' . _('Your choice') . '</p>';
		$content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_cancel\" method=\"post\">";
		$content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sid . "'>";
		$content .= "<input type=\"submit\" value=\"" . _('Cancel send from file') . "\" class=\"button\"></p>";
		$content .= "</form>";
		$content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_process\" method=\"post\">";
		$content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sid . "'>";
		$content .= "<input type=\"submit\" value=\"" . _('Send SMS to valid entries') . "\" class=\"button\"></p>";
		$content .= "</form>";
		
		_p($content);
		break;
	
	case 'upload_cancel':
		if ($sid = $_REQUEST['sid']) {
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSendfromfile WHERE sid='$sid'";
			dba_query($db_query);
			$_SESSION['dialog']['danger'][] = _('Send from file has been cancelled');
		} else {
			$_SESSION['dialog']['danger'][] = _('Invalid session ID');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sendfromfile&op=list'));
		exit();
		break;
	
	case 'upload_process':
		@set_time_limit(0);
		if ($sid = $_REQUEST['sid']) {
			$data = array();
			$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSendfromfile WHERE sid='$sid'";
			$db_result = dba_query($db_query);
			while ($db_row = dba_fetch_array($db_result)) {
				$c_sms_to = $db_row['sms_to'];
				$c_sms_msg = $db_row['sms_msg'];
				$c_username = $db_row['sms_username'];
				$c_hash = md5($c_username . $c_sms_msg);
				if ($c_sms_to && $c_username && $c_sms_msg) {
					$data[$c_hash]['sms_to'][] = $c_sms_to;
					$data[$c_hash]['message'] = $c_sms_msg;
					$data[$c_hash]['username'] = $c_username;
				}
			}
			foreach ($data as $hash => $item) {
				$sms_to = $item['sms_to'];
				$message = $item['message'];
				$username = $item['username'];
				_log('hash:' . $hash . ' u:' . $username . ' m:[' . $message . '] to_count:' . count($sms_to), 3, 'sendfromfile upload_process');
				if ($username && $message && count($sms_to)) {
					$type = 'text';
					$unicode = core_detect_unicode($message);
					$message = addslashes($message);
					list($ok, $to, $smslog_id, $queue) = sendsms_helper($username, $sms_to, $message, $type, $unicode);
				}
			}
			$db_query = "DELETE FROM " . _DB_PREF_ . "_featureSendfromfile WHERE sid='$sid'";
			dba_query($db_query);
			$_SESSION['dialog']['info'][] = _('SMS has been sent to valid numbers in uploaded file');
		} else {
			$_SESSION['dialog']['danger'][] = _('Invalid session ID');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sendfromfile&op=list'));
		exit();
		break;
}
