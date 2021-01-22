#!/usr/bin/php -q
<?php

// SET PLAYSMS WEB PATH
// ================================================================================

/**
 * You can edit this script and fill only $PLAYSMS_WEB below with the location
 * of your playSMS web path, it must be a full path.
 *
 * Example: 
 *
 *     $PLAYSMS_WEB = '/home/example/web';
 * 
 * You can also set $PLAYSMS_WEB outside this script instead.
 * 
 * Example, run this in console:
 *
 *     export PLAYSMS_WEB='/home/example/web'
 */

$PLAYSMS_WEB = '';


// DO NOT EDIT PAST THIS LINE
// ================================================================================

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


// init
// ================================================================================

if (!(PHP_SAPI == 'cli')) {
	echo "playSMS daemon must be called from cli";
	exit();
}

error_reporting(0);

set_time_limit(0);

define('_SECURE_', 1);

// declare this script started from playSMS daemon
$DAEMON_PROCESS = true;
define('_DAEMON_PROCESS_', true);
$core_config['daemon_process'] = true;

// get $PLAYSMS_WEB from env
if (!$PLAYSMS_WEB) {
	$PLAYSMS_WEB = $_SERVER['PLAYSMS_WEB'];
}

// error if $PLAYSMS_WEB undefined
if (!$PLAYSMS_WEB) {
	echo "\n";
	echo "Locate playSMS web path and set in shell environment\n\n";
	echo "For example, run this in shell once before running playSMS daemon:\n\n";
	echo "    export PLAYSMS_WEB=\"/home/example/web\"\n\n";
	echo "And then run playSMS daemon:\n\n";
	echo "    playsmsd status\n\n";
	exit();
}

// make sure playSMS web is in $PLAYSMS_WEB
if (!(file_exists($PLAYSMS_WEB . '/appsetup.php') && file_exists($PLAYSMS_WEB . '/init.php'))) {
	echo "playSMS web not found in " . $PLAYSMS_WEB . "\n";
}

// verify and define playSMS daemon location
define('_PLAYSMSD_', realpath($argv[0]));
if (!is_executable(_PLAYSMSD_)) {
    echo "playSMS daemon script " . _PLAYSMSD_ . " is not executable\n";
    exit();
}

// move base operation to $PLAYSMS_WEB
chdir($PLAYSMS_WEB);

// we don't need this anymore
unset($PLAYSMS_WEB);

// load app init and functions
if (file_exists('init.php')) {
    include 'init.php';
    
	$fn = $core_config['apps_path']['libs'] . '/function.php';
	if (file_exists($fn)) {    
	    include $fn;
	} else {
	    echo "playSMS is not properly installed\n";
    	exit();
	}
} else {
    echo "playSMS is not properly installed\n";
    exit();
}


// main
// ================================================================================

// Usage:
// playsmsd COMMAND [LOOP_FLAG COMMAND_PARAM]

$param = $argv;

// Daemon service
$COMMAND = ( isset($param[1]) ? strtolower($param[1]) : '' );

if ($COMMAND == '_fork_') {
	array_shift($param);
	$COMMAND = ( isset($param[1]) ? strtolower($param[1]) : '' );
}

// Loop flag: loop => execute in a loop, once => execute only once
$LOOP_FLAG = ( isset($param[2]) ? strtolower($param[2]) : 'loop');

// Service parameters
$COMMAND_PARAM = ( isset($param[3]) ? $param[3] : '' );

switch ($COMMAND) {
    case 'watchdog':
    case 'start':

        // start playsmsd services
        playsmsd_start();

        exit();
        break;

    case 'stop':

        // stop playsmsd services
        playsmsd_stop();

        exit();
        break;

    case 'restart':

        // stop, wait for 2 seconds and then start
        playsmsd_stop();
        
        // start
        playsmsd_start();

        exit();
        break;

    case 'status':

        if (playsmsd_allrunning()) {
            echo "playsmsd is running\n";
            playsmsd_pids_show();
        } else {
            echo "playsmsd is not running\n";
        }

        exit();
        break;

    case 'info':
    case 'check':

        // non-JSON output
        playsmsd_check(false);

        exit();
        break;

    case 'check_json':

        // JSON output
        playsmsd_check(true);

        exit();
        break;

    case 'log':

        // View log
        $debug_file = ($param[2] ? $param[2] : '');
        playsmsd_log($debug_file);

        exit();
        break;
        
    case 'version':
	    echo core_get_version() . PHP_EOL;
	    
	    exit();
    	break;
}

if (!$COMMAND) {
    echo "Usage: playsmsd <start|stop|restart|status|info|check|check_json|log|version>\n";
    exit();
}


// sub
// ================================================================================

