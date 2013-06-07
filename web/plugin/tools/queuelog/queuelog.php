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
			<table width=100% cellpadding=1 cellspacing=2 border=0 class=\"sortable\">
			<thead>
			<tr>
				<th align=center width=4>*</th>
				<th align=center width=30%>"._('Queue Code')."</th>
				<th align=center width=20%>"._('Date/Time')."</th>
		";
		if (isadmin()) {
			$content .= "
				<th align=center width=10%>"._('User')."</th>
			";
		}
		$content .= "
				<th align=center width=10%>"._('Group')."</th>
				<th align=center width=10%>"._('Count')."</th>
				<th align=center width=20%>"._('Message')."</th>
			</tr>
			</thead>
			<tbody>
		";
		$data = queuelog_get($nav['limit'], $nav['offset']);
		for ($c=count($data)-1;$c>=0;$c--) {
			$i = $count - $nav['offset'] + $c + 1 - count($data);
			$c_queue_code = $data[$c]['queue_code'];
			$c_datetime_entry = $data[$c]['datetime_entry'];
			$c_username = uid2username($data[$c]['uid']);
			$c_group = phonebook_groupid2code($data[$c]['gpid']);
			$c_count = $data[$c]['count'];
			$c_message = stripslashes(core_display_text($data[$c]['message'], 15));
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr>
					<td valign=top class=$td_class align=center>".$i.".</td>
					<td valign=top class=$td_class align=center>".$c_queue_code."</td>
					<td valign=top class=$td_class align=center>".$c_datetime_entry."</td>
			";
			if (isadmin()) {
				$content .= "
					<td valign=top class=$td_class align=center>".$c_username."</td>
				";
			}
			$content .= "
					<td valign=top class=$td_class align=center>".$c_group."</td>
					<td valign=top class=$td_class align=center>".$c_count."</td>
					<td valign=top class=$td_class align=left>".$c_message."</td>
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