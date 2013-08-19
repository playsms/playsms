<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

$c_username = $core_config['user']['username'];

if (($uname = $_REQUEST['uname']) && isadmin()) {
	$c_username = trim($uname);
	$url_uname = '&uname='.$c_username;
}

switch ($op) {
	case "user_pref":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		if ($c_user = dba_search(_DB_PREF_.'_tblUser', '*', array('username' => $c_username))) {
			$name = $c_user[0]['name'];
			$email = $c_user[0]['email'];
			$address = $c_user[0]['address'];
			$city = $c_user[0]['city'];
			$state = $c_user[0]['state'];
			$country = $c_user[0]['country'];
			$zipcode = $c_user[0]['zipcode'];
			$sender = core_sanitize_sender($c_user[0]['sender']);
		} else {
			$_SESSION['error_string'] = _('User does not exists').' ('._('username').': '.$uname.')';
			$referrer = ( $_SESSION['referrer'] ? $_SESSION['referrer'] : 'user_list_tab1' );
			header("Location: index.php?app=menu&inc=user_mgmnt&op=".$referrer);
			exit();
		}

		// get country option
		$option_country = "<option value=\"0\">--" . _('Please select') . "--</option>\n";
		$result = dba_search(_DB_PREF_.'_tblUser_country', '*', '', '', array('ORDER BY' => 'country_name'));
		for ($i=0;$i<count($result);$i++) {
			$country_id = $result[$i]['country_id'];
			$country_name = $result[$i]['country_name'];
			$selected = "";
			if ($country_id == $country) {
				$selected = "selected";
			}
			$option_country .= "<option value=\"$country_id\" $selected>$country_name</option>\n";
		}
		if ($uname && isadmin()) {
			$content .= "<h2>" . _('Manage user') . "</h2>";
			$button_delete = "<input type=button class=button value='". _('Delete') ."' onClick=\"javascript: ConfirmURL('" . _('Are you sure you want to delete user ?') . " (" . _('username') . ": " . $c_username . ")','index.php?app=menu&inc=user_mgmnt&op=user_del".$url_uname."')\">";
		} else {
			$content .= "<h2>" . _('Preferences') . "</h2>";
		}
		$content .= "
			<form action=\"index.php?app=menu&inc=user_pref&op=user_pref_save".$url_uname."\" method=post enctype=\"multipart/form-data\">
			<table width=100%>
			<tbody>
			<tr><td colspan=2><h3>" . _('Login information') . "</h3></td></tr>
			<tr><td width=270>" . _('Username') . "</td><td>".$c_username."</td></tr>
			<tr><td>" . _('Password') . "</td><td><input type=password size=30 maxlength=30 name=up_password></td></tr>
			<tr><td>" . _('Re-type password') . "</td><td><input type=password size=30 maxlength=30 name=up_password_conf></td></tr>
			<tr><td colspan=2>&nbsp;</td></tr>
			<tr><td colspan=2><h3>" . _('Personal information') . "</h3></td></tr>
			<tr><td>" . _('Name') . " $nd</td><td><input type=text size=30 maxlength=100 name=up_name value=\"$name\"></td></tr>
			<tr><td>" . _('Email') . " $nd</td><td><input type=text size=30 maxlength=30 name=up_email value=\"$email\"></td></tr>
			<tr><td>" . _('Address') . "</td><td><input type=text size=30 maxlength=250 name=up_address value=\"$address\"></td></tr>
			<tr><td>" . _('City') . "</td><td><input type=text size=30 maxlength=100 name=up_city value=\"$city\"></td></tr>
			<tr><td>" . _('State or Province') . "</td><td><input type=text size=30 maxlength=100 name=up_state value=\"$state\"></td></tr>
			<tr><td>" . _('Country') . "</td><td><select name=up_country>$option_country</select></td></tr>
			<tr><td>" . _('Zipcode') . "</td><td><input type=text size=10 maxlength=10 name=up_zipcode value=\"$zipcode\"></td></tr>
			</tbody>
			</table>
			<input type=submit class=button value='" . _('Save') . "'> ".$button_delete."
			</form>";
		echo $content;
		break;
	case "user_pref_save":
		$_SESSION['error_string'] = _('No changes made');
		$fields = array(
			'name', 'email', 'address', 'city', 'state', 'country', 'password', 'zipcode'
		);
		for ($i=0;$i<count($fields);$i++) {
			$up[$fields[$i]] = trim($_POST['up_'.$fields[$i]]);
		}
		$up['username'] = $c_username;
		$up['lastupdate_datetime'] = core_adjust_datetime(core_get_datetime());
		if ($up['name'] && $up['email']) {
			$v = user_add_validate($up);
			if ($v['status']) {
				$continue = true;
				if ($up['password'] && $_POST['up_password_conf']) {
					if ($up['password'] == $_POST['up_password_conf']) {
						$up['password'] = md5($up['password']);
					} else {
						$_SESSION['error_string'] = _('Password does not match');
						$continue = false;
					}
				} else {
					unset($up['password']);
				}
				if ($continue) {
					if (dba_update(_DB_PREF_.'_tblUser', $up, array('username' => $c_username))) {
						if ($up['password']) {
							$_SESSION['error_string'] = _('Preferences has been saved and password updated');
						} else if ($up['token']) {
							$_SESSION['error_string'] = _('Preferences has been saved and webservices token updated');
						} else {
							$_SESSION['error_string'] = _('Preferences has been saved');
						}
					} else {
						$_SESSION['error_string'] = _('Fail to save preferences');
					}
				}
			} else {
				$_SESSION['error_string'] = $v['error_string'];
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all field');
		}
		header("Location: index.php?app=menu&inc=user_pref&op=user_pref".$url_uname);
		exit();
		break;
}
?>