<?php
/*
 * email_message.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/PHPlibrary/mimemessage/email_message.php,v 1.29 2003/01/29 02:23:01 mlemos Exp $
 *
 *
 */

class email_message_class
{
	/* Private variables */

	var $headers=array("To"=>"","Subject"=>"");
	var $body=-1;
	var $body_parts=0;
	var $parts=array();
	var $total_parts=0;
	var $free_parts=array();
	var $total_free_parts=0;
	var $delivery=array("State"=>"");
	var $next_token="";
	var $php_version=0;
	var $mailings=array();
	var $last_mailing=0;
	var $header_length_limit=512;
	var $cache_body=0;

	/* Public variables */

	var $email_regular_expression="^([-!#\$%&'*+./0-9=?A-Z^_`a-z{|}~])+@([-!#\$%&'*+/0-9=?A-Z^_`a-z{|}~]+\\.)+[a-zA-Z]{2,6}\$";
	var $mailer='http://www.phpclasses.org/mimemessage $Revision: 1.29 $';
	var $default_charset="ISO-8859-1";
	var $line_break="\n";
	var $line_length=75;
	var $line_quote_prefix="> ";
	var $file_buffer_length=8000;
	var $debug="";
	var $error="";
	var $mailing_path="";
	var $body_cache=array();

	/* Private methods */

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

	Function GetFilenameExtension($filename)
	{
		return(GetType($dot=strrpos($filename,"."))=="integer" ? substr($filename,$dot) : "");
	}

	Function OutputError($error)
	{
		if(strcmp($function=$this->debug,"")
		&& strcmp($error,""))
		$function($error);
		return($this->error=$error);
	}

	Function GetPHPVersion()
	{
		if($this->php_version==0)
		{
			$version=explode(".",function_exists("phpversion") ? phpversion() : "3.0.7");
			$this->php_version=$version[0]*1000000+$version[1]*1000+$version[2];
		}
		return($this->php_version);
	}

	Function FormatHeader($header_name,$header_value)
	{
		$length=strlen($header_value);
		for($header_data="",$header_line=$header_name.": ",$line_length=strlen($header_line),$position=0;$position<$length;)
		{
			for($space=$position,$line_length=strlen($header_line);$space<$length;)
			{
				if(GetType($next=strpos($header_value," ",$space+1))!="integer")
				$next=$length;
				if($next-$position+$line_length>$this->header_length_limit)
				{
					if($space==$position)
					$space=$next;
					break;
				}
				$space=$next;
			}
			$header_data.=$header_line.substr($header_value,$position,$space-$position).$this->line_break;
			if($space<$length)
			$header_line="";
			$position=$space;
		}
		return($header_data);
	}

	Function SendMail($to,$subject,&$body,&$headers)
	{
		$return_path="";
		if(IsSet($this->delivery["Headers"]))
		{
			$headers_values=$this->delivery["Headers"];
			for($header=0,Reset($headers_values);$header<count($headers_values);$header++,Next($headers_values))
			{
				if(strtolower(Key($headers_values))=="return-path")
				{
					$return_path=$headers_values[Key($headers_values)];
					break;
				}
			}
			if(strlen($return_path))
			{
				if(!defined("PHP_OS"))
				return($this->OutputError("it is not possible to set the Return-Path header with your PHP version"));
				if(!strcmp(substr(PHP_OS,0,3),"WIN"))
				return($this->OutputError("it is not possible to set the Return-Path header directly from a PHP script on Windows"));
				if($this->GetPHPVersion()<4000005)
				return($this->OutputError("it is not possible to set the Return-Path header in PHP version older than 4.0.5"));
			}
		}
		$success=(strlen($return_path) ? mail($to,$subject,$body,$headers,"-f".$return_path) : mail($to,$subject,$body,$headers));
		return($success ? "" : $this->OutputError("it was not possible to send e-mail message"));
	}

	Function StartSendingMessage()
	{
		if(strcmp($this->delivery["State"],""))
		return($this->OutputError("the message was already started to be sent"));
		$this->delivery=array("State"=>"SendingHeaders");
		return("");
	}

	Function SendMessageHeaders($headers)
	{
		if(strcmp($this->delivery["State"],"SendingHeaders"))
		{
			if(!strcmp($this->delivery["State"],""))
			return($this->OutputError("the message was not yet started to be sent"));
			else
			return($this->OutputError("the message headers were already sent"));
		}
		$this->delivery["Headers"]=$headers;
		$this->delivery["State"]="SendingBody";
		return("");
	}

	Function SendMessageBody(&$data)
	{
		if(strcmp($this->delivery["State"],"SendingBody"))
		return($this->OutputError("the message headers were not yet sent"));
		if(IsSet($this->delivery["Body"]))
		$this->delivery["Body"].=$data;
		else
		$this->delivery["Body"]=$data;
		return("");
	}

