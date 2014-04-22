<?php

/*
 * The MIT License
 *
 * Copyright 2014 Anton Raharja <antonrd at gmail dot com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
*/

namespace Playsms;

/**
 * Dead simple PHP template engine
 *
 * @author Anton Raharja
 * @link https://github.com/antonraharja/tpl
 */
class Tpl
{

	// actual template file full path
	private $_filename;

	// variables holding the content
	private $_content;
	private $_result;
	private $_compiled;

	// default configuration
	private $_config_echo = 'echo';
	private $_config_dir_template = './templates';
	private $_config_dir_cache = './cache';
	private $_config_extension = '.html';
	
	// array holding configuration
	public $config = array();
	
	// template rules
	public $name;
	public $vars = array();
	public $ifs = array();
	public $loops = array();
	public $injects = array();

	/**
	 * Constructor
	 * @param array $config Default configuration
	 */
	function __construct($config = array()) {
		$default = array(
			'echo' => $this->_config_echo,
			'dir_template' => $this->_config_dir_template,
			'dir_cache' => $this->_config_dir_cache,
			'extension' => $this->_config_extension,
		);

		$this->config = array_merge($default, $config);
	}
	
	// private methods
	
	
	
	/**
	 * Template string manipulation
	 * @param  string $key     Template key
	 * @param  string $val     Template value
	 */
	private function _setString($key, $val) {
		$this->_result = str_replace('{{' . $key . '}}', $val, $this->_result);
	}
	
	/**
	 * Template loop manipulation
	 * @param  string $key     Template key
	 * @param  string $val     Template value
	 */
	private function _setArray($key, $val) {
		preg_match("/<loop\." . $key . ">(.*?)<\/loop\." . $key . ">/s", $this->_result, $l);
		
		$loop_content = '';
		$loop = $l[1];
		foreach ($val as $v) {
			$loop_replaced = $loop;
			foreach ($v as $x => $y) {
				$loop_replaced = str_replace('{{' . $key . '.' . $x . '}}', $y, $loop_replaced);
			}
			$loop_content.= $loop_replaced;
		}
		
		$this->_result = preg_replace("/<loop\." . $key . ">(.*?)<\/loop\." . $key . ">/s", $loop_content, $this->_result);
		$this->_result = str_replace("<loop." . $key . ">", '', $this->_result);
		$this->_result = str_replace("</loop." . $key . ">", '', $this->_result);
	}
	
	/**
	 * Template boolean manipulation
	 * @param  string $key     Template key
	 * @param  string $val     Template value
	 */
	private function _setBool($key, $val) {
		if ($key && !$val) {
			$this->_result = preg_replace("/<if\." . $key . ">(.*?)<\/if\." . $key . ">/s", '', $this->_result);
		}
		$this->_result = str_replace("<if." . $key . ">", '', $this->_result);
		$this->_result = str_replace("</if." . $key . ">", '', $this->_result);
	}
	
	/**
	 * Set content from file
	 */
	private function _setContentFromFile() {
		
		// empty original template content
		$this->setContent('');
		
		// check for template file and load it
		if ($filename = $this->getTemplate()) {
			if (file_exists($filename)) {
				$content = trim(file_get_contents($this->_filename));
				$this->setContent($content);
			}
		}
	}
	
	/**
	 * Process original content according to template rules and settings
	 */
	private function _compile() {
		
		// remove spaces
		$this->_result = str_replace('{{ ', '{{', $this->_content);
		$this->_result = str_replace(' }}', '}}', $this->_result);
		
		// check if
		if ($this->ifs) {
			foreach ($this->ifs as $key => $val) {
				$this->_setBool($key, $val);
			}
			empty($this->ifs);
		}
		
		// check loop
		if ($this->loops) {
			foreach ($this->loops as $key => $val) {
				$this->_setArray($key, $val);
			}
			empty($this->loops);
		}
		
		// check static replaces
		if ($this->vars) {
			foreach ($this->vars as $key => $val) {
				$this->_setString($key, $val);
			}
			empty($this->vars);
		}
		
		// include global vars
		if (is_array($this->injects)) {
			foreach ($this->injects as $inject) {
				global $ {
					$inject
				};
			}
			extract($this->injects);
		}
		
		// remove if and loop traces
		$this->_result = preg_replace("/<if\..*?>(.*?)<\/if\..*?>/s", '', $this->_result);
		$this->_result = preg_replace("/<loop\..*?>(.*?)<\/loop\..*?>/s", '', $this->_result);
		
		// check dynamic variables
		$pattern = "\{\{(.*?)\}\}";
		preg_match_all("/" . $pattern . "/", $this->_result, $matches, PREG_SET_ORDER);
		foreach ($matches as $block) {
			$chunk = $block[0];
			$codes = '<?php ' . $this->config['echo'] . '(' . trim($block[1]) . ')' . '; ?>';
			$this->_result = str_replace($chunk, $codes, $this->_result);
		}
		
		// attempt to create cache file for this template in storage directory
		$cache_file = md5($this->_filename) . '.compiled';
		$cache = $this->config['dir_cache'] . '/' . $cache_file;
		$fd = @fopen($cache, 'w+');
		@fwrite($fd, $this->_result);
		@fclose($fd);
		
		// when failed, try to create in /tmp
		if (!file_exists($cache)) {
			$cache = '/tmp/' . $cache_file;
			$fd = @fopen($cache, 'w+');
			@fwrite($fd, $this->_result);
			@fclose($fd);
		}
		
		// if template cache file created then include it, else use eval() to compile
		if (file_exists($cache)) {
			ob_start();
			include $cache;
			$this->_compiled = ob_get_contents();
			ob_end_clean();
			@unlink($cache);
		} else {
			ob_start();
			eval('?>' . $this->_result . '<?php ');
			$this->_compiled = ob_get_contents();
			ob_end_clean();
		}
	}
	
