<?php
class View {
	
	protected $_request;
	protected $_response;
	protected $_file;
	protected $_vars = array();
	protected $_encoding = 'utf-8';
	
	public function __construct() {
		$this->_request = Application::getRequest();
		$this->_response = Application::getResponse();
	}
	
	public function __set($key, $val)
	{
		if ('_' != substr($key, 0, 1)) {
			$this->_vars[$key] = $val;
			return;
		}
	}
	
	public function __get($key)
	{
		if (isset($this->_vars[$key])) {
			return $this->_vars[$key];
		}
		return null;
	}
	
	public function __unset($key)
	{
		if ('_' != substr($key, 0, 1) && isset($this->_vars[$key])) {
			unset($this->_vars[$key]);
		}
	}
	
	public function __isset($key)
	{
		if ('_' != substr($key, 0, 1)) {
			return isset($this->_vars[$key]);
		}
	
		return false;
	}
	
	public function getVars()
	{
		return $this->_vars;
	}
	
	public function clearVars()
	{
		$this->_vars = array();
	}
	
	public function setEncoding($enc) {
		$this->_encoding = $enc;
	}
	
	public function getEncoding($enc) {
		return $this->_encoding;
	}
	
	public function escape($var) {
		return htmlspecialchars($var, ENT_COMPAT, $this->_encoding);
	}
	
	public function render($controller, $action) {
		return $this->renderScript($controller . DIRECTORY_SEPARATOR . $action . '.phtml');
	}
	
	public function renderScript($name) {
		$this->_file = APPLICATION_PATH . 'views/' . $name;
		unset($name);
		include $this->_file;
	}
}