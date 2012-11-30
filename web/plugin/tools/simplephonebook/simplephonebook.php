<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

if ($route = $_REQUEST['route']) {
	$fn = $apps_path['plug'].'/tools/simplephonebook/'.$route.'.php';
	if (file_exists($fn)) {
		include $fn;
		exit();
	}
}

$db_query = "SELECT * FROM "._DB_PREF_."_toolsSimplephonebook_group WHERE uid='$uid' ORDER BY gp_name";
$db_result = dba_query($db_query);
while ($db_row = dba_fetch_array($db_result))
{
	$gpid = $db_row['gpid'];
	$fm_name = "fm_phonebook_".$db_row['gp_code'];

	// published should show icon unpublish and the other way around (emmanuel)
	$db_query1 = "SELECT gpidpublic FROM "._DB_PREF_."_toolsSimplephonebook_group_public WHERE uid='$uid' AND gpid='$gpid'";
	$db_result1 = dba_num_rows($db_query1);
	if ($db_result1 > 0)
	{
		$option_public = "<a href=\"index.php?app=menu&inc=tools_simplephonebook&route=phonebook&op=hide_from_public&gpid=$gpid\">$simplephonebook_icon_unpublish</a>";
	}
	else
	{
		$option_public = "<a href=\"index.php?app=menu&inc=tools_simplephonebook&route=phonebook&op=share_this_group&gpid=$gpid\">$simplephonebook_icon_publish</a>";
	}

	$option_group_edit = "<a href=\"index.php?app=menu&inc=tools_simplephonebook&route=dir_edit&op=edit&gpid=$gpid\">$icon_edit</a>";

	$option_group_export = "<a href=\"index.php?app=menu&inc=tools_simplephonebook&route=phonebook_exim&op=export&gpid=$gpid\">$simplephonebook_icon_export</a>";
	$option_group_import = "<a href=\"index.php?app=menu&inc=tools_simplephonebook&route=phonebook_exim&op=import&gpid=$gpid\">$simplephonebook_icon_import</a>";

	$list_of_phonenumber .= "
	<form name=\"".strtolower($fm_name).$username."\" action=\"index.php?app=menu&inc=tools_simplephonebook&route=phonebook\" method=post>
	<p><a href=\"javascript:ConfirmURL('Are you sure you want to delete group ".$db_row['gp_name']." with all its members ?','index.php?app=menu&inc=tools_simplephonebook&route=phone_del&op=group&gpid=$gpid')\">$icon_delete</a> Group: ".$db_row['gp_name']." - code: ".$db_row['gp_code']." <!-- <a href=\"javascript: PopupSendSms('BC','".$db_row['gp_code']."')\">$icon_sendsms</a> -->$option_public $option_group_edit $option_group_export $option_group_import
	<table width=100% cellpadding=1 cellspacing=2 border=0 class=\"sortable\">
	<tr>
	    <td class=box_title width=4>&nbsp;*&nbsp;</td>
	    <td class=box_title width=200>"._('Name')."</td>
	    <td class=box_title width=100>"._('Number')."</td>
	    <td class=box_title>"._('Email')."</td>
	    <td class=box_title width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.".strtolower($fm_name).$username.")></td>
	</tr>
    ";
	$db_query1 = "SELECT * FROM "._DB_PREF_."_toolsSimplephonebook WHERE gpid='$gpid' AND uid='$uid' ORDER BY p_desc";
	$db_result1 = dba_query($db_query1);
	$i = 0;
	while ($db_row1 = dba_fetch_array($db_result1))
	{
		$i++;
		$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
		$list_of_phonenumber .= "
	    <tr>
		<td width=4 class=$td_class>&nbsp;$i.&nbsp;</td>
		<td class=$td_class width=35%>&nbsp;".$db_row1['p_desc']."</td>
		<td class=$td_class width=40%>&nbsp;".$db_row1['p_email']."</td>
		<td class=$td_class width=4>
		    <input type=hidden name=pid".$i." value=\"".$db_row1['pid']."\">
		    <input type=checkbox name=chkid".$i.">
		</td>
	    </tr>
	";
	}
	$option_action = "
	<option value=edit>"._('Edit selections')."</option>
	<option value=copy>"._('Copy selections')."</option>
	<option value=move>"._('Move selections')."</option>
	<option value=delete>"._('Delete selections')."</option>
    ";
	$item_count = $i;
	$list_of_phonenumber .= "
	</table>
	<table width=100% cellpadding=0 cellspacing=0 border=0>
	<tr>
	    <td width=100% colspan=2 align=right>
	        "._('Select action').": <select name=op>$option_action</select> <input type=submit class=button value=\""._('Go')."\">
	    </td>
	</tr>
	</table>
	<input type=hidden name=item_count value=\"$item_count\">	
	</form>
	<p>
    ";
}

