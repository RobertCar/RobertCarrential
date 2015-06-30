<?php
abstract class AdminControllerAbstract extends ControllerAbstract {
	
	/**
	 * @var Model_Employee
	 */
	protected $employee;
	
	public function init() {
		parent::init();
		if (!in_array($this->_request->getActionName(), array('login', 'logout'))) {
			$authString = $this->_getParam(Model_Employee::COOKIE_NAME, '');
			$data = StringHelper::decrypt($authString);
			if (is_array($data)) {
				$id = $data['id'];
				$password = $data['password'];
				$employee = new Model_Employee($id);
				if ($employee->exists() && $employee->get('password')==$password) {
					$this->view->employee = $this->employee = $employee;
					$this->_request->setUserParam('EMPLOYEE_ID', $employee->get('id'));
				}
			}
			$this->checkAuth();
		}
	}

	protected function hasPrivilege(array $roles) {
		return in_array($this->employee->getRole(), $roles);
	}
	
	private function checkAuth() {
		if (empty($this->employee)) {
			setcookie(Model_Employee::COOKIE_NAME, '', null, '/');
			$this->error('您还未登录，无法操作');
		}
	}
}