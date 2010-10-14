<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

$gpid = $_REQUEST['gpid'];

switch ($op)
{
    case "export":
	if ($gpid)
	{
    	    $db_query = "SELECT * FROM "._DB_PREF_."_toolsSimplephonebook WHERE uid='$uid' AND gpid='$gpid'";
	    $filename = "phonebook-".phonebook_groupid2code($gpid)."-".date(Ymd,time()).".csv";
	}
	else
	{
	    $db_query = "SELECT * FROM "._DB_PREF_."_toolsSimplephonebook WHERE uid='$uid'";
	    $filename = "phonebook-".date(Ymd,time()).".csv";
	}
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result))
	{
    	    $content .= "\"".$db_row['p_desc']."\",\"".$db_row['p_num']."\",\"".$db_row['p_email']."\"\r\n";
	}
	ob_end_clean();
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment;filename=\"$filename\"");
	echo $content;
	die();
	break;
    case "import":
	if ($gpid) {
	    if ($err) {
		$content = "<div class=error_string>$err</div>";
	    }
	    $content .= "
		<h2>"._('Import phonebook')." ("._('Group code').": ".phonebook_groupid2code($gpid).")</h2>
		<p>
		<form action=\"index.php?app=menu&inc=tools_simplephonebook&route=phonebook_exim&op=import_confirmation&gpid=$gpid\" enctype=\"multipart/form-data\" method=\"post\">
		    "._('Please select CSV file for phonebook entries')." ("._('format : Name,Mobile,Email').")<br>
		    <p><input type=\"file\" name=\"fnpb\">
		    <p><input type=\"checkbox\" name=\"replace\" value=\"ok\"> "._('Same item(s) will be replaced')."
		    <p><input type=\"submit\" value=\""._('Import')."\" class=\"button\">
		</form>
	    ";
	} else {
	    // FIXME
	}
	echo $content;
	break;
    case "import_confirmation":
	$replace = $_POST['replace'];
	$fnpb = $_FILES['fnpb'];
	$fnpb_tmpname = $_FILES['fnpb']['tmp_name'];
	$content = "
	    <h2>"._('Import confirmation')."</h2>
	    <p>
	    <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"1\" class=\"sortable\">
	    <tr>
		<td class=\"box_title\" width=\"4\">*</td>
		<td class=\"box_title\" width=\"40%\">"._('Name')."</td>
		<td class=\"box_title\" width=\"30%\">"._('Mobile')."</td>
		<td class=\"box_title\" width=\"30%\">"._('Email')."</td>
	    </tr>
	";
	if (file_exists($fnpb_tmpname))
	{
	    $fp = fopen($fnpb_tmpname,"r");
	        $file_content = fread($fp,filesize($fnpb_tmpname));
	    fclose($fp);
	    $parse_phonebook = explode("\r\n",$file_content);
	    $row_num = count($parse_phonebook);
	    for ($i=0;$i<$row_num;$i++)
	    {
		if(!empty($parse_phonebook) && strlen($parse_phonebook[$i])>1)
		{
		    $j=$i+1;
		    $parse_phonebook[$i] = str_replace(";",",",$parse_phonebook[$i]);
		    $parse_param = explode(",",str_replace("\"","",$parse_phonebook[$i]));
		    if(isset($parse_param[0]) && isset($parse_param[1]) && $parse_param[0] && $parse_param[1])
		    {
	    		$content .= "
	    		<tr>
			    <td align=center>$j.</td>
			    <td>&nbsp;$parse_param[0]</td>
			    <td>&nbsp;$parse_param[1]</td>
			    <td>&nbsp;$parse_param[2]</td>
			</tr>";
			$phonebook_post .= "
			<input type=\"hidden\" name=\"Name$i\" value=\"$parse_param[0]\">
			<input type=\"hidden\" name=\"Number$i\" value=\"$parse_param[1]\">
			<input type=\"hidden\" name=\"Email$i\" value=\"$parse_param[2]\">";		    
		    }

		}
	    }
	    if ($replace=="ok")
	    {
		$rstatus = _('Replace all entries in database');
	    }
	    else
	    {
		$rstatus = _('Add all entries');
	    }
	    $content .= "
		</table>
		<p>"._('Import above phonebook entries ?')."
		<p>"._('Status')." : $rstatus
		<form action=\"index.php?app=menu&inc=tools_simplephonebook&route=phonebook_exim&op=import_yes&gpid=$gpid\" method=\"post\">
		<input type=\"submit\" value=\""._('Import')."\" class=\"button\">
		$phonebook_post
		<input type=\"hidden\" name=\"replace\" value=\"$replace\">
		<input type=\"hidden\" name=\"num\" value=\"$j\">
		<p><input type=button class=button value=\""._('Back')."\" onClick=javascript:linkto('index.php?app=menu&inc=tools_simplephonebook&route=phonebook_exim&op=import&gpid=$gpid')>
		</form>
	    ";
	    echo $content;
	}
	else
	{
	    $error_string = _('Fail to upload CSV file for phonebook');
	    header("Location: index.php?app=menu&inc=tools_simplephonebook&route=phonebook_exim&op=import&gpid=$gpid&err=".urlencode($error_string));
	}
	break;
    case "import_yes":
	$num = $_POST['num'];
	$replace = $_POST['replace'];
	for ($a=0;$a<$num;$a++)
	{
	    $Name[$a] = $_POST['Name'.$a];
	    $Number[$a] = $_POST['Number'.$a];
	    $Email[$a] = $_POST['Email'.$a];
	}
	if ($replace=="ok")
	{
	    $db_query = "SELECT * FROM "._DB_PREF_."_toolsSimplephonebook WHERE gpid='$gpid'";
	    $db_result = dba_query($db_query);
	    $j = 0;
	    while ($db_row=dba_fetch_array($db_result))
	    {
	        $j++;
	        $pid[$j] = $db_row['pid'];
	        $pdesc[$j] = $db_row['p_desc'];
	    }
	    for ($i=0;$i<$num;$i++)
	    {
		$m = 0;
		for ($k=1;$k<=$j;$k++)
		{
		    if ($Name[$i]==$pdesc[$k])
		    {
			if ($Number[$i])
			{
			    $db_query1 = "UPDATE "._DB_PREF_."_toolsSimplephonebook SET c_timestamp='".mktime()."',p_num='".$Number[$i]."',p_email='".$Email[$i]."' WHERE pid='".$pid[$k]."' AND gpid='$gpid'";
			    $db_result1 = dba_affected_rows($db_query1);
			    if ($db_result1 > 0)
			    {
				// FIXME
			    }
			    else
			    {
				// FIXME
			    }
			}
			$m++;
		    }
		}
		if ($m <= 0)
		{
		    if ($Name[$i] && $Number[$i])
		    {
			$db_query2 = "
			    INSERT INTO "._DB_PREF_."_toolsSimplephonebook (uid,p_desc,p_num,p_email,gpid)
			    VALUES ('$uid','".$Name[$i]."','".$Number[$i]."','".$Email[$i]."','$gpid')
			";
			$db_result2 = dba_insert_id($db_query2);
			if ($db_result2 > 0)
			{
			    // FIXME
			}
			else
			{
			    // FIXME
			}
		    }
		}
	    }
	}
	else
	{
	    for ($i=0;$i<$num;$i++)
	    {
		if ($Name[$i] && $Number[$i])
		{
		    $db_query2 = "
			INSERT INTO "._DB_PREF_."_toolsSimplephonebook (uid,p_desc,p_num,p_email,gpid)
			VALUES ('$uid','".$Name[$i]."','".$Number[$i]."','".$Email[$i]."','$gpid')
		    ";
		    $db_result2 = dba_insert_id($db_query2);
		    if ($db_result2 > 0)
		    {
			// FIXME
		    }
		    else
		    {
			// FIXME
		    }
		}
	    }
	}
	header("Location: index.php?app=menu&inc=tools_simplephonebook&op=simplephonebook_list&err=".urlencode($error_string)."");
    break;

}

?>