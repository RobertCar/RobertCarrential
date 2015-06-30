<?php
class Request {
	
	protected $controllerName, $controllerPath;
	protected $actionName, $actionFile;
	protected $userParams = array();
	protected $requestUri;
	protected $_pathInfo;
	protected $_rawBody;
	protected $_dispatched;
	
	public function __construct() {
		$this->initControllerAndActionName();
	}
	
	public function setController($name=null) {
		$controllerName = 'Index';
		$controllerPath = 'index';
		if (!empty($name)) {
			$name = strtolower($name);
			$name = preg_replace('/[^a-z0-9\- ]/', '', $name);
			if (!empty($name)) {
				$controllerPath = $name;
			}
			$name = str_replace('-', ' ', $name);
			$name = str_replace(' ', '', ucwords($name));
			if (!empty($name)) {
				$controllerName = $name;
			}
		}
		$this->controllerName = $controllerName;
		$this->controllerPath = $controllerPath;
	}
	
	public function setAction($name=null) {
		$actionName = 'index';
		$actionFile = 'index';
		if (!empty($name)) {
			$name = strtolower($name);
			$name = preg_replace('/[^a-z0-9\- ]/', '', $name);
			if (!empty($name)) {
				$actionFile = $name;
			}
			$name = str_replace('-', ' ', $name);
			$name = str_replace(' ', '', ucwords($name));
			if (!empty($name)) {
				$actionName = strtolower(substr($name, 0, 1)) . substr($name, 1);
			}
		}
		$this->actionName = $actionName;
		$this->actionFile = $actionFile;
	}
	
	private function initControllerAndActionName() {
		$pathInfo = $this->getPathInfo();
		$string = trim($pathInfo, '/\\');
		$parts = explode('/', $string);
		isset($parts[0]) ? $this->setController($parts[0]) : $this->setController();
		isset($parts[1]) ? $this->setAction($parts[1]) : $this->setAction();
	}
	
	public function setDispatched($flag) {
		$this->_dispatched = $flag ? true : false;
	}
	
	public function isDispatched() {
		return $this->_dispatched;
	}
	
	public function getControllerName() {
		return $this->controllerName;
	}
	
	public function getActionName() {
		return $this->actionName;
	}
	
	public function getControllerClassName() {
		return $this->controllerName . 'Controller';
	}
	
	public function getActionFuncName() {
		return $this->actionName . 'Action';
	}
	
	public function getControllerPath() {
		return $this->controllerPath;
	}
	
	public function getActionFile() {
		return $this->actionFile;
	}
	
	public function getUserParam($key, $default = null) {
		$key = (string)$key;
		if (isset($this->userParams[$key])) {
			return $this->userParams[$key];
		}
		return $default;
	}
	
	public function setUserParam($key, $value) {
		$key = (string)$key;
		if ((null === $value) && isset($this->userParams[$key])) {
			unset($this->userParams[$key]);
		} elseif (null !== $value) {
			$this->userParams[$key] = $value;
		}
		return $this;
	}
	
	public function getUserParams() {
		return $this->_params;
	}
	
	public function clearUserParams() {
		$this->userParams = array();
		return $this;
	}
	
	public function get($key) {
		switch (true) {
			case isset($this->userParams[$key]):
				return $this->userParams[$key];
			case isset($_GET[$key]):
				return $_GET[$key];
			case isset($_POST[$key]):
				return $_POST[$key];
			case isset($_COOKIE[$key]):
				return $_COOKIE[$key];
			case ($key == 'REQUEST_URI'):
				return $this->getRequestUri();
			case ($key == 'PATH_INFO'):
				return $this->getPathInfo();
			case isset($_SERVER[$key]):
				return $_SERVER[$key];
			case isset($_ENV[$key]):
				return $_ENV[$key];
			default:
				return null;
		}
	}
	
	public function getParams() {
		return $_REQUEST;
	}
	
	public function getQuery($key = null, $default = null) {
		if (null === $key) {
			return $_GET;
		}
		return (isset($_GET[$key])) ? $_GET[$key] : $default;
	}
	
