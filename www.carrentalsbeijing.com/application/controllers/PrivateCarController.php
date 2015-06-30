<?php
class PrivateCarController extends ControllerAbstract {

	const AUTH_COOKIE_NAME = 'authtoken';

	protected $user;

	public function init() {
		parent::init();
		if (!in_array($this->_request->getActionName(), array('signUp', 'signIn', 'signOut'))) {
			$authString = $this->_getParam(self::AUTH_COOKIE_NAME, '');
			$data = StringHelper::decrypt($authString);
			if (is_array($data)) {
				$email = $data['email'];
				$password = $data['password'];
				$user = new Model_User($email);
				if ($user->exists() && $user->get('password')==$password) {
					$this->view->user = $this->user = $user;
					$this->_request->setUserParam('EMAIL', $user->get('email'));
				}
			}
			if ($this->_request->getActionName() != 'index' && empty($this->user)) {
				setcookie(self::AUTH_COOKIE_NAME, '', null, '/');
				$this->error('Authentication failed. Please <a href="/private-car/">sign in</a>');
			}
		}
	}

	public function indexAction() {
		if ($this->user) {
			$this->_redirect('/private-car/orders');
			return;
		}
	}

	public function bookingAction() {
		$this->view->activeMenu = 'booking';
		$this->view->vehicleTypes = (new Model_VehicleType())->fetchPagedList(1, 999999)['rows'];
		if (!$this->_request->isPost()) {
			return;
		}
		$arp = new AjaxResponse();
		$arp->setStatus(AjaxResponse::STATUS_FAILED);
		$availableParams = array('forwho', 'city', 'contact-name', 'contact-email', 'contact-phone', 'passenger-names', 'passenger-phone', 'passenger-num', 'when', 'pickup-address', 'dropoff-address', 'vehicle', 'payment-method', 'notes');
		$via = $this->_getParam('via-address', array());
		$viaToSave = array();
		if (is_array($via)) {
			foreach ($via as $k=>$v) {
				if (!empty(trim($v))) {
					$viaToSave[] = trim($v);
				}
			}
		}
		$via = json_encode($viaToSave);
		$sn = Model_Order::generateSn();
		$order = new Model_Order();
		$order->set('sn', $sn);
		$order->set('user_id', $this->user->get('id'));
		foreach ($availableParams as $paramName) {
			$value = trim($this->getParam($paramName, ''));
			$colName = str_replace('-', '_', $paramName);
			$order->set($colName, $value);
		}
		$order->set('via', $via);
		$order->set('created_time', time());
		$order->save();
		//Mail it.
		$time = date('Y-m-d H:i', $order->get('created_time'));
		$mailBody = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>新订单</title>
</head>
<body>
<style>
dt {
	font-weight:bold;
	font-size:12px;
}
dd {
	font-size:14px;
}
</style>
<h2>新订单：{$order->get('sn')}</h2>
<dl>
<dt>下单时间</dt><dd>{$time}</dd>
<dt>下单人</dt><dd>{$order->get('contact_name')}</dd>
<dt>乘客</dt><dd>{$order->get('passenger_names')}</dd>
<dt>时间</dt><dd>{$order->get('when')}</dd>
<dt>城市</dt><dd>{$order->get('city')}</dd>
<dt>起始地</dt><dd>{$order->get('pickup_address')}</dd>
<dt>终点</dt><dd>{$order->get('dropoff_address')}</dd>
</dl>
<p><a href="http://www.carrentalsbeijing.com/private-car-management/order-detail?sn={$order->get('sn')}" target="_blank">查看订单详情</a></p>
✉
</body>
</html>
EOT;
		$mailSubject = "新订单 {$order->get('passenger_names')}从{$order->get('pickup_address')}到{$order->get('dropoff_address')}";
		$mailRecipients = Application::getConfig()['order_mail_recipients'];
		$mailQueue = new Model_MailQueue();
		$mailQueue->set('to', $mailRecipients);
		$mailQueue->set('subject', $mailSubject);
		$mailQueue->set('message', $mailBody);
		$mailQueue->set('created_time', time());
		$mailQueue->save();
		$arp->setStatus(AjaxResponse::STATUS_OK);
		$arp->setMessage($sn);
		$this->json($arp);
	}

	public function ordersAction() {
		$this->view->activeMenu = 'orders';
		$order = new Model_Order();
		$result = $order->fetchPagedList(1, 999999, array('user_id'=>$this->user->get('id')), 'created_time DESC');
		$this->view->orders = $orders = $result['rows'];
	}