	Function EndSendingMessage()
	{
		if(strcmp($this->delivery["State"],"SendingBody"))
		return($this->OutputError("the message body data was not yet sent"));
		if(!IsSet($this->delivery["Headers"])
		|| count($this->delivery["Headers"])==0)
		return($this->OutputError("message has no headers"));
		$line_break=((defined("PHP_OS") && !strcmp(substr(PHP_OS,0,3),"WIN")) ? "\r\n" : $this->line_break);
		$headers=$this->delivery["Headers"];
		for($return_path=$headers_text=$to=$subject="",$header=0,Reset($headers);$header<count($headers);Next($headers),$header++)
		{
			switch(strtolower(Key($headers)))
			{
				case "to":
					$to=$headers[Key($headers)];
					break;
				case "subject":
					$subject=$headers[Key($headers)];
					break;
				case "return-path":
					$return_path=$headers[Key($headers)];
				default:
					$headers_text.=Key($headers).": ".$headers[Key($headers)].$line_break;
			}
		}
		if(!strcmp($to,""))
		return($this->OutputError("it was not specified a valid To: header"));
		if(!strcmp($subject,""))
		return($this->OutputError("it was not specified a valid Subject: header"));
		if(strcmp($error=$this->SendMail($to,$subject,$this->delivery["Body"],$headers_text,$return_path),""))
		return($error);
		$this->delivery=array("State"=>"");
		return("");
	}

	Function StopSendingMessage()
	{
		$this->delivery=array("State"=>"");
		return("");
	}

	Function GetPartBoundary($part)
	{
		if(!IsSet($this->parts[$part]["BOUNDARY"]))
		$this->parts[$part]["BOUNDARY"]=md5(uniqid($part.time()));
	}

	Function GetPartHeaders(&$headers,$part)
	{
		if(!IsSet($this->parts[$part]["Content-Type"]))
		return($this->OutputError("it was added a part without Content-Type: defined"));
		$type=$this->Tokenize($full_type=strtolower($this->parts[$part]["Content-Type"]),"/");
		$sub_type=$this->Tokenize("");
		switch($type)
		{
			case "text":
			case "image":
			case "audio":
			case "video":
			case "application":
				$headers["Content-Type"]=$full_type.(IsSet($this->parts[$part]["CHARSET"]) ? "; charset=".$this->parts[$part]["CHARSET"] : "").(IsSet($this->parts[$part]["NAME"]) ? "; name=\"".$this->parts[$part]["NAME"]."\"" : "");
				if(IsSet($this->parts[$part]["Content-Transfer-Encoding"]))
				$headers["Content-Transfer-Encoding"]=$this->parts[$part]["Content-Transfer-Encoding"];
				if(IsSet($this->parts[$part]["DISPOSITION"])
				&& strlen($this->parts[$part]["DISPOSITION"]))
				$headers["Content-Disposition"]=$this->parts[$part]["DISPOSITION"].(IsSet($this->parts[$part]["NAME"]) ? "; filename=\"".$this->parts[$part]["NAME"]."\"" : "");
				break;
			case "multipart":
				switch($sub_type)
				{
					case "alternative":
					case "related":
					case "mixed":
					case "parallel":
						$this->GetPartBoundary($part);
						$headers["Content-Type"]=$full_type."; boundary=\"".$this->parts[$part]["BOUNDARY"]."\"";
						break;
					default:
						return($this->OutputError("multipart Content-Type sub_type $sub_type not yet supported"));
				}
				break;
			default:
				return($this->OutputError("Content-Type: $full_type not yet supported"));
		}
		if(IsSet($this->parts[$part]["Content-ID"]))
		$headers["Content-ID"]=$this->parts[$part]["Content-ID"];
		return("");
	}