	public function getPost($key = null, $default = null) {
		if (null === $key) {
			return $_POST;
		}
		return (isset($_POST[$key])) ? $_POST[$key] : $default;
	}
	
	public function getCookie($key = null, $default = null) {
		if (null === $key) {
			return $_COOKIE;
		}
		return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
	}
	
	public function getServer($key = null, $default = null) {
		if (null === $key) {
			return $_SERVER;
		}
		return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
	}
	
	public function getEnv($key = null, $default = null) {
		if (null === $key) {
			return $_ENV;
		}
		return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
	}
	
	public function setRequestUri() {
		if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			// check this first so IIS will catch
			$requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif (
		// IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
		isset($_SERVER['IIS_WasUrlRewritten'])
		&& $_SERVER['IIS_WasUrlRewritten'] == '1'
		&& isset($_SERVER['UNENCODED_URL'])
		&& $_SERVER['UNENCODED_URL'] != ''
		) {
			$requestUri = $_SERVER['UNENCODED_URL'];
		} elseif (isset($_SERVER['REQUEST_URI'])) {
			$requestUri = $_SERVER['REQUEST_URI'];
			// Http proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
			$schemeAndHttpHost = $this->getScheme() . '://' . $this->getHttpHost();
			if (strpos($requestUri, $schemeAndHttpHost) === 0) {
				$requestUri = substr($requestUri, strlen($schemeAndHttpHost));
			}
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
			// IIS 5.0, PHP as CGI
			$requestUri = $_SERVER['ORIG_PATH_INFO'];
			if (!empty($_SERVER['QUERY_STRING'])) {
				$requestUri .= '?' . $_SERVER['QUERY_STRING'];
			}
		} else {
			return $this;
		}
	
		$this->requestUri = $requestUri;
		return $this;
	}
	
	public function getRequestUri() {
		if (empty($this->requestUri)) {
			$this->setRequestUri();
		}
	
		return $this->requestUri;
	}
	
	/**
	* Set the PATH_INFO string
	*
	* @return Request
	*/
	public function setPathInfo()
	{
		if (null === ($requestUri = $this->getRequestUri())) {
			return $this;
		}

		// Remove the query string from REQUEST_URI
		if ($pos = strpos($requestUri, '?')) {
			$requestUri = substr($requestUri, 0, $pos);
		}
		$pathInfo = $requestUri;
		$this->_pathInfo = (string) $pathInfo;
		return $this;
	}
	
	/**
	 * Returns everything between the BaseUrl and QueryString.
	 * This value is calculated instead of reading PATH_INFO
	 * directly from $_SERVER due to cross-platform differences.
	 *
	 * @return string
	 */
	public function getPathInfo()
	{
		if (empty($this->_pathInfo)) {
			$this->setPathInfo();
		}
	
		return $this->_pathInfo;
	}
	
	/**
	* Return the method by which the request was made
	*
	* @return string
	*/
	public function getMethod()
	{
		return $this->getServer('REQUEST_METHOD');
	}
	
	/**
	 * Was the request made by POST?
	 *
	 * @return boolean
	 */
	public function isPost()
	{
		if ('POST' == $this->getMethod()) {
			return true;
		}
	
		return false;
	}
	
	/**
	 * Was the request made by GET?
	 *
	 * @return boolean
	 */
	public function isGet()
	{
		if ('GET' == $this->getMethod()) {
			return true;
		}
	
		return false;
	}
	
	/**
	 * Was the request made by PUT?
	 *
	 * @return boolean
	 */
	public function isPut()
	{
		if ('PUT' == $this->getMethod()) {
			return true;
		}
	
		return false;
	}
	
	/**
	 * Was the request made by DELETE?
	 *
	 * @return boolean
	 */
	public function isDelete()
	{
		if ('DELETE' == $this->getMethod()) {
			return true;
		}
	
		return false;
	}
	
