<?php
if(!valid()){forcenoaccess();};

switch ($op)
{
    case "sms_autoreply_list":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>"._('Manage autoreply')."</h2>
	    <p>
	    <input type=button value=\""._('Add SMS autoreply')."\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_autoreply&op=sms_autoreply_add')\" class=\"button\" />
	    <p>
	";
	if (!isadmin())
	{
	    $query_user_only = "WHERE uid='$uid'";
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply $query_user_only ORDER BY autoreply_keyword";
	$content .= "
    <table cellpadding=1 cellspacing=2 border=0 width=100%>
    <tr>
        <td class=box_title width=25>*</td>
        <td class=box_title width=100>"._('Keyword')."</td>
        <td class=box_title>"._('User')."</td>
        <td class=box_title width=75>"._('Action')."</td>
    </tr>	
	";
	$db_result = dba_query($db_query);
	$i=0;
	while ($db_row = dba_fetch_array($db_result))
	{
	    $i++;
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
	    $owner = uid2username($db_row['uid']);
	    $action = "<a href=menu.php?inc=feature_sms_autoreply&op=sms_autoreply_manage&autoreply_id=".$db_row['autoreply_id'].">$icon_manage</a>&nbsp;";
	    $action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete SMS autoreply ?')." ("._('keyword').": `".$db_row['autoreply_keyword']."`)','menu.php?inc=feature_sms_autoreply&op=sms_autoreply_del&autoreply_id=".$db_row['autoreply_id']."')\">$icon_delete</a>";
	    $content .= "
    <tr>
	<td class=$td_class>&nbsp;$i.</td>
	<td class=$td_class>".$db_row['autoreply_keyword']."</td>
	<td class=$td_class>$owner</td>
	<td class=$td_class align=center>$action</td>
    </tr>
";
	}
	
	$content .= "
    </table>
	";
	echo $content;
	echo "
	    <p>
	    <input type=button value=\""._('Add SMS autoreply')."\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_autoreply&op=sms_autoreply_add')\" class=\"button\" />
	";
	break;
    case "sms_autoreply_manage":
	$autoreply_id = $_REQUEST['autoreply_id'];
	if (!isadmin())
	{
	    $query_user_only = "AND uid='$uid'";
	}
	$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_id='$autoreply_id' $query_user_only";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$manage_autoreply_keyword = $db_row['autoreply_keyword'];
	$o_uid = $db_row['uid'];
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>"._('Manage autoreply')."</h2>
	    <p>
	    <p>"._('SMS autoreply keyword').": <b>$manage_autoreply_keyword</b>
	    <p>
	    <input type=button value=\""._('Add SMS autoreply scenario')."\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_autoreply&op=sms_autoreply_scenario_add&autoreply_id=$autoreply_id')\" class=\"button\" />
	    <p>
    <table cellpadding=1 cellspacing=2 border=0 width=100%>
    <tr>
        <td class=box_title width=25>*</td>
        <td class=box_title width=100>"._('Param')."</td>
        <td class=box_title>"._('Return')."</td>
        <td class=box_title width=100>"._('User')."</td>	
        <td class=box_title width=75>"._('Action')."</td>
    </tr>		    
	";
	$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply_scenario WHERE autoreply_id='$autoreply_id' ORDER BY autoreply_scenario_param1";
	$db_result = dba_query($db_query);
	$j=0;
	while ($db_row = dba_fetch_array($db_result))
	{
	    $j++;
	    $owner = uid2username($o_uid);
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
	    $list_of_param = "";
	    for ($i=1;$i<=7;$i++)
	    { 
		$list_of_param .= $db_row['autoreply_scenario_param$i']."&nbsp;";
	    }
	    $action = "<a href=menu.php?inc=feature_sms_autoreply&op=sms_autoreply_scenario_edit&autoreply_id=$autoreply_id&autoreply_scenario_id=".$db_row['autoreply_scenario_id'].">$icon_edit</a>";
	    $action .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete this SMS autoreply scenario ?')."','menu.php?inc=feature_sms_autoreply&op=sms_autoreply_scenario_del&autoreply_scenario_id=".$db_row['autoreply_scenario_id']."')\">$icon_delete</a>";
	    $content .= "
    <tr>
	<td class=$td_class>&nbsp;$j.</td>
	<td class=$td_class>$list_of_param</td>
	<td class=$td_class>".$db_row['autoreply_scenario_result']."</td>
	<td class=$td_class>$owner</td>
	<td class=$td_class align=center>$action</td>
    </tr>
	";
	}
	$content .= "
    </table>
	    <p>
	    <input type=button value=\""._('Add SMS autoreply scenario')."\" onClick=\"javascript:linkto('menu.php?inc=feature_sms_autoreply&op=sms_autoreply_scenario_add&autoreply_id=$autoreply_id')\" class=\"button\" />
	    </form>
	";
	echo $content;
	break;
    case "sms_autoreply_del":
	$autoreply_id = $_REQUEST['autoreply_id'];
	$db_query = "SELECT autoreply_keyword FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_id='$autoreply_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$keyword_name = $db_row['autoreply_keyword'];
	if ($keyword_name)
	{
	    $db_query = "DELETE FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_keyword='$keyword_name'";
	    if (@dba_affected_rows($db_query))
	    {
		$error_string = _('SMS autoreply has been deleted')." ("._('keyword')." `$keyword_name`)";
	    }
	    else
	    {
		$error_string = _('Fail to delete SMS autoreply')." ("._('keyword')." `$keyword_name`";
	    }
	}
	header ("Location: menu.php?inc=feature_sms_autoreply&op=sms_autoreply_list&err=".urlencode($error_string));
	break;
    case "sms_autoreply_add":
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>"._('Add SMS autoreply')."</h2>
	    <p>
	    <form action=menu.php?inc=feature_sms_autoreply&op=sms_autoreply_add_yes method=post>
	    <p>"._('SMS autoreply keyword').": <input type=text size=10 maxlength=10 name=add_autoreply_keyword value=\"$add_autoreply_keyword\">
	    <p><input type=submit class=button value="._('Add').">
	    </form>
	";
	echo $content;
	break;
    case "sms_autoreply_add_yes":
	$add_autoreply_keyword = strtoupper($_POST['add_autoreply_keyword']);
	if ($add_autoreply_keyword)
	{
	    if (checkavailablekeyword($add_autoreply_keyword))
	    {
		$db_query = "INSERT INTO "._DB_PREF_."_featureAutoreply (uid,autoreply_keyword) VALUES ('$uid','$add_autoreply_keyword')";
		if ($new_uid = @dba_insert_id($db_query))
		{
	    	    $error_string = _('SMS autoreply keyword has been added')." ("._('keyword').": `$add_autoreply_keyword`)";
		}
		else
		{
	    	    $error_string = _('Fail to add SMS autoreply')." ("._('keyword').": `$add_autoreply_keyword`)";
		}
	    }
	    else
	    {
		$error_string = _('SMS keyword already exists, reserved or use by other feature')." ("._('keyword').": `$add_autoreply_keyword`)";
	    }
	}
	else
	{
	    $error_string = _('You must fill all fields');
	}
	header ("Location: menu.php?inc=feature_sms_autoreply&op=sms_autoreply_add&err=".urlencode($error_string));
	break;
	
    // scenario
    case "sms_autoreply_scenario_del":
	$autoreply_scenario_id = $_REQUEST['autoreply_scenario_id'];
	$autoreply_id = $_REQUEST['autoreply_id'];
	$db_query = "SELECT autoreply_scenario_keyword FROM "._DB_PREF_."_featureAutoreply_scenario WHERE autoreply_scenario_id='$autoreply_scenario_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$keyword_name = $db_row['autoreply_scenario_keyword'];
	if ($keyword_name)
	{
	    $db_query = "DELETE FROM "._DB_PREF_."_featureAutoreply_scenario WHERE autoreply_id='$autoreply_id' AND autoreply_scenario_id='$autoreply_scenario_id'";
	    if (@dba_affected_rows($db_query))
	    {
		$error_string = _('SMS autoreply scenario has been deleted')." ("._('keyword')." `$keyword_name`)";
	    }
	    else
	    {
		$error_string = _('Fail to delete SMS autoreply scenario')." ("._('keyword')." `$keyword_name`)";
	    }
	}
	header ("Location: menu.php?inc=feature_sms_autoreply&op=sms_autoreply_scenario_list&err=".urlencode($error_string));
	break;
    case "sms_autoreply_scenario_add":
	$autoreply_id = $_REQUEST['autoreply_id'];
	$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_id='$autoreply_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$autoreply_keyword = $db_row['autoreply_keyword'];
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>"._('Add SMS autoreply scenario')."</h2>
	    <p>
	    <p>"._('SMS autoreply keyword').": <b>$autoreply_keyword</b>
	    <p>
	    <form action=menu.php?inc=feature_sms_autoreply&op=sms_autoreply_scenario_add_yes method=post>
	    <input type=hidden name=autoreply_id value=\"$autoreply_id\">
	<table width=100% cellpadding=1 cellspacing=2 border=0>";
	
	for ($i=1;$i<=7;$i++)
	{
	    $content .= "
	    <tr>
		<td width=190>"._('SMS autoreply scenario parameter')." $i</td><td>:</td><td><input type=text size=20 maxlength=20 name=add_autoreply_scenario_param$i value=\"".${"add_autoreply_scenario_param".$i}."\">\n</td>
	    </tr>";
	}
	$content .= "
	    <tr>
		<td>"._('SMS autoreply scenario replies with')."</td><td>:</td><td><input type=text size=60 name=add_autoreply_scenario_result value=\"$add_autoreply_scenario_result\"></td>
	    </tr>	    
	</table>
	    <p><input type=submit class=button value="._('Add').">
	    <p><input type=button class=button value="._('Back')." onClick=javascript:linkto('menu.php?inc=feature_sms_autoreply&op=sms_autoreply_manage&autoreply_id=$autoreply_id')>
	    </form>
	";
	echo $content;
	break;
    case "sms_autoreply_scenario_add_yes":
	$autoreply_id = $_POST['autoreply_id'];
	$add_autoreply_scenario_result = $_POST['add_autoreply_scenario_result'];
	for ($i=1;$i<=7;$i++)
	{
	    ${"add_autoreply_scenario_param".$i} = strtoupper($_POST['add_autoreply_scenario_param$i']);
	}
	if ($add_autoreply_scenario_result)
	{
	    for ($i=1;$i<=7;$i++)
	    {
		$autoreply_scenario_param_list .= "autoreply_scenario_param$i,";
	    }
	    for ($i=1;$i<=7;$i++)
	    {
		$autoreply_scenario_keyword_param_entry .= "'".${"add_autoreply_scenario_param".$i}."',";
	    }
	    $db_query = "
		INSERT INTO "._DB_PREF_."_featureAutoreply_scenario 
		(autoreply_id,".$autoreply_scenario_param_list."autoreply_scenario_result) VALUES ('$autoreply_id',$autoreply_scenario_keyword_param_entry'$add_autoreply_scenario_result')";
	    if ($new_uid = dba_insert_id($db_query))
	    {
		$error_string = _('SMS autoreply scenario has been added');
	    }
	    else
	    {
	        $error_string = _('Fail to add SMS autoreply scenario');
	    }
	}
	else
	{
	    $error_string = _('You must fill all fields');
	}
	header ("Location: menu.php?inc=feature_sms_autoreply&op=sms_autoreply_scenario_add&autoreply_id=$autoreply_id&err=".urlencode($error_string));
	break;
    case "sms_autoreply_scenario_edit":
	$autoreply_scenario_id = $_REQUEST['autoreply_scenario_id'];
	$autoreply_id = $_REQUEST['autoreply_id'];
	$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply WHERE autoreply_id='$autoreply_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$autoreply_keyword = $db_row['autoreply_keyword'];
	if ($err)
	{
	    $content = "<p><font color=red>$err</font><p>";
	}
	$content .= "
	    <h2>"._('Edit SMS autoreply scenario')."</h2>
	    <p>
	    <p>"._('SMS autoreply keyword').": <b>$autoreply_keyword</b>
	    <p>
	    <form action=menu.php?inc=feature_sms_autoreply&op=sms_autoreply_scenario_edit_yes method=post>
	    <input type=hidden name=autoreply_id value=\"$autoreply_id\">
	    <input type=hidden name=autoreply_scenario_id value=\"$autoreply_scenario_id\">
	<table width=100% cellpadding=1 cellspacing=2 border=0>
	";
	$db_query = "SELECT * FROM "._DB_PREF_."_featureAutoreply_scenario WHERE autoreply_id='$autoreply_id' AND autoreply_scenario_id='$autoreply_scenario_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	for ($i=1;$i<=7;$i++)
	{
	    ${"edit_autoreply_scenario_param".$i} = $db_row['autoreply_scenario_param$i'];
	}
	for ($i=1;$i<=7;$i++)
	{
	    $content .= "
	    <tr>
		<td width=190>"._('SMS autoreply scenario parameter')." $i</td><td>:</td><td><input type=text size=20 maxlength=20 name=edit_autoreply_scenario_param$i value=\"".${"edit_autoreply_scenario_param".$i}."\">\n</td>
	    </tr>";
	}
	$edit_autoreply_scenario_result = $db_row['autoreply_scenario_result'];
	$content .= "
	    <tr>
		<td>"._('SMS autoreply scenario replies with')."</td><td>:</td><td><input type=text size=60 name=edit_autoreply_scenario_result value=\"$edit_autoreply_scenario_result\"></td>
	    </tr>	    	
	    </table>
	    <p><input type=submit class=button value=\""._('Save')."\">
	    <p><input type=button class=button value="._('Back')." onClick=javascript:linkto('menu.php?inc=feature_sms_autoreply&op=sms_autoreply_manage&autoreply_id=$autoreply_id')>
	    </form>
	";
	echo $content;
	break;
    case "sms_autoreply_scenario_edit_yes":
	$autoreply_scenario_id = $_POST['autoreply_scenario_id'];
	$autoreply_id = $_POST['autoreply_id'];
	$edit_autoreply_scenario_result = $_POST['edit_autoreply_scenario_result'];
	for ($i=1;$i<=7;$i++)
	{
	    ${"edit_autoreply_scenario_param".$i} = strtoupper($_POST['edit_autoreply_scenario_param$i']);
	}
	if ($edit_autoreply_scenario_result)
	{
	    for ($i=1;$i<=7;$i++)
	    {
		$autoreply_scenario_param_list .= "autoreply_scenario_param$i='".${"edit_autoreply_scenario_param".$i}."',";
	    }
	    $db_query = "
		UPDATE "._DB_PREF_."_featureAutoreply_scenario 
		SET c_timestamp='".mktime()."',".$autoreply_scenario_param_list."autoreply_scenario_result='$edit_autoreply_scenario_result' 
		WHERE autoreply_id='$autoreply_id' AND autoreply_scenario_id='$autoreply_scenario_id'
	    ";
	    if ($db_result = @dba_affected_rows($db_query))
	    {
		$error_string = _('SMS autoreply scenario has been edited');
	    }
	    else
	    {
	        $error_string = _('Fail to edit SMS autoreply scenario');
	    }
	}
	else
	{
	    $error_string = _('You must fill all fields');
	}
	header ("Location: menu.php?inc=feature_sms_autoreply&op=sms_autoreply_scenario_edit&autoreply_id=$autoreply_id&autoreply_scenario_id=$autoreply_scenario_id&err=".urlencode($error_string));
	break;
}

?>