	Function GetPartBody(&$body,$part)
	{
		if(!IsSet($this->parts[$part]["Content-Type"]))
		return($this->OutputError("it was added a part without Content-Type: defined"));
		$type=$this->Tokenize($full_type=strtolower($this->parts[$part]["Content-Type"]),"/");
		$sub_type=$this->Tokenize("");
		$body="";
		switch($type)
		{
			case "text":
			case "image":
			case "audio":
			case "video":
			case "application":
				if(IsSet($this->parts[$part]["FILENAME"]))
				{
					if(!($file=@fopen($this->parts[$part]["FILENAME"],"rb")))
					return($this->OutputError("could not open part file"));
					while(!feof($file))
					{
						if(GetType($block=@fread($file,$this->file_buffer_length))!="string")
						{
							fclose($file);
							return($this->OutputError("could not read part file"));
						}
						$body.=$block;
					}
					fclose($file);
				}
				else
				{
					if(!IsSet($this->parts[$part]["DATA"]))
					return($this->OutputError("it was added a part without a body PART"));
					$body=$this->parts[$part]["DATA"];
				}
				$encoding=(IsSet($this->parts[$part]["Content-Transfer-Encoding"]) ? strtolower($this->parts[$part]["Content-Transfer-Encoding"]) : "");
				switch($encoding)
				{
					case "base64":
						$body=chunk_split(base64_encode($body));
						break;
					case "":
					case "quoted-printable":
						break;
					default:
						return($this->OutputError($encoding." is not yet a supported encoding type"));
				}
				break;
			case "multipart":
				switch($sub_type)
				{
					case "alternative":
					case "related":
					case "mixed":
					case "parallel":
						$this->GetPartBoundary($part);
						$boundary=$this->line_break."--".$this->parts[$part]["BOUNDARY"];
						$parts=count($this->parts[$part]["PARTS"]);
						for($multipart=0;$multipart<$parts;$multipart++)
						{
							$body.=$boundary.$this->line_break;
							$part_headers=array();
							$sub_part=$this->parts[$part]["PARTS"][$multipart];
							if(strlen($error=$this->GetPartHeaders($part_headers,$sub_part)))
							return($error);
							for($part_header=0,Reset($part_headers);$part_header<count($part_headers);$part_header++,Next($part_headers))
							{
								$header=Key($part_headers);
								$body.=$header.": ".$part_headers[$header].$this->line_break;
							}
							$body.=$this->line_break;
							if(strlen($error=$this->GetPartBody($part_body,$sub_part)))
							return($error);
							$body.=$part_body;
						}
						$body.=$boundary."--".$this->line_break;
						break;
					default:
						return($this->OutputError("multipart Content-Type sub_type $sub_type not yet supported"));
				}
				break;
			default:
				return($this->OutputError("Content-Type: $full_type not yet supported"));
		}
		return("");
	}

	/* Public functions */

	Function ValidateEmailAddress($address)
	{
		return(preg_match('/'.$this->email_regular_expression.'/i',QuoteMeta($address)));
	}

	Function QuotedPrintableEncode($text,$header_charset="",$break_lines=1)
	{
		$length=strlen($text);
		if(strcmp($header_charset,""))
		{
			$break_lines=0;
			for($index=0;$index<$length;$index++)
			{
				$code=Ord($text[$index]);
				if($code<32
				|| $code>127)
				break;
			}
			if($index>0)
			return(substr($text,0,$index).$this->QuotedPrintableEncode(substr($text,$index),$header_charset,0));
		}
		for($whitespace=$encoded="",$line=0,$index=0;$index<$length;$index++)
		{
			$character=$text[$index];
			$order=Ord($character);
			$encode=0;
			switch($order)
			{
				case 9:
				case 32:
					if(!strcmp($header_charset,""))
					{
						$previous_whitespace=$whitespace;
						$whitespace=$character;
						$character="";
					}
					else
					{
						if(!strcmp($order,32))
						$character="_";
						else
						$encode=1;
					}
					break;
				case 10:
				case 13:
					if(strcmp($whitespace,""))
					{
						if($break_lines
						&& $line+3>75)
						{
							$encoded.="=".$this->line_break;
							$line=0;
						}
						$encoded.=sprintf("=%02X",Ord($whitespace));
						$line+=3;
						$whitespace="";
					}
					$encoded.=$character;
					$line=0;
					continue 2;
				default:
					if($order>127
					|| $order<32
					|| !strcmp($character,"=")
					|| (strcmp($header_charset,"")
					&& (!strcmp($character,"?")
					|| !strcmp($character,"_")
					|| !strcmp($character,"(")
					|| !strcmp($character,")"))))
					$encode=1;
					break;
			}
			if(strcmp($whitespace,""))
			{
				if($break_lines
				&& $line+1>75)
				{
					$encoded.="=".$this->line_break;
					$line=0;
				}
				$encoded.=$whitespace;
				$line++;
				$whitespace="";
			}
			if(strcmp($character,""))
			{
				if($encode)
				{
					$character=sprintf("=%02X",$order);
					$encoded_length=3;
				}
				else
				$encoded_length=1;
				if($break_lines
				&& $line+$encoded_length>75)
				{
					$encoded.="=".$this->line_break;
					$line=0;
				}
				$encoded.=$character;
				$line+=$encoded_length;
			}
		}
		if(strcmp($whitespace,""))
		{
			if($break_lines
			&& $line+3>75)
			$encoded.="=".$this->line_break;
			$encoded.=sprintf("=%02X",Ord($whitespace));
		}
		if(strcmp($header_charset,"")
		&& strcmp($text,$encoded))
		return("=?$header_charset?q?$encoded?=");
		else
		return($encoded);
	}

