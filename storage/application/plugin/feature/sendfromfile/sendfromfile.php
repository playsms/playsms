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

switch (_OP_) {
	case 'list':
		$content = _dialog() . '<h2 class=page-header-title>' . _('Send from file') . '</h2><p />';
		if (auth_isadmin()) {
			$info_format = _('destination number, message, username');
		} else {
			$info_format = _('destination number, message');
		}
		$content .= "
			<table class=ps_table>
				<tbody>
					<tr>
						<td>
							<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_confirm\" enctype=\"multipart/form-data\" method=\"post\">
							" . _CSRF_FORM_ . "
							<p>" . _('Please select CSV file') . "</p>
							<p><input type=\"file\" name=\"fncsv\"></p>
							<p class=help-block>" . _('CSV file format') . " : " . $info_format . "</p>
							<p><input type=\"submit\" value=\"" . _('Upload file') . "\" class=\"button\"></p>
							</form>
						</td>
					</tr>
				</tbody>
			</table>";
		_p($content);
		break;
	case 'upload_confirm':
	
		// fixme anton - https://www.exploit-database.net/?id=92843
		$filename = core_sanitize_filename($_FILES['fncsv']['name']);		
		if ($filename && $filename == $_FILES['fncsv']['name']) {
			$fn = $_FILES['fncsv']['tmp_name'];
			$fs = (int) $_FILES['fncsv']['size'];
			if ($fs && ($fs == filesize($fn)) && file_exists($fn)) {
				$continue = TRUE;
			} else {
				_log("file is empty or does not exists fn:" . $_FILES['fncsv']['name'] . " file:" . $fn, 2, "sendfromfile upload_confirm");
				$_FILES = array();

				$continue = FALSE;
			}
		} else {
			_log("insecure file name detected fn:" . $_FILES['fncsv']['name'], 2, "sendfromfile upload_confirm");
			$_FILES = array();

			$continue = FALSE;
		}
		
		if ($continue) {
			list($all_numbers, $item_valid, $item_invalid, $valid, $invalid, $num_of_rows, $sendfromfile_id) = sendfromfile_verify($fn);
		} else {
			$_SESSION['dialog']['danger'][] = _('Invalid CSV file');
			header("Location: " . _u('index.php?app=main&inc=feature_sendfromfile&op=list'));
			exit();
			break;
		}
		
		$content = '<h2 class=page-header-title>' . _('Send from file') . '</h2>';
		$content .= '<p class=lead>' . _('Confirmation') . '</p>';	
		$content .= '<p>' . _('Uploaded file') . ': ' . $filename . '</p>';

		$content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_cancel\" method=\"post\">";
		$content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sendfromfile_id . "'>";
		$content .= "<input type=\"submit\" value=\"" . _('Cancel send from file') . "\" class=\"button\"></p>";
		$content .= "</form>";

		if ($sendfromfile_id && $valid) {
			$content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_process\" method=\"post\">";
			$content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sendfromfile_id . "'>";
			$content .= "<input type=\"submit\" value=\"" . _('Send SMS to valid entries') . "\" class=\"button\"></p>";
			$content .= "</form>";			

			$content .= _('Found valid entries in uploaded file') . ' (' . _('valid entries') . ': ' . $valid . ' ' . _('of') . ' ' . $num_of_rows . ')<p />';
			$content .= '<p class=lead><span class="playsms-icon fa fas fa-thumbs-up" alt="' . _('Valid entries') . '"></span>' . _('Valid entries') . '</p>';
			$content .= "
				<div class=table-responsive>
					<table id=playsms-table-list class=playsms-table-list>
					<thead>";
			if (auth_isadmin()) {
				$content .= "
					<tr>
						<th width=20%>" . _('Destination number') . "</th>
						<th width=60%>" . _('Message') . "</th>
						<th width=20%>" . _('Username') . "</th>
					</tr>";
			} else {
				$content .= "
					<tr>
						<th width=20%>" . _('Destination number') . "</th>
						<th width=80%>" . _('Message') . "</th>
					</tr>";
			}
			$content .= "
					</thead>
					<tbody>";
			$j = 0;
			foreach ($item_valid as $item) {
				if (auth_isadmin()) {
					$content .= "
						<tr>
							<td>" . $item[0] . "</td>
							<td>" . $item[1] . "</td>
							<td>" . $item[2] . "</td>
						</tr>";
				} else {
					$content .= "
						<tr>
							<td>" . $item[0] . "</td>
							<td>" . $item[1] . "</td>
						</tr>";
				}
			}
			$content .= "
					</tbody>
					<tfoot>
						<tr>
							<td id='playsms-table-pager' class='playsms-table-pager' colspan=3>
							<div class='form-inline pull-right'>
								<div class='btn-group btn-group-sm mx-1' role='group'>
									<button type='button' class='btn btn-secondary first'>&#8676;</button>
									<button type='button' class='btn btn-secondary prev'>&larr;</button>
									<span class='pagedisplay'></span>
								</div>
								<div class='btn-group btn-group-sm mx-1' role='group'>
									<button type='button' class='btn btn-secondary next' title='next'>&rarr;</button>
									<button type='button' class='btn btn-secondary last' title='last'>&#8677;</button>
								</div>
								<select class='form-control-sm custom-select px-1 pagesize' title='" . _('Select page size') . "'>
									<option selected='selected' value='10'>10</option>
									<option value='20'>20</option>
									<option value='50'>50</option>
									<option value='100'>100</option>
								</select>
							</div>
							</td>
						</tr>
					</tfoot>
					</table>
				</div>
				<script type='text/javascript'>
					$(document).ready(function() { 
						$('#playsms-table-list').tablesorterPager({container: $('#playsms-table-pager')}); 
					});
				</script>";
		}
		
		if ($invalid) {
			$content .= '<p /><br />';
			$content .= _('Found invalid entries in uploaded file') . ' (' . _('invalid entries') . ': ' . $invalid . ' ' . _('of') . ' ' . $num_of_rows . ')<p />';
			$content .= '<p class=lead><span class="playsms-icon fa fas fa-thumbs-down" alt="' . _('Invalid entries') . '"></span>' . _('Invalid entries') . '</p>';
			$content .= "
				<div class=table-responsive>
					<table id=table-invalid-entries class=playsms-table-list>
					<thead>";
			if (auth_isadmin()) {
				$content .= "
					<tr>
						<th width=20%>" . _('Destination number') . "</th>
						<th width=60%>" . _('Message') . "</th>
						<th width=20%>" . _('Username') . "</th>
					</tr>";
			} else {
				$content .= "
					<tr>
						<th width=20%>" . _('Destination number') . "</th>
						<th width=80%>" . _('Message') . "</th>
					</tr>";
			}
			$content .= "
					</thead>
					<tbody>";
			$j = 0;
			foreach ($item_invalid as $item) {
				if (auth_isadmin()) {
					$content .= "
						<tr>
							<td>" . $item[0] . "</td>
							<td>" . $item[1] . "</td>
							<td>" . $item[2] . "</td>
						</tr>";
				} else {
					$content .= "
						<tr>
							<td>" . $item[0] . "</td>
							<td>" . $item[1] . "</td>
						</tr>";
				}
			}
			$content .= "
					</tbody>
					<tfoot>
						<tr>
							<td id='table-invalid-pager' colspan=3>
							<div class='form-inline pull-right'>
								<div class='btn-group btn-group-sm mx-1' role='group'>
									<button type='button' class='btn btn-secondary first'>&#8676;</button>
									<button type='button' class='btn btn-secondary prev'>&larr;</button>
									<span class='pagedisplay'></span>
								</div>
								<div class='btn-group btn-group-sm mx-1' role='group'>
									<button type='button' class='btn btn-secondary next' title='next'>&rarr;</button>
									<button type='button' class='btn btn-secondary last' title='last'>&#8677;</button>
								</div>
								<select class='form-control-sm custom-select px-1 pagesize' title='" . _('Select page size') . "'>
									<option selected='selected' value='10'>10</option>
									<option value='20'>20</option>
									<option value='50'>50</option>
									<option value='100'>100</option>
								</select>
							</div>
							</td>
						</tr>
					</tfoot>
					</table>
				</div>
				<script type='text/javascript'>
					$(document).ready(function() { 
						$('#table-invalid-entries').tablesorterPager({container: $('#table-invalid-pager')}); 
					});
				</script>";
		}
		
		$content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_cancel\" method=\"post\">";
		$content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sendfromfile_id . "'>";
		$content .= "<input type=\"submit\" value=\"" . _('Cancel send from file') . "\" class=\"button\"></p>";
		$content .= "</form>";

		if ($sendfromfile_id && $valid) {
			$content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_process\" method=\"post\">";
			$content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sendfromfile_id . "'>";
			$content .= "<input type=\"submit\" value=\"" . _('Send SMS to valid entries') . "\" class=\"button\"></p>";
		}
		
		_p($content);
		break;
	
	case 'upload_cancel':
		if ($sendfromfile_id = $_REQUEST['sid']) {
			sendfromfile_destroy($sendfromfile_id);

			$_SESSION['dialog']['danger'][] = _('Send from file has been cancelled');
		} else {
			$_SESSION['dialog']['danger'][] = _('Invalid session ID');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sendfromfile&op=list'));
		exit();
		break;
	
	case 'upload_process':
		if ($sendfromfile_id = $_REQUEST['sid']) {
			sendfromfile_process($sendfromfile_id);

			$_SESSION['dialog']['info'][] = _('SMS to valid numbers in uploaded file has been delivered to queue');
		} else {
			$_SESSION['dialog']['danger'][] = _('Invalid session ID');
		}
		header("Location: " . _u('index.php?app=main&inc=feature_sendfromfile&op=list'));
		exit();
		break;
}
