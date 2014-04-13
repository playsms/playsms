<?php
/*
 * smtp.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/PHPlibrary/smtp.php,v 1.25 2003/03/25 23:13:15 mlemos Exp $
 *
 */

class smtp_class
{
	var $user="";
	var $realm="";
	var $password="";
	var $host_name="";
	var $host_port=25;
	var $localhost="";
	var $timeout=0;
	var $direct_delivery=0;
	var $error="";
	var $debug=0;
	var $esmtp=1;
	var $esmtp_host="";
	var $esmtp_extensions=array();
	var $maximum_piped_recipients=100;
	var $exclude_address="";
	var $getmxrr="GetMXRR";

	/* private variables - DO NOT ACCESS */

	var $state="Disconnected";
	var $connection=0;
	var $pending_recipients=0;
	var $next_token="";
	var $direct_sender="";
	var $connected_domain="";

	/* Private methods - DO NOT CALL */

	Function Tokenize($string,$separator="")
	{
		if(!strcmp($separator,""))
		{
			$separator=$string;
			$string=$this->next_token;
		}
		for($character=0;$character<strlen($separator);$character++)
		{
			if(GetType($position=strpos($string,$separator[$character]))=="integer")
			$found=(IsSet($found) ? min($found,$position) : $position);
		}
		if(IsSet($found))
		{
			$this->next_token=substr($string,$found+1);
			return(substr($string,0,$found));
		}
		else
		{
			$this->next_token="";
			return($string);
		}
	}

	Function OutputDebug($message)
	{
		echo $message,"\n";
	}

	Function GetLine()
	{
		for($line="";;)
		{
			if(feof($this->connection))
			{
				$this->error="reached the end of stream while reading from socket";
				return(0);
			}
			if(GetType($data=fgets($this->connection,100))!="string"
			|| strlen($data)==0)
			{
				$this->error="it was not possible to read line from socket";
				return(0);
			}
			$line.=$data;
			$length=strlen($line);
			if($length>=2
			&& substr($line,$length-2,2)=="\r\n")
			{
				$line=substr($line,0,$length-2);
				if($this->debug)
				$this->OutputDebug("S $line");
				return($line);
			}
		}
	}

	Function PutLine($line)
	{
		if($this->debug)
		$this->OutputDebug("C $line");
		if(!fputs($this->connection,"$line\r\n"))
		{
			$this->error="it was not possible to write line to socket";
			return(0);
		}
		return(1);
	}

	Function PutData(&$data)
	{
		if(strlen($data))
		{
			if($this->debug)
			$this->OutputDebug("C $data");
			if(!fputs($this->connection,$data))
			{
				$this->error="it was not possible to write data to socket";
				return(0);
			}
		}
		return(1);
	}

	Function VerifyResultLines($code,&$responses)
	{
		$responses=array();
		Unset($match_code);
		while(($line=$this->GetLine($this->connection)))
		{
			if(IsSet($match_code))
			{
				if(strcmp($this->Tokenize($line," -"),$match_code))
				{
					$this->error=$line;
					return(0);
				}
			}
			else
			{
				$match_code=$this->Tokenize($line," -");
				if(GetType($code)=="array")
				{
					for($codes=0;$codes<count($code) && strcmp($match_code,$code[$codes]);$codes++);
					if($codes>=count($code))
					{
						$this->error=$line;
						return(0);
					}
				}
				else
				{
					if(strcmp($match_code,$code))
					{
						$this->error=$line;
						return(0);
					}
				}
			}
			$responses[]=$this->Tokenize("");
			if(!strcmp($match_code,$this->Tokenize($line," ")))
			return(1);
		}
		return(-1);
	}

	Function FlushRecipients()
	{
		if($this->pending_sender)
		{
			if($this->VerifyResultLines("250",$responses)<=0)
			return(0);
			$this->pending_sender=0;
		}
		for(;$this->pending_recipients;$this->pending_recipients--)
		{
			if($this->VerifyResultLines(array("250","251"),$responses)<=0)
			return(0);
		}
		return(1);
	}

