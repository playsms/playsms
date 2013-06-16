<?php
defined('_SECURE_') or die('Forbidden');
if(!isadmin()){forcenoaccess();};

switch ($op) {
	case "user_list":
		$referrer = ( $_SESSION['referrer'] ? $_SESSION['referrer'] : 'user_list_tab1' );
		header("Location: index.php?app=menu&inc=user_mgmnt&op=".$referrer);
		break;
	case "user_list_tab1":
		$search_var = array(
			'name' => 'user_mgmnt',
			'url' => 'index.php?app=menu&inc=user_mgmnt&op=user_list_tab1',
		);
		$search = themes_search($search_var);
		$conditions = array('status' => 2);
		if ($search['keyword']) {
			$keywords = array('username' => '%'.$search['keyword'].'%');
		}
		$count = dba_count(_DB_PREF_.'_tblUser', $conditions, $keywords);
		$nav = themes_nav($count, "index.php?app=menu&inc=user_mgmnt&op=user_list_tab1");
		$extras = array('ORDER BY' => 'register_datetime DESC, username', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$list = dba_search(_DB_PREF_.'_tblUser', '*', $conditions, $keywords, $extras);
		$_SESSION['referrer'] = 'user_list_tab1';
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>" . _('Manage user') . "</h2>
			<p>
			<input type='button' value='" . _('Add user') . "' onClick=\"javascript:linkto('index.php?app=menu&inc=user_mgmnt&op=user_add')\" class=\"button\" />
			<input type='button' value='" . _('View normal user') . "' onClick=\"javascript:linkto('index.php?app=menu&inc=user_mgmnt&op=user_list_tab2')\" class=\"button\" />
			<p>".$search['form']."</p>
			<p>".$nav['form']."</p>
			<p>" . _('Status') . ": <b>" . _('Administrator') . "</b><br>
			<table cellpadding='1' cellspacing='2' border='0' width='100%' class='sortable'>
			<thead><tr>
				<td class='box_title' width='25'>*</td>
				<td class='box_title' width='100'>" . _('Registered') . "</td>
				<td class='box_title' width='100'>" . _('Username') . "</td>
				<td class='box_title' width='125'>" . _('Name') . "</td>	
				<td class='box_title' width='150'>" . _('Email') . "</td>
				<td class='box_title' width='150'>" . _('Mobile') . "</td>
				<td class='box_title' width='75'>" . _('Credit') . "</td>
				<td class='box_title' class='sortable_nosort' width='75'>" . _('Action') . "</td>
			</tr></thead>
			<tbody>";
		$j = $nav['top'];
		for ($i=0;$i<count($list);$i++) {
			$j--;
			$td_class = ($j % 2) ? "box_text_odd" : "box_text_even";
			$action = "<a href=\"index.php?app=menu&inc=user_pref&op=user_pref&uname=" . $list[$i]['username'] . "\">$icon_edit</a>";
			$action .= "<a href=\"javascript: ConfirmURL('" . _('Are you sure you want to delete user ?') . " (" . _('username') . ": " . $list[$i]['username'] . ")','index.php?app=menu&inc=user_mgmnt&op=user_del&uname=" . $list[$i]['username'] . "')\">$icon_delete</a>";
			$content .= "
				<tr>
					<td class='$td_class'>&nbsp;".$j.".</td>
					<td class='$td_class'>" . core_display_datetime($list[$i]['register_datetime']) . "</td>
					<td class='$td_class'>" . $list[$i]['username'] . "</td>
					<td class='$td_class'>" . $list[$i]['name'] . "</td>
					<td class='$td_class'>" . $list[$i]['email'] . "</td>	
					<td class='$td_class'>" . $list[$i]['mobile'] . "</td>	
					<td class='$td_class'>" . rate_getusercredit($list[$i]['username']) . "</td>	
					<td class='$td_class' align='center'>$action</td>
				</tr>";
		}
		$content .= "
			</tbody></table>
			<p>".$nav['form']."</p>";
		echo $content;
		break;
	case "user_list_tab2":
		$search_var = array(
			'name' => 'user_mgmnt',
			'url' => 'index.php?app=menu&inc=user_mgmnt&op=user_list_tab2',
		);
		$search = themes_search($search_var);
		$conditions = array('status' => 3);
		if ($search['keyword']) {
			$keywords = array('username' => '%'.$search['keyword'].'%');
		}
		$count = dba_count(_DB_PREF_.'_tblUser', $conditions, $keywords);
		$nav = themes_nav($count, "index.php?app=menu&inc=user_mgmnt&op=user_list_tab2");
		$extras = array('ORDER BY' => 'register_datetime DESC, username', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$list = dba_search(_DB_PREF_.'_tblUser', '*', $conditions, $keywords, $extras);
		$_SESSION['referrer'] = 'user_list_tab2';
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>" . _('Manage user') . "</h2>
			<p>
			<input type='button' value='" . _('Add user') . "' onClick=\"javascript:linkto('index.php?app=menu&inc=user_mgmnt&op=user_add')\" class=\"button\" />
			<input type='button' value='" . _('View administrator') . "' onClick=\"javascript:linkto('index.php?app=menu&inc=user_mgmnt&op=user_list_tab1')\" class=\"button\" />
			<p>".$search['form']."</p>
			<p>".$nav['form']."</p>
			<p>" . _('Status') . ": <b>" . _('Normal user') . "</b><br>
			<table cellpadding='1' cellspacing='2' border='0' width='100%' class='sortable'>
			<thead><tr>
				<td class='box_title' width='25'>*</td>
				<td class='box_title' width='100'>" . _('Registered') . "</td>
				<td class='box_title' width='100'>" . _('Username') . "</td>
				<td class='box_title' width='125'>" . _('Name') . "</td>	
				<td class='box_title' width='150'>" . _('Email') . "</td>
				<td class='box_title' width='150'>" . _('Mobile') . "</td>
				<td class='box_title' width='75'>" . _('Credit') . "</td>
				<td class='box_title' class='sortable_nosort' width='75'>" . _('Action') . "</td>
			</tr></thead>
			<tbody>";
		$j = $nav['top'];
		for ($i=0;$i<count($list);$i++) {
			$list[$i] = core_display_data($list[$i]);
			$j--;
			$td_class = ($j % 2) ? "box_text_odd" : "box_text_even";
			$action = "<a href=\"index.php?app=menu&inc=user_pref&op=user_pref&uname=" . $list[$i]['username'] . "\">$icon_edit</a>";
			$action .= "<a href=\"javascript: ConfirmURL('" . addslashes(_("Are you sure you want to delete user")) . " " . $list[$i]['username'] . " ?','index.php?app=menu&inc=user_mgmnt&op=user_del&uname=" . $list[$i]['username'] . "')\">$icon_delete</a>";
			$content .= "
				<tr>
					<td class='$td_class'>&nbsp;".$j.".</td>
					<td class='$td_class'>" . core_display_datetime($list[$i]['register_datetime']) . "</td>
					<td class='$td_class'>" . $list[$i]['username'] . "</td>
					<td class='$td_class'>" . $list[$i]['name'] . "</td>
					<td class='$td_class'>" . $list[$i]['email'] . "</td>	
					<td class='$td_class'>" . $list[$i]['mobile'] . "</td>	
					<td class='$td_class'>" . rate_getusercredit($list[$i]['username']) . "</td>	
					<td class='$td_class' align='center'>$action</td>
				</tr>";
		}
		$content .= "
			</tbody></table>
			<p>".$nav['form']."</p>";
		echo $content;
		break;
	case "user_del":
		$up['username'] = $_REQUEST['uname'];
		$del_uid = username2uid($up['username']);
		$_SESSION['error_string'] = _('Fail to delete user') . " ".$up['username'];
		if (($del_uid > 1) && ($del_uid != $uid)) {
			$condition = array('uid' => $del_uid);
			if (dba_remove(_DB_PREF_.'_tblUser', $condition)) {
				$_SESSION['error_string'] = _('User has been deleted') . " (" . _('username') . ": ".$up['username'].")";
			}
		}
		if (($del_uid == 1) || ($up['username'] == "admin")) {
			$_SESSION['error_string'] = _('User is immune to deletion') . " (" . _('username') . " ".$up['username'].")";
		} else if ($del_uid == $uid) {
			$_SESSION['error_string'] = _('Currently logged in user is immune to deletion');
		}
		$referrer = ( $_SESSION['referrer'] ? $_SESSION['referrer'] : 'user_list_tab1' );
		header("Location: index.php?app=menu&inc=user_mgmnt&op=".$referrer);
		exit();
		break;
	case "user_add":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$add_datetime_timezone = ( $add_datetime_timezone ? $add_datetime_timezone : core_get_timezone() );

		// get language options
		$lang_list = '';
		for ($i=0;$i<count($core_config['languagelist']);$i++) {
			$language = $core_config['languagelist'][$i];
			$c_language_title = $core_config['plugins']['language'][$language]['title'];
			if ($c_language_title) {
				$lang_list[$c_language_title] = $language;
			}
		}
		if (is_array($lang_list)) {
			foreach ($lang_list as $key => $val) {
				if ($val == $language_module) $selected = "selected";
				$option_language_module .= "<option value=\"".$val."\" $selected>".$key."</option>";
				$selected = "";
			}
		}

		$option_status = "
			<option value='2'>" . _('Administrator') . "</option>
			<option value='3' selected>" . _('Normal User') . "</option>";
		$content .= "
		<h2>" . _('Add user') . "</h2>
		<p>
		<form action='index.php?app=menu&inc=user_mgmnt&op=user_add_yes' method='post'>
		<table width='100%' cellpadding='1' cellspacing='2' border='0'>
		<tbody>
		<tr>
			<td width='175'>" . _('Username') . " $nd</td><td width='5'>:</td><td><input type='text' size='30' maxlength='30' name='add_username' value=\"$add_username\"></td>
		</tr>
		<tr>
			<td>" . _('Full name') . " $nd</td><td>:</td><td><input type='text' size='30' maxlength='30' name='add_name' value=\"$add_name\"></td>
		</tr>
		<tr>
			<td>" . _('Email') . " $nd</td><td>:</td><td><input type='text' size='30' maxlength='30' name='add_email' value=\"$add_email\"></td>
		</tr>
		<tr>
			<td>" . _('Mobile') . "</td><td>:</td><td><input type='text' size='16' maxlength='16' name='add_mobile' value=\"$add_mobile\"> (" . _('Max. 16 numeric or 11 alphanumeric characters') . ")</td>
		</tr>
		<tr>
			<td>" . _('SMS sender ID') . "</td><td>:</td><td><input type='text' size='16' maxlength='16' name='add_sender' value=\"$add_sender\"> (" . _('Max. 16 numeric or 11 alphanumeric characters') . ")</td>
		</tr>
		<tr>
			<td>" . _('SMS footer') . "</td><td>:</td><td><input type='text' size='30' maxlength='30' name='add_footer' value=\"$add_footer\"> (" . _('Max. 30 alphanumeric characters') . ")</td>
		</tr>	    	    	    
		<tr>
			<td>" . _('Timezone') . "</td><td>:</td><td><input type='text' size='5' maxlength='5' name='add_datetime_timezone' value=\"$add_datetime_timezone\"> (" . _('Eg: +0700 for Jakarta/Bangkok timezone') . ")</td>
		</tr>
		<tr>
			<td>" . _('Password') . " $nd</td><td>:</td><td><input type='password' size='30' maxlength='30' name='add_password' value=\"$add_password\"></td>
		</tr>
		<tr>
			<td>" . _('Credit') . "</td><td>:</td><td><input type='text' size='16' maxlength='30' name='add_credit' value=\"" . $core_config['main']['cfg_default_credit'] . "\"></td>
		</tr>
		<tr>
			<td>" . _('User level') . "</td><td>:</td><td><select name='add_status'>$option_status</select></td>
		</tr>
		<tr>
			<td>" . _('Active language') . "</td><td>:</td><td><select name='add_language_module'>$option_language_module</select></td>
		</tr>

		</tbody>
		</table>
		<p><input type='submit' class='button' value='" . _('Add') . "'>
		</form>";
		echo $content;
		break;
	case "user_add_yes":
		$add['email'] = $_POST['add_email'];
		$add['username'] = core_sanitize_username($_POST['add_username']);
		$add['name'] = $_POST['add_name'];
		$add['mobile'] = $_POST['add_mobile'];
		$add['sender'] = core_sanitize_sender($_POST['add_sender']);
		$add['footer'] = $_POST['add_footer'];
		$add['password'] = $_POST['add_password'];
		$add['password'] = md5($add['password']);
		$add['token'] = md5(uniqid($add['username'], true));
		$add['credit'] = $_POST['add_credit'];
		$add['status'] = $_POST['add_status'];
		$add['datetime_timezone'] = $_POST['add_datetime_timezone'];
		$add['language_module'] = $_POST['add_language_module'];
		$next = true;
		if ($add['email'] && $add['username'] && $add['name'] && $add['password']) {
			$v = user_add_validate($add);
			if ($v['status']) {
				$item = array('username' => $add['username'], 'email' => $add['email']);
				if ($add['mobile']) {
					$item['mobile'] = $add['mobile'];
				}
				if (! dba_isavail($item)) {
					$_SESSION['error_string'] = _('User already exists') . " (" . _('username') . ": " . $add['username'] . ")";
					$next = false;
				}
				if ($next) {
					$dt = core_adjust_datetime(core_get_datetime());
					$add['register_datetime'] = $dt;
					$add['lastupdate_datetime'] = $dt;
					if ($new_uid = dba_add(_DB_PREF_.'_tblUser', $add)) {
						rate_setusercredit($new_uid, $add['credit']);
						$_SESSION['error_string'] = _('User has been added') . " (" . _('username') . ": ".$add['username'].")";
					}
				}
			} else {
				$_SESSION['error_string'] = $v['error_string'];
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=user_mgmnt&op=user_add");
		exit();
		break;
}
?>