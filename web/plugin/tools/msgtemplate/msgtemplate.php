<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

$gpid = $_REQUEST['gpid'];
$pid = $_REQUEST['pid'];
$tid = $_REQUEST['tid'];

if ($tid = $_REQUEST['tid']) {
	if (! ($tid = dba_valid(_DB_PREF_.'_toolsMsgtemplate', 'tid', $tid))) {
		forcenoaccess();
	}
}

switch ($op) {
	case "list":
		$fm_name = "fm_smstemp";
		$actions_box = "
			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tbody><tr>
				<td>"._button('index.php?app=menu&inc=tools_msgtemplate&op=add', _('Add message template'))."</td>
				<td width=100%>&nbsp;</td>
				<td><input type=submit name=go value=\""._('Delete selection')."\" class=button onClick=\"return SureConfirm()\"/></td>
			</tr></tbody>
			</table>";
		$content = "
			<h2>"._('Message template')."</h2>
			<p>
			".$actions_box."
			<table width=\"100%\" cellpadding=1 cellspacing=2 border=\"0\" class=\"sortable\">
			<form name=\"$fm_name\" action=\"index.php?app=menu&inc=tools_msgtemplate&op=actions\" method=post>
			<thead><tr>
				<th width=\"4\">&nbsp;*</th>
				<th width=\"30%\">&nbsp;"._('Name')."</th>
				<th width=\"70%\">&nbsp;"._('Content')."</th>
				<th class=\"sorttable_nosort\" align=\"center\"><input type=checkbox onclick=CheckUncheckAll(document.".$fm_name.")></th>
			</tr></thead>
			<tbody>";
		$db_query = "SELECT * FROM "._DB_PREF_."_toolsMsgtemplate WHERE uid='$uid'";
		$db_result = dba_query($db_query);
		$i = 0;
		while ($db_row = dba_fetch_array($db_result)) {
			$i++;
			$tid = $db_row['tid'];
			$temp_title = $db_row['t_title'];
			$temp_text = $db_row['t_text'];
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr>
					<td class=\"$td_class\">&nbsp;$i.&nbsp;</td>
					<td class=\"$td_class\">&nbsp;<a href=\"index.php?app=menu&inc=tools_msgtemplate&op=edit&tid=$tid\">$temp_title</a></td>
					<td class=\"$td_class\">&nbsp;$temp_text</td>
					<td class=\"$td_class\" align=\"center\"><input type=hidden name=tid".$i." value=\"".$db_row['tid']."\"><input type=checkbox name=chkid".$i."></td>
					<input type=hidden name=tid".$i." value=\"".$db_row['tid']."\">
				</tr>";
		}
		$content .= "
			</tbody>
			</table>
			<input type=\"hidden\" name=\"item_count\" value=\"$i\">
			".$actions_box."
			</form>";
		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
		echo $content;
		break;
	case "add":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Message template')."</h2>
			<h3>"._('Add message template')."</h3>
			<p>
			<form action=\"index.php?app=menu&inc=tools_msgtemplate&op=actions&go=add\" method=\"post\">
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td width=200>"._('Message template name')."</td><td width=5>:</td><td><input type=\"text\" size=\"60\" maxlength=\"100\" name=\"t_title\"></td>
			</tr>
			<tr>
				<td>"._('Message template content')."</td><td>:</td><td><input type=text name=t_text size=\"60\"></td>
			</tr>	
			</table>	
			<p><input type=\"submit\" class=\"button\" value=\""._('Save')."\">
			</form>
			<p>"._b('index.php?app=menu&inc=tools_msgtemplate&op=list');
			echo $content;
		break;
	case "edit":
		$db_query = "SELECT * FROM "._DB_PREF_."_toolsMsgtemplate WHERE tid='$tid'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Message template')."</h2>
			<h3>"._('Edit message template')."</h3>
			<p>
			<form action=\"index.php?app=menu&inc=tools_msgtemplate&op=actions&go=edit\" method=\"post\">
			<input type=hidden name=tid value=\"$tid\">
			<table width=100% cellpadding=1 cellspacing=2 border=0>
			<tr>
				<td width=200>"._('Message template name')."</td><td width=5>:</td><td><input type=\"text\" size=\"60\" maxlength=\"100\" name=\"t_title\" value=\"".$db_row['t_title']."\"></td>
			</tr>
			<tr>
				<td>"._('Message template content')."</td><td>:</td><td><input type=text name=t_text size=\"60\" value=\"".$db_row['t_text']."\"></td>
			</tr>	
			</table>
			<p><input type=\"submit\" class=\"button\" value=\""._('Save')."\">
			<input type=\"hidden\" name=\"item_count\" value=\"$i\">
			</form>
			<p>"._b('index.php?app=menu&inc=tools_msgtemplate&op=list');
		echo $content;
		break;
	case "actions":
		$go = $_REQUEST['go'];
		switch ($go) {
			case "add":
				$t_title = $_POST['t_title'];
				$t_text = $_POST['t_text'];
				if ($t_title && $t_text) {
					$db_query = "INSERT INTO "._DB_PREF_."_toolsMsgtemplate (uid,t_title,t_text) VALUES ('$uid','$t_title','$t_text')";
					$db_result = dba_insert_id($db_query);
					if ($db_result > 0) {
						$_SESSION['error_string'] = _('Message template has been saved');
					} else {
						$_SESSION['error_string'] = _('Fail to add message template');
					}
				} else {
					$_SESSION['error_string'] = _('You must fill all fields');
				}
				header("Location: index.php?app=menu&inc=tools_msgtemplate&op=add");
				exit();
				break;
			case "edit":
				$t_title = $_POST['t_title'];
				$t_text = $_POST['t_text'];
				if ($t_title && $t_text) {
					$db_query = "UPDATE "._DB_PREF_."_toolsMsgtemplate SET c_timestamp='".mktime()."',t_title='$t_title', t_text='$t_text' WHERE tid='$tid'";
					$db_result = dba_affected_rows($db_query);
					if ($db_result > 0) {
						$_SESSION['error_string'] = _('Message template has been edited');
					} else {
						$_SESSION['error_string'] = _('Fail to edit message template');
					}
				} else {
					$_SESSION['error_string'] = _('You must fill all fields');
				}
				header("Location: index.php?app=menu&inc=tools_msgtemplate&op=list");
				exit();
				break;
			case _('Delete selection'):
				$item_count = $_POST['item_count'];
				for ($i=1;$i<=$item_count;$i++) {
					$chkid[$i] = $_POST['chkid'.$i];
					$tid[$i] = $_POST['tid'.$i];
				}
				for ($i=1;$i<=$item_count;$i++) {
					if (($chkid[$i] == 'on') && $tid[$i]) {
						$db_query = "DELETE FROM "._DB_PREF_."_toolsMsgtemplate WHERE tid='".$tid[$i]."'";
						$db_result = dba_affected_rows($db_query);
					}
				}
				$_SESSION['error_string'] = _('Selected message template has been deleted');
				header("Location: index.php?app=menu&inc=tools_msgtemplate&op=list");
				exit();
				break;
		}
}

?>