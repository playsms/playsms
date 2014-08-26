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

$poll_id = $_REQUEST['poll_id'];

switch (_OP_) {
	case 'list' :
		$conditions['poll_id'] = $poll_id;
		$list = dba_search(_DB_PREF_ . '_featurePoll', '*', $conditions);
		$poll_keyword = $list[0]['poll_keyword'];
		$output_serialize = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_poll&keyword=" . urlencode($poll_keyword) . "&type=serialize";
		$output_json = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_poll&keyword=" . urlencode($poll_keyword) . "&type=json";
		$output_xml = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_poll&keyword=" . urlencode($poll_keyword) . "&type=xml";
		$output_graph = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_poll&keyword=" . urlencode($poll_keyword) . "&type=graph";
		if ($err = $_SESSION['error_string']) {
			$content = "<div class=error_string>$err</div>";
		}
		$content .= "
			<h2>" . _('Manage poll') . "</h2>
			<h3>" . _('View poll') . " : " . $poll_keyword . "</h3>
			<table class=playsms-table>
				<tr><td class=label-sizer>" . _('PHP serialize output') . "</td><td>:</td><td><a href=\"" . _u($output_serialize) . "\" target=_blank>" . _u($output_serialize) . "</a></td></tr>
				<tr><td>" . _('JSON output') . "</td><td>:</td><td><a href=\"" . _u($output_json) . "\" target=_blank>" . _u($output_json) . "</a></td></tr>
				<tr><td>" . _('XML output') . "</td><td>:</td><td><a href=\"" . _u($output_xml) . "\" target=_blank>" . _u($output_xml) . "</a></td></tr>
				<tr><td>" . _('Graph output') . "</td><td>:</td><td><a href=\"" . _u($output_graph) . "\" target=_blank>" . _u($output_graph) . "</a></td></tr>
			</table>
			<br />
			<h3>" . _('SMS poll graph') . "</h3>";
		$content .= "<img src=\"" . $output_graph . "\">";
		$content .= '<p>' . _back('index.php?app=main&inc=feature_sms_poll&op=sms_poll_list');
		_p($content);
		break;
}