	Function WrapText($text,$line_length=0,$line_break="",$line_prefix="")
	{
		if(strlen($line_break)==0)
		$line_break=$this->line_break;
		if($line_length==0)
		$line_length=$this->line_length;
		$lines=explode("\n",str_replace("\r","\n",str_replace("\r\n","\n",$text)));
		for($wrapped="",$line=0;$line<count($lines);$line++)
		{
			if(strlen($text_line=$lines[$line]))
			{
				for(;strlen($text_line=$line_prefix.$text_line)>$line_length;)
				{
					if(GetType($cut=strrpos(substr($text_line,0,$line_length)," "))!="integer")
					{
						$wrapped.=substr($text_line,0,$line_length).$line_break;
						$cut=$line_length;
					}
					else
					{
						$wrapped.=substr($text_line,0,$cut).$line_break;
						$cut++;
					}
					$text_line=substr($text_line,$cut);
				}
			}
			$wrapped.=$text_line.$line_break;
		}
		return($wrapped);
	}

	Function QuoteText($text,$quote_prefix="")
	{
		if(strlen($quote_prefix)==0)
		$quote_prefix=$this->line_quote_prefix;
		return($this->WrapText($text,$line_length=0,$line_break="",$quote_prefix));
	}

	Function SetHeader($header,$value,$encoding_charset="")
	{
		if(strlen($this->error))
		return($this->error);
		$this->headers["$header"]=(!strcmp($encoding_charset,"") ? "$value" : $this->QuotedPrintableEncode($value,$encoding_charset));
		return("");
	}

	Function SetEncodedHeader($header,$value)
	{
		return($this->SetHeader($header,$value,$this->default_charset));
	}

	Function SetEncodedEmailHeader($header,$address,$name)
	{
		return($this->SetHeader($header,$address." (".$this->QuotedPrintableEncode($name,$this->default_charset).")"));
	}

	Function ResetMessage()
	{
		$this->headers=array();
		$this->body=-1;
		$this->body_parts=0;
		$this->parts=array();
		$this->total_parts=0;
		$this->free_parts=array();
		$this->total_free_parts=0;
		$this->delivery=array("State"=>"");
		$this->error="";
	}

	Function CreatePart(&$definition,&$part)
	{
		$part=-1;
		if(strlen($this->error))
		return($this->error);
		if($this->total_free_parts)
		{
			$this->total_free_parts--;
			$part=$this->free_parts[$this->total_free_parts];
			Unset($this->free_parts[$this->total_free_parts]);
		}
		else
		{
			$part=$this->total_parts;
			$this->total_parts++;
		}
		$this->parts[$part]=$definition;
		return("");
	}

	Function AddPart($part)
	{
		if(strlen($this->error))
		return($this->error);
		switch($this->body_parts)
		{
			case 0;
			$this->body=$part;
			break;
			case 1:
				$parts=array(
				$this->body,
				$part
				);
				if(strlen($error=$this->CreateMixedMultipart($parts,$body)))
				return($error);
				$this->body=$body;
				break;
			default:
				$this->parts[$this->body]["PARTS"][]=$part;
				break;
		}
		$this->body_parts++;
		return("");
	}

	Function ReplacePart($old_part,$new_part)
	{
		$this->parts[$old_part]=$this->parts[$new_part];
		$this->free_parts[$this->total_free_parts]=$new_part;
		$this->total_free_parts++;
		return("");
	}

	Function CreateAndAddPart(&$definition)
	{
		if(strlen($error=$this->CreatePart($definition,$part))
		|| strlen($error=$this->AddPart($part)))
		return($error);
		return("");
	}

	Function CreatePlainTextPart($text,&$part,$charset="")
	{
		if(!strcmp($charset,""))
		$charset=$this->default_charset;
		$definition=array(
			"Content-Type"=>"text/plain",
			"DATA"=>$text
		);
		if(strcmp(strtoupper($charset),"ASCII"))
		$definition["CHARSET"]=$charset;
		return($this->CreatePart($definition,$part));
	}

	Function AddPlainTextPart($text,$charset="")
	{
		if(strlen($error=$this->CreatePlainTextPart($text,$part,$charset))
		|| strlen($error=$this->AddPart($part)))
		return($error);
		return("");
	}

	Function CreateEncodedQuotedPrintableTextPart($text,$charset="",&$part)
	{
		if(!strcmp($charset,""))
		$charset=$this->default_charset;
		$definition=array(
			"Content-Type"=>"text/plain",
			"Content-Transfer-Encoding"=>"quoted-printable",
			"CHARSET"=>$charset,
			"DATA"=>$text
		);
		return($this->CreatePart($definition,$part));
	}

	Function AddEncodedQuotedPrintableTextPart($text,$charset="")
	{
		if(strlen($error=$this->CreateEncodedQuotedPrintableTextPart($text,$charset,$part))
		|| strlen($error=$this->AddPart($part)))
		return($error);
		return("");
	}

