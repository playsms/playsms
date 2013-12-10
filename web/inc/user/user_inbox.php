<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

switch ($op) {
	case "user_inbox":
		$search_category = array(_('Time') => 'in_datetime', _('From') => 'in_sender', _('Message') => 'in_msg');
		$base_url = 'index.php?app=menu&inc=user_inbox&op=user_inbox';
		$search = themes_search($search_category, $base_url);
		$conditions = array('in_uid' => $uid, 'in_hidden' => 0);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_.'_tblUserInbox', $conditions, $keywords);
		$nav = themes_nav($count, $search['url']);
		$extras = array('ORDER BY' => 'in_id DESC', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$list = dba_search(_DB_PREF_.'_tblUserInbox', '*', $conditions, $keywords, $extras);
		unset($tpl);
		$tpl = array(
			'var' => array(
				'SEARCH_FORM' => $search['form'],
				'NAV_FORM' => $nav['form'],
				'Inbox' => _('Inbox'),
				'Export' => $core_config['icon']['export'],
				'Delete' => $core_config['icon']['delete'],
				'From' => _('From'),
				'Message' => _('Message'),
				'ARE_YOU_SURE' => _('Are you sure you want to delete these items ?')
			)
		);
		$i = $nav['top'];
		$j = 0;
		for ($j=0;$j<count($list);$j++) {
			$list[$j] = core_display_data($list[$j]);
			$in_id = $list[$j]['in_id'];
			$in_sender = $list[$j]['in_sender'];
			$p_desc = phonebook_number2name($in_sender);
			$current_sender = $in_sender;
			if ($p_desc) {
				$current_sender = "$in_sender<br />$p_desc";
			}
			$in_datetime = core_display_datetime($list[$j]['in_datetime']);
			$msg = $list[$j]['in_msg'];
			$in_msg = core_display_text($msg);
			$reply = '';
			$forward = '';
			if ($msg && $in_sender) {
				$reply = _a('index.php?app=menu&inc=send_sms&op=send_sms&do=reply&message='.urlencode($msg).'&to='.urlencode($in_sender), $core_config['icon']['reply']);
				$forward = _a('index.php?app=menu&inc=send_sms&op=send_sms&do=forward&message='.urlencode($msg), $core_config['icon']['forward']);
			}
			$i--;
			$tpl['loop']['data'][] = array(
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
			$error_content = "<div class=error_string>$err</div>";
		}
		$tpl['var']['ERROR'] = $error_content;
		$tpl['name'] = 'user_inbox';
		$content = tpl_apply($tpl);
		echo $content;
		break;
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$conditions = array('in_uid' => $uid, 'in_hidden' => 0);
				$list = dba_search(_DB_PREF_.'_tblUserInbox', '*', $conditions, $search['dba_keywords']);
				$data[0] = array(_('User'), _('Time'), _('From'), _('Message'));
				for ($i=0;$i<count($list);$i++) {
					$j = $i + 1;
					$data[$j] = array(
						uid2username($list[$i]['in_uid']),
						core_display_datetime($list[$i]['in_datetime']),
						$list[$i]['in_sender'],
						$list[$i]['in_msg']);
				}
				$content = core_csv_format($data);
				$fn = 'user_inbox-'.$core_config['datetime']['now_stamp'].'.csv';
				core_download($content, $fn, 'text/csv');
				break;
			case 'delete':
				for ($i=0;$i<$nav['limit'];$i++) {
					$checkid = $_POST['checkid'.$i];
					$itemid = $_POST['itemid'.$i];
					if(($checkid=="on") && $itemid) {
						$up = array('c_timestamp' => mktime(), 'in_hidden' => '1');
						dba_update(_DB_PREF_.'_tblUserInbox', $up, array('in_uid' => $uid, 'in_id' => $itemid));
					}
				}
				$ref = $nav['url'].'&search_keyword='.$search['keyword'].'&page='.$nav['page'].'&nav='.$nav['nav'];
				$_SESSION['error_string'] = _('Selected incoming message has been deleted');
				header("Location: ".$ref);
		}
		break;
}
