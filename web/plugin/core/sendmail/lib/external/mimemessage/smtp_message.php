<?php
/*
 * smtp_message.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/PHPlibrary/mimemessage/smtp_message.php,v 1.18 2003/01/14 01:12:12 mlemos Exp $
 *
 *
 */

class smtp_message_class extends email_message_class
{
	var $smtp;
	var $localhost="";
	var $smtp_host="localhost";
	var $smtp_port=25;
	var $smtp_direct_delivery=0;
	var $smtp_getmxrr="getmxrr";
	var $smtp_exclude_address="";
	var $smtp_user="";
	var $smtp_realm="";
	var $smtp_password="";
	var $smtp_debug=0;
	var $esmtp=1;
	var $timeout=25;
	var $invalid_recipients=array();
	var $line_break="\r\n";
	var $email_address_pattern="([-!#\$%&'*+./0-9=?A-Z^_`a-z{|}~])+@([-!#\$%&'*+/0-9=?A-Z^_`a-z{|}~]+\\.)+[a-zA-Z]{2,6}";

	Function GetRFC822Addresses($address,&$addresses)
	{
		if(function_exists("imap_rfc822_parse_adrlist"))
		{
			$parsed_addresses=@imap_rfc822_parse_adrlist($address,$this->localhost);
			for($entry=0;$entry<count($parsed_addresses);$entry++)
			{
				if($parsed_addresses[$entry]->host==".SYNTAX-ERROR.")
				return($parsed_addresses[$entry]->mailbox." ".$parsed_addresses[$entry]->host);
				$parsed_address=$parsed_addresses[$entry]->mailbox."@".$parsed_addresses[$entry]->host;
				if(IsSet($addresses[$parsed_address]))
				$addresses[$parsed_address]++;
				else
				$addresses[$parsed_address]=1;
			}
		}
		else
		{
			$length=strlen($address);
			for($position=0;$position<$length;)
			{
				$match=split($this->email_address_pattern,strtolower(substr($address,$position)),2);
				if(count($match)<2)
				break;
				$position+=strlen($match[0]);
				$next_position=$length-strlen($match[1]);
				$found=substr($address,$position,$next_position-$position);
				if(!strcmp($found,""))
				break;
				if(IsSet($addresses[$found]))
				$addresses[$found]++;
				else
				$addresses[$found]=1;
				$position=$next_position;
			}
		}
		return("");
	}

	Function SetRecipients(&$recipients,&$valid_recipients)
	{
		for($valid_recipients=$recipient=0,Reset($recipients);$recipient<count($recipients);Next($recipients),$recipient++)
		{
			$address=Key($recipients);
			if($this->smtp->SetRecipient($address))
			$valid_recipients++;
			else
			$this->invalid_recipients[$address]=$this->smtp->error;
		}
		return(1);
	}

	Function StartSendingMessage()
	{
		$this->smtp=new smtp_class;
		$this->smtp->localhost=$this->localhost;
		$this->smtp->host_name=$this->smtp_host;
		$this->smtp->host_port=$this->smtp_port;
		$this->smtp->timeout=$this->timeout;
		$this->smtp->debug=$this->smtp_debug;
		$this->smtp->direct_delivery=$this->smtp_direct_delivery;
		$this->smtp->getmxrr=$this->smtp_getmxrr;
		$this->smtp->exclude_address=$this->smtp_exclude_address;
		$this->smtp->user=$this->smtp_user;
		$this->smtp->realm=$this->smtp_realm;
		$this->smtp->password=$this->smtp_password;
		$this->smtp->esmtp=$this->esmtp;
		return($this->smtp->Connect() ? "" : $this->OutputError($this->smtp->error));
	}

	Function SendMessageHeaders($headers)
	{
		for($header_data="",$date_set=0,$header=0,$return_path=$from=$to=$recipients=array(),Reset($headers);$header<count($headers);$header++,Next($headers))
		{
			$header_name=Key($headers);
			switch(strtolower($header_name))
			{
				case "return-path":
					$return_path[$headers[$header_name]]=1;
					break;
				case "from":
					$error=$this->GetRFC822Addresses($headers[$header_name],$from);
					break;
				case "to":
					$error=$this->GetRFC822Addresses($headers[$header_name],$to);
					break;
				case "cc":
				case "bcc":
					$this->GetRFC822Addresses($headers[$header_name],$recipients);
					break;
				case "date":
					$date_set=1;
					break;
			}
			if(strcmp($error,""))
			return($this->OutputError($error));
			if(strtolower($header_name)=="bcc")
			continue;
			$header_data.=$this->FormatHeader($header_name,$headers[$header_name]);
		}
		if(count($from)==0)
		return($this->OutputError("it was not specified a valid From header"));
		if(count($to)==0)
		return($this->OutputError("it was not specified a valid To header"));
		Reset($return_path);
		Reset($from);
		$this->invalid_recipients=array();
		if(!$this->smtp->MailFrom(count($return_path) ? Key($return_path) : Key($from))
		|| !$this->SetRecipients($to,$valid_recipients))
		return($this->OutputError($this->smtp->error));
		if($valid_recipients==0)
		return($this->OutputError("it were not specified any valid recipients"));
		if(!$date_set)
		$header_data.="Date: ".strftime("%a, %d %b %Y %H:%M:%S %Z")."\r\n";
		if(!$this->SetRecipients($recipients,$valid_recipients)
		|| !$this->smtp->StartData()
		|| !$this->smtp->SendData("$header_data\r\n"))
		return($this->OutputError($this->smtp->error));
		return("");
	}

	Function SendMessageBody($data)
	{
		$this->smtp->PrepareData($data,$output);
		return($this->smtp->SendData($output) ? "" : $this->OutputError($this->smtp->error));
	}

	Function EndSendingMessage()
	{
		return($this->smtp->EndSendingData() ? "" : $this->OutputError($this->smtp->error));
	}

	Function StopSendingMessage()
	{
		return($this->smtp->Disconnect() ? "" : $this->OutputError($this->smtp->error));
	}

};

?>