	Function CreateQuotedPrintableTextPart($text,$charset="",&$part)
	{
		return($this->CreateEncodedQuotedPrintableTextPart($this->QuotedPrintableEncode($text),$charset,$part));
	}

	Function AddQuotedPrintableTextPart($text,$charset="")
	{
		return($this->AddEncodedQuotedPrintableTextPart($this->QuotedPrintableEncode($text),$charset));
	}

	Function CreateHTMLPart($html,$charset,&$part)
	{
		if(!strcmp($charset,""))
		$charset=$this->default_charset;
		$definition=array(
			"Content-Type"=>"text/html",
			"CHARSET"=>$charset,
			"DATA"=>$html
		);
		return($this->CreatePart($definition,$part));
	}

	Function AddHTMLPart($html,$charset="")
	{
		if(strlen($error=$this->CreateHTMLPart($html,$charset,$part))
		|| strlen($error=$this->AddPart($part)))
		return($error);
		return("");
	}

	Function CreateEncodedQuotedPrintableHTMLPart($html,$charset,&$part)
	{
		if(!strcmp($charset,""))
		$charset=$this->default_charset;
		$definition=array(
			"Content-Type"=>"text/html",
			"Content-Transfer-Encoding"=>"quoted-printable",
			"CHARSET"=>$charset,
			"DATA"=>$html
		);
		return($this->CreatePart($definition,$part));
	}

	Function AddEncodedQuotedPrintableHTMLPart($html,$charset="")
	{
		if(strlen($error=$this->CreateEncodedQuotedPrintableHTMLPart($html,$charset,$part))
		|| strlen($error=$this->AddPart($part)))
		return($error);
		return("");
	}

	Function CreateQuotedPrintableHTMLPart($html,$charset="",&$part)
	{
		return($this->CreateEncodedQuotedPrintableHTMLPart($this->QuotedPrintableEncode($html),$charset,$part));
	}

	Function AddQuotedPrintableHTMLPart($html,$charset="")
	{
		return($this->AddEncodedQuotedPrintableHTMLPart($this->QuotedPrintableEncode($html),$charset));
	}

	Function CreateFilePart(&$file,&$part)
	{
		$name="";
		if(IsSet($file["FileName"]))
		$name=basename($file["FileName"]);
		else
		{
			if(!IsSet($file["Data"]))
			return($this->OutputError("it was not specified the file part file name"));
		}
		if(IsSet($file["Name"]))
		$name=$file["Name"];
		if(strlen($name)==0)
		return($this->OutputError("it was not specified the file part name"));
		if(IsSet($file["Content-Type"]))
		{
			$content_type=$file["Content-Type"];
			$type=$this->Tokenize(strtolower($content_type),"/");
			$sub_type=$this->Tokenize("");
			switch($type)
			{
				case "text":
				case "image":
				case "audio":
				case "video":
				case "application":
					break;
				case "automatic":
					switch($sub_type)
					{
						case "name":
							switch(strtolower($this->GetFilenameExtension($name)))
							{
								case ".xls":
									$content_type="application/excel";
									break;
								case ".hqx":
									$content_type="application/macbinhex40";
									break;
								case ".doc":
								case ".dot":
								case ".wrd":
									$content_type="application/msword";
									break;
								case ".pdf":
									$content_type="application/pdf";
									break;
								case ".pgp":
									$content_type="application/pgp";
									break;
								case ".ps":
								case ".eps":
								case ".ai":
									$content_type="application/postscript";
									break;
								case ".ppt":
									$content_type="application/powerpoint";
									break;
								case ".rtf":
									$content_type="application/rtf";
									break;
								case ".tgz":
								case ".gtar":
									$content_type="application/x-gtar";
									break;
								case ".gz":
									$content_type="application/x-gzip";
									break;
								case ".php":
								case ".php3":
									$content_type="application/x-httpd-php";
									break;
								case ".js":
									$content_type="application/x-javascript";
									break;
								case ".swf":
								case ".rf":
									$content_type="application/x-shockwave-flash2-preview";
									break;
								case ".tar":
									$content_type="application/x-tar";
									break;
								case ".zip":
									$content_type="application/zip";
									break;
								case ".mid":
								case ".midi":
								case ".kar":
									$content_type="audio/midi";
									break;
								case ".mp2":
								case ".mp3":
								case ".mpga":
									$content_type="audio/mpeg";
									break;
								case ".ra":
									$content_type="audio/x-realaudio";
									break;
								case ".wav":
									$content_type="audio/wav";
									break;
								case ".gif":
									$content_type="image/gif";
									break;
								case ".jpg":
								case ".jpe":
								case ".jpeg":
									$content_type="image/jpeg";
									break;
								case ".png":
									$content_type="image/png";
									break;
								case ".tif":
								case ".tiff":
									$content_type="image/tiff";
									break;
								case ".css":
									$content_type="text/css";
									break;
								case ".txt":
									$content_type="text/plain";
									break;
								case ".htm":
								case ".html":
									$content_type="text/html";
									break;
								case ".xml":
									$content_type="text/xml";
									break;
								case ".mpg":
								case ".mpe":
								case ".mpeg":
									$content_type="video/mpeg";
									break;
								case ".qt":
								case ".mov":
									$content_type="video/quicktime";
									break;
								case ".avi":
									$content_type="video/x-ms-video";
									break;
								default:
									$content_type="application/octet-stream";
									break;
							}
							break;
						default:
							return($this->OutputError($content_type." is not a supported automatic content type detection method"));
					}
					break;
				default:
					return($this->OutputError($content_type." is not a supported file content type"));
			}
		}
		else
		$content_type="application/octet-stream";
		$definition=array(
			"Content-Type"=>$content_type,
			"Content-Transfer-Encoding"=>"base64",
			"NAME"=>$name
		);
		if(IsSet($file["Disposition"]))
		{
			switch(strtolower($file["Disposition"]))
			{
				case "inline":
				case "attachment":
					break;
				default:
					return($this->OutputError($file["Disposition"]." is not a supported message part content disposition"));
			}
			$definition["DISPOSITION"]=$file["Disposition"];
		}
		if(IsSet($file["FileName"]))
		$definition["FILENAME"]=$file["FileName"];
		else
		{
			if(IsSet($file["Data"]))
			$definition["DATA"]=$file["Data"];
		}
		return($this->CreatePart($definition,$part));
	}

