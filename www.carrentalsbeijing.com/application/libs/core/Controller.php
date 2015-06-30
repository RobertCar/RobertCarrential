<?php
abstract class Controller {
	
	/**
	 * @var Request
	 */
	protected $_request;
	
	/**
	* @var Response
	*/
	protected $_response;
	
	/**
	* @var View
	*/
	protected $view;
	
	public function __construct() {
		$this->_request = Application::getRequest();
		$this->_response = Application::getResponse();
		$this->view = Application::getView();
		$this->init();
	}
	
	protected function init() {
		
	}
	
	public function preDispatch() {
		
	}
	
	public function postDispatch() {
		
	}
	
	public function renderScript($script) {
		return $this->view->renderScript($script);
	}
	
	public function setNoOutputBuffer() {
		Application::setNoOutputBuffer();
	}
	
	public function setNoRender() {
		Application::setNoRender();
	}
	
	/**
	* Gets a parameter from the {@link $_request Request object}.  If the
	* parameter does not exist, NULL will be returned.
	*
	* If the parameter does not exist and $default is set, then
	* $default will be returned instead of NULL.
	*
	* @param string $paramName
	* @param mixed $default
	* @return mixed
	*/
	protected function _getParam($paramName, $default = null)
	{
		$value = $this->_request->get($paramName);
		if ((null === $value || '' === $value) && (null !== $default)) {
			$value = $default;
		}
	
		return $value;
	}

	/**
	* Gets a parameter from the {@link $_request Request object}.  If the
	* parameter does not exist, NULL will be returned.
	*
	* If the parameter does not exist and $default is set, then
	* $default will be returned instead of NULL.
	*
	* @param string $paramName
	* @param mixed $default
	* @return mixed
	*/
	protected function getParam($paramName, $default = null) {
		$value = $this->_request->get($paramName);
		if ((null === $value || '' === $value) && (null !== $default)) {
			$value = $default;
		}
	
		return $value;
	}
	
	/**
	* Determine whether a given parameter exists in the
	*
	* @param string $paramName
	* @return boolean
	*/
	protected function _hasParam($paramName)
	{
		return null !== $this->_request->get($paramName);
	}
	
	/**
	 * Return all parameters in the {@link $_request Request object}
	 * as an associative array.
	 *
	 * @return array
	 */
	protected function _getAllParams()
	{
		return $this->_request->getParams();
	}
	
	protected function _redirect($url) {
		$this->_response->setRedirect($url);
	}
}