	public function orderDetailAction() {
		$this->view->activeMenu = 'orders';
		$sn = trim($this->_getParam('sn'));
		if (empty($sn)) {
			$this->error('SN Does not exists.');
			return;
		}
		$order = new Model_Order($sn);
		if (!$order->exists() || $order->get('user_id') != $this->user->get('id')) {
			$this->error('Order does not exists.');
			return;
		}
		$this->view->order = $order;
		$ofd = new Model_OrderForDriver($sn);
		$driverAssigned = false;
		$vehicleAssigned = false;
		if ($ofd->exists() && !empty($ofd->get('driver_code'))) {
			$driverAssigned = new Model_Driver($ofd->get('driver_code'));
		}
		if ($ofd->exists() && !empty($ofd->get('vehicle_id'))) {
			$vehicleAssigned = new Model_Vehicle($ofd->get('vehicle_id'));
		}
		$this->view->driverAssigned = $driverAssigned;
		$this->view->vehicleAssigned = $vehicleAssigned;
		$this->view->vehicleTypes = (new Model_VehicleType())->fetchPagedList(1, 999999)['rows'];
		if (!$this->_request->isXmlHttpRequest()) {
			return;
		}
		// edit order
		$arp = new AjaxResponse();
		$arp->setStatus(AjaxResponse::STATUS_FAILED);
		$availableParams = array('forwho', 'city', 'contact-name', 'contact-email', 'contact-phone', 'passenger-names', 'passenger-phone', 'passenger-num', 'when', 'pickup-address', 'dropoff-address', 'vehicle', 'payment-method', 'notes');
		$via = $this->_getParam('via-address');
		$viaToSave = array();
		if (is_array($via)) {
			foreach ($via as $k=>$v) {
				if (!empty($v)) {
					$viaToSave[] = $v;
				}
			}
		}
		$via = json_encode($viaToSave);
		foreach ($availableParams as $paramName) {
			$value = trim($this->getParam($paramName, ''));
			$colName = str_replace('-', '_', $paramName);
			$order->set($colName, $value);
		}
		$order->set('via', $via);
		$order->save();
		$arp->setStatus(AjaxResponse::STATUS_OK);
		$arp->setMessage($sn);
		$this->json($arp);
	}

	public function signUpAction() {
		if (!$this->_request->isXmlHttpRequest()) {
			return;
		}
		$arp = new AjaxResponse();
		$arp->setStatus(AjaxResponse::STATUS_FAILED);
		$email = trim($this->_getParam('email'));
		$password = trim($this->_getParam('password'));
		if (empty($email) || empty($password)) {
			$this->json($arp);
			return;
		}
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$arp->addErrorItem('email', 'You entered an invalid E-mail address.');
			$this->json($arp);
			return;
		}
		$emailExists = Model_User::isEmailExists($email);
		if ($emailExists) {
			$arp->addErrorItem('email', 'E-mail address already exists.');
			$this->json($arp);
			return;
		}
		$hashedPassword = hash('sha256', $password);
		$user = new Model_User();
		$user->setEmail($email);
		$user->setPassword($hashedPassword);
		$user->setCreatedTime(time());
		$user->save();
		$this->setLoginCookie($email, $hashedPassword);
		$arp->setStatus(AjaxResponse::STATUS_OK);
		$this->json($arp);
	}

	public function signInAction() {
		if (!$this->_request->isXmlHttpRequest()) {
			return;
		}
		$arp = new AjaxResponse();
		$arp->setStatus(AjaxResponse::STATUS_FAILED);
		$email = trim($this->_getParam('email'));
		$password = trim($this->_getParam('password'));
		if (empty($email) || empty($password)) {
			$arp->setMessage('E-mail and password can not be empty.');
			$this->json($arp);
			return;
		}
		$hashedPassword = hash('sha256', $password);
		$user = new Model_User($email);
		if (!$user->exists() || $user->get('password') != $hashedPassword) {
			$arp->setMessage('Authentication failed, You entered an incorrect username, or password.');
			$this->json($arp);
			return;
		}
		$this->setLoginCookie($email, $hashedPassword);
		$arp->setStatus(AjaxResponse::STATUS_OK);
		$this->json($arp);
	}

	public function signOutAction() {
		setcookie(self::AUTH_COOKIE_NAME, '', null, '/');
		$this->_redirect('/private-car');
	}

	protected function setLoginCookie($email, $password) {
		$data = array('email'=>$email, 'password'=>$password);
		$auth = StringHelper::crypt($data);
		setcookie(self::AUTH_COOKIE_NAME, $auth, null, '/');
	}

}