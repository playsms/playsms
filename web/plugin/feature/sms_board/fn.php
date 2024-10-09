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

/**
 * Implementations of hook keyword_isavail()
 * 
 * @param $keyword SMS keyword
 * @return bool true if keyword is available, false if already registered in database
 */
function sms_board_hook_keyword_isavail($keyword)
{
	$keyword = strtoupper(core_sanitize_alphanumeric($keyword));

	$db_query = "SELECT board_id FROM " . _DB_PREF_ . "_featureBoard WHERE board_keyword=?";
	if (dba_num_rows($db_query, [$keyword])) {

		return false;
	}

	return true;
}

/**
 * Implementations of hook recvsms_process()
 * 
 * @param string $sms_datetime date and time when incoming sms inserted to playsms
 * @param string $sms_sender sender on incoming sms
 * @param string $board_keyword check if keyword is for sms_board
 * @param string $board_param get parameters from incoming sms
 * @param string $sms_receiver receiver number that is receiving incoming sms
 * @param string $smsc SMSC
 * @param string $raw_message Original SMS
 * @return array array of keyword owner uid and status, true if incoming sms handled
 */
function sms_board_hook_recvsms_process($sms_datetime, $sms_sender, $board_keyword, $board_param = '', $sms_receiver = '', $smsc = '', $raw_message = '')
{
	$ret = [];

	$uid = 0;
	$status = false;

	$board_keyword = strtoupper(core_sanitize_alphanumeric($board_keyword));
	$board_param = trim($board_param);

	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureBoard WHERE board_keyword=?";
	$db_result = dba_query($db_query, [$board_keyword]);
	if ($db_row = dba_fetch_array($db_result)) {
		$uid = $db_row['uid'];
		$smsc = gateway_decide_smsc($smsc, $db_row['smsc']);
		if (sms_board_handle($db_row, $sms_datetime, $sms_sender, $sms_receiver, $board_keyword, $board_param, $smsc, $raw_message)) {
			$status = true;
		}
	}

	$ret['uid'] = $uid;
	$ret['status'] = $status;

	return $ret;
}

/**
 * Handle incoming SMS to this plugin
 * 
 * @param array $list
 * @param string $sms_datetime
 * @param string $sms_sender
 * @param string $sms_receiver
 * @param string $board_keyword
 * @param string $board_param
 * @param string $smsc
 * @param string $raw_message
 * @return bool
 */
function sms_board_handle($list, $sms_datetime, $sms_sender, $sms_receiver, $board_keyword, $board_param = '', $smsc = '', $raw_message = '')
{
	global $core_config;

	if (!($sms_sender && $board_keyword && $board_param)) {

		return false;
	}

	// masked sender sets here
	$masked_sender = substr_replace($sms_sender, 'xxxx', -4);
	$db_query = "
			INSERT INTO " . _DB_PREF_ . "_featureBoard_log
			(board_id,in_gateway,in_sender,in_masked,in_keyword,in_msg,in_reply,in_datetime)
			VALUES (?,?,?,?,?,?,?,?)";
	if (!dba_insert_id($db_query, [$list['board_id'], $smsc, $sms_sender, $masked_sender, $board_keyword, $board_param, $list['board_reply'], core_get_datetime()])) {

		return false;
	}

	// forward to email
	if ($email = $list['board_forward_email']) {

		// get name from $uid's phonebostatus
		$c_name = phonebook_number2name($list['uid'], $sms_sender);
		$sms_sender = $c_name ? $c_name . ' <' . $sms_sender . '>' : $sms_sender;
		$sms_datetime = core_display_datetime($sms_datetime);
		$subject = "[" . $board_keyword . "] " . _('SMS board from') . " $sms_sender";
		$body = $core_config['main']['web_title'] . PHP_EOL;
		// fixme anton - ran by playsmsd, no http address, disabled for now lostatusing for solution
		// $body.= $core_config['http_path']['base'] . PHP_EOL . "" . PHP_EOL;
		$body .= _('Date and time') . ": $sms_datetime" . PHP_EOL;
		$body .= _('Sender') . ": $sms_sender" . PHP_EOL;
		$body .= _('Receiver') . ": $sms_receiver" . PHP_EOL;
		$body .= _('SMS board keyword') . ": $board_keyword" . PHP_EOL . "" . PHP_EOL;
		$body .= _('Message') . ":" . PHP_EOL . "$board_param" . PHP_EOL . "" . PHP_EOL;
		$body .= $core_config['main']['email_footer'] . PHP_EOL . "" . PHP_EOL;
		$body = stripslashes($body);

		$email_data = [
			'mail_from_name' => $core_config['main']['web_title'],
			'mail_from' => $core_config['main']['email_service'],
			'mail_to' => $email,
			'mail_subject' => $subject,
			'mail_body' => $body
		];
		sendmail($email_data);
	}

	// reply SMS
	if ($message = $list['board_reply']) {
		if ($username = user_uid2username($list['uid'])) {
			$unicode = core_detect_unicode($message);
			sendsms_helper($username, $sms_sender, $message, '', $unicode, $smsc);
		}
	}

	return true;
}

