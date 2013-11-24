<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

switch ($op) {
	case "queuelog_list":
		$count = queuelog_countall();
		$nav = themes_nav($count, "index.php?app=menu&inc=tools_queuelog&op=queuelog_list");
		$content = "
			<h2>"._('View SMS queue')."</h2>
			<p>".$nav['form']."</p>
			<table class=playsms-table class=sortable>
			<thead>
			<tr>
				<th width=30%>"._('Queue Code')."</th>
				<th width=20%>"._('Date/Time')."</th>
		";
		if (isadmin()) {
			$content .= "
				<th width=10%>"._('User')."</th>
			";
		}
		$content .= "
				<th width=20%>"._('Group')."</th>
				<th width=10%>"._('Count')."</th>
				<th width=20%>"._('Message')."</th>
			</tr>
			</thead>
			<tbody>
		";
		$data = queuelog_get($nav['limit'], $nav['offset']);
		for ($c=count($data)-1;$c>=0;$c--) {
			$c_queue_code = $data[$c]['queue_code'];
			$c_datetime_entry = $data[$c]['datetime_entry'];
			$c_username = uid2username($data[$c]['uid']);
			$c_group = phonebook_groupid2code($data[$c]['gpid']);
			$c_count = $data[$c]['count'];
			$c_message = stripslashes(core_display_text($data[$c]['message'], 15));
			$i = $count - $nav['offset'] + $c + 1 - count($data);
			$tr_class = ($i % 2) ? "row_odd" : "row_even";
			$content .= "
				<tr class=$tr_class>
					<td>".$c_queue_code."</td>
					<td>".$c_datetime_entry."</td>
			";
			if (isadmin()) {
				$content .= "
					<td>".$c_username."</td>
				";
			}
			$content .= "
					<td>".$c_group."</td>
					<td>".$c_count."</td>
					<td>".$c_message."</td>
				</tr>
			";
		}
		$content .= "
			</tbody></table>
			<p>".$nav['form']."</p>
		";
		echo $content;
		break;
}

?>