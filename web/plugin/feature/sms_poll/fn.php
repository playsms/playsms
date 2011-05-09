<?php

/*
 * Implementations of hook checkavailablekeyword()
 *
 * @param $keyword
 *   checkavailablekeyword() will insert keyword for checking to the hook here
 * @return
 *   TRUE if keyword is available
 */
function sms_poll_hook_checkavailablekeyword($keyword)
{
	$ok = true;
	$db_query = "SELECT poll_id FROM "._DB_PREF_."_featurePoll WHERE poll_keyword='$keyword'";
	if ($db_result = dba_num_rows($db_query))
	{
		$ok = false;
	}
	return $ok;
}

/*
 * Implementations of hook setsmsincomingaction()
 *
 * @param $sms_datetime
 *   date and time when incoming sms inserted to playsms
 * @param $sms_sender
 *   sender on incoming sms
 * @param $poll_keyword
 *   check if keyword is for sms_poll
 * @param $poll_param
 *   get parameters from incoming sms
 * @param $sms_receiver
 *   receiver number that is receiving incoming sms
 * @return $ret
 *   array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_poll_hook_setsmsincomingaction($sms_datetime,$sms_sender,$poll_keyword,$poll_param='',$sms_receiver='')
{
	$ok = false;
	$db_query = "SELECT uid,poll_id FROM "._DB_PREF_."_featurePoll WHERE poll_keyword='$poll_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result))
	{
		$c_uid = $db_row['uid'];
		if (sms_poll_handle($sms_datetime,$sms_sender,$poll_keyword,$poll_param))
		{
			$ok = true;
		}
	}
	$ret['uid'] = $c_uid;
	$ret['status'] = $ok;
	return $ret;
}

function sms_poll_handle($sms_datetime,$sms_sender,$poll_keyword,$poll_param='')
{
	$ok = false;
	$poll_keyword = strtoupper($poll_keyword);
	$target_choice = strtoupper($poll_param);
	if ($sms_sender && $poll_keyword && $target_choice)
	{
		$db_query = "SELECT poll_id,poll_enable FROM "._DB_PREF_."_featurePoll WHERE poll_keyword='$poll_keyword'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$poll_id = $db_row['poll_id'];
		$poll_enable = $db_row['poll_enable'];
		$db_query = "SELECT choice_id FROM "._DB_PREF_."_featurePoll_choice WHERE choice_keyword='$target_choice' AND poll_id='$poll_id'";
		$db_result = dba_query($db_query);
		$db_row = dba_fetch_array($db_result);
		$choice_id = $db_row['choice_id'];
		if ($poll_id && $choice_id)
		{
			$db_query = "SELECT result_id FROM "._DB_PREF_."_featurePoll_log WHERE poll_sender='$sms_sender' AND poll_id='$poll_id'";
			$already_vote = @dba_num_rows($db_query);
			if ((!$already_vote) && $poll_enable)
			{
				$db_query = "
		    INSERT INTO "._DB_PREF_."_featurePoll_log 
		    (poll_id,choice_id,poll_sender) 
		    VALUES ('$poll_id','$choice_id','$sms_sender')
		";
				dba_query($db_query);
			}
			$ok = true;
		}
	}
	return $ok;
}

function sms_poll_hook_webservices_output($ta,$requests) {
	global $http_path, $themes_module;
	$keyword = $requests['keyword'];
	$db_query = "SELECT poll_id,poll_title FROM "._DB_PREF_."_featurePoll WHERE poll_keyword='$keyword'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$poll_id = $db_row['poll_id'];
	$poll_title = $db_row['poll_title'];
	$db_query = "SELECT result_id FROM "._DB_PREF_."_featurePoll_log WHERE poll_id='$poll_id'";
	$total_voters = @dba_num_rows($db_query);
	if ($poll_id) {
		$mult = $requests['mult'];
		$bodybgcolor = $requests['bodybgcolor'];
		if (!isset($mult)) {
			$mult = "2";
		}
		if (!isset($bodybgcolor)) {
			$bodybgcolor = "#FEFEFE";
		}
		$content = "
	    <html>
	    <head>
	    <title>$web_title</title>
	    <meta name=\"author\" content=\"http://playsms.org\">
	    <link rel=\"stylesheet\" type=\"text/css\" href=\"".$http_path['themes']."/".$themes_module."/jscss/common.css\">
	    </head>
	    <body bgcolor=\"$bodybgcolor\" topmargin=\"0\" leftmargin\"0\">
	    <table cellpadding=1 cellspacing=1 border=0>
	    <tr><td colspan=2 width=100% class=box_text><font size=-2>$poll_title</font></td></tr>
	";
		$db_query = "SELECT * FROM "._DB_PREF_."_featurePoll_choice WHERE poll_id='$poll_id' ORDER BY choice_keyword";
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$choice_id = $db_row['choice_id'];
			$choice_title = $db_row['choice_title'];
			$choice_keyword = $db_row['choice_keyword'];
			$db_query1 = "SELECT result_id FROM "._DB_PREF_."_featurePoll_log WHERE poll_id='$poll_id' AND choice_id='$choice_id'";
			$choice_voted = @dba_num_rows($db_query1);
			if ($total_voters) {
				$percentage = round(($choice_voted/$total_voters)*100);
			} else {
				$percentage = "0";
			}
			$content .= "
		<tr>
		    <td width=90% nowrap class=box_text valign=middle align=left>
			<font size=-2>[' <b>$choice_keyword</b> '] $choice_title</font>
		    </td>
		    <td width=10% nowrap class=box_text valign=middle align=right>
			<font size=-2>$percentage%, $choice_voted</font>
		    </td>
		</tr>
		<tr>
		    <td width=100% nowrap class=box_text valign=middle align=left colspan=2>
			<img src=\"".$http_path['themes']."/".$themes_module."/images/bar.gif\" height=\"12\" width=\"".($mult*$percentage)."\" alt=\"".($percentage)."% ($choice_voted)\"></font><br>
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
		$ret = $content;
	}
	return $ret;
}

?>