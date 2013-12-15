<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isadmin()){auth_block();};

switch ($op) {
	case "sandbox":
		$search_category = array(_('Time') => 'in_datetime', _('From') => 'in_sender', _('Content') => 'in_message');
		$base_url = 'index.php?app=menu&inc=sandbox&op=sandbox';
		$search = themes_search($search_category, $base_url);
		$conditions = array('flag_deleted' => 0, 'in_status' => 0);
		$keywords = $search['dba_keywords'];
		$count = dba_count(_DB_PREF_.'_tblSMSIncoming', $conditions, $keywords, '', $join);
		$nav = themes_nav($count, $search['url']);
		$extras = array('ORDER BY' => 'in_id DESC', 'LIMIT' => $nav['limit'], 'OFFSET' => $nav['offset']);
		$list = dba_search(_DB_PREF_.'_tblSMSIncoming', '*', $conditions, $keywords, $extras, $join);

		$content = "
			<h2>"._('Sandbox')."</h2>
			<p>".$search['form']."</p>
			<form id=fm_sandbox name=fm_sandbox action=\"index.php?app=menu&inc=sandbox&op=actions\" method=POST>
			"._CSRF_FORM_."
			<input type=hidden name=go value=delete>
			<div class=actions_box>
				<div class=pull-left>
					<a href=\"index.php?app=menu&inc=sandbox&op=actions&go=export\">".$core_config['icon']['export']."</a>
				</div>
				<div class=pull-right>
					<a href='#' onClick=\"return SubmitConfirm('"._('Are you sure you want to delete these items ?')."', 'fm_sandbox');\">".$core_config['icon']['delete']."</a>
				</div>
			</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
				<th width=20%>"._('From')."</th>
				<th width=75%>"._('Content')."</th>
				<th width=5% class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_sandbox)></th>
			</tr>
			</thead>
			<tbody>";

		$i = $nav['top'];
		$j = 0;
		for ($j=0;$j<count($list);$j++) {
			$list[$j] = core_display_data($list[$j]);
			$in_username = $list[$j]['username'];
			$in_id = $list[$j]['in_id'];
			$in_sender = $list[$j]['in_sender'];
			$p_desc = phonebook_number2name($in_sender);
			$current_sender = $in_sender;
			if ($p_desc) {
				$current_sender = "$in_sender<br />$p_desc";
			}
			$in_keyword = $list[$j]['in_keyword'];
			$in_datetime = core_display_datetime($list[$j]['in_datetime']);
			if ($c_feature = $list[$j]['in_feature']) {
				$c_feature = "<br />".$c_feature;
			}
			$in_status = ( $list[$j]['in_status'] == 1 ? '<span class=status_handled />' : '<span class=status_unhandled />' );
			$in_status = strtolower($in_status);
			$msg = $list[$j]['in_message'];
			$in_message = core_display_text($msg);
			$reply = '';
			$forward = '';
			if ($msg && $in_sender) {
				$reply = _a('index.php?app=menu&inc=send_sms&op=send_sms&do=reply&message='.urlencode($msg).'&to='.urlencode($in_sender), $core_config['icon']['reply']);
				$forward = _a('index.php?app=menu&inc=send_sms&op=send_sms&do=forward&message='.urlencode($msg), $core_config['icon']['forward']);
			}
			$c_message = "<div id=\"sandbox_msg\">".$in_message."</div><div id=\"msg_label\">".$in_datetime."&nbsp;".$in_status."</div><div id=\"msg_option\">".$reply.$forward."</div>";
			$i--;
			$content .= "
				<tr>
					<td>$current_sender</td>
					<td>$c_message</td>
					<td>
						<input type=hidden name=itemid".$j." value=\"$in_id\">
						<input type=checkbox name=checkid".$j.">
					</td>
				</tr>";
		}

		$content .= "
			</tbody>
			</table>
			</div>
			<div class=pull-right>".$nav['form']."</div>
			</form>";

		if ($err = $_SESSION['error_string']) {
			echo "<div class=error_string>$err</div>";
		}
		echo $content;
		break;
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$conditions = array('flag_deleted' => 0, 'in_status' => 0);
				$list = dba_search(_DB_PREF_.'_tblSMSIncoming', '*', $conditions, $search['dba_keywords'], '', $join);
				$data[0] = array(_('Time'), _('From'), _('Content'));
				for ($i=0;$i<count($list);$i++) {
					$j = $i + 1;
					$data[$j] = array(
						core_display_datetime($list[$i]['in_datetime']),
						$list[$i]['in_sender'],
						$list[$i]['in_message']
					);
				}
				$content = core_csv_format($data);
				$fn = 'sandbox-'.$core_config['datetime']['now_stamp'].'.csv';
				core_download($content, $fn, 'text/csv');
				break;
			case 'delete':
				for ($i=0;$i<$nav['limit'];$i++) {
					$checkid = $_POST['checkid'.$i];
					$itemid = $_POST['itemid'.$i];
					if(($checkid=="on") && $itemid) {
						$up = array('c_timestamp' => mktime(), 'flag_deleted' => '1');
						dba_update(_DB_PREF_.'_tblSMSIncoming', $up, array('in_id' => $itemid));
					}
				}
				$ref = $nav['url'].'&search_keyword='.$search['keyword'].'&page='.$nav['page'].'&nav='.$nav['nav'];
				$_SESSION['error_string'] = _('Selected incoming message has been deleted');
				header("Location: ".$ref);
		}
		break;
}

?>