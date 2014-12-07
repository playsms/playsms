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

/* Inserted for javascript calendar */
print '

<script type="text/javascript"
src="../sms/plugin/themes/common/jscss/moment/moment-with-langs.min.js"></script>

<link rel="stylesheet"
href="../sms/plugin/themes/common/jscss/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css" />

<script type="text/javascript"
src="../sms/plugin/themes/common/jscss/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>

<script type="text/javascript"
src="../sms/plugin/themes/common/jscss/bootstrap-datetimepicker/bootstrap-datetimepicker.pt.js"></script>

';
/* End of insert for javascript calendar */

if (! auth_isvalid()) {
  auth_block();
}

switch (_OP_) {
  case 'list':
  $content = '<h2>' . _('Send from file') . '</h2><p />';
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
          <div class='col-md-8'>
            <form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_confirm\" enctype=\"multipart/form-data\" method=\"post\">
              " . _CSRF_FORM_ . "
              <p>" . _('Select Layout') . "</p>
              <select name='IdLayout' id='IdLayout' class='form-control'>
                <option value='0'>Layout_Default</option>
                <option value='1'>Layout_01</option>
                <option value='2'>Layout_02</option>
                <option value='3'>Layout_03</option>
              </select>
              <p>" . _('Please select CSV file') . "</p>
              <p><input type=\"file\" name=\"fncsv\" class='form-control'></p>
              <div class='well'>
                <p>" . _('Schedule to') . "</p>
                <div class='input-group date' id='msg_schedule' style='width:100%'>
                  <input type='text' id='datetimepicker' class='form-control' />
                  <span class='input-group-addon'>
                    <span class='glyphicon glyphicon-calendar'></span></span>
                  </div>
                  <p><small>" . _('Leave blank for delivery now') . "</small></p>
                  <script>
                    $('#msg_schedule').datetimepicker({language: 'pt',format: 'YYYY-MM-DD HH:mm'});
                  </script>
                  <p>" . _('Send by volume') . "</p>
                  <p>" . _('Limit at') . "<input type='number' value='0' name='lot_limit' style='width:60px' /></p>
                  <p>" . _('Each') . "<input type='number' value='0' name='lot_interval' style='width:60px' />" . _('minutes') . "</p>
                  <p><small>" . _('Leave zero in fields for send all now') . "</small></p>
                </div>
                <p><input type=\"submit\" value=\"" . _('Upload file') . "\" class=\"button\"></p>
              </form>
            </div>
          </td>
        </tr>
      </tbody>
    </table>";
    if ($err = $_SESSION['error_string']) {
      _p("<div class=error_string>$err</div>");
    }
    _p($content);
    break;
    case 'upload_confirm':
    import_file();
    break;
    case 'upload_cancel':
    if ($sid = $_REQUEST['sid']) {
      $db_query = "DELETE FROM " . _DB_PREF_ . "_featureSendfromfile WHERE sid='$sid'";
      if ($db_result = dba_affected_rows($db_query)) {
        $_SESSION['error_string'] = _('Send from file has been cancelled');
      } else {
        $_SESSION['error_string'] = _('Fail to remove cancelled entries from database');
      }
    } else {
      $_SESSION['error_string'] = _('Invalid session ID');
    }
    header("Location: " . _u('index.php?app=main&inc=feature_sendfromfile&op=list'));
    exit();
    break;
    case 'upload_process':
    set_time_limit(600);
    if ($sid = $_REQUEST['sid']) {
      //Parameter for send
      $lot_limit     =  (int) $_SESSION['lot_limit'];
      $lot_schedule  =  $_SESSION['lot_schedule'];
      $lot_interval  =  (int) $_SESSION['lot_interval'];
      if($lot_limit > 0)
      {
        if($lot_schedule == "")
        {
          $lot_schedule = date("Y-m-d H:i:s");
        }
        if($lot_interval==0)
        {
          $lot_interval = 10;
        }
      }
      $db_query = "SELECT * FROM " . _DB_PREF_ . "_featureSendfromfile WHERE sid='$sid'";
      $db_result = dba_query($db_query);
      $qtd = 0;
      while ($db_row = dba_fetch_array($db_result)) {
        // Do for limit
        if($lot_limit > 0)
        {
          // On the limit
          if($qtd++ >= $lot_limit)
          {
            // Calc for new schedule
            $hourNew = strtotime("$lot_schedule + $lot_interval minutes");
            $lot_schedule = date("Y-m-d H:i:s",$hourNew);
            $qtd = 1;
          }
        }
        $c_sms_to = $db_row['sms_to'];
        $c_sms_msg = addslashes($db_row['sms_msg']);
        $c_username = $db_row['sms_username'];
        if ($c_sms_to && $c_sms_msg && $c_username) {
          $type = 'text';
          $unicode = '0';
          $c_sms_msg = addslashes($c_sms_msg);
          if($lot_schedule=="")
          {
            list($ok, $to, $smslog_id, $queue) = sendsms_helper($c_username, $c_sms_to, $c_sms_msg, $type, $unicode);
          }
          else{
            list($ok, $to, $smslog_id, $queue) = sendsms_helper($c_username, $c_sms_to, $c_sms_msg, $type, $unicode,'',NULL,NULL,NULL,$lot_schedule);
          }
        }
      }
      $db_query = "DELETE FROM " . _DB_PREF_ . "_featureSendfromfile WHERE sid='$sid'";
      $db_result = dba_affected_rows($db_query);
      $_SESSION['error_string'] = _('SMS has been sent to valid numbers in uploaded file');
    } else {
      $_SESSION['error_string'] = _('Invalid session ID');
    }
    header("Location: " . _u('index.php?app=main&inc=feature_sendfromfile&op=list'));
    exit();
    break;
  }

