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

// check CLI
if (!(PHP_SAPI == 'cli')) {
    echo "playSMS daemon must be called from cli\n";
    exit();
}

// prevent running this script as root
if (posix_getuid() === 0) {
    echo "playSMS daemon must not run as root\n";
    exit();
}

// suppress errors
error_reporting(0);

// make the script run forever
set_time_limit(0);

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
$COMMAND = (isset($param[1]) ? strtolower($param[1]) : '');

if ($COMMAND == '_fork_') {
    array_shift($param);
    $COMMAND = (isset($param[1]) ? strtolower($param[1]) : '');
}

// Loop flag: loop => execute in a loop, once => execute only once
$LOOP_FLAG = (isset($param[2]) ? strtolower($param[2]) : 'loop');

// Service parameters
$COMMAND_PARAM = (isset($param[3]) ? $param[3] : '');

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

    //echo $COMMAND . " " . $COMMAND_PARAM . " start time:" . time() . "\n";

    // MAIN ONCE BLOCK

    if ($COMMAND) {
        playsmsd_once($COMMAND, $COMMAND_PARAM);
    }

    // END OF ONCE BLOCK

    //echo $COMMAND . " " . $COMMAND_PARAM . " end time:" . time() . "\n";

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
                dlr_fetch();
                break;
        }
        // end switch $COMMAND
        
        if ($COMMAND) {
        	playsmsd_loop($COMMAND, $COMMAND_PARAM);
        }

        // END OF MAIN LOOP BLOCK

        //echo $COMMAND . " end time:" . time() . "\n";

        // sleep for 1s
        sleep(1);

        // empty buffer, yes doubled :)
        ob_end_flush();
        ob_end_flush();
    }

    // while TRUE
}
