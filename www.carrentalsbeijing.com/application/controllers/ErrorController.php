<?php
class ErrorController extends Controller {
	public function init() {
		
	}
	
	public function pageNotFoundAction() {
		$this->view->e = self::_getParam('exception');
		$this->setNoRender();
		$this->renderScript('common/error-404.phtml');
	}
	
	public function internalErrorAction() {
		$this->view->e = self::_getParam('exception');
		$this->setNoRender();
		$this->renderScript('common/error-500.phtml');
	}
}