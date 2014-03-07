<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isadmin()){auth_block();};

switch ($op) {
	case "simplerate_list":
		$content .= "
			<h2>"._('Manage SMS rate')."</h2>
			<p>"._button('index.php?app=menu&inc=tools_simplerate&op=simplerate_add', _('Add rate'))."
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead><tr>
				<th width='50%'>"._('Destination')."</th>
				<th width='20%'>"._('Prefix')."</th>
				<th width='20%'>"._('Rate')."</th>
				<th width='10%'>"._('Action')."</th>
			</tr></thead>
			<tbody>";
		$i=0;
		$db_query = "SELECT * FROM "._DB_PREF_."_toolsSimplerate ORDER BY dst";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$action = "<a href=\""._u('index.php?app=menu&inc=tools_simplerate&op=simplerate_edit&rateid='.$db_row['id'])."\">".$icon_config['edit']."</a>";
			$action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete rate ?')." ("._('destination').": ".$db_row['dst'].", "._('prefix').": ".$db_row['prefix'].")','"._u('index.php?app=menu&inc=tools_simplerate&op=simplerate_del&rateid='.$db_row['id'])."')\">".$icon_config['delete']."</a>";
			$i++;
			$content .= "
				<tr>
					<td>".$db_row['dst']."</td>
					<td>".$db_row['prefix']."</td>	
					<td>".$db_row['rate']."</td>	
					<td>$action</td>
				</tr>";
		}
		$content .= "
			</tbody></table>
			</div>
			"._button('index.php?app=menu&inc=tools_simplerate&op=simplerate_add', _('Add rate'));
		if ($err = $_SESSION['error_string']) {
			_p("<div class=error_string>$err</div>");
		}
		_p($content);
		break;
	case "simplerate_del":
		$rateid = $_REQUEST['rateid'];
		$dst = simplerate_getdst($rateid);
		$prefix = simplerate_getprefix($rateid);
		$_SESSION['error_string'] = _('Fail to delete rate')." ("._('destination').": $dst, "._('prefix').": $prefix)";
		$db_query = "DELETE FROM "._DB_PREF_."_toolsSimplerate WHERE id='$rateid'";
		if (@dba_affected_rows($db_query)) {
			$_SESSION['error_string'] = _('Rate has been deleted')." ("._('destination').": $dst, "._('prefix').": $prefix)";
		}
		header("Location: index.php?app=menu&inc=tools_simplerate&op=simplerate_list");
		exit();
		break;
	case "simplerate_edit":
		$rateid = $_REQUEST['rateid'];
		$dst = simplerate_getdst($rateid);
		$prefix = simplerate_getprefix($rateid);
		$rate = simplerate_getbyid($rateid);
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage SMS rate')."</h2>
			<h3>"._('Edit rate')."</h3>
			<form action='index.php?app=menu&inc=tools_simplerate&op=simplerate_edit_save' method='post'>
			"._CSRF_FORM_."
			<input type='hidden' name='rateid' value=\"$rateid\">
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>"._('Destination')."</td><td><input type='text' size=30 maxlength='30' name='up_dst' value=\"$dst\"></td>
			</tr>
			<tr>
				<td>"._('Prefix')."</td><td><input type='text' size='10' maxlength='10' name='up_prefix' value=\"$prefix\"></td>
			</tr>
			<tr>
				<td>"._('Rate')."</td><td><input type='text' size='10' maxlength='10' name='up_rate' value=\"$rate\"></td>
			</tr>
			</table>	
			<p><input type='submit' class='button' value='"._('Save')."'>
			</form>
			<p>"._back('index.php?app=menu&inc=tools_simplerate&op=simplerate_list');
		_p($content);
		break;
	case "simplerate_edit_save":
		$rateid = $_POST['rateid'];
		$up_dst = $_POST['up_dst'];
		$up_prefix = $_POST['up_prefix'];
		$up_prefix = preg_replace('/[^0-9.]*/','',$up_prefix);
		$up_rate = $_POST['up_rate'];
		$_SESSION['error_string'] = _('No changes made!');
		if ($rateid && $up_dst && ($up_prefix >= 0) && ($up_rate >= 0)) {
			$db_query = "UPDATE "._DB_PREF_."_toolsSimplerate SET c_timestamp='".mktime()."',dst='$up_dst',prefix='$up_prefix',rate='$up_rate' WHERE id='$rateid'";
			if (@dba_affected_rows($db_query)) {
				$_SESSION['error_string'] = _('Rate has been saved')." ("._('destination').": $up_dst, "._('prefix').": $up_prefix)";
			} else {
				$_SESSION['error_string'] = _('Fail to save rate')." ("._('destination').": $up_dst, "._('prefix').": $up_prefix)";
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=tools_simplerate&op=simplerate_edit&rateid=$rateid");
		exit();
		break;
	case "simplerate_add":
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage SMS rate')."</h2>
			<h3>"._('Add rate')."</h3>
			<form action='index.php?app=menu&inc=tools_simplerate&op=simplerate_add_yes' method='post'>
			"._CSRF_FORM_."
			<table class=playsms-table>
			<tr>
				<td class=label-sizer>"._('Destination')."</td><td><input type='text' size=30 maxlength='30' name='add_dst' value=\"$add_dst\"></td>
			</tr>
			<tr>
				<td>"._('Prefix')."</td><td><input type='text' size='10' maxlength='10' name='add_prefix' value=\"$add_prefix\"></td>
			</tr>
			<tr>
				<td>"._('Rate')."</td><td><input type='text' size='10' maxlength='10' name='add_rate' value=\"$add_rate\"></td>
			</tr>
			</table>	
			<p><input type='submit' class='button' value='"._('Save')."'>
			</form>
			<p>"._back('index.php?app=menu&inc=tools_simplerate&op=simplerate_list');
		_p($content);
		break;
	case "simplerate_add_yes":
		$add_dst = $_POST['add_dst'];
		$add_prefix = $_POST['add_prefix'];
		$add_prefix = preg_replace('/[^0-9.]*/','',$add_prefix);
		$add_rate = $_POST['add_rate'];
		if ($add_dst && ($add_prefix >= 0) && ($add_rate >= 0)) {
			$db_query = "SELECT * FROM "._DB_PREF_."_toolsSimplerate WHERE prefix='$add_prefix'";
			$db_result = dba_query($db_query);
			if ($db_row = dba_fetch_array($db_result)) {
				$_SESSION['error_string'] = _('Rate is already exists')." ("._('destination').": ".$db_row['dst'].", "._('prefix').": ".$db_row['prefix'].")";
			} else {
				$db_query = "
					INSERT INTO "._DB_PREF_."_toolsSimplerate (dst,prefix,rate)
					VALUES ('$add_dst','$add_prefix','$add_rate')";
				if ($new_uid = @dba_insert_id($db_query)) {
					$_SESSION['error_string'] = _('Rate has been added')." ("._('destination').": $add_dst, "._('prefix').": $add_prefix)";
				}
			}
		} else {
			$_SESSION['error_string'] = _('You must fill all fields');
		}
		header("Location: index.php?app=menu&inc=tools_simplerate&op=simplerate_add");
		exit();
		break;
}

?>