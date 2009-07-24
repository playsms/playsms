<?
include "init.php";
include "$apps_path[libs]/function.php";

$refresh = strtoupper($_GET[refresh]);
$backagain = strtoupper($_GET[backagain]);
if (($refresh=="YES") && ($backagain!="YES"))
{
    $url = base64_encode($_SERVER[REQUEST_URI]."&backagain=yes");
    header("Location: menu.php?inc=daemon&url=$url");
    die();
}

$show = $_GET[show];

switch ($show)
{
    case "vote":
    case "poll":
	$keyword = $_GET[keyword];
	$db_query = "SELECT poll_id,poll_title FROM "._DB_PREF_."_featurePoll WHERE poll_keyword='$keyword'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$poll_id = $db_row[poll_id];
	$poll_title = $db_row[poll_title];
	$db_query = "SELECT result_id FROM "._DB_PREF_."_featurePoll_log WHERE poll_id='$poll_id'";
	$total_voters = @dba_num_rows($db_query);
	if ($poll_id)
	{
	    $mult = $_GET[mult];
	    $bodybgcolor = $_GET[bodybgcolor];
	    if (!isset($mult)) 
	    {
		$mult = "2";
	    }
	    if (!isset($bodybgcolor))
	    {
		$bodybgcolor = "#FEFEFE";
	    }
	    $content = "
		<html>
		<head>
		<title>$web_title</title>
		<meta name=\"author\" content=\"http://playsms.sourceforge.net\">
		<link rel=\"stylesheet\" type=\"text/css\" href=\"".$http_path[themes]."/".$themes_module."/jscss/common.css\">
		</head>
		<body bgcolor=\"$bodybgcolor\" topmargin=\"0\" leftmargin\"0\">
		<table cellpadding=1 cellspacing=1 border=0>
		<tr><td colspan=2 width=100% class=box_text><font size=-2>$poll_title</font></td></tr>
	    ";
	    $db_query = "SELECT * FROM "._DB_PREF_."_featurePoll_choice WHERE poll_id='$poll_id' ORDER BY choice_keyword";
	    $db_result = dba_query($db_query);
	    while ($db_row = dba_fetch_array($db_result))
	    {
		$choice_id = $db_row[choice_id];
		$choice_title = $db_row[choice_title];
		$choice_keyword = $db_row[choice_keyword];
		$db_query1 = "SELECT result_id FROM "._DB_PREF_."_featurePoll_log WHERE poll_id='$poll_id' AND choice_id='$choice_id'";
		$choice_voted = @dba_num_rows($db_query1);
		if ($total_voters)
		{
		    $percentage = round(($choice_voted/$total_voters)*100);
		}
		else
		{
		    $percentage = "0";
		}
		$content .= "
		    <tr>
			<td width=90% nowrap class=box_text valign=middle align=left>
			    <font size=-2>[ <b>$choice_keyword</b> ] $choice_title</font>
			</td>
			<td width=10% nowrap class=box_text valign=middle align=right>
			    <font size=-2>$percentage%, $choice_voted</font>
			</td>
		    </tr>
		    <tr>
			<td width=100% nowrap class=box_text valign=middle align=left colspan=2>
			    <img src=\"".$http_path[themes]."/".$themes_module."/images/bar.gif\" height=\"12\" width=\"".($mult*$percentage)."\" alt=\"".($percentage)."% ($choice_voted)\"></font><br>
			</td>
		    </tr>
		";
	    }
	    $content .= "
		<tr><td colspan=2><font size=-2><b>Total: $total_voters</b></font></td></tr>
		</table>
		</body>
		</html>
	    ";
	    echo $content;
	}
	break;
    case "board":
    default:
	// Use keyword, tag deprecated
	$keyword = $_GET[keyword];
	if (!$keyword)
	{	
	    $keyword = $_GET[tag];
	}
	if ($keyword)
	{
	    $keyword = strtoupper($keyword);
	    $line = $_GET[line];
	    $type = $_GET[type];
	    switch ($type)
	    {
		case "xml":
		case "rss":
		    $content = outputtorss($keyword,$line);
		    header('Content-Type: text/xml');
		    echo $content;
		    break;
		case "html":
		default:
		    $bodybgcolor = $_GET[bodybgcolor];
		    $oddbgcolor = $_GET[oddbgcolor];
		    $evenbgcolor = $_GET[evenbgcolor];
		    $content = outputtohtml($keyword,$line,$bodybgcolor,$oddbgcolor,$evenbgcolor);
		    echo $content;
	    }
	}
}
	
?>