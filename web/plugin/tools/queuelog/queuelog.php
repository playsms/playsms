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
				<th width=25%>"._('Queue Code')."</th>
		";
		if (isadmin()) {
			$content .= "
				<th width=10%>"._('User')."</th>
				<th width=20%>"._('Scheduled')."</th>
				<th width=10%>"._('Group')."</th>
			";
		} else {
			$content .= "
				<th width=20%>"._('Scheduled')."</th>
				<th width=20%>"._('Group')."</th>
			";
		}
		$content .= "
				<th width=10%>"._('Count')."</th>
				<th width=25%>"._('Message')."</th>
			</tr>
			</thead>
			<tbody>
		";
		$data = queuelog_get($nav['limit'], $nav['offset']);
		for ($c=count($data)-1;$c>=0;$c--) {
			$c_queue_code = $data[$c]['queue_code'];
			$c_datetime_scheduled = core_display_datetime($data[$c]['datetime_scheduled']);
			$c_username = uid2username($data[$c]['uid']);
			$c_group = phonebook_groupid2code($data[$c]['gpid']);
			$c_count = $data[$c]['count'];
			$c_message = stripslashes(core_display_text($data[$c]['message']));
			$content .= "
				<tr>
					<td>".$c_queue_code."</td>
			";
			if (isadmin()) {
				$content .= "
					<td>".$c_username."</td>
					<td>".$c_datetime_scheduled."</td>
					<td>".$c_group."</td>
				";
			} else {
				$content .= "
					<td>".$c_datetime_scheduled."</td>
					<td>".$c_group."</td>
				";
			}
			$content .= "
					<td>".$c_count."</td>
					<td>".$c_message."</td>
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