	/**
	 * Was the request made by HEAD?
	 *
	 * @return boolean
	 */
	public function isHead()
	{
		if ('HEAD' == $this->getMethod()) {
			return true;
		}
	
		return false;
	}
	
	/**
	 * Was the request made by OPTIONS?
	 *
	 * @return boolean
	 */
	public function isOptions()
	{
		if ('OPTIONS' == $this->getMethod()) {
			return true;
		}
	
		return false;
	}
	
	/**
	 * Is the request a Javascript XMLHttpRequest?
	 *
	 * Should work with Prototype/Script.aculo.us, possibly others.
	 *
	 * @return boolean
	 */
	public function isXmlHttpRequest()
	{
		return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
	}
	
	/**
	 * Is this a Flash request?
	 *
	 * @return boolean
	 */
	public function isFlashRequest()
	{
		$header = strtolower($this->getHeader('USER_AGENT'));
		return (strstr($header, ' flash')) ? true : false;
	}
	
	/**
	 * Is https secure request
	 *
	 * @return boolean
	 */
	public function isSecure()
	{
		return ($this->getScheme() === 'https');
	}
	
	/**
	 * Return the raw body of the request, if present
	 *
	 * @return string|false Raw body, or false if not present
	 */
	public function getRawBody()
	{
		if (null === $this->_rawBody) {
			$body = file_get_contents('php://input');
	
			if (strlen(trim($body)) > 0) {
				$this->_rawBody = $body;
			} else {
				$this->_rawBody = false;
			}
		}
		return $this->_rawBody;
	}
	
	/**
	 * Return the value of the given HTTP header. Pass the header name as the
	 * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
	 * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
	 *
	 * @param string $header HTTP header name
	 * @return string|false HTTP header value, or false if not found
	 * @throws Zend_Controller_Request_Exception
	 */
	public function getHeader($header)
	{
		// Try to get it from the $_SERVER array first
		$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
		if (isset($_SERVER[$temp])) {
			return $_SERVER[$temp];
		}
	
		// This seems to be the only way to get the Authorization header on
		// Apache
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if (isset($headers[$header])) {
				return $headers[$header];
			}
			$header = strtolower($header);
			foreach ($headers as $key => $value) {
				if (strtolower($key) == $header) {
					return $value;
				}
			}
		}
	
		return false;
	}
	
	/**
	 * Get the request URI scheme
	 *
	 * @return string
	 */
	public function getScheme()
	{
		return ($this->getServer('HTTPS') == 'on') ? 'https' : 'http';
	}
	
	/**
	 * Get the HTTP host.
	 *
	 * "Host" ":" host [ ":" port ] ; Section 3.2.2
	 * Note the HTTP Host header is not the same as the URI host.
	 * It includes the port while the URI host doesn't.
	 *
	 * @return string
	 */
	public function getHttpHost()
	{
		$host = $this->getServer('HTTP_HOST');
		if (!empty($host)) {
			return $host;
		}
	
		$scheme = $this->getScheme();
		$name   = $this->getServer('SERVER_NAME');
		$port   = $this->getServer('SERVER_PORT');
	
		if(null === $name) {
			return '';
		}
		elseif (($scheme == 'http' && $port == 80) || ($scheme == 'https' && $port == 443)) {
			return $name;
		} else {
			return $name . ':' . $port;
		}
	}
	
	/**
	 * Get the client's IP addres
	 *
	 * @param  boolean $checkProxy
	 * @param boolean $long IP in Long
	 * @return string
	 */
	public function getClientIp($checkProxy = true, $long=false)
	{
		if ($checkProxy && $this->getServer('HTTP_CLIENT_IP') != null) {
			$ip = $this->getServer('HTTP_CLIENT_IP');
		} else if ($checkProxy && $this->getServer('HTTP_X_FORWARDED_FOR') != null) {
			$ip = $this->getServer('HTTP_X_FORWARDED_FOR');
		} else {
			$ip = $this->getServer('REMOTE_ADDR');
		}
	
		return $long ? ip2long($ip) : $ip;
	}
}