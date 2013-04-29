<?php
defined('_SECURE_') or die('Forbidden');

switch ($op) {
	case 'list':
		$conditions['poll_id'] = $poll_id;
		$list = dba_search(_DB_PREF_.'_featurePoll', '*', $conditions);
		$poll_keyword = $list[0]['poll_keyword'];
		$output_serialize = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_poll&keyword=".urlencode($poll_keyword)."&type=serialize";
		$output_json = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_poll&keyword=".urlencode($poll_keyword)."&type=json";
		$output_xml = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_poll&keyword=".urlencode($poll_keyword)."&type=xml";
		$output_graph = $core_config['http_path']['base']."/index.php?app=webservices&ta=sms_poll&keyword=".urlencode($poll_keyword)."&type=graph";
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>"._('Manage poll')."</h2>
			<h3>"._('View poll')." : ".$poll_keyword."</h3>
			<table cellpadding=1 cellspacing=2 border=0 width=100%>
				<tr><td width=100>"._('PHP serialize output')."</td><td>:</td><td><a href=\"".$output_serialize."\" target=_blank>".$output_serialize."</a></td></tr>
				<tr><td>"._('JSON output')."</td><td>:</td><td><a href=\"".$output_json."\" target=_blank>".$output_json."</a></td></tr>
				<tr><td>"._('XML output')."</td><td>:</td><td><a href=\"".$output_xml."\" target=_blank>".$output_xml."</a></td></tr>
				<tr><td>"._('Graph output')."</td><td>:</td><td><a href=\"".$output_graph."\" target=_blank>".$output_graph."</a></td></tr>
			</table>
			<hr>
			<h3>"._('SMS poll graph')."</h3>";
		$db_query = "SELECT * FROM "._DB_PREF_."_featurePoll_choice WHERE poll_id='$poll_id' ORDER BY choice_keyword";
		$db_result = dba_query($db_query);
		$results= "";
		$answers = "";
		$no_results="";
		while ($db_row = dba_fetch_array($db_result)) {
			$choice_id = $db_row['choice_id'];
			$choice_title = $db_row['choice_title'];
			$answers .= $choice_title . ",";
			$choice_keyword = $db_row['choice_keyword'];
			$db_query1 = "SELECT result_id FROM "._DB_PREF_."_featurePoll_log WHERE poll_id='$poll_id' AND choice_id='$choice_id'";
			$choice_voted = @dba_num_rows($db_query1);
			$results .= $choice_voted . ",";
			$no_results .= "0,";
		}
		$answers = substr_replace($answers,"",-1);
		$results = substr_replace($results,"",-1);
		$no_results = substr_replace($no_results,"",-1);
		if ($results == $no_results) {
			$content .= "<p>"._('This poll has 0 votes!');
		} else {
			$content .= "<img src=\"".$output_graph."\">";
		}
		$content .= "<p>"._b('index.php?app=menu&inc=feature_sms_poll&op=sms_poll_list');
		echo $content;
		break;
}

?>