function sms_board_output_serialize($keyword, $line = 10)
{
	$ret = [];

	$keyword = strtoupper(core_sanitize_keyword($keyword));
	$line = $line > 0 ? $line : 10;

	$ret['board']['keyword'] = $keyword;
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureBoard_log WHERE in_keyword=? ORDER BY in_datetime DESC LIMIT " . (int) $line;
	$db_result = dba_query($db_query, [$keyword]);
	$i = 0;
	while ($db_row = dba_fetch_array($db_result)) {
		$ret['item'][$i]['sender'] = $db_row['in_masked'];
		$ret['item'][$i]['message'] = $db_row['in_msg'];
		$ret['item'][$i]['datetime'] = core_display_datetime($db_row['in_datetime']);
		$i++;
	}

	return serialize($ret);
}

function sms_board_output_json($keyword, $line = 10)
{
	$ret = unserialize(sms_board_output_serialize($keyword, $line));

	return json_encode($ret);
}

function sms_board_output_xml($keyword, $line = 10)
{
	$keyword = strtoupper(core_sanitize_keyword($keyword));
	$line = $line > 0 ? $line : 10;

	$xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
	$xml .= '<board keyword="' . $keyword . '">' . PHP_EOL;
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureBoard_log WHERE in_keyword=? ORDER BY in_datetime DESC LIMIT " . (int) $line;
	$db_result = dba_query($db_query, [$keyword]);
	while ($db_row = dba_fetch_array($db_result)) {
		$sender = $db_row['in_masked'];
		$message = $db_row['in_msg'];
		$datetime = core_display_datetime($db_row['in_datetime']);
		$xml .= '<item>' . PHP_EOL;
		$xml .= '<title>' . $sender . '</title>' . PHP_EOL;
		$xml .= '<message>' . $message . '</message>' . PHP_EOL;
		$xml .= '<datetime>' . $datetime . '</datetime>' . PHP_EOL;
		$xml .= '</item>' . PHP_EOL;
	}
	$xml .= '</board>';

	return $xml;
}

/**
 * Output RSS
 * 
 * @param string $keyword SMS keyword
 * @param int $line number of lines
 * @param string $format valid values are: "PIE0.1", "mbox", "RSS0.91", "RSS1.0", "RSS2.0", "OPML", "ATOM0.3", "HTML", "JS"
 * @return string
 */