	Function AddFilePart(&$file)
	{
		if(strlen($error=$this->CreateFilePart($file,$part))
		|| strlen($error=$this->AddPart($part)))
		return($error);
		return("");
	}

	Function CreateMultipart(&$parts,&$part,$type)
	{
		$definition=array(
			"Content-Type"=>"multipart/".$type,
			"PARTS"=>$parts
		);
		return($this->CreatePart($definition,$part));
	}

	Function AddMultipart(&$parts,$type)
	{
		if(strlen($error=$this->CreateMultipart($parts,$part,$type))
		|| strlen($error=$this->AddPart($part)))
		return($error);
		return("");
	}

	Function CreateAlternativeMultipart(&$parts,&$part)
	{
		return($this->CreateMultiPart($parts,$part,"alternative"));
	}

	Function AddAlternativeMultipart(&$parts)
	{
		return($this->AddMultipart($parts,"alternative"));
	}

	Function CreateRelatedMultipart(&$parts,&$part)
	{
		return($this->CreateMultipart($parts,$part,"related"));
	}

	Function AddRelatedMultipart(&$parts)
	{
		return($this->AddMultipart($parts,"related"));
	}

	Function CreateMixedMultipart(&$parts,&$part)
	{
		return($this->CreateMultipart($parts,$part,"mixed"));
	}

	Function AddMixedMultipart(&$parts)
	{
		return($this->AddMultipart($parts,"mixed"));
	}

	Function CreateParallelMultipart(&$parts,&$part)
	{
		return($this->CreateMultipart($parts,$part,"paralell"));
	}

	Function AddParalellMultipart(&$parts)
	{
		return($this->AddMultipart($parts,"paralell"));
	}

	Function GetPartContentID($part)
	{
		if(!IsSet($this->parts[$part]))
		return($path);
		if(!IsSet($this->parts[$part]["Content-ID"]))
		{
			$extension=(IsSet($this->parts[$part]["NAME"]) ? $this->GetFilenameExtension($this->parts[$part]["NAME"]) : "");
			$this->parts[$part]["Content-ID"]=md5(uniqid($part.time())).$extension;
		}
		return($this->parts[$part]["Content-ID"]);
	}

	Function Send()
	{
		if(strlen($this->error))
		return($this->error);
		$headers=$this->headers;
		if(strcmp($this->mailer,""))
		$headers["X-Mailer"]=$this->mailer;
		$headers["MIME-Version"]="1.0";
		if($this->body_parts==0)
		return($this->OutputError("message has no body parts"));
		if(strlen($error=$this->GetPartHeaders($headers,$this->body)))
		return($error);
		if($this->cache_body
		&& IsSet($this->body_cache[$this->body]))
		$body=$this->body_cache[$this->body];
		else
		{
			if(strlen($error=$this->GetPartBody($body,$this->body)))
			return($error);
			if($this->cache_body)
			$this->body_cache[$this->body]=$body;
		}
		if(strcmp($error=$this->StartSendingMessage(),""))
		return($error);
		if(strlen($error=$this->SendMessageHeaders($headers))==0
		&& strlen($error=$this->SendMessageBody($body))==0)
		$error=$this->EndSendingMessage();
		$this->StopSendingMessage();
		return($error);
	}

