<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

switch ($op) {
	case "list":
		$search_category = array(_('Name') => 'name', _('Code'));
		$base_url = 'index.php?app=menu&inc=tools_phonebook&route=group&op=list';
		$search = themes_search($search_category, $base_url);
		$conditions = array('uid' => $core_config['user']['uid']);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_.'_toolsPhonebook_group', $conditions, $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = array('ORDER BY' => 'name', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$fields = 'id, name, code';
		$list = dba_search(_DB_PREF_.'_toolsPhonebook_group', $fields, $conditions, $keywords, $extras);

		$actions_box = "
			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tbody><tr>
				<td><input type=button class=button value=\""._('Add group')."\" onClick=\"javascript:window.location.href='index.php?app=menu&inc=tools_phonebook&route=group&op=add'\"></td>
				<td width=100%>&nbsp;</td>
				<td><input type=submit name=go value=\""._('Delete selection')."\" class=button onClick=\"return SureConfirm()\"/></td>
			</tr></tbody>
			</table>";

		$content = "
			<h2>"._('Phonebook')."</h2>
			<h3>"._('Group')."</h3>
			<p>".$search['form']."</p>
			<p>".$nav['form']."</p>
			<form name=\"fm_inbox\" action=\"index.php?app=menu&inc=tools_phonebook&route=group&op=actions\" method=post>
			".$actions_box."
			<table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
			<thead>
			<tr>
				<th align=center width=4>*</th>
				<th align=center width=70%>"._('Name')."</th>
				<th align=center width=30%>"._('Code')."</th>
				<th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_inbox)></td>
			</tr>
			</thead>
			<tbody>";

		$j = 0;
		for ($j=0;$j<count($list);$j++) {
			$gpid = $list[$j]['id'];
			$name = "<a href=\"index.php?app=menu&inc=tools_phonebook&route=group&op=edit&gpid=".$gpid."\">".$list[$j]['name']."</a>";
			$code = $list[$j]['code'];
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr>
					<td valign=top class=$td_class align=left>$i.</td>
					<td valign=top class=$td_class align=center>$name</td>
					<td valign=top class=$td_class align=center>$code</td>
					<td class=$td_class width=4>
						<input type=hidden name=itemid".$j." value=\"".$gpid."\">
						<input type=checkbox name=checkid".$j.">
					</td>
				</tr>";
		}

		$content .= "
			</tbody>
			</table>
			".$actions_box."
			</form>
			<p>"._b('index.php?app=menu&inc=tools_phonebook&op=phonebook_list');

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
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tbody>
				<tr>
					<td width=75>"._('Group name')."</td><td width=5>:</td>
					<td><input type=text name=group_name size=50></td>
				</tr>
				<tr>
					<td>"._('Group code')."</td><td>:</td>
					<td><input type=text name=group_code size=10> ("._('please use uppercase and make it short').")</td>
				</tr>
			</tbody>
			</table>
			<p>"._('Note').": "._('Group code used by keyword')." BC ("._('broadcast SMS from single SMS').")
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
			<input type=hidden name=gpid value=\"$gpid\">
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tbody>
			<tr>
				<td width=75>"._('Group name')."</td><td width=5>:</td>
				<td><input type=text name=group_name value=\"".phonebook_groupid2name($gpid)."\" size=50></td>
			</tr>
			<tr>
				<td>"._('Group code')."</td><td>:</td>
				<td><input type=text name=group_code value=\"".phonebook_groupid2code($gpid)."\" size=10> ("._('please use uppercase and make it short').")</td>
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
			case _('Delete selection'):
				for ($i=0;$i<$nav['limit'];$i++) {
					$checkid = $_POST['checkid'.$i];
					$itemid = $_POST['itemid'.$i];
					if(($checkid=="on") && $itemid) {
						dba_remove(_DB_PREF_.'_toolsPhonebook_group', array('id' => $itemid));
						dba_remove(_DB_PREF_.'_toolsPhonebook', array('gpid' => $itemid));
					}
				}
				$ref = $nav['url'].'&search_keyword='.$search['keyword'].'&search_category='.$search['category'].'&page='.$nav['page'].'&nav='.$nav['nav'];
				$_SESSION['error_string'] = _('Selected group has been deleted');
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