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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_SECURE_') or die('Forbidden');

$board_id = $_REQUEST['board_id'];

switch (_OP_) {
	case 'list':
		$conditions['board_id'] = $board_id;
		$list = dba_search(_DB_PREF_ . '_featureBoard', '*', $conditions);
		$board_keyword = $list[0]['board_keyword'];

		$output_serialize = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_board&keyword=" . urlencode($board_keyword) . "&type=serialize";
		$output_json = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_board&keyword=" . urlencode($board_keyword) . "&type=json";
		$output_xml = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_board&keyword=" . urlencode($board_keyword) . "&type=xml";
		$output_rss091 = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_board&keyword=" . urlencode($board_keyword) . "&type=feed&format=rss0.91";
		$output_rss10 = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_board&keyword=" . urlencode($board_keyword) . "&type=feed&format=1.0";
		$output_rss20 = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_board&keyword=" . urlencode($board_keyword) . "&type=feed&format=2.0";
		$output_atom = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_board&keyword=" . urlencode($board_keyword) . "&type=feed&format=atom";
		$output_mbox = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_board&keyword=" . urlencode($board_keyword) . "&type=feed&format=mbox";
		$output_html = $core_config['http_path']['base'] . "/index.php?app=webservices&op=sms_board&keyword=" . urlencode($board_keyword) . "&type=html";

		$content = "
			<h2>" . _('Manage board') . "</h2>
			<h3>" . _('View board') . " : " . $board_keyword . "</h3>
			<table class=playsms-table>
				<tr><td class=label-sizer>" . _('PHP serialize output') . "</td><td><a href=\"" . _u($output_serialize) . "\" target=_blank>" . _u($output_serialize) . "</a></td></tr>
				<tr><td>" . _('JSON output') . "</td><td><a href=\"" . _u($output_json) . "\" target=_blank>" . _u($output_json) . "</a></td></tr>
				<tr><td>" . _('XML output') . "</td><td><a href=\"" . _u($output_xml) . "\" target=_blank>" . _u($output_xml) . "</a></td></tr>
				<tr><td>" . _('RSS 0.91 output') . "</td><td><a href=\"" . _u($output_rss091) . "\" target=_blank>" . _u($output_rss091) . "</a></td></tr>
				<tr><td>" . _('RSS 1.0 output') . "</td><td><a href=\"" . _u($output_rss10) . "\" target=_blank>" . _u($output_rss10) . "</a></td></tr>
				<tr><td>" . _('RSS 2.0 output') . "</td><td><a href=\"" . _u($output_rss20) . "\" target=_blank>" . _u($output_rss20) . "</a></td></tr>
				<tr><td>" . _('RSS ATOM output') . "</td><td><a href=\"" . _u($output_atom) . "\" target=_blank>" . _u($output_atom) . "</a></td></tr>
				<tr><td>" . _('MBOX output') . "</td><td><a href=\"" . _u($output_mbox) . "\" target=_blank>" . _u($output_mbox) . "</a></td></tr>
				<tr><td>" . _('HTML output') . "</td><td><a href=\"" . _u($output_html) . "\" target=_blank>" . _u($output_html) . "</a></td></tr>
			</table>
			" . _back('index.php?app=main&inc=feature_sms_board&op=sms_board_list');
		_p($content);
		break;
}
