<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

/*
 * Implementations of hook checkavailablekeyword()
 *
 * @param $keyword
 *   checkavailablekeyword() will insert keyword for checking to the hook here
 * @return
 *   TRUE if keyword is available
 */
function sms_board_hook_checkavailablekeyword($keyword)
{
	$ok = true;
	$db_query = "SELECT board_id FROM "._DB_PREF_."_featureBoard WHERE board_keyword='$keyword'";
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
 * @param $board_keyword
 *   check if keyword is for sms_board
 * @param $board_param
 *   get parameters from incoming sms
 * @param $sms_receiver
 *   receiver number that is receiving incoming sms
 * @return $ret
 *   array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_board_hook_setsmsincomingaction($sms_datetime,$sms_sender,$board_keyword,$board_param='',$sms_receiver='',$raw_message='')
{
	$ok = false;
	$db_query = "SELECT uid,board_id FROM "._DB_PREF_."_featureBoard WHERE board_keyword='$board_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result))
	{
		$c_uid = $db_row['uid'];
		if (sms_board_handle($c_uid, $sms_datetime,$sms_sender,$sms_receiver,$board_keyword,$board_param,$raw_message))
		{
			$ok = true;
		}
	}
	$ret['uid'] = $c_uid;
	$ret['status'] = $ok;
	return $ret;
}

function sms_board_handle($c_uid, $sms_datetime,$sms_sender,$sms_receiver,$board_keyword,$board_param='',$raw_message='')
{
	global $web_title,$email_service,$email_footer,$gateway_module, $datetime_now;
	$ok = false;
	if ($sms_sender && $board_keyword && $board_param)
	{
		// masked sender sets here
		$masked_sender = substr_replace($sms_sender,'xxxx',-4);
		$db_query = "
	    INSERT INTO "._DB_PREF_."_featureBoard_log 
	    (in_gateway,in_sender,in_masked,in_keyword,in_msg,in_datetime) 
	    VALUES ('$gateway_module','$sms_sender','$masked_sender','$board_keyword','$board_param','$datetime_now')
	";
		if ($cek_ok = @dba_insert_id($db_query))
		{
			$db_query1 = "SELECT board_forward_email FROM "._DB_PREF_."_featureBoard WHERE board_keyword='$board_keyword'";
			$db_result1 = dba_query($db_query1);
			$db_row1 = dba_fetch_array($db_result1);
			$email = $db_row1['board_forward_email'];
			if ($email)
			{
				// get name from c_uid's phonebook
				$c_username = uid2username($c_uid);
				$c_name = phonebook_number2name($sms_sender, $c_username);
				$sms_sender = $c_name ? $c_name.' <'.$sms_sender.'>' : $sms_sender;
				$sms_datetime = core_display_datetime($sms_datetime);
					
				$subject = "[SMSGW-".$board_keyword."] "._('from')." $sms_sender";
				$body = _('Forward WebSMS')." ($web_title)\n\n";
				$body .= _('Date and time').": $sms_datetime\n";
				$body .= _('Sender').": $sms_sender\n";
				$body .= _('Receiver').": $sms_receiver\n";
				$body .= _('Keyword').": $board_keyword\n\n";
				$body .= _('Message').":\n$board_param\n\n";
				$body .= $email_footer."\n\n";
				sendmail($email_service,$email,$subject,$body);
			}
			$ok = true;
		}
	}
	return $ok;
}

function sms_board_output_rss($keyword,$line="10")
{
	global $apps_path,$web_title;
	include_once $apps_path['plug']."/feature/sms_board/lib/external/feedcreator/feedcreator.class.php";
	$keyword = strtoupper($keyword);
	if (!$line) { $line = "10"; };
	$format_output = "RSS0.91";
	$rss = new UniversalFeedCreator();
	$db_query1 = "SELECT * FROM "._DB_PREF_."_featureBoard_log WHERE in_keyword='$keyword' ORDER BY in_datetime DESC LIMIT 0,$line";
	$db_result1 = dba_query($db_query1);
	while ($db_row1 = dba_fetch_array($db_result1))
	{
		$title = $db_row1['in_masked'];
		$description = $db_row1['in_msg'];
		$datetime = $db_row1['in_datetime'];
		$items = new FeedItem();
		$items->title = $title;
		$items->description = $description;
		$items->comments = $datetime;
		$items->date = strtotime($datetime);
		$rss->addItem($items);
	}
	$feeds = $rss->createFeed($format_output);
	return $feeds;
}

// part of SMS board
function sms_board_output_html($keyword,$line="10",$pref_bodybgcolor="#E0D0C0",$pref_oddbgcolor="#EEDDCC",$pref_evenbgcolor="#FFEEDD")
{
	global $apps_path,$web_title;
	$keyword = strtoupper($keyword);
	if (!$line) { $line = "10"; };
	if (!$pref_bodybgcolor) { $pref_bodybgcolor = "#E0D0C0"; }
	if (!$pref_oddbgcolor) { $pref_oddbgcolor = "#EEDDCC"; }
	if (!$pref_evenbgcolor) { $pref_evenbgcolor = "#FFEEDD"; }
	$db_query = "SELECT board_pref_template FROM "._DB_PREF_."_featureBoard WHERE board_keyword='$keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result))
	{
		$template = $db_row['board_pref_template'];
		$db_query1 = "SELECT * FROM "._DB_PREF_."_featureBoard_log WHERE in_keyword='$keyword' ORDER BY in_datetime DESC LIMIT 0,$line";
		$db_result1 = dba_query($db_query1);
		$content = "<html>\n<head>\n<title>$web_title - "._('Keyword').": $keyword</title>\n<meta name=\"author\" content=\"http://playsms.org\">\n</head>\n<body bgcolor=\"$pref_bodybgcolor\" topmargin=\"0\" leftmargin=\"0\">\n<table width=100% cellpadding=2 cellspacing=2>\n";
		$i = 0;
		while ($db_row1 = dba_fetch_array($db_result1))
		{
			$i++;
			$sender = $db_row1['in_masked'];
			$datetime = $db_row1['in_datetime'];
			$message = $db_row1['in_msg'];
			$tmp_template = $template;
			$tmp_template = str_replace("{SENDER}",$sender,$tmp_template);
			$tmp_template = str_replace("{DATETIME}",$datetime,$tmp_template);
			$tmp_template = str_replace("{MESSAGE}",$message,$tmp_template);
			if (($i % 2) == 0)
			{
				$pref_zigzagcolor = "$pref_evenbgcolor";
			}
			else
			{
				$pref_zigzagcolor = "$pref_oddbgcolor";
			}
			$content .= "\n<tr><td width=100% bgcolor=\"$pref_zigzagcolor\">\n$tmp_template</td></tr>\n\n";
		}
		$content .= "</table>\n</body>\n</html>\n";
		return $content;
	}
}

function sms_board_hook_webservices_output($ta,$requests) {
	$keyword = $requests['keyword'];
	if (!$keyword) {
		$keyword = $requests['tag'];
	}
	if ($keyword) {
		$keyword = strtoupper($keyword);
		$line = $requests['line'];
		$type = $requests['type'];
		switch ($type) {
			case "xml":
			case "rss":
				$content = sms_board_output_rss($keyword,$line);
				header('Content-Type: text/xml');
				$ret = $content;
				break;
			case "html":
			default:
				$bodybgcolor = $requests['bodybgcolor'];
				$oddbgcolor = $requests['oddbgcolor'];
				$evenbgcolor = $requests['evenbgcolor'];
				$content = sms_board_output_html($keyword,$line,$bodybgcolor,$oddbgcolor,$evenbgcolor);
				$ret = $content;
		}
	}
	return $ret;
}

?>