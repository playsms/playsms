<?php

/**
 * GoogleTranslateWrapper: PHP wrapper for Google Translation services
 * Copyright (C) 2010  Sameer Borate
 *
 * GoogleTranslateWrapper is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GoogleTranslateWrapper is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GoogleTranslateWrapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category    GoogleTranslateWrapper
 * @package     GoogleTranslateWrapper
 * @author      Sameer Borate
 * @copyright   2010 Sameer Borate
 */


/**
 * GoogleTranslateWrapper Main Class
 *
 * @category    GoogleTranslateWrapper
 * @package     GoogleTranslateWrapper
 * @author      Sameer Borate
 * @link        http://www.codediesel.com
 * @copyright   2010 Sameer Borate
 * @version     1.7.3
 */

class GoogleTranslateWrapper
{
	/**
	 * URL of Google translate
	 * @var string
	 */
	private $_googleTranslateUrl = 'http://ajax.googleapis.com/ajax/services/language/translate';

	/**
	 * URL of Google language detection
	 * @var string
	 */
	private $_googleDetectUrl = 'http://ajax.googleapis.com/ajax/services/language/detect';

	/**
	 * Language to translate from
	 * @var string
	 */
	private $_fromLang = '';

	/**
	 * Language to translate to
	 * @var string
	 */
	private $_toLang = '';

	/**
	 * API version
	 * @var string
	 */
	private $_version = '1.0';

	/**
	 * Text to translate
	 * @var string
	 */
	private $_text = '';

	/**
	 * Site url using the code
	 * @var string
	 */
	private $_siteUrl = '';

	/**
	 * Google API key
	 * @var string
	 */
	private $_apiKey = '';

	/**
	 * Host IP address
	 * @var string
	 */
	private $_ip = '';

	/**
	 * POST fields
	 * @var string
	 */
	private $_postFields;

	/**
	 * Translated Text
	 * @var string
	 */
	private $_translatedText;

	/**
	 * Service Error
	 * @var string
	 */
	private $_serviceError = "";

	/**
	 * Translation success
	 * @var boolean
	 */
	private $_success = false;

	/**
	 * Translation character limit.
	 * Currently the limit set by Google is 5000
	 * @var integer
	 */
	private $_stringLimit = 5000;

	/**
	 * Chunk array
	 * @var array
	 */
	private $_chunks = 0;

	/**
	 * Current data chunk
	 * @var string
	 */
	private $_currentChunk = 0;

	/**
	 * Total chunks
	 * @var integer
	 */
	private $_totalChunks = 0;

	/**
	 * Detected source language
	 * @var string
	 */
	private $_detectedSourceLanguage = "";

	const DETECT = 1;
	const TRANSLATE = 2;


	/**
	 * Build a POST url to query Google
	 *
	 */
	private function _composeUrl($type)
	{
		if($type == self::TRANSLATE)
		{
			$fields = array('v'         => $this->_version,
                            'q'         => $this->_text,
                            'langpair'  => $this->_fromLang . "|" . $this->_toLang);
		}
		elseif($type == self::DETECT)
		{
			$fields = array('v' => $this->_version,
                            'q' => $this->_text);
		}

		if($this->_apiKey != "") $fields['key'] = $this->_apiKey;
		if($this->_ip != "") $fields['userip'] = $this->_ip;

		$this->_postFields = http_build_query($fields, "&");
	}


	/**
	 * Reset variables to be used for next query
	 *
	 */
	private function _reset()
	{
		$this->_fromLang = '';
		$this->_toLang = '';
		$this->_text = '';
		$this->_translatedText = '';
		$this->_postFields = '';
		$this->_serviceError = '';
		$this->_chunks = 0;
		$this->_currentChunk = 0;
		$this->_totalChunks = 0;
		$this->_detectedSourceLanguage = "";
	}


	/**
	 * Convert JSON response to an array.
	 *
	 * json_decode function is only available in PHP version 5.2.0 and above.
	 * So we use the PEAR package to decode JSON if the json_decode function
	 * is not available.
	 *
	 * @param string POST fields
	 * @return string response
	 */
	private function _decodeJSON($contents)
	{
		if (!function_exists('json_decode'))
		{
			require_once 'JSON.php';
			$json_handle = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
			return $json_handle->decode($contents);
		}
		else
		{
			return json_decode($contents, true);
		}
	}


