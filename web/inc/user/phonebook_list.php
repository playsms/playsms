<?php
if(!valid()){forcenoaccess();};

$db_query = "SELECT * FROM "._DB_PREF_."_tblUserGroupPhonebook WHERE uid='$uid' ORDER BY gp_name";
$db_result = dba_query($db_query);
while ($db_row = dba_fetch_array($db_result))
{
    $gpid = $db_row['gpid'];
    $fm_name = "fm_phonebook_".$db_row['gp_code'];

    $db_query1 = "SELECT gpidpublic FROM "._DB_PREF_."_tblUserGroupPhonebook_public WHERE uid='$uid' AND gpid='$gpid'";
    $db_result1 = dba_num_rows($db_query1);
    if ($db_result1 > 0)
    {
	$option_public = "<a href=\"menu.php?inc=phonebook&op=hide_from_public&gpid=$gpid\">$icon_publicphonebook</a>";
    }
    else
    {
	$option_public = "<a href=\"menu.php?inc=phonebook&op=share_this_group&gpid=$gpid\">$icon_unpublicphonebook</a>";
    }

    $option_group_edit = "<a href=\"menu.php?inc=dir_edit&op=edit&gpid=$gpid\">$icon_edit</a>";

    $option_group_export = "<a href=\"menu.php?inc=phonebook_exim&op=export&gpid=$gpid\">$icon_export</a>";
    $option_group_import = "<a href=\"menu.php?inc=phonebook_exim&op=import&gpid=$gpid\">$icon_import</a>";

    $list_of_phonenumber .= "
	<form name=\"".strtolower($fm_name).$username."\" action=\"menu.php?inc=phonebook\" method=post>
	<p><a href=\"javascript:ConfirmURL('Are you sure you want to delete group `".$db_row['gp_name']."` with all its members ?','menu.php?inc=phone_del&op=group&gpid=$gpid')\">$icon_delete</a> Group: ".$db_row['gp_name']." - code: ".$db_row['gp_code']." <!-- <a href=\"javascript: PopupSendSms('BC','".$db_row['gp_code']."')\">$icon_sendsms</a> -->$option_public $option_group_edit $option_group_export $option_group_import $option_public
	<table width=100% cellpadding=1 cellspacing=2 border=0 class=\"sortable\">
	<tr>
	    <td class=box_title width=4>&nbsp;*&nbsp;</td>
	    <td class=box_title width=200>Name</td>
	    <td class=box_title width=100>Number</td>
	    <td class=box_title>Email</td>
	    <td class=box_title width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.".strtolower($fm_name).$username.")></td>
	</tr>
    ";
    $db_query1 = "SELECT * FROM "._DB_PREF_."_tblUserPhonebook WHERE gpid='$gpid' AND uid='$uid' ORDER BY p_desc";
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
		<td class=$td_class width=25%>&nbsp;<!-- <a href=\"javascript: PopupSendSms('PV','".$db_row1['p_num']."')\"> --> ".$db_row1['p_num']." <!-- </a> --></td>
		<td class=$td_class width=40%>&nbsp;".$db_row1['p_email']."</td>
		<td class=$td_class width=4>
		    <input type=hidden name=pid".$i." value=\"".$db_row1['pid']."\">
		    <input type=checkbox name=chkid".$i.">
		</td>
	    </tr>
	";
    }
    $option_action = "
	<option value=edit>Edit selections</option>
	<option value=copy>Copy selections</option>
	<option value=move>Move selections</option>
	<option value=delete>Delete selections</option>
    ";
    $item_count = $i;
    $list_of_phonenumber .= "
	</table>
	<table width=100% cellpadding=0 cellspacing=0 border=0>
	<tr>
	    <td width=100% colspan=2 align=right>
	        Select action: <select name=op>$option_action</select> <input type=submit class=button value=\"Go\">
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
	"._DB_PREF_."_tblUserGroupPhonebook.gpid as gpid, 
	"._DB_PREF_."_tblUserGroupPhonebook.gp_name as gp_name,
	"._DB_PREF_."_tblUserGroupPhonebook.gp_code as gp_code,
	"._DB_PREF_."_tblUserGroupPhonebook.uid as uid
    FROM "._DB_PREF_."_tblUserGroupPhonebook,"._DB_PREF_."_tblUserGroupPhonebook_public 
    WHERE 
	"._DB_PREF_."_tblUserGroupPhonebook.gpid="._DB_PREF_."_tblUserGroupPhonebook_public.gpid AND
	NOT ("._DB_PREF_."_tblUserGroupPhonebook.uid = '$uid')
    ORDER BY gp_name
";
$db_result = dba_query($db_query);
while ($db_row = dba_fetch_array($db_result))
{
    $gpid = $db_row['gpid'];
    $fm_name = "fm_phonebook_".$db_row['gp_code'];
    $c_uid = $db_row['uid'];
    $c_username = uid2username($c_uid);
    $list_of_phonenumber .= "
	<p>By: ".$c_username." - group: ".$db_row['gp_name']." - code: ".$db_row['gp_code']." <!-- <a href=\"javascript: PopupSendSms('BC','".$db_row['gp_code']."')\">$icon_sendsms</a> -->
	<table width=100% cellpadding=1 cellspacing=2 border=0 class=\"sortable\">
	<tr>
	    <td class=box_title width=4>&nbsp;*&nbsp;</td>
	    <td class=box_title width=200>Name</td>
	    <td class=box_title width=100>Number</td>
	    <td class=box_title>Email</td>
	</tr>
    ";
    $db_query1 = "SELECT * FROM "._DB_PREF_."_tblUserPhonebook WHERE gpid='$gpid' ORDER BY p_desc";
    $db_result1 = dba_query($db_query1);
    $i = 0;
    while ($db_row1 = dba_fetch_array($db_result1))
    {
	$i++;
        $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";	
	$list_of_phonenumber .= "
	    <tr>
		<td class=$td_class width=4>&nbsp;$i.&nbsp;</td>
		<td class=$td_class width=35%>&nbsp;".$db_row1['p_desc']."</td>
		<td class=$td_class width=25%>&nbsp;<!-- <a href=\"javascript: PopupSendSms('PV','".$db_row1['p_num']."')\"> --> ".$db_row1['p_num']." <!-- </a> --></td>
		<td class=$td_class width=40%>&nbsp;".$db_row1['p_email']."</td>
	    </tr>
	";
    }
    $item_count = $i;
    $list_of_phonenumber .= "
	</table>
	<p>
    ";
}

// ----

$content = "
    <h2>Phonebook</h2>
    <p>
";
if ($err)
{
    $content .= "<p><font color=red>$err</font><p>";
}
$content .= "
    <p>
	<input type=button value=\"Create Group\" onClick=\"javascript:linkto('menu.php?inc=dir_create&op=create')\" class=\"button\" />
	<input type=button value=\"Add Number to Group\" onClick=\"javascript:linkto('menu.php?inc=phone_add&op=add')\" class=\"button\" />
	<input type=button value=\"Export All\" onClick=\"javascript:linkto('menu.php?inc=phonebook_exim&op=export')\" class=\"button\" />
    <p>$list_of_phonenumber
    <p>
	<input type=button value=\"Create Group\" onClick=\"javascript:linkto('menu.php?inc=dir_create&op=create')\" class=\"button\" />
	<input type=button value=\"Add Number to Group\" onClick=\"javascript:linkto('menu.php?inc=phone_add&op=add')\" class=\"button\" />
	<input type=button value=\"Export All\" onClick=\"javascript:linkto('menu.php?inc=phonebook_exim&op=export')\" class=\"button\" />
";

echo $content;

?>