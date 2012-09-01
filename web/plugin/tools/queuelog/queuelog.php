<?php if(!(defined('_SECURE_'))){die('Intruder alert');}; ?>
<?php

switch ($op)
{
	case "queuelog_list":
		if(!$page){$page = 1;}
		if(!$nav){$nav = 1;}
		$line_per_page = 50;
		$max_nav = 15;
		$num_rows = queuelog_countall();
		$pages = ceil($num_rows/$line_per_page);
		$nav_pages = themes_navbar($pages, $nav, $max_nav, "index.php?app=menu&inc=tools_queuelog&op=queuelog_list", $page);
		$limit = ($page-1)*$line_per_page;
		$content = "
			<h2>"._('View SMS queue')."</h2>
			<p>".$nav_pages."</p>
			<table width=100% cellpadding=1 cellspacing=2 border=0 class=\"sortable\">
			<thead>
			<tr>
				<th align=center width=4>*</th>
				<th align=center width=20%>"._('Queue Code')."</th>
				<th align=center width=10%>"._('Date/Time')."</th>
		";
		if ($core_config['user']['status'] == 2) {
			$content .= "
				<th align=center width=10%>"._('Username')."</th>
			";
		}
		$content .= "
				<th align=center width=4>"._('Group')."</th>
				<th align=center width=60%>"._('Message')."</th>
			</tr>
			</thead>
			<tbody>
		";
		$data = queuelog_get($line_per_page, $limit);
		for ($c=count($data)-1;$c>=0;$c--) {
			$i = $c + 1;
			$c_queue_code = $data[$c]['queue_code'];
			$c_datetime_entry = $data[$c]['datetime_entry'];
			$c_username = uid2username($data[$c]['uid']);
			$c_group = phonebook_groupid2code($data[$c]['gpid']);
			$c_message = stripslashes(core_display_text($data[$c]['message'].' '.$data[$c]['footer'], 25));
			$td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
			$content .= "
				<tr>
					<td valign=top class=$td_class align=left>".$i.".</td>
					<td valign=top class=$td_class align=center>".$c_queue_code."</td>
					<td valign=top class=$td_class align=center>".$c_datetime_entry."</td>
			";
			if ($core_config['user']['status'] == 2) {
				$content .= "
					<td valign=top class=$td_class align=center>".$c_username."</td>
				";
			}
			$content .= "
					<td valign=top class=$td_class align=center>".$c_group."</td>
					<td valign=top class=$td_class align=left>".$c_message."</td>
				</tr>
			";
		}
		$content .= "
			</tbody></table>
			<p>".$nav_pages."</p>
		";
		echo $content;
		break;
}

?>