<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isvalid()){auth_block();};

switch ($op) {
	case "phonebook_list":
		$search_category = array(_('Name') => 'A.name', _('Mobile') => 'mobile', _('Email') => 'email', _('Group code') => 'code');
		$base_url = 'index.php?app=menu&inc=tools_phonebook&op=phonebook_list';
		$search = themes_search($search_category, $base_url);
		
		$fields = 'DISTINCT A.id AS pid, A.name AS name, A.mobile AS mobile, A.email AS email';
		$join = 'INNER JOIN '._DB_PREF_.'_toolsPhonebook_group AS B ON A.uid=B.uid ';
		$join .= 'INNER JOIN '._DB_PREF_.'_toolsPhonebook_group_contacts AS C ON A.id=C.pid AND B.id=C.gpid';
		$conditions = array('B.uid' => $core_config['user']['uid']);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_.'_toolsPhonebook AS A', $conditions, $keywords, '', $join);
		$nav = themes_nav($count, $search['url']);
		$extras = array('ORDER BY' => 'A.name, mobile', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$list = dba_search(_DB_PREF_.'_toolsPhonebook AS A', $fields, $conditions, $keywords, $extras, $join);

		$content = "
			<h2>"._('Phonebook')."</h2>
			<p>".$search['form']."</p>
			<form name=fm_phonebook_list id=fm_phonebook_list action='index.php?app=menu&inc=tools_phonebook&op=actions' method=post>
			"._CSRF_FORM_."
			<input type=hidden name=go value=delete>
			<div class=actions_box>
				<div class=pull-left>
					<a href='index.php?app=menu&inc=tools_phonebook&route=group&op=list'>".$core_config['icon']['group']."</a>
					<a href='index.php?app=menu&inc=tools_phonebook&route=import&op=list'>".$core_config['icon']['import']."</a>
					<a href='index.php?app=menu&inc=tools_phonebook&op=actions&go=export'>".$core_config['icon']['export']."</a>
					<a href='index.php?app=menu&inc=tools_phonebook&op=phonebook_add'>".$core_config['icon']['add']."</a>
				</div>
				<div class=pull-right>
					<a href='#' onClick=\"return SubmitConfirm('" . _('Are you sure you want to delete these items ?') . "', 'fm_phonebook_list');\">" . $core_config['icon']['delete'] . "</a>						
				</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=25%>"._('Name')."</th>
				<th width=25%>"._('Mobile')."</th>
				<th width=30%>"._('Email')."</th>
				<th width=15%>"._('Group code')."</th>
				<th width=5%><input type=checkbox onclick=CheckUncheckAll(document.fm_phonebook_list)></th>
			</tr>
			</thead>
			<tbody>";

		$i = $nav['top'];
		$j = 0;
		for ($j=0;$j<count($list);$j++) {
			$pid = $list[$j]['pid'];
			$name = $list[$j]['name'];
			$mobile = $list[$j]['mobile'];
			$email = $list[$j]['email'];
			$group_code = "";
			$groupfields = 'B.id AS id, B.code AS code';
			$groupconditions = array('B.uid' => $core_config['user']['uid'], 'C.pid' => $list[$j]['pid']);
			$groupextras = array('ORDER BY' => 'B.code ASC', 'LIMIT' => $nav['limit']);
			$groupjoin = 'INNER JOIN '._DB_PREF_.'_toolsPhonebook_group_contacts AS C ON C.gpid = B.id';
			$grouplist = dba_search(_DB_PREF_.'_toolsPhonebook_group AS B', $groupfields, $groupconditions, '', $groupextras, $groupjoin);
			for ($k=0;$k<count($grouplist);$k++) {
				$group_code .= "<a href=\"index.php?app=menu&inc=tools_phonebook&route=group&op=edit&gpid=".$grouplist[$k]['id']."\">".strtoupper($grouplist[$k]['code'])."</a>&nbsp;";
			}
			$i--;
			$c_i = "<a href=\"index.php?app=menu&inc=tools_phonebook&op=phonebook_edit&id=".$pid."\">".$i.".</a>";
			$content .= "
				<tr>
					<td><a href='index.php?app=menu&inc=tools_phonebook&op=phonebook_edit&pid=".$pid."'>$name</a></td>
					<td>$mobile</td>
					<td>$email</td>
					<td>$group_code</td>
					<td>
						<input type=hidden name=itemid".$j." value=\"$pid\">
						<input type=checkbox name=checkid".$j.">
					</td>
				</tr>";
		}

		$content .= "
			</tbody>
			</table>
			</div>
			<div class=pull-right>".$nav['form']."</div>
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
			<form action=\"index.php?app=menu&inc=tools_phonebook&op=actions&go=add\" name=fm_addphone method=POST>
			"._CSRF_FORM_."
			<table class=playsms-table>
			<tbody>
			<tr><td class=label-sizer>"._('Group')."</td><td><select name=gpids[] multiple>$list_of_group</select></td></tr>
			<tr><td>"._('Name')."</td><td><input type=text name=name size=30></td></tr>
			<tr><td>"._('Mobile')."</td><td><input type=text name=mobile value=\"".$phone."\" size=30></td></tr>
			<tr><td>"._('Email')."</td><td><input type=text name=email size=30></td></tr>
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
		$pid = $_REQUEST['pid'];
		$list = dba_search(_DB_PREF_.'_toolsPhonebook', 'name, mobile, email', array('id' => $pid, 'uid' => $uid));
		$db_query = "SELECT * FROM "._DB_PREF_."_toolsPhonebook_group WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$selected = '';
			$conditions = array('gpid' => $db_row['id'], 'pid' => $pid);
			if (dba_isexists(_DB_PREF_.'_toolsPhonebook_group_contacts', $conditions, 'AND')) {
				$selected = 'selected';
			}
			$list_of_group .= "<option value=".$db_row['id']." $selected>".$db_row['name']." - "._('code').": ".$db_row['code']."</option>";
		}
		$content = "
			<h2>"._('Phonebook')."</h2>
			<h3>"._('Edit contact')."</h3>
			<form action=\"index.php?app=menu&inc=tools_phonebook&op=actions&go=edit\" name=fm_addphone method=POST>
			"._CSRF_FORM_."
			<input type=hidden name=pid value=\"".$pid."\">
			<table class=playsms-table>
			<tbody>
			<tr><td width=100>"._('Group')."</td><td><select name=gpids[] multiple>$list_of_group</select></td></tr>
			<tr><td>"._('Name')."</td><td><input type=text name=name value=\"".$list[0]['name']."\" size=30></td></tr>
			<tr><td>"._('Mobile')."</td><td><input type=text name=mobile value=\"".$list[0]['mobile']."\" size=30></td></tr>
			<tr><td>"._('Email')."</td><td><input type=text name=email value=\"".$list[0]['email']."\" size=30></td></tr>
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
			case 'export':
				$fields = 'A.id AS pid, A.name AS name, A.mobile AS mobile, A.email AS email, B.code AS code';
				$join = 'INNER JOIN '._DB_PREF_.'_toolsPhonebook_group AS B ON A.uid=B.uid ';
				$join .= 'INNER JOIN '._DB_PREF_.'_toolsPhonebook_group_contacts AS C ON A.id = C.pid AND C.gpid = B.id';
				$conditions = array('B.uid' => $core_config['user']['uid'], 'C.gpid=B.id');
				$keywords = $search['dba_keywords'];
				$extras = array('ORDER BY' => 'A.name, mobile', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
				$list = dba_search(_DB_PREF_.'_toolsPhonebook AS A', $fields, $conditions, $keywords, $extras, $join);
				$data[0] = array(_('Name'), _('Mobile'), _('Email'), _('Group code'));
				for ($i=0;$i<count($list);$i++) {
					$j = $i + 1;
					$data[$j] = array(
						$list[$i]['name'],
						$list[$i]['mobile'],
						$list[$i]['email'],
						$list[$i]['code']);
				}
				$content = core_csv_format($data);
				$fn = 'phonebook-'.$core_config['datetime']['now_stamp'].'.csv';
				core_download($content, $fn, 'text/csv');
				break;
			case 'delete':
				for ($i=0;$i<$nav['limit'];$i++) {
					$checkid = $_POST['checkid'.$i];
					$itemid = $_POST['itemid'.$i];
					if(($checkid=="on") && $itemid) {
						dba_remove(_DB_PREF_.'_toolsPhonebook', array('id' => $itemid));
						dba_remove(_DB_PREF_.'_toolsPhonebook_group_contacts', array('pid' => $itemid));
					}
				}
				$ref = $nav['url'].'&search_keyword='.$search['keyword'].'&search_category='.$search['category'].'&page='.$nav['page'].'&nav='.$nav['nav'];
				$_SESSION['error_string'] = _('Selected contact has been deleted');
				header("Location: ".$ref);
				exit();
				break;
			case 'add':
				$uid = $core_config['user']['uid'];
				$gpids = $_POST['gpids'];
				if (is_array($gpids)) {
					foreach ($gpids as $gpid) {
						$save_to_group = FALSE;
						$mobile = str_replace("\'","",$_POST['mobile']);
						$mobile = str_replace("\"","",$mobile);
						$name = str_replace("\'","",$_POST['name']);
						$name = str_replace("\"","",$name);
						$email = str_replace("\'","",$_POST['email']);
						$email = str_replace("\"","",$email);
						if ($gpid && $mobile && $name) {
							$list = dba_search(_DB_PREF_.'_toolsPhonebook', 'id', array('uid' => $uid, 'mobile' => $mobile));
							if ($c_pid = $list[0]['id']) {
								$save_to_group = TRUE;
							} else {
								$items = array('uid' => $uid, 'name' => $name, 'mobile' => $mobile, 'email' => $email);
								if ($c_pid = dba_add(_DB_PREF_.'_toolsPhonebook', $items)) {
									$save_to_group = TRUE;
								} else {
									logger_print('fail to add contact gpid:'.$gpid.' pid:'.$c_pid.' m:'.$mobile.' n:'.$name.' e:'.$email, 3, 'phonebook_add');
								}
							}
							if ($save_to_group) {
								$items = array('gpid' => $gpid, 'pid' => $c_pid);
								if (dba_isavail(_DB_PREF_.'_toolsPhonebook_group_contacts', $items, 'AND')) {
									if (dba_add(_DB_PREF_.'_toolsPhonebook_group_contacts', $items)) {
										logger_print('contact added to group gpid:'.$gpid.' pid:'.$c_pid.' m:'.$mobile.' n:'.$name.' e:'.$email, 3, 'phonebook_add');
									} else {
										logger_print('contact added but fail to save in group gpid:'.$gpid.' pid:'.$c_pid.' m:'.$mobile.' n:'.$name.' e:'.$email, 3, 'phonebook_add');
									}
								}
							}
						}
					}
					$_SESSION['error_string'] = _('Contact has been added');
				} else {
					$_SESSION['error_string'] = _('You must fill required fields');
				}
				header("Location: index.php?app=menu&inc=tools_phonebook&op=phonebook_add");
				exit();
				break;
			case 'edit':
				$uid = $core_config['user']['uid'];
				$c_pid = $_POST['pid'];
				$gpids = $_POST['gpids'];
				if (is_array($gpids)) {
					$maps = '';
					foreach($gpids as $gpid) {
						$save_to_group = FALSE;
						$mobile = str_replace("\'","",$_POST['mobile']);
						$mobile = str_replace("\"","",$mobile);
						$name = str_replace("\'","",$_POST['name']);
						$name = str_replace("\"","",$name);
						$email = str_replace("\'","",$_POST['email']);
						$email = str_replace("\"","",$email);
						$_SESSION['error_string'] = _('You must fill mandatory fields');
						if ($c_pid && $gpid && $mobile && $name) {
							$items = array('name' => $name, 'mobile' => $mobile, 'email' => $email);
							$conditions = array('id' => $c_pid, 'uid' => $uid);
							dba_update(_DB_PREF_.'_toolsPhonebook', $items, $conditions, 'AND');
							$maps[][$c_pid] = $gpid;
							logger_print('contact edited gpid:'.$gpid.' pid:'.$c_pid.' m:'.$mobile.' n:'.$name.' e:'.$email, 3, 'phonebook_edit');
						}
					}
					if (is_array($maps)) {
						dba_remove(_DB_PREF_.'_toolsPhonebook_group_contacts', array('pid' => $c_pid));
						foreach ($maps as $map) {
							foreach ($map as $key => $val) {
								$gpid = $val;
								$c_pid = $key;
								$items = array('gpid' => $gpid, 'pid' => $c_pid);
								if (dba_isavail(_DB_PREF_.'_toolsPhonebook_group_contacts', $items, 'AND')) {
									if (dba_add(_DB_PREF_.'_toolsPhonebook_group_contacts', $items)) {
										logger_print('contact added to group gpid:'.$gpid.' pid:'.$c_pid.' m:'.$mobile.' n:'.$name.' e:'.$email, 3, 'phonebook_edit');
									} else {
										logger_print('contact edited but fail to save in group gpid:'.$gpid.' pid:'.$c_pid.' m:'.$mobile.' n:'.$name.' e:'.$email, 3, 'phonebook_edit');
									}
								}
							}
						}
					}
					$_SESSION['error_string'] = _('Contact has been edited');
				} else {
					$_SESSION['error_string'] = _('You must fill required fields');
				}
				header("Location: index.php?app=menu&inc=tools_phonebook&op=phonebook_list");
				exit();
				break;
		}
		break;
}
