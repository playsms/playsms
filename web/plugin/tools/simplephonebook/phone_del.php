<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

$gpid = $_REQUEST['gpid'];
$pid = $_REQUEST['pid'];

switch ($op)
{
    case "group":
	if ($gpid)
	{
	    $db_query = "DELETE FROM "._DB_PREF_."_toolsSimplephonebook_group WHERE gpid='$gpid' AND uid='$uid'";
	    if (@dba_affected_rows($db_query))
	    {
		$db_query = "DELETE FROM "._DB_PREF_."_toolsSimplephonebook WHERE gpid='$gpid' AND uid='$uid'";
		$db_result = dba_query($db_query);
	    }
	}
	header ("Location: menu.php?inc=tools_simplephonebook&op=simplephonebook_list");
	break;
    case "user":
	if ($pid)
	{
	    $db_query = "DELETE FROM "._DB_PREF_."_toolsSimplephonebook WHERE pid='$pid' AND uid='$uid'";
	    $db_result = dba_query($db_query);
	}
	header ("Location: menu.php?inc=tools_simplephonebook&op=simplephonebook_list");
	break;
}

?>