function import_file()
  {
    // Select for layout
    $idLayout = ($_POST['IdLayout']=="")?"0":$_POST['IdLayout'];

    // Store param for send
    $_SESSION['lot_schedule']  =  $_POST['sms_schedule'];
    $_SESSION['lot_limit']     =  $_POST['lot_limit'];
    $_SESSION['lot_interval']  =  $_POST['lot_interval'];

    echo "<p>Schedule: " . $_POST['sms_schedule'] . "</p>";
    echo "<p>Limit: " . $_POST['lot_limit'] . "</p>";
    echo "<p>Interval: " . $_POST['lot_interval'] . "</p>";

    switch ($idLayout) {
      case '0':
      import_default();
      break;
      case '1':
      import_layout_01();
      break;
      case '2':
      import_layout_02();
      break;
      case '3':
      import_layout_03();
      break;
    }
  }

function import_default()
  {
    global $user_config;
    global $sendfromfile_row_limit;

    $filename = $_FILES['fncsv']['name'];
    $fn = $_FILES['fncsv']['tmp_name'];
    $fs = $_FILES['fncsv']['size'];
    $row = 0;
    $valid = 0;
    $invalid = 0;
    if (($fs == filesize($fn)) && file_exists($fn)) {
      if (($fd = fopen($fn, 'r')) !== FALSE) {
        $sid = uniqid('SID', true);
        $continue = true;
        while ((($data = fgetcsv($fd, $fs, ',')) !== FALSE) && $continue) {
          $row++;
          $sms_to = "55".trim($data[0]);
          $sms_msg = trim($data[1]);
          $sms_msg = sendfromfile_convert_toUTF8( $sms_msg );

          if (auth_isadmin()) {
            $sms_username = trim($data[2]); // Verify alternative
            $uid = user_username2uid($sms_username);
          } else {
            $sms_username = $user_config['username'];
            $uid = $user_config['uid'];
            $data[2] = $sms_username;
          }

          // Check if exist number on Blacklist
          if(!sendfromfile_blacklist($sms_to))
          {
            if ($sms_to && $sms_msg && $uid) {
              $db_query = "INSERT INTO " . _DB_PREF_ . "_featureSendfromfile (uid,sid,sms_datetime,sms_to,sms_msg,sms_username) ";
              $db_query .= "VALUES ('$uid','$sid','" . core_get_datetime() . "','$sms_to','" . addslashes($sms_msg) . "','$sms_username')";
              if ($db_result = dba_insert_id($db_query)) {
                $valid++;
                $item_valid[$valid - 1] = $data;
              } else {
                $invalid++;
                $item_invalid[$invalid - 1] = $data;
              }
            } else if ($sms_to || $sms_msg) {
              $invalid++;
              $item_invalid[$invalid - 1] = $data;
            }
            $num_of_rows = $valid + $invalid;
            if ($num_of_rows >= $sendfromfile_row_limit) {
              $continue = false;
            }
          }
          else {
            // Numero foi bloqueado
            $data[0] .= "(blocked)";
            $invalid++;
            $item_invalid[$invalid - 1]    = $data;
          }
        }
      }
    } else {
      $_SESSION['error_string'] = _('Invalid CSV file');
      header("Location: " . _u('index.php?app=main&inc=feature_sendfromfile&op=list'));
      exit();
      break;
    }

    $show_limit = 20;

    $content = '<h2>' . _('Send from file') . '</h2><p />';
    $content .= '<h3>' . _('Confirmation') . '</h3><p />';
    $content .= _('Uploaded file') . ': ' . $filename . '<p />';
    if ($valid) {
      $content .= _('Found valid entries in uploaded file') . ' (' . _('valid entries') . ': ' . $valid . ' ' . _('of') . ' ' . $num_of_rows . ')<p />';
      $content .= '<h3>' . _('Valid entries') . '</h3>';
      $content .= "
      <div class=table-responsive>
      <table class=playsms-table-list>
      <thead><tr>
      <th width=5%>*</th>
      <th width=20%>" . _('Destination number') . "</th>
      <th width=55%>" . _('Message') . "</th>
      <th width=20%>" . _('Username') . "</th>
      </tr></thead>
      <tbody>";
      $j = 0;
      $maxshow = count( $item_valid );
      $maxshow = ( $maxshow < $show_limit ) ? $maxshow : $show_limit;
      for ($i = 0; $i < $maxshow; $i++) {
        if ($item_valid[$i][0] && $item_valid[$i][1] && $item_valid[$i][2]) {
          $j++;
          $content .= "
          <tr>
          <td>&nbsp;" . $j . ".</td>
          <td>" . $item_valid[$i][0] . "</td>
          <td>" . $item_valid[$i][1] . "</td>
          <td>" . $item_valid[$i][2] . "</td>
          </tr>";
        }
      }
      $content .= "</tbody></table></div>";
    }
    if ($invalid) {
      $content .= '<p /><br />';
      $content .= _('Found invalid entries in uploaded file') . ' (' . _('invalid entries') . ': ' . $invalid . ' ' . _('of') . ' ' . $num_of_rows . ')<p />';
      $content .= '<h3>' . _('Invalid entries') . '</h3>';
      $content .= "
      <div class=table-responsive>
      <table class=playsms-table-list>
      <thead><tr>
      <th width=4>*</th>
      <th width='20%'>" . _('Destination number') . "</th>
      <th width='60%'>" . _('Message') . "</th>
      <th width='20%'>" . _('Username') . "</th>
      </tr></thead>";
      $j = 0;
      $maxshow = count( $item_invalid );
      $maxshow = ( $maxshow < $show_limit ) ? $maxshow : $show_limit;
      for ($i = 0; $i < $maxshow; $i++) {
        if ($item_invalid[$i][0] || $item_invalid[$i][1] || $item_invalid[$i][2]) {
          $j++;
          $content .= "
          <tr>
          <td>" . $j . ".</td>
          <td>" . $item_invalid[$i][0] . "</td>
          <td>" . $item_invalid[$i][1] . "</td>
          <td>" . $item_invalid[$i][2] . "</td>
          </tr>";
        }
      }
      $content .= "</tbody></table></div>";
    }
    $content .= '<h3>' . _('Your choice') . ': </h3><p />';
    $content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_cancel\" method=\"post\">";
    $content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sid . "'>";
    $content .= "<input type=\"submit\" value=\"" . _('Cancel send from file') . "\" class=\"button\"></p>";
    $content .= "</form>";
    $content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_process\" method=\"post\">";
    $content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sid . "'>";
    $content .= "<input type=\"submit\" value=\"" . _('Send SMS to valid entries') . "\" class=\"button\"></p>";
    $content .= "</form>";
    _p($content);
  }