	/**
	 * Process the built query using cURL and POST
	 *
	 * @param string POST fields
	 * @return string response
	 */
	private function _remoteQuery($query)
	{
		if(!function_exists('curl_init'))
		{
			return "";
		}

		/* Setup CURL and its options*/
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->_googleTranslateUrl);
		curl_setopt($ch, CURLOPT_REFERER, $this->_siteUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

		$response = curl_exec($ch);

		return $response;
	}


	/**
	 * Process the built query using cURL and GET
	 *
	 * @param string GET fields
	 * @return string response
	 */
	private function _remoteQueryDetect($query)
	{
		if(!function_exists('curl_init'))
		{
			return "";
		}

		$ch = curl_init();
		$url = $this->_googleDetectUrl . "?" . $query;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, $this->_siteUrl);

		$response = curl_exec($ch);
		return $response;
	}


	/**
	 * Self test the class
	 *
	 * @return boolean
	 */
	public function selfTest()
	{
		if(!function_exists('curl_init'))
		{
			echo "cURL not installed.";
		}
		else
		{
			$testText = $this->translate("hello", "fr", "en");
			echo ($testText == "bonjour") ? "Test Ok." : "Test Failed.";
		}
	}

	/**
	 * Check if the last translation was a success
	 *
	 * @return boolean
	 */
	public function isSuccess()
	{
		return $this->_success;
	}

	/**
	 * Get the last generated service error
	 *
	 * @return String
	 */
	public function getLastError()
	{
		return $this->_serviceError;
	}


	/**
	 * Get the detected source language, if the source is not provided
	 * during query
	 *
	 * @return String
	 */
	public function getDetectedSource()
	{
		return $this->_detectedSourceLanguage;
	}


	/**
	 * Set credentials (optional) when accessing Google translation services
	 *
	 * @param string $apiKey your google api key
	 */
	public function setCredentials($apiKey, $ip)
	{
		$this->_apiKey = $apiKey;
		$this->_ip = $ip;
	}


	/**
	 * Set Referrer header
	 *
	 * @param string $siteUrl your website url
	 */
	public function setReferrer($siteUrl)
	{
		$this->_siteUrl = $siteUrl;
	}


	/**
	 * Translate the given text
	 * @param string $text text to translate
	 * @param string $to language to translate to
	 * @param string $from optional language to translate from
	 * @return boolean | string
	 */
	public function translate($text = '', $to, $from = '')
	{
		$this->_success = false;

		if($text == '' || $to == '')
		{
			return false;
		}
		else
		{
			if($this->_totalChunks == 0)
			{
				$this->_chunks = str_split($text, $this->_stringLimit);
				$this->_totalChunks = count($this->_chunks);

				$this->_currentChunk = 0;
				$this->_text = $this->_chunks[$this->_currentChunk];
				$this->_toLang = $to;
				$this->_fromLang = $from;
			}
			else
			{
				$this->_text = $text;
				$this->_toLang = $to;
				$this->_fromLang = $from;
			}
		}

		$this->_composeUrl(self::TRANSLATE);

		if($this->_text != '' && $this->_postFields != '')
		{
			$contents = $this->_remoteQuery($this->_postFields);
			$json = $this->_decodeJSON($contents);

			if($json['responseStatus'] == 200)
			{
				$this->_translatedText .= $json['responseData']['translatedText'];
				if(isset($json['responseData']['detectedSourceLanguage']))
				{
					$this->_detectedSourceLanguage = $json['responseData']['detectedSourceLanguage'];
				}

				$this->_currentChunk++;

				if($this->_currentChunk >= $this->_totalChunks) {
					$translatedText = $this->_translatedText;
					$this->_reset();
					$this->_success = true;

					return $translatedText;
				}
				else {
					return $this->translate($this->_chunks[$this->_currentChunk], $to, $from);
				}

			}
			else
			{
				$this->_reset();
				$this->_serviceError = 	$json['responseDetails'];
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Detect the language of the given text
	 * @param string $text text language to detect
	 * @return boolean | string
	 */
	public function detectLanguage($text)
	{

		if($text == '')
		{
			return false;
		}
		else
		{
			/* Keep the text length to 1000 characters. Google behaves
			 * inconsistently for more characters.
			 */
			$this->_text = substr($text, 0, 1000);
		}


		$this->_composeUrl(self::DETECT);

		if($this->_text != '' && $this->_postFields != '')
		{
			$contents = $this->_remoteQueryDetect($this->_postFields);
			$json = $this->_decodeJSON($contents);

			if($json['responseStatus'] == 200)
			{
				$this->_reset();
				return $json['responseData'];
			}
			else
			{
				$this->_serviceError = 	$json['responseData'];
				return false;
			}
		}
		else
		{
			return false;
		}

	}

}


?>