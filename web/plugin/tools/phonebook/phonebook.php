<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

if ($route = $_REQUEST['route']) {
	$fn = $apps_path['plug'].'/tools/phonebook/'.$route.'.php';
	$fn = core_sanitize_path($fn);
	if (file_exists($fn)) {
		include $fn;
		unset($_SESSION['error_string']);
		exit();
	}
}

switch ($op) {
	case "phonebook_list":
		$search_category = array(_('Name') => 'A.name', _('Mobile') => 'mobile', _('Email') => 'email', _('Group code') => 'code');
		$base_url = 'index.php?app=menu&inc=tools_phonebook&op=phonebook_list';
		$search = themes_search($search_category, $base_url);
		$conditions = array('B.uid' => $core_config['user']['uid']);
		$keywords = $search['dba_keywords'];
		$join = 'INNER JOIN '._DB_PREF_.'_toolsPhonebook_group AS B ON A.gpid=B.id';
		$count = dba_count(_DB_PREF_.'_toolsPhonebook AS A', $conditions, $keywords, '', $join);
		$nav = themes_nav($count, $search['url']);
		$extras = array('ORDER BY' => 'A.name DESC', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$fields = 'A.id AS pid, A.name AS name, mobile, email, code';
		$list = dba_search(_DB_PREF_.'_toolsPhonebook AS A', $fields, $conditions, $keywords, $extras, $join);

		$actions_box = "
			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tbody><tr>
				<td><input type=button class=button value=\""._('Add contact')."\" onClick=\"javascript:window.location.href='index.php?app=menu&inc=tools_phonebook&op=phonebook_add'\"></td>
				<td><input type=button class=button value=\""._('Group')."\" onClick=\"javascript:window.location.href='index.php?app=menu&inc=tools_phonebook&route=group&op=list'\"></td>
				<td><input type=button class=button value=\""._('Import')."\" onClick=\"javascript:window.location.href='index.php?app=menu&inc=tools_phonebook&route=import&op=list'\"></td>
				<td><input type=submit name=go value=\""._('Export')."\" class=button /></td>
				<td width=100%>&nbsp;</td>
				<td><input type=submit name=go value=\""._('Delete selection')."\" class=button onClick=\"return SureConfirm()\"/></td>
			</tr></tbody>
			</table>";

		$content = "
			<h2>"._('Phonebook')."</h2>
			<p>".$search['form']."</p>
			<p>".$nav['form']."</p>
			<form name=\"fm_inbox\" action=\"index.php?app=menu&inc=tools_phonebook&op=actions\" method=post>
			".$actions_box."
			<table cellpadding=1 cellspacing=2 border=0 width=100% class=\"sortable\">
			<thead>
			<tr>
				<th align=center width=4>*</th>
				<th align=center width=30%>"._('Name')."</th>
				<th align=center width=30%>"._('Mobile')."</th>
				<th align=center width=30%>"._('Email')."</th>
				<th align=center width=10%>"._('Group code')."</th>
				<th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_inbox)></td>
			</tr>
			</thead>
			<tbody>";

		$i = $nav['top'];
		$j = 0;
		for ($j=0;$j<count($list);$j++) {
			$pid = $list[$j]['pid'];
			$name = "<a href=\"index.php?app=menu&inc=tools_phonebook&op=phonebook_edit&id=".$pid."\">".$list[$j]['name']."</a>";
			$mobile = $list[$j]['mobile'];
			$email = $list[$j]['email'];
			$group_code = strtoupper($list[$j]['code']);
			$i--;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr>
					<td valign=top class=$td_class align=left>$i.</td>
					<td valign=top class=$td_class align=center>$name</td>
					<td valign=top class=$td_class align=center>$mobile</td>
					<td valign=top class=$td_class align=center>$email</td>
					<td valign=top class=$td_class align=center>$group_code</td>
					<td class=$td_class width=4>
						<input type=hidden name=itemid".$j." value=\"$pid\">
						<input type=checkbox name=checkid".$j.">
					</td>
				</tr>";
		}

		$content .= "
			</tbody>
			</table>
			".$actions_box."
			<p>".$nav['form']."</p>
			</form>";

		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
		echo $content;
		break;
	case "phonebook_add":
		$phone = trim(urlencode($_REQUEST['phone']));
		$uid = $core_config['user']['uid'];
		$db_query = "SELECT * FROM "._DB_PREF_."_toolsPhonebook_group WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$list_of_group .= "<option value=".$db_row['id'].">".$db_row['name']." - "._('code').": ".$db_row['code']."</option>";
		}
		$content = "
			<h2>"._('Phonebook')."</h2>
			<h3>"._('Add contact')."</h3>
			<p>
			<form action=\"index.php?app=menu&inc=tools_phonebook&op=actions&go=add\" name=fm_addphone method=POST>
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tbody>
			<tr><td width=100>"._('Group')."</td><td width=5>:</td><td><select name=gpid>$list_of_group</select></td></tr>
			<tr><td>"._('Name')."</td><td>:</td><td><input type=text name=name size=30></td></tr>
			<tr><td>"._('Mobile')."</td><td>:</td><td><input type=text name=mobile value=\"".$phone."\" size=30></td></tr>
			<tr><td>"._('Email')."</td><td>:</td><td><input type=text name=email size=30></td></tr>
			</tbody>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			<p>"._b('index.php?app=menu&inc=tools_phonebook&op=phonebook_list');
		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
		echo $content;
		break;
	case "phonebook_edit":
		$uid = $core_config['user']['uid'];
		$id = $_REQUEST['id'];
		$list = dba_search(_DB_PREF_.'_toolsPhonebook', 'gpid, name, mobile, email', array('id' => $id, 'uid' => $uid));
		$db_query = "SELECT * FROM "._DB_PREF_."_toolsPhonebook_group WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$selected = ( $db_row['id'] == $list[0]['gpid'] ? 'selected' : '' );
			$list_of_group .= "<option value=".$db_row['id']." $selected>".$db_row['name']." - "._('code').": ".$db_row['code']."</option>";
		}
		$content = "
			<h2>"._('Phonebook')."</h2>
			<h3>"._('Edit contact')."</h3>
			<p>
			<form action=\"index.php?app=menu&inc=tools_phonebook&op=actions&go=edit\" name=fm_addphone method=POST>
			<input type=hidden name=id value=\"".$id."\">
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tbody>
			<tr><td width=100>"._('Group')."</td><td width=5>:</td><td><select name=gpid>$list_of_group</select></td></tr>
			<tr><td>"._('Name')."</td><td>:</td><td><input type=text name=name value=\"".$list[0]['name']."\" size=30></td></tr>
			<tr><td>"._('Mobile')."</td><td>:</td><td><input type=text name=mobile value=\"".$list[0]['mobile']."\" size=30></td></tr>
			<tr><td>"._('Email')."</td><td>:</td><td><input type=text name=email value=\"".$list[0]['email']."\" size=30></td></tr>
			</tbody>
			</table>
			<p><input type=submit class=button value=\""._('Save')."\">
			</form>
			<p>"._b('index.php?app=menu&inc=tools_phonebook&op=phonebook_list');
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
			case _('Export'):
				$uid = $core_config['user']['uid'];
				$fields = 'A.name AS name, mobile, email, code';
				$join = 'INNER JOIN '._DB_PREF_.'_toolsPhonebook_group AS B ON A.gpid=B.id';
				$list = dba_search(_DB_PREF_.'_toolsPhonebook AS A', $fields, array('A.uid' => $uid), $search['dba_keywords'], '', $join);
				$data[0] = array(_('Name'), _('Mobile'), _('Email'), _('Group code'));
				for ($i=0;$i<count($list);$i++) {
					$j = $i + 1;
					$data[$j] = array(
						$list[$i]['name'],
						$list[$i]['mobile'],
						$list[$i]['email'],
						$list[$i]['code']);
				}
				$content = csv_format($data);
				$fn = 'phonebook-'.$core_config['datetime']['now_stamp'].'.csv';
				download($content, $fn, 'text/csv');
				break;
			case _('Delete selection'):
				for ($i=0;$i<$nav['limit'];$i++) {
					$checkid = $_POST['checkid'.$i];
					$itemid = $_POST['itemid'.$i];
					if(($checkid=="on") && $itemid) {
						dba_remove(_DB_PREF_.'_toolsPhonebook', array('id' => $itemid));
					}
				}
				$ref = $nav['url'].'&search_keyword='.$search['keyword'].'&search_category='.$search['category'].'&page='.$nav['page'].'&nav='.$nav['nav'];
				$_SESSION['error_string'] = _('Selected contact has been deleted');
				header("Location: ".$ref);
				exit();
				break;
			case 'add':
				$uid = $core_config['user']['uid'];
				$gpid = $_POST['gpid'];
				$mobile = str_replace("\'","",$_POST['mobile']);
				$mobile = str_replace("\"","",$mobile);
				$name = str_replace("\'","",$_POST['name']);
				$name = str_replace("\"","",$name);
				$email = str_replace("\'","",$_POST['email']);
				$email = str_replace("\"","",$email);
				$_SESSION['error_string'] = _('You must fill all field');
				if ($gpid && $mobile && $name) {
					$db_query = "SELECT mobile,name FROM "._DB_PREF_."_toolsPhonebook WHERE uid='$uid' AND gpid='$gpid' AND mobile='$mobile'";
					$db_result = dba_query($db_query);
					if ($db_row = dba_fetch_array($db_result)) {
						$_SESSION['error_string'] = _('Contact is already exists')." ("._('mobile').": ".$mobile.", "._('name').": ".$db_row['name'].")";
					} else {
						$db_query = "INSERT INTO "._DB_PREF_."_toolsPhonebook (gpid,uid,mobile,name,email) VALUES ('$gpid','$uid','$mobile','$name','$email')";
						$db_result = dba_query($db_query);
						$_SESSION['error_string'] = _('Contact has been added')." ("._('mobile').": ".$mobile.", "._('name').": ".$name.")";
					}
				}
				header("Location: index.php?app=menu&inc=tools_phonebook&op=phonebook_add");
				exit();
				break;
			case 'edit':
				$uid = $core_config['user']['uid'];
				$id = $_POST['id'];
				$gpid = $_POST['gpid'];
				$mobile = str_replace("\'","",$_POST['mobile']);
				$mobile = str_replace("\"","",$mobile);
				$name = str_replace("\'","",$_POST['name']);
				$name = str_replace("\"","",$name);
				$email = str_replace("\'","",$_POST['email']);
				$email = str_replace("\"","",$email);
				$_SESSION['error_string'] = _('You must fill all field');
				if ($id && $gpid && $mobile && $name) {
					$db_query = "UPDATE "._DB_PREF_."_toolsPhonebook SET c_timestamp='".mktime()."',gpid='$gpid',name='$name',mobile='$mobile',email='$email' WHERE id='$id' AND uid='$uid'";
					$db_result = dba_query($db_query);
					$_SESSION['error_string'] = _('Contact has been edited')." ("._('mobile').": ".$mobile.", "._('name').": ".$name.")";
				}
				header("Location: index.php?app=menu&inc=tools_phonebook&op=phonebook_list");
				exit();
				break;
		}
		break;
}

?>