function import_layout_01()
  {
    // Layout Sample
    //0       1         2        3
    //phone;message;account;username (admin)
    //556666666666;MARIA, we need to contact You, call at 14hs. call XXXX XXXX.;gfdgfgf6546

    global $user_config;
    global $sendfromfile_row_limit;

    // Layout positions
    $col_phone = 0;
    $col_message = 1;
    $col_contract = 2;
    $col_username = 3;

    $delimiter = ';';
    $filename = $_FILES['fncsv']['name'];
    $fn = $_FILES['fncsv']['tmp_name'];
    $fs = $_FILES['fncsv']['size'];
    $row = 0;
    $valid = 0;
    $invalid = 0;
    if (($fs == filesize($fn)) && file_exists($fn)) {
      if (($fd = fopen($fn, 'r')) !== FALSE) {
        $sid = uniqid('SID', true);
        $continue = true;
        while ((($data = fgetcsv($fd, $fs, $delimiter)) !== FALSE) && $continue) {
          $row++;
          // Jump first line of file
          if($row==1)
          {
            continue;
          }

          $sms_to = trim($data[$col_phone]);
          $sms_msg = trim($data[$col_message]);
          $sms_msg = sendfromfile_convert_toUTF8( $sms_msg );
          $data[$col_message] = $sms_msg;

          if (auth_isadmin()) {
            $sms_username = trim($data[$col_username]); // Verify alternative
            $uid = user_username2uid($sms_username);
          } else {
            $sms_username = $user_config['username'];
            $uid = $user_config['uid'];
            $data[2] = $sms_username;
          }

          // Check if exist number on Blacklist
          if(!sendfromfile_blacklist($sms_to))
          {
            if ($sms_to && $sms_msg && $uid) {
              $db_query = "INSERT INTO " . _DB_PREF_ . "_featureSendfromfile (uid,sid,sms_datetime,sms_to,sms_msg,sms_username) ";
              $db_query .= "VALUES ('$uid','$sid','" . core_get_datetime() . "','$sms_to','" . addslashes($sms_msg) . "','$sms_username')";
              if ($db_result = dba_insert_id($db_query)) {
                $valid++;
                $item_valid[$valid - 1] = $data;
              } else {
                $invalid++;
                $item_invalid[$invalid - 1] = $data;
              }
            } else if ($sms_to || $sms_msg) {
              $invalid++;
              $item_invalid[$invalid - 1] = $data;
            }
            $num_of_rows = $valid + $invalid;
            if ($num_of_rows >= $sendfromfile_row_limit) {
              $continue = false;
            }
          }
          else {
            // Number blocked
            $data[0] .= "(blocked)";
            $invalid++;
            $item_invalid[$invalid - 1]    = $data;
          }
        }
      }
    } else {
      $_SESSION['error_string'] = _('Invalid CSV file');
      header("Location: " . _u('index.php?app=main&inc=feature_sendfromfile&op=list'));
      exit();
      break;
    }

    $show_limit = 20;

    $content = '<h2>' . _('Send from file') . '</h2><p />';
    $content .= '<h3>' . _('Confirmation') . '</h3><p />';
    $content .= _('Uploaded file') . ': ' . $filename . '<p />';
    if ($valid) {
      $content .= _('Found valid entries in uploaded file') . ' (' . _('valid entries') . ': ' . $valid . ' ' . _('of') . ' ' . $num_of_rows . ')<p />';
      $content .= '<h3>' . _('Valid entries') . '</h3>';
      $content .= "
      <div class=table-responsive>
      <table class=playsms-table-list>
      <thead><tr>
      <th width=5%>*</th>
      <th width=20%>" . _('Destination number') . "</th>
      <th width=55%>" . _('Message') . "</th>
      <th width=20%>" . _('Username') . "</th>
      </tr></thead>
      <tbody>";
      $j = 0;
      $maxshow = count( $item_valid );
      $maxshow = ( $maxshow < $show_limit ) ? $maxshow : $show_limit;

      for ($i = 0; $i < $maxshow; $i++) {
        if ($item_valid[$i][0] && $item_valid[$i][1] && $item_valid[$i][2]) {
          $j++;
          $content .= "
          <tr>
          <td>&nbsp;" . $j . ".</td>
          <td>" . $item_valid[$i][0] . "</td>
          <td>" . $item_valid[$i][1] . "</td>
          <td>" . $item_valid[$i][2] . "</td>
          </tr>";
        }
      }
      $content .= "</tbody></table></div>";
    }
    if ($invalid) {
      $content .= '<p /><br />';
      $content .= _('Found invalid entries in uploaded file') . ' (' . _('invalid entries') . ': ' . $invalid . ' ' . _('of') . ' ' . $num_of_rows . ')<p />';
      $content .= '<h3>' . _('Invalid entries') . '</h3>';
      $content .= "
      <div class=table-responsive>
      <table class=playsms-table-list>
      <thead><tr>
      <th width=4>*</th>
      <th width='20%'>" . _('Destination number') . "</th>
      <th width='60%'>" . _('Message') . "</th>
      <th width='20%'>" . _('Username') . "</th>
      </tr></thead>";
      $j = 0;
      $maxshow = count( $item_invalid );
      $maxshow = ( $maxshow < $show_limit ) ? $maxshow : $show_limit;

      for ($i = 0; $i < $maxshow; $i++) {
        if ($item_invalid[$i][0] || $item_invalid[$i][1] || $item_invalid[$i][2]) {
          $j++;
          $content .= "
          <tr>
          <td>" . $j . ".</td>
          <td>" . $item_invalid[$i][0] . "</td>
          <td>" . $item_invalid[$i][1] . "</td>
          <td>" . $item_invalid[$i][2] . "</td>
          </tr>";
        }
      }
      $content .= "</tbody></table></div>";
    }
    $content .= '<h3>' . _('Your choice') . ': </h3><p />';
    $content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_cancel\" method=\"post\">";
    $content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sid . "'>";
    $content .= "<input type=\"submit\" value=\"" . _('Cancel send from file') . "\" class=\"button\"></p>";
    $content .= "</form>";
    $content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_process\" method=\"post\">";
    $content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sid . "'>";
    $content .= "<input type=\"submit\" value=\"" . _('Send SMS to valid entries') . "\" class=\"button\"></p>";
    $content .= "</form>";
    _p($content);
  }

