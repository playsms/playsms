<?php

/*
 * Implementations of hook checkavailablekeyword()
 *
 * @param $keyword
 *   checkavailablekeyword() will insert keyword for checking to the hook here
 * @return 
 *   TRUE if keyword is NOT available
 */
function sms_poll_hook_checkavailablekeyword($keyword)
{
    $ok = false;
    $db_query = "SELECT poll_id FROM "._DB_PREF_."_featurePoll WHERE poll_keyword='$keyword'";
    if ($db_result = dba_num_rows($db_query))
    {
        $ok = true;
    }
    return $ok;
}

/*
 * Implementations of hook setsmsincomingaction()
 *
 * @param $sms_datetime
 *   date and time when incoming sms inserted to playsms
 * @param $sms_sender
 *   sender on incoming sms
 * @param $poll_keyword
 *   check if keyword is for sms_poll
 * @param $poll_param
 *   get parameters from incoming sms
 * @return $ret
 *   array of keyword owner uid and status, TRUE if incoming sms handled
 */
function sms_poll_hook_setsmsincomingaction($sms_datetime,$sms_sender,$poll_keyword,$poll_param='')
{
    $ok = false;
    $db_query = "SELECT uid,poll_id FROM "._DB_PREF_."_featurePoll WHERE poll_keyword='$poll_keyword'";
    $db_result = dba_query($db_query);
    if ($db_row = dba_fetch_array($db_result))
    {
	$c_uid = $db_row['uid'];
	if (sms_poll_handle($sms_datetime,$sms_sender,$poll_keyword,$poll_param))
	{
	    $ok = true;
	}
    }
    $ret['uid'] = $c_uid;
    $ret['status'] = $ok;
    return $ret;
}

function sms_poll_handle($sms_datetime,$sms_sender,$poll_keyword,$poll_param='')
{
    $ok = false;
    $poll_keyword = strtoupper($poll_keyword);
    $target_choice = strtoupper($poll_param);
    if ($sms_sender && $poll_keyword && $target_choice)
    {
	$db_query = "SELECT poll_id,poll_enable FROM "._DB_PREF_."_featurePoll WHERE poll_keyword='$poll_keyword'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$poll_id = $db_row['poll_id'];
	$poll_enable = $db_row['poll_enable'];
	$db_query = "SELECT choice_id FROM "._DB_PREF_."_featurePoll_choice WHERE choice_keyword='$target_choice' AND poll_id='$poll_id'";
	$db_result = dba_query($db_query);
	$db_row = dba_fetch_array($db_result);
	$choice_id = $db_row['choice_id'];
	if ($poll_id && $choice_id)
	{
	    $db_query = "SELECT result_id FROM "._DB_PREF_."_featurePoll_log WHERE poll_sender='$sms_sender' AND poll_id='$poll_id'";
	    $already_vote = @dba_num_rows($db_query);
	    if ((!$already_vote) && $poll_enable)
	    {
		$db_query = "
		    INSERT INTO "._DB_PREF_."_featurePoll_log 
		    (poll_id,choice_id,poll_sender) 
		    VALUES ('$poll_id','$choice_id','$sms_sender')
		";
		dba_query($db_query);
	    }
	    $ok = true;
	}
    }
    return $ok;    
}

?>