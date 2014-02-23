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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');
if(!auth_isadmin()){auth_block();};

switch ($op) {
	case "user_list":
		$referrer = ( $_SESSION['referrer'] ? $_SESSION['referrer'] : 'user_list_tab1' );
		header("Location: index.php?app=menu&inc=user_mgmnt&op=".$referrer);
		break;
	case "user_list_tab1":
		$search_var = array(
			_('Registered') => 'register_datetime',
			_('Username') => 'username',
			_('Name') => 'name',
			_('Mobile') => 'mobile'
		);
		$search = themes_search($search_var);
		$conditions = array('status' => 2);
		$keywords = $search['dba_keywords'];
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
			<h3>" . _('Administrator') . "</h3>
			<input type='button' value='" . _('Add user') . "' onClick=\"javascript:linkto('index.php?app=menu&inc=user_mgmnt&op=user_add')\" class=\"button\" />
			<input type='button' value='" . _('View normal user') . "' onClick=\"javascript:linkto('index.php?app=menu&inc=user_mgmnt&op=user_list_tab2')\" class=\"button\" />
			<p>".$search['form']."</p>			
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width='20%'>" . _('Registered') . "</th>
				<th width='20%'>" . _('Username') . "</th>
				<th width='20%'>" . _('Name') . "</th>
				<th width='20%'>" . _('Mobile') . "</th>
				<th width='10%'>" . _('Credit') . "</th>
				<th width='10%'>" . _('Action') . "</th>
			</tr></thead>
			<tbody>";
		$j = $nav['top'];
		for ($i=0;$i<count($list);$i++) {
			$action = "<a href=\"index.php?app=menu&inc=user_pref&op=user_pref&uname=" . $list[$i]['username'] . "\">".$core_config['icon']['user_pref']."</a>";
			$action .= "<a href=\"index.php?app=menu&inc=user_config&op=user_config&uname=" . $list[$i]['username'] . "\">".$core_config['icon']['user_config']."</a>";
			$action .= "<a href=\"javascript: ConfirmURL('" . addslashes(_("Are you sure you want to delete user")) . " " . $list[$i]['username'] . " ?','index.php?app=menu&inc=user_mgmnt&op=user_del&uname=" . $list[$i]['username'] . "')\">".$core_config['icon']['user_delete']."</a>";
			$j--;
			$content .= "
				<tr>
					<td>" . core_display_datetime($list[$i]['register_datetime']) . "</td>
					<td>" . $list[$i]['username'] . "</td>
					<td>" . $list[$i]['name'] . "</td>
					<td>" . $list[$i]['mobile'] . "</td>	
					<td>" . rate_getusercredit($list[$i]['username']) . "</td>	
					<td>$action</td>
				</tr>";
		}
		$content .= "
			</tbody></table>
			</div>
			<div class=pull-right>".$nav['form']."</div>";
		echo $content;
		break;
	case "user_list_tab2":
		$search_var = array(
			_('Registered') => 'register_datetime',
			_('Username') => 'username',
			_('Name') => 'name',
			_('Mobile') => 'mobile'
		);
		$search = themes_search($search_var);
		$conditions = array('status' => 3);
		$keywords = $search['dba_keywords'];
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
			<h3>" . _('Normal user') . "</h3>
			<input type='button' value='" . _('Add user') . "' onClick=\"javascript:linkto('index.php?app=menu&inc=user_mgmnt&op=user_add')\" class=\"button\" />
			<input type='button' value='" . _('View administrator') . "' onClick=\"javascript:linkto('index.php?app=menu&inc=user_mgmnt&op=user_list_tab1')\" class=\"button\" />
			<p>".$search['form']."</p>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width='20%'>" . _('Registered') . "</th>
				<th width='20%'>" . _('Username') . "</th>
				<th width='20%'>" . _('Name') . "</th>
				<th width='20%'>" . _('Mobile') . "</th>
				<th width='10%'>" . _('Credit') . "</th>
				<th width='10%'>" . _('Action') . "</th>
			</tr></thead>
			<tbody>";
		$j = $nav['top'];
		for ($i=0;$i<count($list);$i++) {
			$list[$i] = core_display_data($list[$i]);
			$action = "<a href=\"index.php?app=menu&inc=user_pref&op=user_pref&uname=" . $list[$i]['username'] . "\">".$core_config['icon']['user_pref']."</a>";
			$action .= "<a href=\"index.php?app=menu&inc=user_config&op=user_config&uname=" . $list[$i]['username'] . "\">".$core_config['icon']['user_config']."</a>";
			$action .= "<a href=\"javascript: ConfirmURL('" . addslashes(_("Are you sure you want to delete user")) . " " . $list[$i]['username'] . " ?','index.php?app=menu&inc=user_mgmnt&op=user_del&uname=" . $list[$i]['username'] . "')\">".$core_config['icon']['user_delete']."</a>";
			$j--;
			$content .= "
				<tr>
					<td>" . core_display_datetime($list[$i]['register_datetime']) . "</td>
					<td>" . $list[$i]['username'] . "</td>
					<td>" . $list[$i]['name'] . "</td>
					<td>" . $list[$i]['mobile'] . "</td>	
					<td>" . rate_getusercredit($list[$i]['username']) . "</td>	
					<td>$action</td>
				</tr>";
		}
		$content .= "
			</tbody></table>
			</div>
			<div class=pull-right>".$nav['form']."</div>";
		echo $content;
		break;
	case "user_add":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$add_datetime_timezone = $_REQUEST['add_datetime_timezone'];
		$add_datetime_timezone = ( $add_datetime_timezone ? $add_datetime_timezone : core_get_timezone() );

		// get language options
		$lang_list = '';
		for ($i=0;$i<count($core_config['languagelist']);$i++) {
			$language = $core_config['languagelist'][$i];
			$c_language_title = $core_config['plugin'][$language]['title'];
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
		<h2>"._('Manage user')."</h2>
		<h3>"._('Add user')."</h3>
		<form action='index.php?app=menu&inc=user_mgmnt&op=user_add_yes' method=POST>
		"._CSRF_FORM_."
		<table class=playsms-table>
		<tbody>
		<tr>
			<td class=label-sizer>" . _('User level') . "</td><td><select name='add_status'>$option_status</select></td>
		</tr>
		<tr>
			<td>" . _mandatory('Username') . "</td><td><input type='text' size=30 maxlength='30' name='add_username' value=\"$add_username\"></td>
		</tr>
		<tr>
			<td>" . _mandatory('Password') . "</td><td><input type='password' size=30 maxlength='30' name='add_password' value=\"$add_password\"></td>
		</tr>
		<tr>
			<td>" . _mandatory('Full name') . "</td><td><input type='text' size=30 maxlength='100' name='add_name' value=\"$add_name\"></td>
		</tr>
		<tr>
			<td>" . _mandatory('Email') . "</td><td><input type='text' size=30 maxlength='250' name='add_email' value=\"$add_email\"></td>
		</tr>
		<tr>
			<td>" . _('Mobile') . "</td><td><input type='text' size='16' maxlength='16' name='add_mobile' value=\"$add_mobile\"> " . _hint(_('Max. 16 numeric or 11 alphanumeric characters')) . "</td>
		</tr>
		<tr>
			<td>" . _('SMS sender ID') . "</td><td><input type='text' size='16' maxlength='16' name='add_sender' value=\"$add_sender\"> " . _hint(_('Max. 16 numeric or 11 alphanumeric characters')) . "</td>
		</tr>
		<tr>
			<td>" . _('SMS footer') . "</td><td><input type='text' size=30 maxlength='30' name='add_footer' value=\"$add_footer\"> " . _hint(_('Max. 30 alphanumeric characters')) . "</td>
		</tr>	    	    	    
		<tr>
			<td>" . _('Timezone') . "</td><td><input type='text' size='5' maxlength='5' name='add_datetime_timezone' value=\"$add_datetime_timezone\"> " . _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')) . "</td>
		</tr>
		<tr>
			<td>" . _('Credit') . "</td><td><input type='text' size='5' maxlength='30' name='add_credit' value=\"" . $core_config['main']['cfg_default_credit'] . "\"></td>
		</tr>
		<tr>
			<td>" . _('Active language') . "</td><td><select name='add_language_module'>$option_language_module</select></td>
		</tr>
		</tbody>
		</table>
		<input type='submit' class='button' value='" . _('Save') . "'>
		</form>
		<p>"._back('index.php?app=menu&inc=user_mgmnt&op=user_list');
		echo $content;
		break;
	case "user_add_yes":
		$add['email'] = $_POST['add_email'];
		$add['status'] = $_POST['add_status'];
		$add['username'] = $_POST['add_username'];
		$add['password'] = $_POST['add_password'];
		$add['mobile'] = $_POST['add_mobile'];
		$add['name'] = $_POST['add_name'];
		$add['credit'] = $_POST['add_credit'];
		$add['sender'] = $_POST['add_sender'];
		$add['footer'] = $_POST['add_footer'];
		$add['datetime_timezone'] = $_POST['add_datetime_timezone'];
		$add['language_module'] = $_POST['add_language_module'];
		$ret = user_add($add);
		$_SESSION['error_string'] = $ret['error_string'];
		header("Location: index.php?app=menu&inc=user_mgmnt&op=user_add");
		exit();
		break;
	case "user_del":
		$up['username'] = $_REQUEST['uname'];
		$del_uid = user_username2uid($up['username']);
		$ret = user_remove($del_uid);
		$_SESSION['error_string'] = $ret['error_string'];
		$referrer = ( $_SESSION['referrer'] ? $_SESSION['referrer'] : 'user_list_tab1' );
		header("Location: index.php?app=menu&inc=user_mgmnt&op=".$referrer);
		exit();
		break;
}
