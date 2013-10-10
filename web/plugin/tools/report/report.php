<?php
if(!valid()){forcenoaccess();};

$slid = $_GET['slid'];
$sms_price = 25;

switch ($op)
{
    case "report_admin":
        if(!isadmin()){forcenoaccess();};

	$content = "
	    <h2>"._('Report')."</h2>
	    <table width='100%' cellpadding='1' cellspacing='2' border='0' class='sortable'>
        <thead>
	    <tr>
	      <th align='center' width='20%'>"._('User')."</th>
	      <th align='center' width='15%'>"._('Pending')."</th>
	      <th align='center' width='15%'>"._('Sent')."</th>
	      <th align='center' width='15%'>"._('Delivered')."</th>
	      <th align='center' width='15%'>"._('Failed')."</th>
	      <th align='center' width='15%'>"._('Deleted')."</th>
	      <!--th align=center width=20%>Solde</th-->
	      <!--th align=center width=4 class=\"sorttable_nosort\">Action</th-->
	      <!--th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_outgoing)></td-->
	    </tr>
        </thead>
        <tbody>
	";

	$l = 0;

	// USER LIST RESTRIVAL
	$db_queryU = "SELECT * FROM "._DB_PREF_."_tblUser ORDER BY username";
        $db_resultU = dba_query($db_queryU);
	while ($db_rowU = dba_fetch_array($db_resultU))
        {
	$l++;
	$username = $db_rowU['username'];
	$uid = username2uid($username);
	// SMS SENT
	$db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='1' AND flag_deleted='0'";
	$db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_sent = $db_row['count'];
        $sum_num_rows_sent = ($sum_num_rows_sent+$num_rows_sent);

        // SMS PENDING
        $db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='0' AND flag_deleted='0'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_pending = $db_row['count'];
        $sum_num_rows_pending = ($sum_num_rows_pending+$num_rows_pending);

	// SMS DELIVERED
        $db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='3' AND flag_deleted='0'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_delivered = $db_row['count'];
	$sum_num_rows_delivered = ($sum_num_rows_delivered+$num_rows_delivered);

	// SMS FAILED
        $db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='2' AND flag_deleted='0'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_failed = $db_row['count'];
	$sum_num_rows_failed = ($sum_num_rows_failed+$num_rows_failed);

        // SMS DELETED
        $db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND flag_deleted='1'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_deleted = $db_row['count'];
        $sum_num_rows_deleted = ($sum_num_rows_deleted+$num_rows_deleted);


	$total_price = ($num_rows_delivered*$sms_price);
	$sign = "";
	if ($total_price > 0) {
	  $sign = "-";
	}
	elseif($total_price < 0) {
	  $sign = "+";
	}

	    //$td_class = "box_text_odd";	    
	    $td_class = ($l & 1) ? 'odd' : 'even';    
	    $content .= "
		<tr>
	          <td valign='top' class='$td_class' align='center' width='4'>$username</td>
	          <td valign='top' class='$td_class' align='center' width='10%'><a href='index.php?app=menu&inc=tools_report&op=report_sms_list&status=Pending&uid=$uid'>$num_rows_pending</a></td>
	          <td valign='top' class='$td_class' align='center' width='10%'><a href='index.php?app=menu&inc=tools_report&op=report_sms_list&status=Sent&uid=$uid'>$num_rows_sent</a></td>
	          <td valign='top' class='$td_class' align='center' width='10%'><a href='index.php?app=menu&inc=tools_report&op=report_sms_list&status=Delivered&uid=$uid'>$num_rows_delivered</a></td>
	          <td valign='top' class='$td_class' align='center' width='10%'><a href='index.php?app=menu&inc=tools_report&op=report_sms_list&status=Failed&uid=$uid'>$num_rows_failed</a></td>
	          <td valign='top' class='$td_class' align='center' width='10%'><a href='index.php?app=menu&inc=tools_report&op=report_sms_list&status=Deleted&uid=$uid'>$num_rows_deleted</a></td>
	          <!--td valign=top class=$td_class align=center width=10%>$sign $total_price CFP</td-->
		  <!--td class=$td_class width=4>
		    <input type=hidden name=slid".$j." value=\"$current_slid\">
		    <input type=checkbox name=chkid".$j.">
		</td-->		  
		</tr>
	    ";
	}

	$sum_total_price = ($sum_num_rows_delivered*$sms_price);
	$sum_total = ($sum_num_rows_pending+$sum_num_rows_delivered+$sum_num_rows_sent+$sum_num_rows_failed);
        $content .= "
        <thead>
                <tr class=\"sortable\">
                  <th valign='top' align='left' width='4'>"._('Total').":&nbsp;&nbsp;&nbsp;$sum_total</td>
                  <th valign='top' align='center' width='10%'>$sum_num_rows_pending</th>
                  <th valign='top' align='center' width='10%'>$sum_num_rows_sent</th>
                  <th valign='top' align='center' width='10%'>$sum_num_rows_delivered</th>
                  <th valign='top' align='center' width='10%'>$sum_num_rows_failed</th>
                  <th valign='top' align='center' width='10%'>$sum_num_rows_deleted</th>
                  <!--th valign=top align=center width=10%>$sum_total_price CFP</th-->
                  <!--th valign=top align=center width=10%></th-->
                  <!--th valign=top align=center width=10%></th-->
                </tr>
        </thead>
        ";


	$item_count = $j;
	$content .= "
	</tbody></table>
	<!--table width=100% cellpadding=0 cellspacing=0 border=0>
	<tr>
	    <td width=100% colspan=2 align=right>
		<input type=hidden name=item_count value=\"$item_count\">
		<input type=submit value=\"Delete selection\" class=button />
		<input type=submit value=\"Recycle selection\" class=button />
	    </td>
	</tr>
	</table-->	
	";

	if ($err)
	{
	    echo "<font color=red>$err</font><br><br>";
	}
	echo $content;
	break;

    case "report_user":

        $content = "
	    <h2>"._('Report')."</h2>
            <table width='100%' cellpadding='1' cellspacing='2' border='0' class='sortable'>
        <thead>
            <tr>
              <th align='center' width='4'>*</th>
              <th align='center' width='20%'>"._('Pending')."</th>
              <th align='center' width='20%'>"._('Sent')."</th>
              <th align='center' width='20%'>"._('Delivered')."</th>
              <th align='center' width='20%'>"._('Failed')."</th>
              <!--th align='center' width='20%'>Balance</th-->
              <!--th align=center width=4 class=\"sorttable_nosort\">Action</th>
              <th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_outgoing)></td-->
            </tr>
        </thead>
        <tbody>
        ";

        // SMS PENDING
        $db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='0'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_pending = $db_row['count'];
        $db_query = "SELECT SUM(n_sms) FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='0'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $n_sms_pending_total = $db_row['SUM(n_sms)'];

        // SMS SENT
        $db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='1'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_sent = $db_row['count'];
        $db_query = "SELECT SUM(n_sms) FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='1'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $n_sms_sent_total = $db_row['SUM(n_sms)'];

        // SMS DELIVERED
        $db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='3'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_delivered = $db_row['count'];
        $db_query = "SELECT SUM(n_sms) FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='3'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $n_sms_delivered_total = $db_row['SUM(n_sms)'];

        // SMS FAILED
        $db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='2'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_failed = $db_row['count'];
        $db_query = "SELECT SUM(n_sms) FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='2'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $n_sms_failed_total = $db_row['SUM(n_sms)'];


        $total_price = (($num_rows_delivered+$num_rows_sent)*$sms_price);
        $sign = "";
        if ($total_price > 0) {
          $sign = "-";
        }
        elseif($total_price < 0) {
          $sign = "+";
        }

            $td_class = "box_text_odd";
            $content .= "
                <tr>
                  <td valign='top' class='$td_class' align='left' width='4'>*</td>
                  <td valign='top' class='$td_class' align='center' width='10%'>$num_rows_pending</td>
                  <td valign='top' class='$td_class' align='center' width='10%'>$num_rows_sent</td>
                  <td valign='top' class='$td_class' align='center' width='10%'>$num_rows_delivered</td>
                  <td valign='top' class='$td_class' align='center' width='10%'>$num_rows_failed</td>
                  <!--td valign='top' class='$td_class' align='center' width='10%'>$sign $total_price CFP</td-->
                <!--td class=$td_class width=4>
                    <input type=hidden name=slid".$j." value=\"$current_slid\">
                    <input type=checkbox name=chkid".$j.">
                </td-->
                </tr>
            ";
        $item_count = $j;
        $content .= "
        </tbody></table>
        <!--table width=100% cellpadding=0 cellspacing=0 border=0>
        <tr>
            <td width=100% colspan=2 align=right>
                <input type=hidden name=item_count value=\"$item_count\">
                <input type=submit value=\"Delete selection\" class=button />
            </td>
        </tr>
        </table-->
        </form>
        ";
        if ($err)
        {
            echo "<font color='red'>$err</font><br><br>";
        }
        echo $content;
        break;

    case "report_sms_list":
	$status = $_GET['status'];	
	$uid = $_GET['uid'];	
	
	if($status == "Pending")
		$p_status_num = '0';
	else if($status == "Sent")
		$p_status_num = '1';
	else if($status == "Delivered")
		$p_status_num = '3';
	else if($status == "Failed")
		$p_status_num = '2';
	else if($status == "Deleted")
                $flag_del = '1';
	
        if(!$page){$page = 1;}
        if(!$nav){$nav = 1;}

        $line_per_page = 50;
        $max_nav = 15;

	if($flag_del) {
          $db_query = "SELECT count(*) as count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND flag_deleted='1'";
	} else {
          $db_query = "SELECT count(*) as count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND flag_deleted='0' AND p_status='$p_status_num'";
	}
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows = $db_row['count'];

        $pages = ceil($num_rows/$line_per_page);
        $nav_pages = themes_navbar($pages, $nav, $max_nav, "index.php?app=menu&inc=tools_report&op=report_sms_list&status=$status&uid=$uid", $page);
        $limit = ($page-1)*$line_per_page;

        $content = "
            <h2>"._($status.' SMS')."</h2>
            <p>$nav_pages</p>
            <!-- form name=\"fm_outgoing\" action=\"index.php?app=menu&inc=tools_report&op=report_manage_all\" method=post onSubmit=\"return SureConfirm()\" -->
            <form name=\"fm_outgoing\" action=\"index.php?app=menu&inc=tools_report&op=report_manage_all\" method=post>
            <table width=100% cellpadding=1 cellspacing=2 border=0 class=\"sortable\">
        <thead>
            <tr>
              <th align=center width=4>*</th>
              <th align=center width=20%>"._('Time')."</th>
              <th align=center width=10%>"._('Receiver')."</th>
              <th align=center width=60%>"._('Message')."</th>
              <th align=center width=10%>"._('Status')."</th>
              <th align=center width=4>"._('Group')."</th>
              <th align=center width=4 class=\"sorttable_nosort\">"._('Action')."</th>
              <th width=4 class=\"sorttable_nosort\"><input type=checkbox onclick=CheckUncheckAll(document.fm_outgoing)></td>
            </tr>
        </thead>
        <tbody>
        ";

	if($flag_del) {
           $db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND flag_deleted='1' ORDER BY smslog_id DESC LIMIT $limit,$line_per_page";
	} else {
           $db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND flag_deleted='0' AND p_status='$p_status_num' ORDER BY smslog_id DESC LIMIT $limit,$line_per_page";
	}
        $db_result = dba_query($db_query);
        $i = ($num_rows-($line_per_page*($page-1)))+1;
        $j=0;
        while ($db_row = dba_fetch_array($db_result))
        {
            $j++;
            $current_slid = $db_row['smslog_id'];
            $p_dst = $db_row['p_dst'];
            $p_desc = phonebook_number2name($p_dst);
            $current_p_dst = $p_dst;
            if ($p_desc)
            {
                $current_p_dst = "$p_dst<br>($p_desc)";
            }
            $hide_p_dst = $p_dst;
            if ($p_desc)
            {
                $hide_p_dst = "$p_dst ($p_desc)";
            }
            $p_sms_type = $db_row['p_sms_type'];
            $hide_p_dst = str_replace("\'","",$hide_p_dst);
            $hide_p_dst = str_replace("\"","",$hide_p_dst);
            $p_msg = core_display_text($db_row['p_msg'], 25);
            if (($p_footer = $db_row['p_footer']) && (($p_sms_type == "text") || ($p_sms_type == "flash")))
            {
                $p_msg = $p_msg." $p_footer";
            }
            $p_datetime = core_display_datetime($db_row['p_datetime']);
            $p_update = $db_row['p_update'];
            $p_status = $db_row['p_status'];
            $p_gpid = $db_row['p_gpid'];
            $flag_deleted = $db_row['flag_deleted'];
            // 0 = pending
            // 1 = sent
            // 2 = failed
            // 3 = delivered
	    if ($flag_deleted == "1")
            {
                $p_status = "<p><font color=black>"._('Deleted')."</font></p>";
            }
	    else if ($p_status == "1")
            {
                $p_status = "<p><font color=green>"._('Sent')."</font></p>";
            }
            else if ($p_status == "2")
            {
                $p_status = "<p><font color=red>"._('Failed')."</font></p>";
            }
            else if ($p_status == "3")
            {
                $p_status = "<p><font color=green>"._('Delivered')."</font></p>";
            }
            else
            {
                $p_status = "<p><font color=orange>"._('Pending')."</font></p>";
            }
            if ($p_gpid)
            {
                $p_gpcode = strtoupper(phonebook_groupid2code($p_gpid));
            }
            else
            {
                $p_gpcode = "&nbsp;";
            }
            $i--;
            $td_class = ($i % 2) ? "box_text_odd" : "box_text_even";
            $content .= "
                <tr>
                  <td valign=top class=$td_class align=left width=4>$i.</td>
                  <td valign=top class=$td_class align=center width=10%>$p_datetime</td>
                  <td valign=top class=$td_class align=center width=15%>$current_p_dst</td>
                  <td valign=top class=$td_class align=left width=55%>$p_msg</td>
                  <td valign=top class=$td_class align=center width=10%>$p_status</td>
                  <td valign=top class=$td_class align=center width=5%>$p_gpcode</td>
                  <td valign=top class=$td_class align=center width=5%>";
		  if($status == "Failed") {
		    $content .= "<a href='index.php?app=menu&inc=tools_report&op=report_recycle&slid=$current_slid&uid=$uid'>$report_icon_resent</a>";
		  }
                    $content .= "<a href=\"javascript: ConfirmURL('"._('Are you sure you want to delete outgoing SMS ?')." ("._('to').": `$hide_p_dst`, "._('number').": $i)','index.php?app=menu&inc=user_outgoing&op=user_outgoing_del&slid=$current_slid')\">$icon_delete</a>
                  </td>
                <td class=$td_class width=4>
                    <input type=hidden name=slid".$j." value=\"$current_slid\">
                    <input type=checkbox name=chkid".$j.">
                </td>
                </tr>
            ";
        }
        $item_count = $j;
        $content .= "
        </tbody></table>
        <table width=100% cellpadding=0 cellspacing=0 border=0>
        <tr>
            <td width=100% colspan=2 align=right>
                <input type='hidden' name='item_count' value='$item_count'>
                <input type='submit' name='delete' value='"._('Delete selection')."' class='button' />
                <input type='submit' name='recycle' value='"._('Recycle selection')."' class='button' />
            </td>
        </tr>
        </table>
        </form>
        <p>$nav_pages</p>
        ";
        if ($err)
        {
            echo "<div class=error_string>$err</div><br><br>";
        }
        echo $content;
        break;

    case "report_recycle":
	$smslog_id = $_GET['slid'];
	$uid = $_GET['uid'];
	$db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE smslog_id=$smslog_id";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);

	$ret = resendsms($smslog_id,$db_row['p_src'],$db_row['p_footer'],$db_row['p_dst'],$db_row['p_msg'],$db_row['uid'],$db_row['p_gpid'],$db_row['p_sms_type'],$db_row['unicode']);
	echo $ret['smslog_id'];

        break;

    case "report_manage_all":
        $item_count = $_POST['item_count'];
	$uid = $_GET['uid'];

	if($_POST['delete'] == "Delete selection") {
           for ($i=1;$i<=$item_count;$i++) {
             $chkid = $_POST['chkid'.$i];
             $slid = $_POST['slid'.$i];

             if(($chkid=="on") && $slid) {
                $db_query = "UPDATE "._DB_PREF_."_tblSMSOutgoing SET c_timestamp='".mktime()."',flag_deleted='1' WHERE smslog_id='$slid'";
                $db_result = dba_affected_rows($db_query);
             }
           }
        //header ("Location: index.php?app=menu&inc=user_outgoing&op=user_outgoing&err=".urlencode(_('Selected outgoing SMS has been deleted')));
	} elseif($_POST['recycle'] == "Recycle selection") {
	   $l = 0;
           for ($i=1;$i<=$item_count;$i++) {
             $chkid	= $_POST['chkid'.$i];
             $smslog_id	= $_POST['slid'.$i];

             if(($chkid=="on") && $smslog_id) {
       		$db_query = "SELECT * FROM "._DB_PREF_."_tblSMSOutgoing WHERE smslog_id=$smslog_id";
        	$db_result = dba_query($db_query);
        	$db_row = dba_fetch_array($db_result);
		$l++;
        	$ret = resendsms($smslog_id,$db_row['p_src'],$db_row['p_footer'],$db_row['p_dst'],$db_row['p_msg'],$db_row['uid'],$db_row['p_gpid'],$db_row['p_sms_type'],$db_row['unicode']);
	
             }
           }
        header ("Location: index.php?app=menu&inc=tools_report&op=report_sms_list&status=Failed&uid=$uid&err=".urlencode(_('Selected Failed SMS have been recycled:'))." $l");
 
	}

        break;

}

?>
