<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

switch ($op) {
	case "list":
		break;
	case "add":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('add group')."</h2>
			<p>
			<form action=index.php?app=menu&inc=tools_phonebook&route=group&op=add_yes method=POST>
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
			<p><input type=submit class=button value=\""._('add')."\"> 
			</form>";
		echo $content;
		break;
	case "add_yes":
		$group_name = $_POST['group_name'];
		$group_code = strtoupper(trim($_POST['group_code']));
		if ($group_name && $group_code) {
			$db_query = "SELECT code FROM "._DB_PREF_."_toolsPhonebook_group WHERE uid='$uid' AND code='$group_code'";
			$db_result = dba_query($db_query);
			if ($db_row = dba_fetch_array($db_result)) {
				$_SESSION['error_string'] = _('Group code is already exists')." ("._('code').": $group_code)";
				header("Location: index.php?app=menu&inc=tools_phonebook&route=group&op=add");
				die();
			} else {
				$db_query = "INSERT INTO "._DB_PREF_."_toolsPhonebook_group (uid,name,code) VALUES ('$uid','$group_name','$group_code')";
				$db_result = dba_query($db_query);
				$_SESSION['error_string'] = _('Group code has been added')." ("._('group').": $group_name, "._('code').": $group_code)";
				header("Location:  index.php?app=menu&inc=tools_phonebook&route=group&op=add");
				die();
			}
		}
		$_SESSION['error_string'] = _('You must fill all field');
		header("Location: index.php?app=menu&inc=tools_phonebook&route=group&op=add");
		exit();
		break;
}

?>