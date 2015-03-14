<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

/*
 * Implementations of hook checkavailablekeyword() @param $keyword checkavailablekeyword() will insert keyword for checking to the hook here @return TRUE if keyword is available
 */
function sms_board_hook_checkavailablekeyword($keyword) {
	$ok = true;
	
	$db_query = "SELECT board_id FROM " . _DB_PREF_ . "_featureBoard WHERE board_keyword='$keyword'";
	if ($db_result = dba_num_rows($db_query)) {
		$ok = false;
	}
	
	return $ok;
}

/*
 * Implementations of hook setsmsincomingaction() @param $sms_datetime date and time when incoming sms inserted to playsms @param $sms_sender sender on incoming sms @param $board_keyword check if keyword is for sms_board @param $board_param get parameters from incoming sms @param $sms_receiver receiver number that is receiving incoming sms @return $ret array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_board_hook_setsmsincomingaction($sms_datetime, $sms_sender, $board_keyword, $board_param = '', $sms_receiver = '', $smsc = '', $raw_message = '') {
	$ok = false;
	
	$db_query = "SELECT uid,board_id FROM " . _DB_PREF_ . "_featureBoard WHERE board_keyword='$board_keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$c_uid = $db_row['uid'];
		if (sms_board_handle($c_uid, $sms_datetime, $sms_sender, $sms_receiver, $board_keyword, $board_param, $smsc, $raw_message)) {
			$ok = true;
		}
	}
	$ret['uid'] = $c_uid;
	$ret['status'] = $ok;
	
	return $ret;
}

function sms_board_handle($c_uid, $sms_datetime, $sms_sender, $sms_receiver, $board_keyword, $board_param = '', $smsc = '', $raw_message = '') {
	global $core_config;
	
	$ok = false;
	
	$board_keyword = strtoupper(trim($board_keyword));
	$board_param = trim($board_param);
	if ($sms_sender && $board_keyword && $board_param) {
		
		// masked sender sets here
		$masked_sender = substr_replace($sms_sender, 'xxxx', -4);
		$db_query = "
			INSERT INTO " . _DB_PREF_ . "_featureBoard_log
			(in_gateway,in_sender,in_masked,in_keyword,in_msg,in_datetime)
			VALUES ('$smsc','$sms_sender','$masked_sender','$board_keyword','$board_param','" . core_get_datetime() . "')";
		if ($cek_ok = @dba_insert_id($db_query)) {
			$db_query1 = "SELECT board_forward_email FROM " . _DB_PREF_ . "_featureBoard WHERE board_keyword='$board_keyword'";
			$db_result1 = dba_query($db_query1);
			$db_row1 = dba_fetch_array($db_result1);
			$email = $db_row1['board_forward_email'];
			if ($email) {
				
				// get name from c_uid's phonebook
				$c_name = phonebook_number2name($c_uid, $sms_sender);
				$sms_sender = ($c_name ? $c_name . ' <' . $sms_sender . '>' : $sms_sender);
				$sms_datetime = core_display_datetime($sms_datetime);
				$subject = "[" . $board_keyword . "] " . _('SMS board from') . " $sms_sender";
				$body = $core_config['main']['web_title'] . "\n";
				// fixme anton - ran by playsmsd, no http address, disabled for now looking for solution
				// $body.= $core_config['http_path']['base'] . "\n\n";
				$body .= _('Date and time') . ": $sms_datetime\n";
				$body .= _('Sender') . ": $sms_sender\n";
				$body .= _('Receiver') . ": $sms_receiver\n";
				$body .= _('SMS board keyword') . ": $board_keyword\n\n";
				$body .= _('Message') . ":\n$board_param\n\n";
				$body .= $core_config['main']['email_footer'] . "\n\n";
				$body = stripslashes($body);
				
				$email_data = array(
					'mail_from_name' => $core_config['main']['web_title'],
					'mail_from' => $core_config['main']['email_service'],
					'mail_to' => $email,
					'mail_subject' => $subject,
					'mail_body' => $body 
				);
				sendmail($email_data);
			}
			$ok = true;
		}
	}
	
	return $ok;
}

function sms_board_output_serialize($keyword, $line = "10") {
	$keyword = strtoupper($keyword);
	$line = ($line ? $line : '10');
	$ret['board']['keyword'] = $keyword;
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureBoard_log WHERE in_keyword='$keyword' ORDER BY in_datetime DESC LIMIT $line";
	$db_result = dba_query($db_query);
	$i = 0;
	while ($db_row = dba_fetch_array($db_result)) {
		$ret['item'][$i]['sender'] = $db_row['in_masked'];
		$ret['item'][$i]['message'] = $db_row['in_msg'];
		$ret['item'][$i]['datetime'] = core_display_datetime($db_row['in_datetime']);
		$i++;
	}
	
	return serialize($ret);
}

function sms_board_output_json($keyword, $line = "10") {
	$ret = unserialize(sms_board_output_serialize($keyword, $line));
	
	return json_encode($ret);
}

function sms_board_output_xml($keyword, $line = "10") {
	$keyword = strtoupper($keyword);
	$line = ($line ? $line : '10');
	$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$xml .= '<board keyword="' . $keyword . '">' . "\n";
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureBoard_log WHERE in_keyword='$keyword' ORDER BY in_datetime DESC LIMIT $line";
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$sender = $db_row['in_masked'];
		$message = $db_row['in_msg'];
		$datetime = core_display_datetime($db_row['in_datetime']);
		$xml .= '<item>' . "\n";
		$xml .= '<title>' . $sender . '</title>' . "\n";
		$xml .= '<message>' . $message . '</message>' . "\n";
		$xml .= '<datetime>' . $datetime . '</datetime>' . "\n";
		$xml .= '</item>' . "\n";
	}
	$xml .= '</board>';
	
	return $xml;
}

function sms_board_output_rss($keyword, $line = "10", $format = "RSS0.91") {
	global $core_config;
	$keyword = strtoupper($keyword);
	$line = ($line ? $line : '10');
	$format_output = ($format ? $format : "RSS0.91");
	include_once $core_config['apps_path']['plug'] . "/feature/sms_board/lib/external/feedcreator/feedcreator.class.php";
	$rss = new UniversalFeedCreator();
	$rss->title = $core_config['main']['web_title'];
	$rss->description = _('SMS Board') . ' ' . $keyword;
	$db_query1 = "SELECT * FROM " . _DB_PREF_ . "_featureBoard_log WHERE in_keyword='$keyword' ORDER BY in_datetime DESC LIMIT $line";
	$db_result1 = dba_query($db_query1);
	while ($db_row1 = dba_fetch_array($db_result1)) {
		$title = $db_row1['in_masked'];
		$description = $db_row1['in_msg'];
		$datetime = core_display_datetime($db_row1['in_datetime']);
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
function sms_board_output_html($keyword, $line = "10") {
	global $core_config;
	
	$web_title = $core_config['main']['web_title'];
	$keyword = strtoupper($keyword);
	if (!$line) {
		$line = "10";
	}
	$db_query = "SELECT board_css,board_pref_template FROM " . _DB_PREF_ . "_featureBoard WHERE board_keyword='$keyword'";
	$db_result = dba_query($db_query);
	if ($db_row = dba_fetch_array($db_result)) {
		$css_url = trim($db_row['board_css']);
		if (!$css_url) {
			$css_url = $core_config['http_path']['themes'] . '/common/jscss/sms_board.css';
		}
		$template = trim($db_row['board_pref_template']);
		$db_query1 = "SELECT * FROM " . _DB_PREF_ . "_featureBoard_log WHERE in_keyword='$keyword' ORDER BY in_datetime DESC LIMIT $line";
		$db_result1 = dba_query($db_query1);
		$css = "\n<!-- ADDITIONAL CSS BEGIN -->\n";
		$css .= "<style type='text/css'>\n";
		$css .= trim(file_get_contents($css_url)) . "\n";
		$css .= "</style>\n";
		$css .= "<!-- ADDITIONAL CSS END -->\n";
		$content = "<html>\n<head>\n<title>" . $keyword . "</title>\n<meta name=\"author\" content=\"http://playsms.org\">\n" . $css . "\n</head>\n";
		$content .= "<body>\n<div class=sms_board_view>\n";
		$i = 0;
		while ($db_row1 = dba_fetch_array($db_result1)) {
			$i++;
			$sender = $db_row1['in_masked'];
			$datetime = core_display_datetime($db_row1['in_datetime']);
			$message = $db_row1['in_msg'];
			$tmp_template = $template;
			$tmp_template = str_replace("{SENDER}", $sender, $tmp_template);
			$tmp_template = str_replace("{DATETIME}", $datetime, $tmp_template);
			$tmp_template = str_replace("{MESSAGE}", $message, $tmp_template);
			$content .= trim($tmp_template) . "\n";
		}
		$content .= "</div>\n</body>\n</html>\n";
		
		return $content;
	}
}

function sms_board_hook_webservices_output($operation, $requests, $returns) {
	$keyword = $requests['keyword'];
	if (!$keyword) {
		$keyword = $requests['tag'];
	}
	
	if (!($operation == 'sms_board' && $keyword)) {
		return FALSE;
	}
	
	$keyword = strtoupper($keyword);
	$line = $requests['line'];
	$type = $requests['type'];
	$format = $requests['format'];
	switch ($type) {
		case "serialize":
			if ($content = sms_board_output_serialize($keyword, $line)) {
				$returns['modified'] = TRUE;
				$returns['param']['content'] = $content;
				$returns['param']['content-type'] = 'text/plain';
			}
			break;
		
		case "json":
			if ($content = sms_board_output_json($keyword, $line)) {
				$returns['modified'] = TRUE;
				$returns['param']['content'] = $content;
				$returns['param']['content-type'] = 'text/json';
			}
			break;
		
		case "xml":
			if ($content = sms_board_output_xml($keyword, $line)) {
				$returns['modified'] = TRUE;
				$returns['param']['content'] = $content;
				$returns['param']['content-type'] = 'text/xml';
			}
			break;
		
		case "feed":
			// before sms_board_output_rss, and dont set content-type
			if ($content = sms_board_output_rss($keyword, $line, $format)) {
				$returns['modified'] = TRUE;
				$returns['param']['content'] = $content;
				if ($format == 'mbox') {
					$returns['param']['content-type'] = 'text/plain';
				} else {
					$returns['param']['content-type'] = 'text/xml';
				}
			}
			break;
		
		case "html":
		default :
			$bodybgcolor = $requests['bodybgcolor'];
			$oddbgcolor = $requests['oddbgcolor'];
			$evenbgcolor = $requests['evenbgcolor'];
			if ($content = sms_board_output_html($keyword, $line, $bodybgcolor, $oddbgcolor, $evenbgcolor)) {
				$returns['modified'] = TRUE;
				$returns['param']['content'] = $content;
				$returns['param']['content-type'] = 'text/html';
			}
	}
	
	return $returns;
}
