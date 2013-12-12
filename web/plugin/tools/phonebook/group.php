<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

switch ($op) {
	case "list":
		$search_category = array(_('Name') => 'name', _('Group code') => 'code');
		$base_url = 'index.php?app=menu&inc=tools_phonebook&route=group&op=list';
		$search = themes_search($search_category, $base_url);
		$conditions = array('uid' => $core_config['user']['uid']);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_.'_toolsPhonebook_group', $conditions, $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = array('ORDER BY' => 'name', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$fields = 'id, name, code';
		$list = dba_search(_DB_PREF_.'_toolsPhonebook_group', $fields, $conditions, $keywords, $extras);

		$content = "
			<h2>"._('Phonebook')."</h2>
			<h3>"._('Group')."</h3>
			<p>".$search['form']."</p>
			<form id=fm_phonebook_group_list name=fm_phonebook_group_list action='index.php?app=menu&inc=tools_phonebook&route=group&op=actions' method=post>
			"._CSRF_FORM_."
			<input type=hidden name=go value=delete>
			<div class=actions_box>
				<div class=pull-left>
					<a href='index.php?app=menu&inc=tools_phonebook&route=group&op=add'>".$core_config['icon']['add']."</a>
				</div>
				<div class=pull-right>".$nav['form']."</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=60%>"._('Name')."</th>
				<th width=35%>"._('Group code')."</th>
				<th width=5%>"._('Action')."</th>
			</tr>
			</thead>
			<tbody>";

		$j = 0;
		for ($j=0;$j<count($list);$j++) {
			$gpid = $list[$j]['id'];
			$name = $list[$j]['name'];
			$code = $list[$j]['code'];
			$i++;
			$content .= "
				<tr>
					<td><a href='index.php?app=menu&inc=tools_phonebook&route=group&op=edit&gpid=".$gpid."'>$name</a></td>
					<td>$code</td>
					<td>
						<a href='index.php?app=menu&inc=tools_phonebook&route=group&op=actions&go=delete&gpid=".$gpid."' onClick=\"return SureConfirm();\">".$core_config['icon']['delete']."</a>
					</td>
				</tr>";
		}

		$content .= "
			</tbody>
			</table>
			</div>
			</form>
			"._b('index.php?app=menu&inc=tools_phonebook&op=phonebook_list');

		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
		echo $content;
		break;
	case "add":
		$content = "
			<h2>"._('Phonebook')."</h2>
			<h3>"._('Add group')."</h3>
			<p>
			<form action=\"index.php?app=menu&inc=tools_phonebook&route=group&op=actions&go=add\" method=POST>
			"._CSRF_FORM_."
			<table class=playsms-table>
			<tbody>
				<tr>
					<td class=label-sizer>"._('Group name')."</td>
					<td><input type=text name=group_name size=30></td>
				</tr>
				<tr>
					<td>"._('Group code')."</td>
					<td><input type=text name=group_code size=10> "._hint(_('Group code used by keyword')." BC ("._('broadcast SMS from single SMS').") "._('please use uppercase and make it short')."")."</td>
				</tr>
			</tbody>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\"> 
			</form>
			<p>"._b('index.php?app=menu&inc=tools_phonebook&route=group&op=list');
		echo $content;
		break;
	case "edit":
		$gpid = $_REQUEST['gpid'];
		$content = "
			<h2>"._('Phonebook')."</h2>
			<h3>"._('Edit group')."</h3>
			<p>
			<form action=\"index.php?app=menu&inc=tools_phonebook&route=group&op=actions&go=edit\" method=POST>
			"._CSRF_FORM_."
			<input type=hidden name=gpid value=\"$gpid\">
			<table class=playsms-table>
			<tbody>
			<tr>
				<td class=label-sizer>"._('Group name')."</td>
				<td><input type=text name=group_name value=\"".phonebook_groupid2name($gpid)."\" size=30></td>
			</tr>
			<tr>
				<td>"._('Group code')."</td>
				<td><input type=text name=group_code value=\"".phonebook_groupid2code($gpid)."\" size=10> "._hint(_('please use uppercase and make it short'))."</td>
			</tr>
			</tbody>
			</table>
			<p>"._('Note').": "._('Group code used by keyword')." BC ("._('broadcast SMS from single SMS').")
			<p><input type=submit class=button value=\""._('Save')."\"> 
			</form>
			<p>"._b('index.php?app=menu&inc=tools_phonebook&route=group&op=list');
		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
		echo $content;
		break;
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'delete':
				if ($gpid = $_REQUEST['gpid']) {
					if (! dba_count(_DB_PREF_.'_toolsPhonebook_group_contacts', array('gpid' => $gpid))) {
						if (dba_remove(_DB_PREF_.'_toolsPhonebook_group', array('uid' => $core_config['user']['uid'], 'id' => $gpid))) {
							$_SESSION['error_string'] = _('Selected group has been deleted');
						} else {
							$_SESSION['error_string'] = _('Fail to delete group');
						}

					} else {
						$_SESSION['error_string'] = _('Unable to delete group until the group is empty');
					}
				}
				$ref = $nav['url'].'&search_keyword='.$search['keyword'].'&search_category='.$search['category'].'&page='.$nav['page'].'&nav='.$nav['nav'];
				header("Location: ".$ref);
				exit();
				break;
			case 'add':
				$group_name = $_POST['group_name'];
				$group_code = strtoupper(trim($_POST['group_code']));
				$uid = $core_config['user']['uid'];
				$_SESSION['error_string'] = _('You must fill all field');
				if ($group_name && $group_code) {
					$db_query = "SELECT code FROM "._DB_PREF_."_toolsPhonebook_group WHERE uid='$uid' AND code='$group_code'";
					$db_result = dba_query($db_query);
					if ($db_row = dba_fetch_array($db_result)) {
						$_SESSION['error_string'] = _('Group code is already exists')." ("._('code').": $group_code)";
					} else {
						$db_query = "INSERT INTO "._DB_PREF_."_toolsPhonebook_group (uid,name,code) VALUES ('$uid','$group_name','$group_code')";
						$db_result = dba_query($db_query);
						$_SESSION['error_string'] = _('Group code has been added')." ("._('group').": $group_name, "._('code').": $group_code)";
					}
				}
				header("Location: index.php?app=menu&inc=tools_phonebook&route=group&op=list");
				exit();
				break;
			case 'edit':
				$gpid = $_POST['gpid'];
				$group_name = $_POST['group_name'];
				$group_code = strtoupper(trim($_POST['group_code']));
				$uid = $core_config['user']['uid'];
				$_SESSION['error_string'] = _('You must fill all field');
				if ($gpid && $group_name && $group_code) {
					$db_query = "SELECT code FROM "._DB_PREF_."_toolsPhonebook_group WHERE uid='$uid' AND code='$group_code' AND NOT id='$gpid'";
					$db_result = dba_query($db_query);
					if ($db_row = dba_fetch_array($db_result)) {
						$_SESSION['error_string'] = _('No changes has been made');
					} else {
						$db_query = "UPDATE "._DB_PREF_."_toolsPhonebook_group SET c_timestamp='".mktime()."',name='$group_name',code='$group_code' WHERE uid='$uid' AND id='$gpid'";
						$db_result = dba_query($db_query);
						$_SESSION['error_string'] = _('Group has been edited')." ("._('group').": $group_name, "._('code')." $group_code)";
					}
				}
				header("Location: index.php?app=menu&inc=tools_phonebook&route=group&op=edit&gpid=$gpid");
				exit();
				break;
		}
		break;
}

?>