if ($LOOP_FLAG == 'once') {

    // execute one time only

    // MAIN ONCE BLOCK

    //echo $COMMAND . " start time:" . time() . "\n";

    if ($COMMAND == 'sendqueue') {
        if ($COMMAND_PARAM) {
            $param = explode('_', $COMMAND_PARAM);
            if (($param[0] == 'Q') && ($queue = $param[1])) {
                $chunk = ((int) $param[2] ? (int) $param[2] : 0);
                sendsms_daemon($queue, $chunk);
            }
        }
    }

    if ($COMMAND == 'playsmsd') {
        if ($COMMAND_PARAM) {
            playsmsd_once($COMMAND_PARAM);
        }
    }

    // END OF ONCE BLOCK

    //echo $COMMAND . " end time:" . time() . "\n";

    exit();
} else if ($LOOP_FLAG == 'loop') {

    // execute in a loop

    $DAEMON_LOOPING = true;

    while ($DAEMON_LOOPING) {

        //echo $COMMAND . " start time:" . time() . "\n";

        // fixme anton - stop re-include init.php on every 'while' to get the most updated configurations
        // need to get updated $core_config and thats it, no need to run whole init
        //include 'init.php';

        // MAIN LOOP BLOCK

        switch ($COMMAND) {
            case 'schedule':
                playsmsd();
                break;

            case 'ratesmsd':
                rate_update();
                break;

            case 'dlrssmsd':
                dlrd();
                getsmsstatus();
                break;

            case 'recvsmsd':
                recvsmsd();
                getsmsinbox();
                break;

            case 'sendsmsd':

                // init step
                // $core_config['sendsmsd_queue'] = number of simultaneous queues
                // $core_config['sendsmsd_chunk'] = number of chunk per queue
                $c_list = array();
                $list = dba_search(_DB_PREF_ . '_tblSMSOutgoing_queue', 'id, queue_code', array(
                    'flag' => '0',
                ));
                foreach ($list as $db_row) {
                    $c_datetime_scheduled = strtotime($db_row['datetime_scheduled']);
                    if ($c_datetime_scheduled <= strtotime(core_get_datetime())) {
                        $c_list[] = $db_row;
                    }
                }

                $list = array();
                $sendsmsd_queue_count = (int) $core_config['sendsmsd_queue'];
                if ($sendsmsd_queue_count > 0) {
                    for ($i = 0; $i < $sendsmsd_queue_count; $i++) {
                        if ($c_list[$i]) {
                            $list[] = $c_list[$i];
                        }
                    }
                } else {
                    $list = $c_list;
                }

                foreach ($list as $db_row) {
                    // $db_row['queue_code'] = queue code
                    // $db_row['queue_count'] = number of entries in a queue
                    // $db_row['sms_count'] = number of SMS in an entry
                    $num = 0;
                    $db_query2 = "SELECT id FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst WHERE queue_id='" . $db_row['id'] . "'";
                    $db_result2 = dba_query($db_query2);
                    while ($db_row2 = dba_fetch_array($db_result2)) {
                        $num++;
                        if ($chunk = floor($num / $core_config['sendsmsd_chunk_size'])) {
                            $db_query3 = "UPDATE " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst SET chunk='" . $chunk . "' WHERE id='" . $db_row2['id'] . "'";
                            $db_result3 = dba_query($db_query3);
                        }
                    }

                    if ($num > 0) {
                        // destination found, update queue to process step
                        sendsms_queue_update($db_row['queue_code'], array(
                            'flag' => 3,
                        ));
                    } else {
                        // no destination found, something's not right with the queue, mark it as done (flag 1)
                        if (sendsms_queue_update($db_row['queue_code'], array(
                            'flag' => 1,
                        ))) {
                            _log('destination not found enforce finish queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
                        } else {
                            _log('destination not found fail to enforce finish queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
                        }
                    }
                }

                // process step
                $queue = array();

                $list = dba_search(_DB_PREF_ . '_tblSMSOutgoing_queue', 'id, queue_code', array(
                    'flag' => '3',
                ), '', $extras);
                foreach ($list as $db_row) {
                    // get chunks
                    $c_chunk_found = 0;
                    $db_query2 = "SELECT chunk FROM " . _DB_PREF_ . "_tblSMSOutgoing_queue_dst WHERE queue_id='" . $db_row['id'] . "' AND flag='0' GROUP BY chunk LIMIT " . $core_config['sendsmsd_chunk'];
                    $db_result2 = dba_query($db_query2);
                    while ($db_row2 = dba_fetch_array($db_result2)) {
                        $c_chunk = (int) $db_row2['chunk'];
                        $queue[] = 'Q_' . $db_row['queue_code'] . '_' . $c_chunk;
                        $c_chunk_found++;
                    }

                    if ($c_chunk_found < 1) {
                        // no chunk found, something's not right with the queue, mark it as done (flag 1)
                        if (sendsms_queue_update($db_row['queue_code'], array(
                            'flag' => 1,
                        ))) {
                            _log('chunk not found enforce finish queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
                        } else {
                            _log('chunk not found fail to enforce process queue:' . $db_row['queue_code'], 2, 'playsmsd sendsmsd');
                        }
                    }
                }

                // execute step
                $queue = array_unique($queue);
                if (count($queue) > 0) {
                    foreach ($queue as $q) {
                        $is_sending = (playsmsd_pid_get($q) ? true : false);
                        if (!$is_sending) {
                            $RUN_THIS = 'nohup ionice -c3 nice -n19 ' . _PLAYSMSD_ . ' _fork_ sendqueue once ' . $q . ' >/dev/null 2>&1 & printf "%u" $!';
                            echo $COMMAND . " execute: " . $RUN_THIS . "\n";
                            shell_exec($RUN_THIS);
                        }
                    }
                }
                break;

            default:
                $DAEMON_LOOPING = false;
        }

        // END OF MAIN LOOP BLOCK

        //echo $COMMAND . " end time:" . time() . "\n";

		// sleep for 0.1s
        time_nanosleep(0, 100000000);

        // empty buffer, yes doubled :)
        ob_end_flush();
        ob_end_flush();
    }

    // while TRUE
}
