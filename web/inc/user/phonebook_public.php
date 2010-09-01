<?php
if(!valid()){forcenoaccess();};

/*
$db_query = "SELECT * FROM "._DB_PREF_."_tblUserGroupPhonebook WHERE uid='$uid'";
$db_result = dba_query($db_query);
while ($db_row = dba_fetch_array($db_result))
{
    $gpid = $db_row[gpid];
    $list_of_phonenumber .= "<font size=+1>[<a href=\"javascript:ConfirmURL('Are you sure you want to delete group `$db_row[gp_name]` with all its members ?','menu.php?inc=phone_del&op=group&gpid=$gpid')\">x</a>] Group: <font color=darkgreen>$db_row[gp_name]</font> - code: <font color=darkgreen>$db_row[gp_code]</font> [<a href=\"javascript: PopupSendSms('BC','$db_row[gp_code]')\">send</a>]</font><br>\n";
    $db_query1 = "SELECT * FROM "._DB_PREF_."_tblUserPhonebook WHERE gpid='$gpid' AND uid='$uid'";
    $db_result1 = dba_query($db_query1);
    while ($db_row1 = dba_fetch_array($db_result1))
    {
	$list_of_phonenumber .= "[<a href=\"javascript:ConfirmURL('Are you sure you want to delete mobiles number `$db_row1[p_num]` owned by `$db_row1[p_desc]` ?','menu.php?inc=phone_del&op=user&pid=$db_row1[pid]')\">x</a>] <font size=-1>Number: <font color=darkgreen>$db_row1[p_num]</font> - Owner: <font color=darkgreen>$db_row1[p_desc]</font> [<a href=\"javascript: PopupSendSms('PV','$db_row1[p_num]')\">send</a>]<br>\n";
    }
    $list_of_phonenumber .= "<br>";
}
*/

$db_query = "
    SELECT 
	"._DB_PREF_."_tblUserGroupPhonebook.gpid as gpid, 
	"._DB_PREF_."_tblUserGroupPhonebook.gp_name as gp_name,
	"._DB_PREF_."_tblUserGroupPhonebook.gp_code as gp_code
    FROM "._DB_PREF_."_tblUserGroupPhonebook,"._DB_PREF_."_tblUserGroupPhonebook_public 
    WHERE "._DB_PREF_."_tblUserGroupPhonebook.gpid="._DB_PREF_."_tblUserGroupPhonebook_public.gpid
    ORDER BY gp_name
";
$db_result = dba_query($db_query);
while ($db_row = dba_fetch_array($db_result))
{
    $gpid = $db_row[gpid];
    $fm_name = "fm_phonebook_".$db_row[gp_code];
    $list_of_phonenumber .= "
	<form name=\"$fm_name\" action=\"menu.php?inc=phonebook\" method=post>
	<p>Group: $db_row[gp_name] - code: $db_row[gp_code] <!-- <a href=\"javascript: PopupSendSms('BC','$db_row[gp_code]')\">$icon_sendsms</a> -->
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	<tr>
	    <td class=box_title width=4>&nbsp;*&nbsp;</td>
	    <td class=box_title width=35%>Owner</td>
	    <td class=box_title width=25%>Number</td>
	    <td class=box_title width=40%>Email</td>
	</tr>
    ";
    $db_query1 = "SELECT * FROM "._DB_PREF_."_tblUserPhonebook WHERE gpid='$gpid' ORDER BY p_desc";
    $db_result1 = dba_query($db_query1);
    $i = 0;
    while ($db_row1 = dba_fetch_array($db_result1))
    {
	// $list_of_phonenumber .= "[<a href=\"javascript:ConfirmURL('Are you sure you want to delete mobiles number `$db_row1[p_num]` owned by `$db_row1[p_desc]` ?','menu.php?inc=phone_del&op=user&pid=$db_row1[pid]')\">x</a>] <font size=-1>Number: <font color=darkgreen>$db_row1[p_num]</font> - Owner: <font color=darkgreen>$db_row1[p_desc]</font> [<a href=\"javascript: PopupSendSms('PV','$db_row1[p_num]')\">send</a>]<br>\n";
	$i++;
        $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";	
	$list_of_phonenumber .= "
	    <tr>
		<td class=$td_class width=4>&nbsp;$i.&nbsp;</td>
		<td class=$td_class width=35%>&nbsp;$db_row1[p_desc]</td>
		<td class=$td_class width=25%>&nbsp;<!-- <a href=\"javascript: PopupSendSms('PV','$db_row1[p_num]')\"> --> $db_row1[p_num] <!-- </a> --></td>
		<td class=$td_class width=40%>&nbsp;$db_row1[p_email]</td>
	    </tr>
	";
    }
    $option_action = "
	<option value=edit>Edit selections</option>
	<option value=move>Move selections</option>
	<option value=delete>Delete selections</option>
    ";
    $item_count = $i;
    $list_of_phonenumber .= "
	</table>
	<p>
    ";
}

$content = "
    <h2>Public phonebook</h2>
    <p>
";
if ($err)
{
    $content .= "<p><font color=red>$err</font><p>";
}
$content .= "
    <p>$list_of_phonenumber
";

echo $content;

?>