function import_layout_02()
  {
    // Layout Sample
    //120140409CLIENT ROW ID
    //2$PRIMNAME$ Remember about this date and inform. code: $CODCLI$.
    //31166666666 PAULO ROBERTO DINIZ           08vvgfdgdgf
    //311666666666PAULO ROBERTO DINIZ           08fdcefefeg

    global $user_config;
    global $sendfromfile_row_limit;

    $filename = $_FILES['fncsv']['name'];
    $fn = $_FILES['fncsv']['tmp_name'];
    $fs = $_FILES['fncsv']['size'];
    $row = 0;
    $valid = 0;
    $invalid = 0;

    $message = "";
    $contract = "";
    if (($fs == filesize($fn)) && file_exists($fn)) {
      if (($fd = fopen($fn, 'r')) !== FALSE) {
        $sid = uniqid('SID', true);
        $continue = true;
        while ((($buffer = fgets($fd)) !== FALSE) && $continue) {
          $row++;

          // Capture type of line
          $tipo_linha = substr($buffer, 0,1);

          if($tipo_linha=="1")
          {
            // Line 1 data about client...
            continue;
          }

          if($tipo_linha=="2")
          {
            // Line 2 message
            $message  = substr($buffer, 1);
            continue;
          }

          if($tipo_linha!="3")
          {
            // Invalid line
            continue;
          }

          $name_cli = trim(substr($buffer, 12, 30));
          $contract = trim(substr($buffer, 42));
          $sms_to = "55".trim(substr($buffer,1,11));

          // Build a message with variables, if have
          $sms_msg = $message;
          $v_variables   = array('$PRIMNAME$', '$CODCLI$');
          $v_namecli = explode(" ",$name_cli);
          $v_replace  = array(trim($v_namecli[0]),$contract);
          $sms_msg = str_replace($v_variables, $v_replace, $sms_msg);
          $sms_msg = sendfromfile_convert_toUTF8( $sms_msg );
          // Build a array data for compatibility
          $data = array(
            $sms_to,        //phone
            $sms_msg,       //message
            ""              //username
          );

          if (auth_isadmin()) {
            $sms_username = trim($data[2]); // Verify alternative
            $uid = user_username2uid($sms_username);
          } else {
            $sms_username = $user_config['username'];
            $uid = $user_config['uid'];
            $data[2] = $sms_username;
          }

          // Check if exist number on Blacklist
          if(!sendfromfile_blacklist($sms_to))
          {
            if ($sms_to && $sms_msg && $uid) {
              $db_query = "INSERT INTO " . _DB_PREF_ . "_featureSendfromfile (uid,sid,sms_datetime,sms_to,sms_msg,sms_username) ";
              $db_query .= "VALUES ('$uid','$sid','" . core_get_datetime() . "','$sms_to','" . addslashes($sms_msg) . "','$sms_username')";
              if ($db_result = dba_insert_id($db_query)) {
                $valid++;
                $item_valid[$valid - 1] = $data;
              } else {
                $invalid++;
                $item_invalid[$invalid - 1] = $data;
              }
            } else if ($sms_to || $sms_msg) {
              $invalid++;
              $item_invalid[$invalid - 1] = $data;
            }
            $num_of_rows = $valid + $invalid;
            if ($num_of_rows >= $sendfromfile_row_limit) {
              $continue = false;
            }
          }
          else {
            // Number blocked
            $data[0] .= "(blocked)";
            $invalid++;
            $item_invalid[$invalid - 1]    = $data;
          }
        }
      }
    } else {
      $_SESSION['error_string'] = _('Invalid CSV file');
      header("Location: " . _u('index.php?app=main&inc=feature_sendfromfile&op=list'));
      exit();
      break;
    }

    $show_limit = 20;

    $content = '<h2>' . _('Send from file') . '</h2><p />';
    $content .= '<h3>' . _('Confirmation') . '</h3><p />';
    $content .= _('Uploaded file') . ': ' . $filename . '<p />';
    if ($valid) {
      $content .= _('Found valid entries in uploaded file') . ' (' . _('valid entries') . ': ' . $valid . ' ' . _('of') . ' ' . $num_of_rows . ')<p />';
      $content .= '<h3>' . _('Valid entries') . '</h3>';
      $content .= "
      <div class=table-responsive>
      <table class=playsms-table-list>
      <thead><tr>
      <th width=5%>*</th>
      <th width=20%>" . _('Destination number') . "</th>
      <th width=55%>" . _('Message') . "</th>
      <th width=20%>" . _('Username') . "</th>
      </tr></thead>
      <tbody>";
      $j = 0;
      $maxshow = count( $item_valid );
      $maxshow = ( $maxshow < $show_limit ) ? $maxshow : $show_limit;
      for ($i = 0; $i < $maxshow; $i++) {
        if ($item_valid[$i][0] && $item_valid[$i][1] && $item_valid[$i][2]) {
          $j++;
          $content .= "
          <tr>
          <td>&nbsp;" . $j . ".</td>
          <td>" . $item_valid[$i][0] . "</td>
          <td>" . $item_valid[$i][1] . "</td>
          <td>" . $item_valid[$i][2] . "</td>
          </tr>";
        }
      }
      $content .= "</tbody></table></div>";
    }
    if ($invalid) {
      $content .= '<p /><br />';
      $content .= _('Found invalid entries in uploaded file') . ' (' . _('invalid entries') . ': ' . $invalid . ' ' . _('of') . ' ' . $num_of_rows . ')<p />';
      $content .= '<h3>' . _('Invalid entries') . '</h3>';
      $content .= "
      <div class=table-responsive>
      <table class=playsms-table-list>
      <thead><tr>
      <th width=4>*</th>
      <th width='20%'>" . _('Destination number') . "</th>
      <th width='60%'>" . _('Message') . "</th>
      <th width='20%'>" . _('Username') . "</th>
      </tr></thead>";
      $j = 0;

      $maxshow = count( $item_invalid );
      $maxshow = ( $maxshow < $show_limit ) ? $maxshow : $show_limit;
      for ($i = 0; $i < $maxshow; $i++) {
        if ($item_invalid[$i][0] || $item_invalid[$i][1] || $item_invalid[$i][2]) {
          $j++;
          $content .= "
          <tr>
          <td>" . $j . ".</td>
          <td>" . $item_invalid[$i][0] . "</td>
          <td>" . $item_invalid[$i][1] . "</td>
          <td>" . $item_invalid[$i][2] . "</td>
          </tr>";
        }
      }
      $content .= "</tbody></table></div>";
    }
    $content .= '<h3>' . _('Your choice') . ': </h3><p />';
    $content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_cancel\" method=\"post\">";
    $content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sid . "'>";
    $content .= "<input type=\"submit\" value=\"" . _('Cancel send from file') . "\" class=\"button\"></p>";
    $content .= "</form>";
    $content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_process\" method=\"post\">";
    $content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sid . "'>";
    $content .= "<input type=\"submit\" value=\"" . _('Send SMS to valid entries') . "\" class=\"button\"></p>";
    $content .= "</form>";
    _p($content);
  }