	// public methods
	
	

	/**
	 * Set configuration
	 * - echo         : PHP display/print command (default: echo)
	 * - dir_template : Template files path (default: ./templates)
	 * - dir_cache    : Compiled files path (default: ./cache)
	 * - extension    : File extension (default: .html)
	 * @param array $config Default configuration
	 */
	public function setConfig($config) {
		$this->config = array_merge($this->config, $config);
		
		$this->config['echo'] = ( $this->config['echo'] ? $this->config['echo'] : $this->_config_echo );
		$this->config['dir_template'] = ( $this->config['dir_template'] ? $this->config['dir_template'] : $this->_config_dir_template );
		$this->config['dir_cache'] = ( $this->config['dir_cache'] ? $this->config['dir_cache'] : $this->_config_dir_cache );
		$this->config['extension'] = ( $this->config['extension'] ? $this->config['extension'] : $this->_config_extension );

		return $this;
	}
	
	/**
	 * Get configuration
	 * @return array Default configuration
	 */
	public function getConfig() {
		return $this->config;
	}
	
	/**
	 * Set template name
	 * @param string $name Name
	 * @return mixed Tpl object
	 */
	function setName($name) {
		$this->name = $name;
		
		return $this;
	}
	
	/**
	 * Set template static variables
	 * @param array $vars Variables
	 * @return mixed Tpl object
	 */
	function setVars($vars) {
		if (is_array($vars)) {
			$this->vars = $vars;
		}
		
		return $this;
	}
	
	/**
	 * Set template logic rules
	 * @param array $ifs IF logic rules
	 * @return mixed Tpl object
	 */
	function setIfs($ifs) {
		if (is_array($ifs)) {
			$this->ifs = $ifs;
		}
		
		return $this;
	}
	
	/**
	 * Set template loop rules
	 * @param array $loops Loop rules
	 * @return mixed Tpl object
	 */
	function setLoops($loops) {
		if (is_array($loops)) {
			$this->loops = $loops;
		}
		
		return $this;
	}
	
	/**
	 * Set template injected global variables
	 * @param array $injects List of injected global variables
	 * @return mixed Tpl object
	 */
	function setInjects($injects) {
		if (is_array($injects)) {
			$this->injects = $injects;
		}
		
		return $this;
	}
	
	/**
	 * Compile template
	 * @return mixed Tpl object
	 */
	function compile() {
		
		// if no setContent() then load the from file
		if (!$this->getContent()) {
			
			// if no setTemplate() then use default template file
			if (!$this->getTemplate()) {
				$this->setTemplate($this->config['dir_template'] . '/' . $this->name . $this->config['extension']);
			}
			
			$this->_setContentFromFile();
		}
		
		$this->_compile();
		
		return $this;
	}
	
	/**
	 * Set full path template file
	 * @param string $filename Filename
	 * @return mixed Tpl object
	 */
	function setTemplate($filename) {
		$this->_filename = $filename;
		
		return $this;
	}
	
	/**
	 * Get full path template filename
	 * @return string Filename
	 */
	function getTemplate() {
		return $this->_filename;
	}
	
	/**
	 * Set original template content
	 * @param string $content Original content
	 * @return mixed Tpl object
	 */
	function setContent($content) {
		$this->_content = $content;
		
		return $this;
	}
	
	/**
	 * Get original template content
	 * @return string Original content
	 */
	function getContent() {
		return $this->_content;
	}
	
	/**
	 * Get manipulated template content
	 * @return string Manipulated content
	 */
	function getResult() {
		return $this->_result;
	}
	
	/**
	 * Get compiled template content
	 * @return string Compiled content
	 */
	function getCompiled() {
		return $this->_compiled;
	}
}
