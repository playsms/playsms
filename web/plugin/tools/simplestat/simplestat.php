<?
if(!valid()){forcenoaccess();};

$slid = $_GET['slid'];
$sms_price = 25;

switch ($op)
{
    case "simplestat_list":

	//$db_query = "SELECT count(*) as count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND flag_deleted='0'";
	//$db_query = "SELECT count(*) as count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid'";
	//$db_result = dba_query($db_query);
	//$db_row = dba_fetch_array($db_result);
	//$num_rows = $db_row['count'];

	$content = "
	    <h2>"._('Statistics')."</h2>
	    <p>"._('Sent SMS')."</p>
	    <form name=\"fm_outgoing\" action=\"menu.php?inc=user_outgoing&op=act_del\" method=post onSubmit=\"return SureConfirm()\">
	    <table width='100%' cellpadding='1' cellspacing='2' border='0' class='sortable'>
        <thead>
	    <tr>
	      <th align='center' width='20%'>"._('User')."</th>
	      <th align='center' width='15%'>"._('In queue SMS')."</th>
	      <th align='center' width='15%'>"._('Sent SMS')."</th>
	      <th align='center' width='15%'>"._('Delivered SMS')."</th>
	      <th align='center' width='15%'>"._('Failed SMS')."</th>
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
	$db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='1'";
	$db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_sent = $db_row['count'];
        $sum_num_rows_sent = ($sum_num_rows_sent+$num_rows_sent);

        // SMS PENDING
        $db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='0'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_pending = $db_row['count'];
        $sum_num_rows_pending = ($sum_num_rows_pending+$num_rows_pending);

	// SMS DELIVERED
        $db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='3'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_delivered = $db_row['count'];
	$sum_num_rows_delivered = ($sum_num_rows_delivered+$num_rows_delivered);

	// SMS FAILED
        $db_query = "SELECT COUNT(*) AS count FROM "._DB_PREF_."_tblSMSOutgoing WHERE uid='$uid' AND p_status='2'";
        $db_result = dba_query($db_query);
        $db_row = dba_fetch_array($db_result);
        $num_rows_failed = $db_row['count'];
	$sum_num_rows_failed = ($sum_num_rows_failed+$num_rows_failed);

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
	          <td valign='top' class='$td_class' align='center' width='10%'>$num_rows_pending</td>
	          <td valign='top' class='$td_class' align='center' width='10%'>$num_rows_sent</td>
	          <td valign='top' class='$td_class' align='center' width='10%'>$num_rows_delivered</td>
	          <td valign='top' class='$td_class' align='center' width='10%'>$num_rows_failed</td>
	          <!--td valign=top class=$td_class align=center width=10%>$sign $total_price CFP</td-->
	          <!--td valign=top class=$td_class align=center width=4>
		    <a href=\"javascript: ConfirmURL('Etes-vous certain de vouloir supprimer les SMS sortant vers `$hide_p_dst`, num&eacute;ro $i ?','menu.php?inc=user_outgoing&op=user_outgoing_del&slid=$current_slid')\">$icon_delete</a>
		  </td-->
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
	    </td>
	</tr>
	</table-->	
	</form>
	";

	if ($err)
	{
	    echo "<font color=red>$err</font><br><br>";
	}
	echo $content;
	break;
}

?>