	/* Public methods */

	Function Connect($domain="")
	{
		$this->error=$error="";
		$this->esmtp_host="";
		$this->esmtp_extensions=array();
		$hosts=array();
		if($this->direct_delivery)
		{
			if(strlen($domain)==0)
			return(1);
			$hosts=$weights=$mxhosts=array();
			$getmxrr=$this->getmxrr;
			if(function_exists($getmxrr)
			&& $getmxrr($domain,$hosts,$weights))
			{
				for($host=0;$host<count($hosts);$host++)
				$mxhosts[$weights[$host]]=$hosts[$host];
				KSort($mxhosts);
				for(Reset($mxhosts),$host=0;$host<count($mxhosts);Next($mxhosts),$host++)
				$hosts[$host]=$mxhosts[Key($mxhosts)];
			}
			else
			{
				if(strcmp(@gethostbyname($domain),$domain)!=0)
				$hosts[]=$domain;
			}
		}
		else
		{
			if(strlen($this->host_name))
			$hosts[]=$this->host_name;
		}
		if(count($hosts)==0)
		{
			$this->error="could not determine the SMTP to connect";
			return(0);
		}
		if(strcmp($this->state,"Disconnected"))
		{
			$this->error="connection is already established";
			return(0);
		}
		for($host=0;;$host++)
		{
			$domain=$hosts[$host];
			// fixme anton - ereg is depreceted in php 5.3
			// if(ereg('^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$',$domain))
			if(preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/',$domain))
			$ip=$domain;
			else
			{
				if($this->debug)
				$this->OutputDebug("Resolving SMTP server domain \"$domain\"...");
				if(!strcmp($ip=@gethostbyname($domain),$domain))
				$ip="";
			}
			if(strlen($ip)==0
			|| (strlen($this->exclude_address)
			&& !strcmp(@gethostbyname($this->exclude_address),$ip)))
			{
				if($host==count($hosts)-1)
				{
					$this->error="could not resolve the host domain \"".$domain."\"";
					return(0);
				}
				continue;
			}
			if($this->debug)
			$this->OutputDebug("Connecting to SMTP server ip \"$ip\"...");
			if(($this->connection=($this->timeout ? fsockopen($ip,$this->host_port,$errno,$error,$this->timeout) : fsockopen($ip,$this->host_port))))
			break;
			if($host==count($hosts)-1)
			{
				switch($this->timeout ? strval($error) : "??")
				{
					case "-3":
						$this->error="-3 socket could not be created";
						break;
					case "-4":
						$this->error="-4 dns lookup on hostname \"".$domain."\" failed";
						break;
					case "-5":
						$this->error="-5 connection refused or timed out";
						break;
					case "-6":
						$this->error="-6 fdopen() call failed";
						break;
					case "-7":
						$this->error="-7 setvbuf() call failed";
						break;
					default:
						$this->error=$error." could not connect to the host \"".$domain."\"";
						break;
				}
				return(0);
			}
		}
		if($this->debug)
		$this->OutputDebug("Connected to SMTP server ip \"$ip\".");
		if(!strcmp($localhost=$this->localhost,"")
		&& !strcmp($localhost=getenv("SERVER_NAME"),"")
		&& !strcmp($localhost=getenv("HOST"),""))
		$localhost="localhost";
		$success=0;
		if($this->VerifyResultLines("220",$responses)>0)
		{
			$fallback=1;
			if($this->esmtp
			|| strlen($this->user))
			{
				if($this->PutLine("EHLO $localhost"))
				{
					if(($success_code=$this->VerifyResultLines("250",$responses))>0)
					{
						$this->esmtp_host=$this->Tokenize($responses[0]," ");
						for($response=1;$response<count($responses);$response++)
						{
							$extension=strtoupper($this->Tokenize($responses[$response]," "));
							$this->esmtp_extensions[$extension]=$this->Tokenize("");
						}
						$success=1;
						$fallback=0;
					}
					else
					{
						if($success_code==0)
						{
							$code=$this->Tokenize($this->error," -");
							switch($code)
							{
								case "421":
									$fallback=0;
									break;
							}
						}
					}
				}
				else
				$fallback=0;
			}
			if($fallback)
			{
				if($this->PutLine("HELO $localhost")
				&& $this->VerifyResultLines("250",$responses)>0)
				$success=1;
			}
			if($success
			&& strlen($this->user))
			{
				if(!IsSet($this->esmtp_extensions["AUTH"]))
				{
					$this->error="server does not require authentication";
					$success=0;
				}
				else
				{
					for($authentication=$this->Tokenize($this->esmtp_extensions["AUTH"]," ");strlen($authentication);$authentication=$this->Tokenize(" "))
					{
						switch($authentication)
						{
							case "PLAIN":
								$success=($this->PutLine("AUTH PLAIN ".base64_encode($this->user.chr(0).$this->user.(strlen($this->realm) ? "@".$this->realm : "").chr(0).$this->password))
								&& $this->VerifyResultLines("235",$responses));
								break 2;
							case "LOGIN":
								$success=($this->PutLine("AUTH LOGIN")
								&& $this->VerifyResultLines("334",$responses)
								&& $this->PutLine(base64_encode($this->user.(strlen($this->realm) ? "@".$this->realm : "")))
								&& $this->VerifyResultLines("334",$responses)
								&& $this->PutLine(base64_encode($this->password))
								&& $this->VerifyResultLines("235",$responses));
								break 2;
						}
					}
					if($success
					&& strlen($authentication)==0)
					{
						$this->error="the server does not require a supported authentication method";
						$success=0;
					}
				}
			}
		}
		if($success)
		{
			$this->state="Connected";
			$this->connected_domain=$domain;
		}
		else
		{
			fclose($this->connection);
			$this->connection=0;
		}
		return($success);
	}

	Function MailFrom($sender)
	{
		if($this->direct_delivery)
		{
			switch($this->state)
			{
				case "Disconnected":
					$this->direct_sender=$sender;
					return(1);
				case "Connected":
					$sender=$this->direct_sender;
					break;
				default:
					$this->error="direct delivery connection is already established and sender is already set";
					return(0);
			}
		}
		else
		{
			if(strcmp($this->state,"Connected"))
			{
				$this->error="connection is not in the initial state";
				return(0);
			}
		}
		$this->error="";
		if(!$this->PutLine("MAIL FROM:<$sender>"))
		return(0);
		if(!IsSet($this->esmtp_extensions["PIPELINING"])
		&& $this->VerifyResultLines("250",$responses)<=0)
		return(0);
		$this->state="SenderSet";
		if(IsSet($this->esmtp_extensions["PIPELINING"]))
		$this->pending_sender=1;
		$this->pending_recipients=0;
		return(1);
	}

	Function SetRecipient($recipient)
	{
		if($this->direct_delivery)
		{
			if(GetType($at=strrpos($recipient,"@"))!="integer")
			return("it was not specified a valid direct recipient");
			$domain=substr($recipient,$at+1);
			switch($this->state)
			{
				case "Disconnected":
					if(!$this->Connect($domain))
					return(0);
					if(!$this->MailFrom(""))
					{
						$error=$this->error;
						$this->Disconnect();
						$this->error=$error;
						return(0);
					}
					break;
				case "SenderSet":
				case "RecipientSet":
					if(strcmp($this->connected_domain,$domain))
					{
						$this->error="it is not possible to deliver directly to recipients of different domains";
						return(0);
					}
					break;
				default:
					$this->error="connection is already established and the recipient is already set";
					return(0);
			}
		}
		else
		{
			switch($this->state)
			{
				case "SenderSet":
				case "RecipientSet":
					break;
				default:
					$this->error="connection is not in the recipient setting state";
					return(0);
			}
		}
		$this->error="";
		if(!$this->PutLine("RCPT TO:<$recipient>"))
		return(0);
		if(IsSet($this->esmtp_extensions["PIPELINING"]))
		{
			$this->pending_recipients++;
			if($this->pending_recipients>=$this->maximum_piped_recipients)
			{
				if(!$this->FlushRecipients())
				return(0);
			}
		}
		else
		{
			if($this->VerifyResultLines(array("250","251"),$responses)<=0)
			return(0);
		}
		$this->state="RecipientSet";
		return(1);
	}

	Function StartData()
	{
		if(strcmp($this->state,"RecipientSet"))
		{
			$this->error="connection is not in the start sending data state";
			return(0);
		}
		$this->error="";
		if(!$this->PutLine("DATA"))
		return(0);
		if($this->pending_recipients)
		{
			if(!$this->FlushRecipients())
			return(0);
		}
		if($this->VerifyResultLines("354",$responses)<=0)
		return(0);
		$this->state="SendingData";
		return(1);
	}

	Function PrepareData(&$data,&$output)
	{
		if(function_exists("preg_replace"))
		$output=preg_replace(array("/(^|[^\r])\n/","/\r([^\n])/","/\\.(\r|\$)/"),array("\\1\r\n","\r\n\\1","..\\1"),$data);
		else
		$output=preg_replace("/\\.(\r|\$)","..\\1/",preg_replace("/\r([^\n])/","\r\n\\1",preg_replace("/(^|[^\r])\n/","\\1\r\n",$data)));
	}

	Function SendData($data)
	{
		if(strcmp($this->state,"SendingData"))
		{
			$this->error="connection is not in the sending data state";
			return(0);
		}
		$this->error="";
		return($this->PutData($data));
	}

	Function EndSendingData()
	{
		if(strcmp($this->state,"SendingData"))
		{
			$this->error="connection is not in the sending data state";
			return(0);
		}
		$this->error="";
		if(!$this->PutLine("\r\n.")
		|| $this->VerifyResultLines("250",$responses)<=0)
		return(0);
		$this->state="Connected";
		return(1);
	}

	Function ResetConnection()
	{
		switch($this->state)
		{
			case "Connected":
				return(1);
			case "SendingData":
				$this->error="can not reset the connection while sending data";
				return(0);
			case "Disconnected":
				$this->error="can not reset the connection before it is established";
				return(0);
		}
		$this->error="";
		if(!$this->PutLine("RSET")
		|| $this->VerifyResultLines("250",$responses)<=0)
		return(0);
		$this->state="Connected";
		return(1);
	}

	Function Disconnect($quit=1)
	{
		if(!strcmp($this->state,"Disconnected"))
		{
			$this->error="it was not previously established a SMTP connection";
			return(0);
		}
		$this->error="";
		if(!strcmp($this->state,"Connected")
		&& $quit
		&& (!$this->PutLine("QUIT")
		|| $this->VerifyResultLines("221",$responses)<=0))
		return(0);
		fclose($this->connection);
		$this->connection=0;
		$this->state="Disconnected";
		if($this->debug)
		$this->OutputDebug("Disconnected.");
		return(1);
	}

	Function SendMessage($sender,$recipients,$headers,$body)
	{
		if(($success=$this->Connect()))
		{
			if(($success=$this->MailFrom($sender)))
			{
				for($recipient=0;$recipient<count($recipients);$recipient++)
				{
					if(!($success=$this->SetRecipient($recipients[$recipient])))
					break;
				}
				if($success
				&& ($success=$this->StartData()))
				{
					for($header_data="",$header=0;$header<count($headers);$header++)
					$header_data.=$headers[$header]."\r\n";
					if(($success=$this->SendData($header_data."\r\n")))
					{
						$this->PrepareData($body,$body_data);
						$success=$this->SendData($body_data);
					}
					if($success)
					$success=$this->EndSendingData();
				}
			}
			$error=$this->error;
			$disconnect_success=$this->Disconnect($success);
			if($success)
			$success=$disconnect_success;
			else
			$this->error=$error;
		}
		return($success);
	}

};

?>