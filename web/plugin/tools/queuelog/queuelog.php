<?php
defined('_SECURE_') or die('Forbidden');
if(!auth_isvalid()){auth_block();};

switch (_OP_) {
	case "queuelog_list":
		$count = queuelog_countall();
		$nav = themes_nav($count, "index.php?app=main&inc=tools_queuelog&op=queuelog_list");
		if ($err = $_SESSION['error_string']) {
			$error_content = "<div class=error_string>$err</div>";
		}
		$content = $error_content."
			<h2>"._('View SMS queue')."</h2>
			<div align=center>".$nav['form']."</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
		";
		if (auth_isadmin()) {
			$content .= "
				<th width=20%>"._('Queue Code')."</th>
				<th width=15%>"._('User')."</th>
			";
		} else {
			$content .= "
				<th width=30%>"._('Queue Code')."</th>
			";
		}
		$content .= "
				<th width=15%>"._('Scheduled')."</th>
				<th width=10%>"._('Count')."</th>
				<th width=30%>"._('Message')."</th>
				<th width=10%>"._('Action')."</th>
			</tr>
			</thead>
			<tbody>
		";
		$data = queuelog_get($nav['limit'], $nav['offset']);
		for ($c=count($data)-1;$c>=0;$c--) {
			$c_queue_code = $data[$c]['queue_code'];
			$c_datetime_scheduled = core_display_datetime($data[$c]['datetime_scheduled']);
			$c_username = user_uid2username($data[$c]['uid']);
			$c_count = $data[$c]['count'];
			$c_message = stripslashes(core_display_text($data[$c]['message']));
			$c_action = "<a href=\"javascript: ConfirmURL('" . addslashes(_("Are you sure you want to delete queue")) . " " . $c_queue_code . " ?','"._u('index.php?app=main&inc=tools_queuelog&op=queuelog_delete&queue='.$c_queue_code)."')\">".$icon_config['delete']."</a>";
			$content .= "
				<tr>
			";
			if (auth_isadmin()) {
				$content .= "
					<td>".$c_queue_code."</td>
					<td>".$c_username."</td>
				";
			} else {
				$content .= "
					<td>".$c_queue_code."</td>
				";
			}
			$content .= "
					<td>".$c_datetime_scheduled."</td>
					<td>".$c_count."</td>
					<td>".$c_message."</td>
					<td>".$c_action."</td>
				</tr>
			";
		}
		$content .= "
			</tbody></table>
			</div>
			<div align=center>".$nav['form']."</div>
		";
		_p($content);
		break;
	case "queuelog_delete":
		if ($queue = $_REQUEST['queue']) {
			if (queuelog_delete($queue)) {
				$_SESSION['error_string'] = _('Queue has been remove');
			}
		}
		header("Location: index.php?app=main&inc=tools_queuelog&op=queuelog_list");
		exit();
		break;
}