function sms_board_output_rss($keyword, $line = 10, $format = "RSS0.91")
{
	global $core_config;

	$keyword = strtoupper(core_sanitize_keyword($keyword));
	$line = $line > 0 ? $line : 10;
	$formats = ["RSS0.91", "RSS1.0", "RSS2.0", "ATOM"];
	$format_output = "RSS0.91";
	foreach ( $formats as $c_format ) {
		if (strtolower($c_format) == strtolower($format)) {
			$format_output = $format;
			break;
		}
	}

	$rss = new UniversalFeedCreator();
	$rss->title = $core_config['main']['web_title'];
	$rss->description = _('SMS Board') . ' ' . $keyword;
	$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureBoard_log WHERE in_keyword=? ORDER BY in_datetime DESC LIMIT " . (int) $line;
	$db_result = dba_query($db_query, [$keyword]);
	while ($db_row = dba_fetch_array($db_result)) {
		$title = $db_row['in_masked'];
		$description = $db_row['in_msg'];
		$datetime = core_display_datetime($db_row['in_datetime']);
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
function sms_board_output_html($keyword, $line = 10)
{
	$content = "";

	$keyword = strtoupper(core_sanitize_keyword($keyword));
	$line = $line > 0 ? $line : 10;

	$db_query = "SELECT board_css FROM " . _DB_PREF_ . "_featureBoard WHERE board_keyword=?";
	$db_result = dba_query($db_query, [$keyword]);
	if ($db_row = dba_fetch_array($db_result)) {
		$css_url = trim($db_row['board_css']) ? trim($db_row['board_css']) : _APPS_PATH_THEMES_ . '/common/jscss/sms_board.css';

		$css = "<!-- ADDITIONAL CSS BEGIN -->" . PHP_EOL;
		$css .= "<style type='text/css'>" . PHP_EOL;
		$css .= trim(file_get_contents($css_url)) . PHP_EOL;
		$css .= "</style>" . PHP_EOL;
		$css .= "<!-- ADDITIONAL CSS END -->" . PHP_EOL;

		$content = "<html>" . PHP_EOL . "<head>" . PHP_EOL . "<title>" . $keyword . "</title>" . PHP_EOL . $css . "</head>" . PHP_EOL;
		$content .= "<body>" . PHP_EOL . "<div class=sms_board_view>" . PHP_EOL;
		$db_query = "SELECT * FROM " . _DB_PREF_ . "_featureBoard_log WHERE in_keyword=? ORDER BY in_datetime DESC LIMIT " . (int) $line;
		$db_result = dba_query($db_query, [$keyword]);
		while ($db_row = dba_fetch_array($db_result)) {
			$sender = $db_row['in_masked'];
			$datetime = core_display_datetime($db_row['in_datetime']);
			$message = $db_row['in_msg'];
			$content .= "<div class=sms_board_row id=sms_board_row_" . $db_row['in_id'] . ">" . PHP_EOL;
			$content .= "<div class=sender>" . $sender . "</div>" . PHP_EOL;
			$content .= "<div class=datetime>" . $datetime . "</div>" . PHP_EOL;
			$content .= "<div class=message>" . $message . "</div>" . PHP_EOL;
			$content .= "</div>" . PHP_EOL;
		}
		$content .= "</div>" . PHP_EOL . "</body>" . PHP_EOL . "</html>" . PHP_EOL;
	}

	return $content;
}

function sms_board_hook_webservices_output($operation, $requests, $returns)
{
	$returns = [];

	$keyword = $requests['keyword'];
	if (!$keyword) {
		$keyword = $requests['tag'];
	}

	if (!($operation == 'sms_board' && $keyword)) {

		return $returns;
	}

	$keyword = strtoupper(core_sanitize_keyword($keyword));
	$line = isset($requests['line']) && (int) $requests['line'] > 0 ? (int) $requests['line'] : 10;

	$type = strtolower($requests['type']);
	$format = strtolower($requests['format']);
	switch ($type) {
		case "serialize":
			if ($content = sms_board_output_serialize($keyword, $line)) {
				$returns['modified'] = true;
				$returns['param']['content'] = $content;
				$returns['param']['content-type'] = 'text/plain';
			}
			break;

		case "json":
			if ($content = sms_board_output_json($keyword, $line)) {
				$returns['modified'] = true;
				$returns['param']['content'] = $content;
				$returns['param']['content-type'] = 'text/json';
			}
			break;

		case "xml":
			if ($content = sms_board_output_xml($keyword, $line)) {
				$returns['modified'] = true;
				$returns['param']['content'] = $content;
				$returns['param']['content-type'] = 'text/xml';
			}
			break;

		case "feed":
			if ($content = sms_board_output_rss($keyword, $line, $format)) {
				$returns['modified'] = true;
				$returns['param']['content'] = $content;
				if ($format == 'mbox') {
					$returns['param']['content-type'] = 'text/plain';
				} else {
					$returns['param']['content-type'] = 'text/xml';
				}
			}
			break;

		case "html":
		default:
			if ($content = sms_board_output_html($keyword, $line)) {
				$returns['modified'] = true;
				$returns['param']['content'] = $content;
				$returns['param']['content-type'] = 'text/html';
			}
	}

	return $returns;
}

/**
 * Check for valid ID
 * 
 * @param int $id
 * @return bool
 */
function sms_board_check_id($id)
{
	return core_check_id($id, _DB_PREF_ . '_featureBoard', 'board_id');
}