// ----

$db_query = "
    SELECT 
	"._DB_PREF_."_toolsSimplephonebook_group.gpid as gpid, 
	"._DB_PREF_."_toolsSimplephonebook_group.gp_name as gp_name,
	"._DB_PREF_."_toolsSimplephonebook_group.gp_code as gp_code,
	"._DB_PREF_."_toolsSimplephonebook_group.uid as uid
    FROM "._DB_PREF_."_toolsSimplephonebook_group,"._DB_PREF_."_toolsSimplephonebook_group_public 
    WHERE 
	"._DB_PREF_."_toolsSimplephonebook_group.gpid="._DB_PREF_."_toolsSimplephonebook_group_public.gpid AND
	NOT ("._DB_PREF_."_toolsSimplephonebook_group.uid = '$uid')
    ORDER BY gp_name
";
$db_result = dba_query($db_query);
while ($db_row = dba_fetch_array($db_result)) {
	$fm_name = "fm_phonebook_".$db_row['gp_code'];
	$gpid = $db_row['gpid'];
	$c_count = phonebook_getmembercountbyid($gpid);
	$c_uid = $db_row['uid'];
	if ($c_count && ($c_username = uid2username($c_uid))) {
		$list_of_phonenumber .= "
			<p>"._('Shared by').": ".$c_username." - group: ".$db_row['gp_name']." - code: ".$db_row['gp_code']." <!-- <a href=\"javascript: PopupSendSms('BC','".$db_row['gp_code']."')\">$icon_sendsms</a> -->
			<table width=100% cellpadding=1 cellspacing=2 border=0 class=\"sortable\">
			<tr>
				<td class=box_title width=4>&nbsp;*&nbsp;</td>
				<td class=box_title width=200>"._('Name')."</td>
				<td class=box_title width=100>"._('Number')."</td>
				<td class=box_title>"._('Email')."</td>
			</tr>";
		$db_query1 = "SELECT * FROM "._DB_PREF_."_toolsSimplephonebook WHERE gpid='$gpid' ORDER BY p_desc";
		$db_result1 = dba_query($db_query1);
		$i = 0;
		while ($db_row1 = dba_fetch_array($db_result1)) {
			$i++;
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$list_of_phonenumber .= "
				<tr>
					<td class=$td_class width=4>&nbsp;$i.&nbsp;</td>
					<td class=$td_class width=35%>&nbsp;".$db_row1['p_desc']."</td>
					<td class=$td_class width=40%>&nbsp;".$db_row1['p_email']."</td>
				</tr>";
		}
		$item_count = $i;
		$list_of_phonenumber .= "
			</table>
			<p>";
	}
}

// ----

if ($err = $_SESSION['error_string']) {
	$content = "<div class=error_string>$err</div>";
}
$content .= "
    <h2>"._('Phonebook')."</h2>
    <p>
";
$content .= "
    <p>
	<input type=button value=\""._('Create group')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=tools_simplephonebook&route=dir_create&op=create')\" class=\"button\" />
	<input type=button value=\""._('Add number to group')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=tools_simplephonebook&route=phone_add&op=add')\" class=\"button\" />
	<input type=button value=\""._('Export all')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=tools_simplephonebook&route=phonebook_exim&op=export')\" class=\"button\" />
    <p>$list_of_phonenumber
    <p>
	<input type=button value=\""._('Create group')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=tools_simplephonebook&route=dir_create&op=create')\" class=\"button\" />
	<input type=button value=\""._('Add number to group')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=tools_simplephonebook&route=phone_add&op=add')\" class=\"button\" />
	<input type=button value=\""._('Export all')."\" onClick=\"javascript:linkto('index.php?app=menu&inc=tools_simplephonebook&route=phonebook_exim&op=export')\" class=\"button\" />
";

echo $content;

?>