function import_layout_03()
  {
    //Layout sample
    //0       1    2        3         4
    //PHONE;NAME;ACCOUNT;MESSAGEM;USERNAME(ADMIN)
    //1166666666;ZULMAR A RIBEIRO;195768512;#PNAME#, Take care about payment. Inform contract #CONTRACT
    // Define positions
    $col_phone = 0;
    $col_name = 1;
    $col_contract = 2;
    $col_message = 3;
    $col_username = 4;

    global $user_config;
    global $sendfromfile_row_limit;

    $delimiter = ';';
    $filename = $_FILES['fncsv']['name'];
    $fn = $_FILES['fncsv']['tmp_name'];
    $fs = $_FILES['fncsv']['size'];
    $row = 0;
    $valid = 0;
    $invalid = 0;
    if (($fs == filesize($fn)) && file_exists($fn)) {
      if (($fd = fopen($fn, 'r')) !== FALSE) {
        $sid = uniqid('SID', true);
        $continue = true;
        while ((($data = fgetcsv($fd, $fs, $delimiter)) !== FALSE) && $continue) {
          $row++;
          // Jump first row from file
          if($row==1)
          {
            continue;
          }

          $sms_country = "55";  // Include Country Code on phone number change for your
          $sms_phone = $sms_country . trim($data[$col_phone]);
          $sms_to = $sms_phone;
          $sms_msg = trim($data[$col_message]);

          // Build message with variables, if have
          $v_variables   = array('#PNAME#', '#CONTRACT#','$PRIMNAME$');
          $v_name = explode(" ",$data[$col_name]);
          $v_replace  = array(trim($v_name[0]),trim($data[$col_contract]),$v_name[0]);
          $sms_msg = str_replace($v_variables, $v_replace, $sms_msg);
          $sms_msg = sendfromfile_convert_toUTF8( $sms_msg );

          if (auth_isadmin()) {
            $sms_username = trim($data[$col_username]); // Verify alternative
            $uid = user_username2uid($sms_username);
          } else {
            $sms_username = $user_config['username'];
            $uid = $user_config['uid'];
            $data[$col_username] = $sms_username;
          }

          // Update array
          $data = array(
            $sms_to,
            $sms_msg,
            $sms_username
          );

          // Check if exist number on Blacklist
          if(!sendfromfile_blacklist($sms_to))
          {
            if ($sms_to && $sms_msg && $uid) {
              $db_query = "INSERT INTO " . _DB_PREF_ . "_featureSendfromfile (uid,sid,sms_datetime,sms_to,sms_msg,sms_username) ";
              $db_query .= "VALUES ('$uid','$sid','" . core_get_datetime() . "','$sms_to','" . addslashes($sms_msg) . "','$sms_username')";
              if ($db_result = dba_insert_id($db_query)) {
                $valid++;
                $item_valid[$valid - 1] = $data;
              } else {
                $invalid++;
                $item_invalid[$invalid - 1] = $data;
              }
            } else if ($sms_to || $sms_msg) {
              $invalid++;
              $item_invalid[$invalid - 1] = $data;
            }
            $num_of_rows = $valid + $invalid;
            if ($num_of_rows >= $sendfromfile_row_limit) {
              $continue = false;
            }
          }
          else{
            // Number blocked
            $data[0] .= "(blocked)";
            $invalid++;
            $item_invalid[$invalid - 1]    = $data;
          }
        }
      }
    } else {
      $_SESSION['error_string'] = _('Invalid CSV file');
      header("Location: " . _u('index.php?app=main&inc=feature_sendfromfile&op=list'));
      exit();
      break;
    }

    $show_limit = 20;

    $content = '<h2>' . _('Send from file') . '</h2><p />';
    $content .= '<h3>' . _('Confirmation') . '</h3><p />';
    $content .= _('Uploaded file') . ': ' . $filename . '<p />';
    if ($valid) {
      $content .= _('Found valid entries in uploaded file') . ' (' . _('valid entries') . ': ' . $valid . ' ' . _('of') . ' ' . $num_of_rows . ')<p />';
      $content .= '<h3>' . _('Valid entries') . '</h3>';
      $content .= "
      <div class=table-responsive>
      <table class=playsms-table-list>
      <thead><tr>
      <th width=5%>*</th>
      <th width=20%>" . _('Destination number') . "</th>
      <th width=55%>" . _('Message') . "</th>
      <th width=20%>" . _('Username') . "</th>
      </tr></thead>
      <tbody>";
      $j = 0;

      $maxshow = count( $item_valid );
      $maxshow = ( $maxshow < $show_limit ) ? $maxshow : $show_limit;
      for ($i = 0; $i < $maxshow; $i++) {
        if ($item_valid[$i][0] && $item_valid[$i][1] && $item_valid[$i][2]) {
          $j++;
          $content .= "
          <tr>
          <td>&nbsp;" . $j . ".</td>
          <td>" . $item_valid[$i][0] . "</td>
          <td>" . $item_valid[$i][1] . "</td>
          <td>" . $item_valid[$i][2] . "</td>
          </tr>";
        }
      }
      $content .= "</tbody></table></div>";
    }
    if ($invalid) {
      $content .= '<p /><br />';
      $content .= _('Found invalid entries in uploaded file') . ' (' . _('invalid entries') . ': ' . $invalid . ' ' . _('of') . ' ' . $num_of_rows . ')<p />';
      $content .= '<h3>' . _('Invalid entries') . '</h3>';
      $content .= "
      <div class=table-responsive>
      <table class=playsms-table-list>
      <thead><tr>
      <th width=4>*</th>
      <th width='20%'>" . _('Destination number') . "</th>
      <th width='60%'>" . _('Message') . "</th>
      <th width='20%'>" . _('Username') . "</th>
      </tr></thead>";
      $j = 0;
      $maxshow = count( $item_invalid );
      $maxshow = ( $maxshow < $show_limit ) ? $maxshow : $show_limit;
      for ($i = 0; $i < $maxshow; $i++) {
        if ($item_invalid[$i][0] || $item_invalid[$i][1] || $item_invalid[$i][2]) {
          $j++;
          $content .= "
          <tr>
          <td>" . $j . ".</td>
          <td>" . $item_invalid[$i][0] . "</td>
          <td>" . $item_invalid[$i][1] . "</td>
          <td>" . $item_invalid[$i][2] . "</td>
          </tr>";
        }
      }
      $content .= "</tbody></table></div>";
    }
    $content .= '<h3>' . _('Your choice') . ': </h3><p />';
    $content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_cancel\" method=\"post\">";
    $content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sid . "'>";
    $content .= "<input type=\"submit\" value=\"" . _('Cancel send from file') . "\" class=\"button\"></p>";
    $content .= "</form>";
    $content .= "<form action=\"index.php?app=main&inc=feature_sendfromfile&op=upload_process\" method=\"post\">";
    $content .= _CSRF_FORM_ . "<input type=hidden name=sid value='" . $sid . "'>";
    $content .= "<input type=\"submit\" value=\"" . _('Send SMS to valid entries') . "\" class=\"button\"></p>";
    $content .= "</form>";
    _p($content);
  }

function sendfromfile_blacklist($phone)
  {
    $phone = trim($phone);
    $db_query = "SELECT IFNULL(COUNT(*),0) qtd FROM playsms_featureBlacklist WHERE phone = '$phone'";
    $db_result = dba_query($db_query);
    while ( $db_row = dba_fetch_array($db_result) ) {
      return ( $db_row["qtd"] > 0 );
    }
    return FALSE;
  }

function sendfromfile_convert_toUTF8($text)
  {
    // convert the string to the target encoding
    $result = mb_convert_encoding($text, "UTF-8", "ISO-8859-1, ISO-8859-2, WINDOWS-1252, ASCII");
    return $result;
  }
