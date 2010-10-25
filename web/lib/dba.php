<?php
if(!(defined('_SECURE_'))){die('Intruder alert');};

// DB.php is part of PHP PEAR-DB package
// previously removed in 0.9.2 but re-added in this release due to its complicated installation
include_once $apps_path['libs'].'/external/pear-db/DB.php';

// --------------------------------------------------------------------------//

function dba_connect($username,$password,$dbname,$hostname,$port="",$persistant="true") {
    global $apps_path;
    $access = $username;
    if ($password) {
	$access = "$username:$password";
    }
    $host = $hostname;
    if ($port) {
	$host = "$hostname:$port";
    }
    $dsn = _DB_TYPE_."://$access@$host/$dbname";
    $dba_object = DB::connect("$dsn","$persistant");

    if (DB::isError($dba_object)) {
        // $error_msg = "DB Name: $dbname<br>DB Host: $host";
        ob_end_clean();
        die ("<p align=left>".$dba_object->getMessage()."<br>".$error_msg."<br>");
    }
    return $dba_object;
}

function dba_query_simple($mystring) {
    global $dba_object, $dba_DB, $DBA_ROW_COUNTER, $DBA_LIMIT_FROM, $DBA_LIMIT_COUNT;
    $result = $dba_object->query($mystring);
    if (DB::isError($dba_object)) {
        // ob_end_clean();
        // die ("<p align=left>".$dba_object->getMessage()."<br>".$dba_object->userinfo."<br>");
	return "";
    }
    return $result;
}

function dba_query($mystring, $from="0", $count="0") {
    global $dba_object, $dba_DB, $DBA_ROW_COUNTER, $DBA_LIMIT_FROM, $DBA_LIMIT_COUNT;

    // log all db query
    if (function_exists('logger_print')) {
	logger_print("q:".$mystring, 4, "dba query");
    }
    
    $DBA_ROW_COUNTER = 0;

    if ($DBA_LIMIT_COUNT > 0) {
       $from = $DBA_LIMIT_FROM;
       $count = $DBA_LIMIT_COUNT;
    }
    $DBA_LIMIT_FROM = 0;
    $DBA_LIMIT_COUNT = 0;

    if (($from == 0) && ($count == 0)) {
       $result = dba_query_simple($mystring);
       return $result;
    }

    $is_special = false;
    switch ($dba_DB) {
       case "mssql":
          $limit = $from + $count;
          if ($limit == $count) {
              $str_limit = "SELECT TOP $limit";
              $mystring = str_replace ("SELECT",$str_limit,$mystring);
              $is_special = true;
          }
          break;
       case "mysql":
          $str_limit = " LIMIT $from, $count";
          $mystring .= $str_limit;
          $is_special = true;
          break;
       default:
          break;
    }

    if ($is_special) {
        $result = $dba_object->query($mystring);
    } else {
        $result = $dba_object->limitQuery($mystring, $from, $count);
    }

    if (DB::isError($dba_object)) {
        // ob_end_clean();
        // die ("<p align=left>".$dba_object->getMessage()."<br>".$dba_object->userinfo."<br>");
	return "";
    }

    if (!$is_special) {
        $result->limit_from = $from;
        $result->limit_count = $count;
    }

    return $result;
}

function dba_fetch_array($myresult, $rownum=null) {
    global $DBA_ROW_COUNTER;
    if (!$myresult) {
	return "";
    }
    
    if (!$DBA_ROW_COUNTER) {
       $DBA_ROW_COUNTER = $myresult->limit_from;
    }
    if (DB::isError($myresult)) {
        // ob_end_clean();
        // die ("<p align=left>".$myresult->getMessage()."<br>".$myresult->userinfo."<br>");
	return "";
    }
    $myresult->row_counter = $DBA_ROW_COUNTER++;
    $result = $myresult->fetchRow(DB_FETCHMODE_ASSOC, $rownum);
    return $result;
}

function dba_fetch_row($myresult, $rownum=null) {
    global $DBA_ROW_COUNTER;
    if (!$myresult) {
	return "";
    }

    if (!$DBA_ROW_COUNTER) {
       $DBA_ROW_COUNTER = $myresult->limit_from;
    }
    if (DB::isError($myresult)) {
        // ob_end_clean();
        // die ("<p align=left>".$myresult->getMessage()."<br>".$myresult->userinfo."<br>");
	return "";
    }
    $myresult->row_counter = $DBA_ROW_COUNTER++;
    $result = $myresult->fetchRow(DB_FETCHMODE_ORDERED, $rownum);
    return $result;
}

function dba_num_rows($mystring) {
    global $dba_object, $dba_DB, $DBA_ROW_COUNTER, $DBA_LIMIT_FROM, $DBA_LIMIT_COUNT;
    $myresult = dba_query ($mystring);
    if (DB::isError($myresult)) {
        // ob_end_clean();
        // die ("<p align=left>".$myresult->getMessage()."<br>".$myresult->userinfo."<br>");
	return 0;
    }
    if ($result = $myresult->numRows()) return $result;
    return 0;
}

function dba_affected_rows($mystring) {
    global $dba_object, $dba_DB, $DBA_ROW_COUNTER, $DBA_LIMIT_FROM, $DBA_LIMIT_COUNT;
    $myresult = dba_query ($mystring);
    if (DB::isError($myresult)) {
        // ob_end_clean();
        // die ("<p align=left>".$myresult->getMessage()."<br>".$myresult->userinfo."<br>");
	return 0;
    }
    if ($result = $dba_object->affectedRows()) return $result;
    return 0;
}

function dba_insert_id($mystring) {
    global $dba_object, $dba_DB, $DBA_ROW_COUNTER, $DBA_LIMIT_FROM, $DBA_LIMIT_COUNT;
    if (dba_query ($mystring)) {
	switch (_DB_TYPE_) {
	    case "mysql":
    		$myquery = "SELECT @@IDENTITY";
    		$result_tmp = dba_query($myquery);
    		list($result) = dba_fetch_row($result_tmp);
		break;
	    case "sqlite3":
    		$myquery = "SELECT last_insert_rowid()";
    		$result_tmp = dba_query($myquery);
    		$result = dba_fetch_row($result_tmp);
		$result = $result[0];
	}
    }
    return $result;
}

function dba_disconnect() {
    global $dba_object;
    if ($dba_object->disconnect()) {
	return 1;
    } else {
	return 0;
    }
}

?>