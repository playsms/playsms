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

/**
 * Get pid for certain playsmsd process
 *
 * @param string $process
 *        process name
 * @return integer PID
 */
function playsmsd_pid_get($process)
{
    return (int) trim(shell_exec('ps -eo pid,command|grep playsmsd|grep "' . $process . '"|grep -v grep|sed -e "s/^ *//" -e "s/ *$//"|cut -d" " -f1|tr "\n" " "'));
}

/**
 * Get pids for all playsmsd main process
 *
 * @return array PIDs
 */
function playsmsd_pids()
{
    $services = playsmsd_services();
    foreach ($services as $service) {
    	$pids[$service] = playsmsd_pid_get($service);
    }
    
    return $pids;
}

/**
 * Show pids
 */
function playsmsd_pids_show()
{
    $pids = playsmsd_pids();
    foreach ($pids as $service => $pid) {
    	echo $service . " at pid " . $pid . "\n";
   	}
}

/**
 * Check whether or not playsmsd processes are running
 *
 * @return boolean TRUE if all processes are running
 */
function playsmsd_isrunning()
{
	$isrunning = false;
	
    $pids = playsmsd_pids();
    foreach ($pids as $pid) {
        if ($pid) {
            $isrunning = true;
        } else {
            $isrunning = false;

            break;
        }
    }

    return $isrunning;
}

/**
 * Start playsmsd scripts
 */
function playsmsd_start()
{
    global $PLAYSMS_BIN;

    if (playsmsd_isrunning()) {
        echo "playsmsd is already running\n";
        playsmsd_pids_show();
        
        exit();
    }

    // stop all daemons
    shell_exec('$PLAYSMS_BIN stop >/dev/null 2>&1 & printf "%u" $!');
    sleep(1);

    // run playsmsd services
    $services = playsmsd_services();
    foreach ($services as $service) {
	    $pids[$service] = shell_exec('nohup ionice -c3 nice -n19 ' . $PLAYSMS_BIN . ' ' . $service . ' >/dev/null 2>&1 & printf "%u" $!');
    }

    if (playsmsd_isrunning()) {
        echo "playsmsd has been started\n";
        playsmsd_pids_show();
    } else {
        echo "Unable to start playsmsd\n";
    }
}

/**
 * Stop playsmsd scripts
 */
function playsmsd_stop()
{
    $pids = playsmsd_pids();
    foreach ($pids as $key => $val) {
        if ($key && $val) {
            echo $key . " at pid " . $val . " will be killed..\n";
            shell_exec("kill " . $val . " >/dev/null 2>&1");
        }
    }

    if (playsmsd_isrunning()) {
        echo "Unable to stop playsmsd\n";
        playsmsd_pids_show();
    } else {
        echo "playsmsd has been stopped\n";
    }
}

/**
 * Stop child scripts
 */
function playsmsd_stop_childs()
{
    $pids['sendqueue'] = playsmsd_pid_get('sendqueue');
    foreach ($pids as $key => $val) {
        if ($key && $val) {
            echo $key . " at pid " . $val . " will be killed..\n";
            shell_exec("kill " . $val . " >/dev/null 2>&1");
        }
    }
}

/**
 * Check variables and states of playsmsd
 *
 * @param boolean $json
 *        TRUE for json output
 * @return string
 */
function playsmsd_check($json)
{
    global $PLAYSMS_WEB, $PLAYSMS_STR, $PLAYSMS_LOG, $PLAYSMS_BIN;

    $data = array(
        'PLAYSMS_WEB' => $PLAYSMS_WEB,
        'PLAYSMS_STR' => $PLAYSMS_STR,
        'PLAYSMS_LOG' => $PLAYSMS_LOG,
        'PLAYSMS_BIN' => $PLAYSMS_BIN,
        'IS_RUNNING' => playsmsd_isrunning(),
        'PIDS' => playsmsd_pids(),
    );

    if ($json) {
        echo json_encode($data);
    } else {
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    echo $key . " " . $k . " = " . $v . "\n";
                }
            } else {
                echo $key . " = " . $val . "\n";
            }
        }
    }
}

/**
 * View log
 *
 * @param string $debug_file
 *        Save log to debug file
 */
function playsmsd_log($debug_file = '')
{
    global $core_config;

    $log = $core_config['apps_path']['logs'] . '/' . $core_config['logfile'];
    if (file_exists($log) && is_writable($log)) {

        $process = 'tail -n 0 -f ' . $log . ' 2>&1';
        if ($debug_file) {
            @shell_exec('touch ' . $debug_file);
            if (file_exists($debug_file)) {
                $process .= '| tee ' . $debug_file;
            }
        }

        $handle = popen($process, 'r');
        while (!feof($handle)) {
            $buffer = fgets($handle);
            echo $buffer;
            flush();
        }
        pclose($handle);
    } else {
    	echo "playSMS log file " . $log . " not found or unwritable\n";
    }
}

/**
 * Get playSMS daemon services
 *
 * @return array Service names
 */
function playsmsd_services() {

	// fixme anton - will add core_hook() here

	$services = [
		'schedule',
		'ratesmsd',
		'dlrssmsd',
		'sendsmsd',
		'recvsmsd',
	];
	
	return $services;
}
