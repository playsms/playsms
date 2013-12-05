<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

switch ($op) {
	case "queuelog_list":
		$count = queuelog_countall();
		$nav = themes_nav($count, "index.php?app=menu&inc=tools_queuelog&op=queuelog_list");
		$content = "
			<h2>"._('View SMS queue')."</h2>
			<div align=center>".$nav['form']."</div>
			<div class=table-responsive>
			<table class=playsms-table-list>
			<thead>
			<tr>
		";
		if (isadmin()) {
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
			$c_username = uid2username($data[$c]['uid']);
			$c_count = $data[$c]['count'];
			$c_message = stripslashes(core_display_text($data[$c]['message']));
			$content .= "
				<tr>
			";
			if (isadmin()) {
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
		echo $content;
		break;
}

?>