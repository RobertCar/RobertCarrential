<?php
class IndexController extends ControllerAbstract {

	public function init() {
		parent::init();
	}

	public function indexAction() {
		$this->_redirect('/private-car');
	}
}