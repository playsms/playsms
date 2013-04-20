<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

$uid = $core_config['user']['uid'];

switch ($op) {
	case "list":
		$content .= "
			<h2>"._('Phonebook')."</h2>
			<h3>"._('Import')."</h3>
			<p>
			<form action=\"index.php?app=menu&inc=tools_phonebook&route=import&op=import\" enctype=\"multipart/form-data\" method=\"post\">
			"._('Please select CSV file for phonebook entries')." ("._('format')." : "._('Name').", "._('Mobile').", "._('Email').", "._('Group code').")<br>
			<p><input type=\"file\" name=\"fnpb\">
			<p><input type=\"submit\" value=\""._('Import')."\" class=\"button\">
			</form>
			<p>"._b('index.php?app=menu&inc=tools_phonebook&op=phonebook_list');
		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div><br><br>";
		}
		echo $content;
		break;
	case "import":
		$fnpb = $_FILES['fnpb'];
		$fnpb_tmpname = $_FILES['fnpb']['tmp_name'];
		$content = "
			<h2>"._('Phonebook')."</h2>
			<h3>"._('Import confirmation')."</h3>
			<p>
			<table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
			<thead><tr>
				<th width=\"4\">*</th>
				<th width=\"30%\">"._('Name')."</th>
				<th width=\"20%\">"._('Mobile')."</th>
				<th width=\"30%\">"._('Email')."</th>
				<th width=\"20%\">"._('Group code')."</th>
			</tr></thead><tbody>";
		if (file_exists($fnpb_tmpname)) {
			$fp = fopen($fnpb_tmpname, "r");
			$file_content = fread($fp, filesize($fnpb_tmpname));
			fclose($fp);
			$parse_phonebook = explode("\n", $file_content);
			$row_num = ( count($parse_phonebook) <= 100 ? count($parse_phonebook) : 100 );
			$j = 0;
			for ($i = 0; $i < $row_num; $i++) {
				if (!empty($parse_phonebook) && strlen($parse_phonebook[$i]) > 1) {
					$parse_phonebook[$i] = str_replace(";", ",", $parse_phonebook[$i]);
					$parse_param = explode(",", str_replace("\"", "", $parse_phonebook[$i]));
					$gid = phonebook_groupcode2id($uid, $parse_param[3]);
					if ($parse_param[0] && $parse_param[1] && $parse_param[3] && $gid) {
						$j++;
						$td_class = ($j % 2) ? "box_text_odd" : "box_text_even";
						$content .= "
							<tr>
							<td align=center class=$td_class>$j.</td>
							<td align=center class=$td_class>$parse_param[0]</td>
							<td align=center class=$td_class>$parse_param[1]</td>
							<td align=center class=$td_class>$parse_param[2]</td>
							<td align=center class=$td_class>$parse_param[3]</td>
							</tr>";
						$phonebook_post .= "
							<input type=\"hidden\" name=\"data_name$j\" value=\"$parse_param[0]\">
							<input type=\"hidden\" name=\"data_mobile$j\" value=\"$parse_param[1]\">
							<input type=\"hidden\" name=\"data_email$j\" value=\"$parse_param[2]\">
							<input type=\"hidden\" name=\"data_code$j\" value=\"$parse_param[3]\">";
					}
					unset($parse_param);
				}
			}
			$content .= "
				</tbody></table>
				<p>"._('Import above phonebook entries ?')."
				<form action=\"index.php?app=menu&inc=tools_phonebook&route=import&op=import_yes\" method=\"post\">
				<input type=\"submit\" value=\""._('Import')."\" class=\"button\">
				".$phonebook_post."
				<input type=\"hidden\" name=\"num\" value=\"$j\">
				</form>
				<p>"._b('index.php?app=menu&inc=tools_phonebook&route=import&op=list');
			echo $content;
		} else {
			$_SESSION['error_string'] = _('Fail to upload CSV file for phonebook');
			header("Location: index.php?app=menu&inc=tools_phonebook&route=import&op=list");
			exit();
		}
		break;
	case "import_yes":
		$num = $_POST['num'];
		for ($i=1; $i<=$num; $i++) {
			$data_name[$i] = trim($_POST['data_name'.$i]);
			$data_mobile[$i] = trim($_POST['data_mobile'.$i]);
			$data_email[$i] = trim($_POST['data_email'.$i]);
			if ($data_code[$i] = trim($_POST['data_code'.$i])) {
				$data_gpid[$i] = phonebook_groupcode2id($uid, $data_code[$i]);
			}
			if ($data_name[$i] && $data_mobile[$i] && $data_gpid[$i]) {
				dba_remove(_DB_PREF_.'_toolsPhonebook', array('name' => $data_name[$i], 'gpid' => $data_gpid[$i], 'uid' => $uid));
				dba_add(_DB_PREF_.'_toolsPhonebook', array('uid' => $uid, 'name' => $data_name[$i], 'mobile' => $data_mobile[$i], 'email' => $data_email[$i], 'gpid' => $data_gpid[$i]));
			}
		}
		$_SESSION['error_string'] = _('Contacts has been imported');
		header("Location: index.php?app=menu&inc=tools_phonebook&route=import&op=list");
		exit();
		break;
}

?>