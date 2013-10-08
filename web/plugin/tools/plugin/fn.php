<?php

function plugin_hook_interceptsendsms($mobile_sender,$sms_sender,$sms_to,$sms_msg,$uid,$gpid,$sms_type,$unicode) {
   return $ret;	
}

function plugin_get_status($plugin,$name) {
    global $gateway_module;
	
    if ($plugin == "gateway") {
	if($gateway_module == $name) {
           $plugin_status = "<b><font color=green>"._('Enabled')."</font></b>";
	} else {
           $plugin_status = "<b><font color=red>"._('Disabled')."</font></b>";
	}
    } else if($plugin == "tools") {
	$field = $name."_enable";
	$db_query = "SELECT ".$field." FROM "._DB_PREF_."_tools".ucfirst($name)."_cfg";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$plugin_status = $db_row[$field];

        if($plugin_status == "1") {
           $plugin_status = "<b><font color=green>"._('Enabled')."</font></b>";
        } else {
           $plugin_status = "<b><font color=red>"._('Disabled')."</font></b>";
        }
    } else {
	$plugin_status = "<b><font color=grey>"._('NA')."</font></b>";
    }
    return $plugin_status;
}

function plugin_list_dir($plugin_id) {
        global $apps_path;
        $upload_path = $apps_path['plug']."/";

        $dir = opendir($upload_path);
        $html = "<td><b>"._('Name')."</b></td>";
        $html .= "<td><b>"._('Modification')."</b></td>";
        $html .= "</tr>";

        while ($f = readdir($dir)) {
         if(is_dir($upload_path.$f) && $f != "." && $f != "..") {
	    $dir_tab[] = $f;
         }
        }
        return $dir_tab;
}

function plugin_list_subdir($plugin) {
        global $apps_path;
        $upload_path = $apps_path['plug']."/".$plugin."/";

        $dir = opendir($upload_path);
	$z = 0;
        while ( $f = readdir($dir) ) {
	 $template = preg_match('/^_/', $f, $match);
         if( is_dir($upload_path.$f) && $f != "." && $f != ".." && $template != true  && $f != 'common') {
	   $subdir_tab[$z][name] .= $f;
	   $subdir_tab[$z][version] .= exec("cat ".$apps_path['plug']."/".$plugin."/".$f."/docs/VERSION");
	   $subdir_tab[$z][date] .= dd(filemtime($upload_path.$f));
	   $subdir_tab[$z][status] .= plugin_get_status($plugin,$f);
	   $z++;
         }
        }
        return $subdir_tab;
}

function dd($date) {
   return date("d/m/Y H:i:s",$date);
}

function plugin_table($plugin_dir) {

    $table = "
            <table id='m' cellpadding='1' cellspacing='2' border='0' width='100%' class=\"sortable\">
                <tr>
                    <td class='box_title' width='5%'><b>*</td>
                    <td class='box_title' width='15%'><b>"._('Name')."</b></td>
                    <td class='box_title' width='30%'><b>"._('Description')."</b></td>
                    <td class='box_title' width='3%'><b>"._('Version')."</b></td>
                    <td class='box_title' width='15%'><b>"._('Author')."</b></td>
                    <td class='box_title' width='15%'><b>"._('Date')."</b></td>
                    <td class='box_title' width='5%'><b>"._('Status')."</b></td>
                    <td class='box_title' width='5%'><b>"._('Action')."</b></td>
                </tr>";

          $subdir_tab = plugin_list_subdir($plugin_dir);

          for($l=0;$l<sizeof($subdir_tab);$l++) {
	    unset($plugin_info);
  	    $xml_file = "plugin/".$plugin_dir."/".$subdir_tab[$l][name]."/docs/info.xml";
	    include "plugin/".$plugin_dir."/".$subdir_tab[$l][name]."/docs/info.php";

          if( $fc = file_get_contents($xml_file) ) {
                $plugin_info = core_xml_to_array($fc);
          	#print_r($plugin_info);
          } else {
                logger_print("XML info file not present:".$error, 2, "plugin");
          }

            $table .= "
             <tr>
                <td>".(int)($l+1)."</td>
                <td>".$plugin_info['name']."</td>
                <td>".$plugin_info['description']."</td>
                <td>".$plugin_info['release']."</td>
                <td>".$plugin_info['author']."</td>
                <td>".$plugin_info['date']."</td>
                <td>".$plugin_info['status']."</td>
                <td>--</td>
             </tr>";
          }

   $table .= "</table>";
   return $table;
}

?>
