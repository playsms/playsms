<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isvalid()){auth_block();};

$uid = $user_config['uid'];

switch (_OP_) {
	case "list":
		$content .= "
			<h2>"._('Phonebook')."</h2>
			<h3>"._('Import')."</h3>
			<table class=ps_table>
				<tbody>
					<tr>
						<td>
							<form action=\"index.php?app=main&inc=feature_phonebook&route=import&op=import\" enctype=\"multipart/form-data\" method=POST>
							"._CSRF_FORM_."
							<p>"._('Please select CSV file for phonebook entries')."</p>
							<p><input type=\"file\" name=\"fnpb\"></p>
							<p class=text-info>"._('format')." : "._('Name').", "._('Mobile').", "._('Email').", "._('Group code')."</p>
							<p><input type=\"submit\" value=\""._('Import')."\" class=\"button\"></p>
							</form>
						</td>
					</tr>
				</tbody>
			</table>
			"._back('index.php?app=main&inc=feature_phonebook&op=phonebook_list');
		if ($err = $_SESSION['error_string']) {
			_p("<div class=error_string>$err</div>");
		}
		_p($content);
		break;
	case "import":
		$fnpb = $_FILES['fnpb'];
		$fnpb_tmpname = $_FILES['fnpb']['tmp_name'];
		$content = "
			<h2>"._('Phonebook')."</h2>
			<h3>"._('Import confirmation')."</h3>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width=\"5%\">*</th>
				<th width=\"25%\">"._('Name')."</th>
				<th width=\"25%\">"._('Mobile')."</th>
				<th width=\"30%\">"._('Email')."</th>
				<th width=\"15%\">"._('Group code')."</th>
			</tr></thead><tbody>";
		if (file_exists($fnpb_tmpname)) {
			$session_import = 'phonebook_'._PID_;
			unset($_SESSION['tmp'][$session_import]);
			ini_set('auto_detect_line_endings', TRUE);
			if (($fp = fopen($fnpb_tmpname, "r")) !== FALSE) {
				$i = 0;
				while ($c_contact = fgetcsv($fp, 1000, ',', '"', '\\')) {
					if ($i > $phonebook_row_limit) {
						break;
					}
					if ($i > 0) {
						$contacts[$i] = $c_contact;
					}
					$i++;
				}
				$i = 0;
				foreach ($contacts as $contact) {
					$c_gid = phonebook_groupcode2id($uid, $contact[3]);
					if ($contact[0] && $contact[1] && $c_gid) {
						$i++;
						$content .= "
							<tr>
							<td>$i.</td>
							<td>$contact[0]</td>
							<td>$contact[1]</td>
							<td>$contact[2]</td>
							<td>$contact[3]</td>
							</tr>";
						$k = $i - 1;
						$_SESSION['tmp'][$session_import][$k] = $contact;
					}
				}
			}
			ini_set('auto_detect_line_endings', FALSE);
			$content .= "
				</tbody></table>
				</div>
				<p>"._('Import above phonebook entries ?')."</p>
				<form action=\"index.php?app=main&inc=feature_phonebook&route=import&op=import_yes\" method=POST>
				"._CSRF_FORM_."
				<input type=\"hidden\" name=\"number_of_row\" value=\"$j\">
				<input type=\"hidden\" name=\"session_import\" value=\"".$session_import."\">
				<p><input type=\"submit\" class=\"button\" value=\""._('Import')."\"></p>
				</form>
				"._back('index.php?app=main&inc=feature_phonebook&route=import&op=list');
			_p($content);
		} else {
			$_SESSION['error_string'] = _('Fail to upload CSV file for phonebook');
			header("Location: "._u('index.php?app=main&inc=feature_phonebook&route=import&op=list'));
			exit();
		}
		break;
	case "import_yes":
		set_time_limit(600);
		$num = $_POST['number_of_row'];
		$session_import = $_POST['session_import'];
		$data = $_SESSION['tmp'][$session_import];
		//$i = 0;
		foreach ($data as $d) {
			$name = trim($d[0]);
			$mobile = trim($d[1]);
			$email = trim($d[2]);
			if ($group_code = trim($d[3])) {
				$gpid = phonebook_groupcode2id($uid, $group_code);
			}
			if ($name && $mobile && $gpid) {
				$list = dba_search(_DB_PREF_.'_featurePhonebook', 'id', array('uid' => $uid, 'mobile' => $mobile));
				if ($c_pid = $list[0]['id']) {
					$save_to_group = TRUE;
				} else {
					$items = array('uid' => $uid, 'name' => $name, 'mobile' => $mobile, 'email' => $email);
					if ($c_pid = dba_add(_DB_PREF_.'_featurePhonebook', $items)) {
						$save_to_group = TRUE;
					} else {
						logger_print('fail to add contact gpid:'.$gpid.' pid:'.$c_pid.' m:'.$mobile.' n:'.$name.' e:'.$email, 3, 'phonebook_add');
					}
				}
				if ($save_to_group) {
					$items = array('gpid' => $gpid, 'pid' => $c_pid);
					if (dba_isavail(_DB_PREF_.'_featurePhonebook_group_contacts', $items, 'AND')) {
						if (dba_add(_DB_PREF_.'_featurePhonebook_group_contacts', $items)) {
							logger_print('contact added to group gpid:'.$gpid.' pid:'.$c_pid.' m:'.$mobile.' n:'.$name.' e:'.$email, 3, 'phonebook_add');
						} else {
							logger_print('contact added but fail to save in group gpid:'.$gpid.' pid:'.$c_pid.' m:'.$mobile.' n:'.$name.' e:'.$email, 3, 'phonebook_add');
						}
					}
				}
				//$i++;
				//logger_print("no:".$i." gpid:".$gpid." uid:".$uid." name:".$name." mobile:".$mobile." email:".$email, 3, "phonebook import");
			}
			unset($gpid);
		}
		$_SESSION['error_string'] = _('Contacts has been imported');
		header("Location: "._u('index.php?app=main&inc=feature_phonebook&route=import&op=list'));
		exit();
		break;
}