	Function Mail($to,$subject,$message,$additional_headers="",$additional_parameters="")
	{
		$this->ResetMessage();
		$this->headers=array("To"=>$to,"Subject"=>$subject);
		$content_type="";
		while(strlen($additional_headers))
		{
			preg_match("/([^\r\n]+)(\r?\n)?(.*)\$/",$additional_headers,$matches);
			$header=$matches[1];
			$additional_headers=$matches[3];
			if(!preg_match("/^([^:]+):[ \t]+(.+)\$/",$header,$matches))
			{
				$this->error="invalid header \"$header\"";
				return(0);
			}
			if(strtolower($matches[1])=="content-type")
			{
				if(strlen($content_type))
				{
					$this->error="the content-type header was specified more than once.";
					return(0);
				}
				$content_type=$matches[2];
			}
			else
			$this->SetHeader($matches[1],$matches[2]);
		}
		if(strlen($additional_parameters))
		{
			if(preg_match("/^[ \t]*-f[ \t]*([^@]+@[^ \t]+)[ \t]*(.*)\$/i",$additional_parameters,$matches))
			{
				if(!preg_match('/'.$this->email_regular_expression.'/i',$matches[1]))
				{
					$this->error="it was specified an invalid e-mail address for the additional parameter -f";
					return(0);
				}
				if(strlen($matches[2]))
				{
					$this->error="it were specified some additional parameters after -f e-mail address parameter that are not supported";
					return(0);
				}
				$this->SetHeader("Return-Path",$matches[1]);
			}
			else
			{
				$this->error="the additional parameters that were specified are not supported";
				return(0);
			}
		}
		if(strlen($content_type)==0)
		$content_type="text/plain";
		$definition=array(
			"Content-Type"=>$content_type,
			"DATA"=>$message
		);
		$this->CreateAndAddPart($definition);
		$this->Send();
		return(strlen($this->error)==0);
	}

	Function OpenMailing(&$mailing,&$mailing_properties)
	{
		if(strlen($this->error))
		return($this->error);
		if(!IsSet($mailing_properties["Name"])
		|| strlen($mailing_properties["Name"])==0)
		return($this->OutputError("it was not specified a valid mailing Name"));
		if(!IsSet($mailing_properties["Return-Path"])
		|| strlen($mailing_properties["Return-Path"])==0)
		return($this->OutputError("it was not specified a valid mailing Return-Path"));
		$separator="";
		$directory_separator=(defined("DIRECTORY_SEPARATOR") ? DIRECTORY_SEPARATOR : ((defined("PHP_OS") && !strcmp(substr(PHP_OS,0,3),"WIN")) ? "\\" : "/"));
		$length=strlen($this->mailing_path);
		if($length)
		{
			if($this->mailing_path[$length-1]!=$directory_separator)
			$separator=$directory_separator;
		}
		$base_path=$this->mailing_path.$separator.$mailing_properties["Name"];
		if($this->body_parts==0)
		return($this->OutputError("message has no body parts"));
		$line_break="\n";
		$headers=$this->headers;
		if(strlen($this->mailer))
		$headers["X-Mailer"]=$this->mailer;
		$headers["MIME-Version"]="1.0";
		if(strlen($error=$this->GetPartHeaders($headers,$this->body)))
		return($error);
		if(!($header_file=fopen($base_path.".h","wb")))
		return($this->OutputError("could not open mailing headers file ".$base_path.".h"));
		for($header=0,Reset($headers);$header<count($headers);Next($headers),$header++)
		{
			$header_name=Key($headers);
			if(!@fwrite($header_file,$header_name.": ".$headers[$header_name].$line_break))
			{
				fclose($header_file);
				return($this->OutputError("could not write to the mailing headers file ".$base_path.".h"));
			}
		}
		if(!@fflush($header_file))
		{
			fclose($header_file);
			@unlink($base_path.".h");
			return($this->OutputError("could not write to the mailing headers file ".$base_path.".h"));
		}
		fclose($header_file);
		if(strlen($error=$this->GetPartBody($body,$this->body)))
		{
			@unlink($base_path.".h");
			return($error);
		}
		if(!($body_file=fopen($base_path.".b","wb")))
		{
			@unlink($base_path.".h");
			return($this->OutputError("could not open mailing body file ".$base_path.".b"));
		}
		if(!@fwrite($body_file,$body)
		|| !@fflush($body_file))
		{
			fclose($body_file);
			@unlink($base_path.".b");
			@unlink($base_path.".h");
			return($this->OutputError("could not write to the mailing body file ".$base_path.".b"));
		}
		fclose($body_file);
		if(!($envelope=fopen($base_path.".e","wb")))
		{
			@unlink($base_path.".b");
			@unlink($base_path.".h");
			return($this->OutputError("could not open mailing envelope file ".$base_path.".e"));
		}
		if(!@fwrite($envelope,"F".$mailing_properties["Return-Path"].chr(0))
		|| !@fflush($envelope))
		{
			@fclose($envelope);
			@unlink($base_path.".e");
			@unlink($base_path.".b");
			@unlink($base_path.".h");
			return($this->OutputError("could not write to the return path to the mailing envelope file ".$base_path.".e"));
		}
		$mailing=++$this->last_mailing;
		$this->mailings[$mailing]=array(
			"Envelope"=>$envelope,
			"BasePath"=>$base_path
		);
		return("");
	}

