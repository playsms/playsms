<?php
if(!valid()){forcenoaccess();};

switch ($op)
{
    case "add":
	$phone = urlencode($_REQUEST['phone']);
	$db_query = "SELECT * FROM "._DB_PREF_."_tblUserGroupPhonebook WHERE uid='$uid'";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result))
	{
	    $list_of_group .= "<option value=".$db_row['gpid'].">".$db_row['gp_name']." - "._('code').": ".$db_row['gp_code']."</option>";
	}
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>"._('Add number to group')."</h2>
	    <p>
	    <form action=menu.php?inc=phone_add&op=add_yes name=fm_addphone method=POST>
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	    <tr>
		<td width=150>"._('Add number to group')."</td><td width=5>:</td><td><select name=gpid>$list_of_group</select></td>
	    </tr>
	    <tr>
		<td>"._('Name')."</td><td>:</td><td><input type=text name=p_desc size=50></td>
	    </tr>	    
	    <tr>
		<td>"._('Mobile')."</td><td>:</td><td><input type=text name=p_num value=\"$phone\" size=20> ("._('International format').")</td>
	    </tr>	    
	    <tr>
		<td>"._('Email')."</td><td>:</td><td><input type=text name=p_email size=20></td>
	    </tr>	    
	</table>	    
	    <p><input type=submit class=button value=\""._('Add')."\"> 
	    </form>
	";
	echo $content;
	break;
    case "add_yes":
	$gpid = $_POST['gpid'];
	$p_num = str_replace("\'","",$_POST['p_num']);
	$p_num = str_replace("\"","",$p_num);
	$p_desc = str_replace("\'","",$_POST['p_desc']);
	$p_desc = str_replace("\"","",$p_desc);
	$p_email = str_replace("\'","",$_POST['p_email']);
	$p_email = str_replace("\"","",$p_email);
	if ($gpid && $p_num && $p_desc)
	{
	    $db_query = "SELECT p_num,p_desc FROM "._DB_PREF_."_tblUserPhonebook WHERE uid='$uid' AND gpid='$gpid' AND p_num='$p_num'";
	    $db_result = dba_query($db_query);
	    if ($db_row = dba_fetch_array($db_result))
	    {
		header("Location: menu.php?inc=phone_add&op=add&err=".urlencode(_('Number is already exists')." ("._('number').":`$p_num`, "._('name').": `".$db_row['p_desc']."`)"));
		die();
	    }
	    else
	    {
		$db_query = "INSERT INTO "._DB_PREF_."_tblUserPhonebook (gpid,uid,p_num,p_desc,p_email) VALUES ('$gpid','$uid','$p_num','$p_desc','$p_email')";
		$db_result = dba_query($db_query);
		header("Location: menu.php?inc=phone_add&op=add&err=".urlencode(_('Number has been added')." ("._('number').": `$p_num`, "._('name').": `$p_desc`)"));
		die();
	    }
	}
	header("Location: menu.php?inc=phone_add&op=add&err=".urlencode(_('You must fill all field')));
	break;
}

?>