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

if (!auth_isvalid()) {
	auth_block();
}
;

switch (_OP_) {
	case "user_inbox":
		$search_category = array(
			_('Time') => 'in_datetime',
			_('From') => 'in_sender',
			_('Message') => 'in_msg' 
		);
		$base_url = 'index.php?app=main&inc=feature_report&route=user_inbox&op=user_inbox';
		$search = themes_search($search_category, $base_url);
		$conditions = array(
			'in_uid' => $user_config['uid'],
			'flag_deleted' => 0 
		);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_ . '_tblSMSInbox', $conditions, $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = array(
			'ORDER BY' => 'in_id DESC',
			'LIMIT' => $nav['limit'],
			'OFFSET' => $nav['offset'] 
		);
		$list = dba_search(_DB_PREF_ . '_tblSMSInbox', '*', $conditions, $keywords, $extras);
		unset($tpl);
		$tpl = array(
			'vars' => array(
				'SEARCH_FORM' => $search['form'],
				'NAV_FORM' => $nav['form'],
				'Inbox' => _('Inbox'),
				'Export' => $icon_config['export'],
				'Delete' => $icon_config['delete'],
				'From' => _('From'),
				'Message' => _('Message'),
				'ARE_YOU_SURE' => _('Are you sure you want to delete these items ?') 
			) 
		);
		$i = $nav['top'];
		$j = 0;
		for ($j = 0; $j < count($list); $j++) {
			$list[$j] = core_display_data($list[$j]);
			$in_id = $list[$j]['in_id'];
			$in_sender = $list[$j]['in_sender'];
			$current_sender = report_resolve_sender($user_config['uid'], $in_sender);
			$in_datetime = core_display_datetime($list[$j]['in_datetime']);
			$msg = $list[$j]['in_msg'];
			$in_msg = core_display_text($msg);
			$reply = '';
			$forward = '';
			if ($msg && $in_sender) {
				$reply = _sendsms($in_sender, $msg, 'index.php?app=main&inc=feature_report&route=user_inbox&op=user_inbox', $icon_config['reply']);
				$forward = _sendsms('', $msg, 'index.php?app=main&inc=feature_report&route=user_inbox&op=user_inbox', $icon_config['forward']);
			}
			$i--;
			$tpl['loops']['data'][] = array(
				'tr_class' => $tr_class,
				'current_sender' => $current_sender,
				'in_msg' => $in_msg,
				'in_datetime' => $in_datetime,
				'in_status' => $in_status,
				'reply' => $reply,
				'forward' => $forward,
				'in_id' => $in_id,
				'j' => $j 
			);
		}
		$error_content = '';
		if ($err = $_SESSION['error_string']) {
			$error_content = _err_display();
		}
		$tpl['vars']['ERROR'] = $error_content;
		$tpl['name'] = 'user_inbox';
		$content = tpl_apply($tpl);
		_p($content);
		break;
	
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$conditions = array(
					'in_uid' => $user_config['uid'],
					'flag_deleted' => 0 
				);
				$list = dba_search(_DB_PREF_ . '_tblSMSInbox', '*', $conditions, $search['dba_keywords']);
				$data[0] = array(
					_('User'),
					_('Time'),
					_('From'),
					_('Message') 
				);
				for ($i = 0; $i < count($list); $i++) {
					$j = $i + 1;
					$data[$j] = array(
						user_uid2username($list[$i]['in_uid']),
						core_display_datetime($list[$i]['in_datetime']),
						$list[$i]['in_sender'],
						$list[$i]['in_msg'] 
					);
				}
				$content = core_csv_format($data);
				$fn = 'user_inbox-' . $core_config['datetime']['now_stamp'] . '.csv';
				core_download($content, $fn, 'text/csv');
				break;
			
			case 'delete':
				for ($i = 0; $i < $nav['limit']; $i++) {
					$checkid = $_POST['checkid' . $i];
					$itemid = $_POST['itemid' . $i];
					if (($checkid == "on") && $itemid) {
						$up = array(
							'c_timestamp' => mktime(),
							'flag_deleted' => '1' 
						);
						dba_update(_DB_PREF_ . '_tblSMSInbox', $up, array(
							'in_uid' => $user_config['uid'],
							'in_id' => $itemid 
						));
					}
				}
				$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
				$_SESSION['error_string'] = _('Selected incoming message has been deleted');
				header("Location: " . _u($ref));
				exit();
		}
		break;
}