	Function AddMailingRecipient($mailing,&$recipient_properties)
	{
		if(strlen($this->error))
		return($this->error);
		if(!IsSet($this->mailings[$mailing]))
		return($this->OutputError("it was not specified a valid mailing"));
		if(!IsSet($recipient_properties["Address"])
		|| strlen($recipient_properties["Address"])==0)
		return($this->OutputError("it was not specified a valid mailing recipient Address"));
		if(!@fwrite($this->mailings[$mailing]["Envelope"],"T".$recipient_properties["Address"].chr(0)))
		return($this->OutputError("could not write recipient address to the mailing envelope file"));
		return("");
	}

	Function EndMailing($mailing)
	{
		if(strlen($this->error))
		return($this->error);
		if(!IsSet($this->mailings[$mailing]))
		return($this->OutputError("it was not specified a valid mailing"));
		if(!IsSet($this->mailings[$mailing]["Envelope"]))
		return($this->OutputError("the mailing was already ended"));
		if(!@fwrite($this->mailings[$mailing]["Envelope"],chr(0))
		|| !fflush($this->mailings[$mailing]["Envelope"]))
		return($this->OutputError("could not end writing to the mailing envelope file"));
		fclose($this->mailings[$mailing]["Envelope"]);
		Unset($this->mailings[$mailing]["Envelope"]);
		return("");
	}

	Function SendMailing($mailing)
	{
		if(strlen($this->error))
		return($this->error);
		if(!IsSet($this->mailings[$mailing]))
		return($this->OutputError("it was not specified a valid mailing"));
		if(IsSet($this->mailings[$mailing]["Envelope"]))
		return($this->OutputError("the mailing was not yet ended"));
		$this->ResetMessage();
		$base_path=$this->mailings[$mailing]["BasePath"];
		if(GetType($header_lines=@File($base_path.".h"))!="array")
		return($this->OutputError("could not read the mailing headers file ".$base_path.".h"));
		for($line=0;$line<count($header_lines);$line++)
		{
			$header_name=$this->Tokenize($header_lines[$line],": ");
			$this->headers[$header_name]=trim($this->Tokenize("\n"));
		}
		if(!($envelope_file=fopen($base_path.".e","rb")))
		return($this->OutputError("could not open the mailing envelope file ".$base_path.".e"));
		for($bcc=$data="",$position=0;!feof($envelope_file) || strlen($data);)
		{
			if(GetType($break=strpos($data,chr(0),$position))!="integer")
			{
				if(GetType($chunk=fread($envelope_file,$this->file_buffer_length))!="string")
				{
					fclose($envelope_file);
					return($this->OutputError("could not read the mailing envelop file ".$base_path.".e"));
				}
				$data=substr($data,$position).$chunk;
				$position=0;
				continue;
			}
			if($break==$position)
			break;
			switch($data[$position])
			{
				case "F":
					$this->headers["Return-Path"]=substr($data,$position+1,$break-$position-1);
					break;
				case "T":
					$bcc.=(strlen($bcc)==0 ? "" : ", ").substr($data,$position+1,$break-$position-1);
					break;
				default:
					return($this->OutputError("invalid mailing envelope file ".$base_path.".e"));
			}
			$position=$break+1;
		}
		fclose($envelope_file);
		if(strlen($bcc)==0)
		return($this->OutputError("the mailing envelop file ".$base_path.".e does not contain any recipients"));
		$this->headers["Bcc"]=$bcc;
		if(!($body_file=fopen($base_path.".b","rb")))
		return($this->OutputError("could not open the mailing body file ".$base_path.".b"));
		for($data="";!feof($body_file);)
		{
			if(GetType($chunk=fread($body_file,$this->file_buffer_length))!="string")
			{
				fclose($body_file);
				return($this->OutputError("could not read the mailing body file ".$base_path.".b"));
			}
			$data.=$chunk;
		}
		fclose($body_file);
		if(strlen($error=$this->StartSendingMessage()))
		return($error);
		if(strlen($error=$this->SendMessageHeaders($this->headers))==0
		&& strlen($error=$this->SendMessageBody($data))==0)
		$error=$this->EndSendingMessage();
		$this->StopSendingMessage();
		return($error);
	}
};

?>