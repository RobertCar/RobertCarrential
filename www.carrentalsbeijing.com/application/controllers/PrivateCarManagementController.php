<?php
class PrivateCarManagementController extends ControllerAbstract {

	const AUTH_COOKIE_NAME = 'mgmtauthtoken';

	protected $admins = array(
			'cs' => '123',
		);

	public function init() {
		parent::init();
		$loggedIn = false;
		if (!in_array($this->_request->getActionName(), array('index', 'logout'))) {
			$authString = $this->_getParam(self::AUTH_COOKIE_NAME, '');
			$data = StringHelper::decrypt($authString);
			if (is_array($data)) {
				$name = $data['name'];
				$password = $data['password'];
				if (array_key_exists($name, $this->admins) && $password == $this->admins[$name]) {
					$loggedIn = true;
				}
			}
			if (!$loggedIn) {
				setcookie(self::AUTH_COOKIE_NAME, '', null, '/');
				$this->error('Authentication failed. <a href="/private-car-management/">Login</a>');
			}
		}
	}

	public function indexAction() {
		if (!$this->_request->isPost()) {
			return;
		}
		$name = $this->_getParam('name', '');
		$password = $this->_getParam('password', '');
		if (array_key_exists($name, $this->admins) && $password == $this->admins[$name]) {
			$this->setLoginCookie($name, $password);
			$this->_redirect('/private-car-management/orders');
		} else {
			setcookie(self::AUTH_COOKIE_NAME, '', null, '/');
			$this->error('Authentication failed.');
		}
	}

	public function ordersAction() {
		$this->view->activeMenu = 'orders';
		$order = new Model_Order();
		$result = $order->fetchPagedList(1, 999999, array(), 'created_time DESC');
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
		$this->view->order = $order;
		$ofd = $this->view->orderForDriver = new Model_OrderForDriver($sn);
		$vehicle = new Model_Vehicle();
		$vehicleList = $vehicle->fetchPagedList(1, 999999);
		$this->view->vehicles = $vehicleList['rows'];
		$this->view->driver = $driver = new Model_Driver($ofd->get('driver_code'));
		$driverList = $driver->fetchPagedList(1, 999999);
		$this->view->drivers = $driverList['rows'];
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
				if (!empty(trim($v))) {
					$viaToSave[] = trim($v);
				}
			}
		}
		$via = json_encode($viaToSave);
		foreach ($availableParams as $paramName) {
			$value = trim($this->_getParam($paramName, ''));
			$colName = str_replace('-', '_', $paramName);
			$order->set($colName, $value);
		}
		$order->set('via', $via);
		$order->save();
		$arp->setStatus(AjaxResponse::STATUS_OK);
		$arp->setMessage($sn);
		$this->json($arp);
	}

	public function editOrderForDriverAction() {
		$sn = $this->_getParam('sn');
		if (empty($sn)) {
			$this->error('nothing to edit');
			return;
		}
		$order = new Model_Order($sn);
		$ofd = new Model_OrderForDriver($sn);
		$ofd->set('sn', $sn);
		$availableParams = array('driver-code', 'vehicle-id', 'contact-name', 'contact-phone', 'pickup-address', 'pickup-coordinates', 'dropoff-address', 'dropoff-coordinates', 'payment-method', 'notes');
		foreach ($availableParams as $paramName) {
			$value = trim($this->_getParam($paramName, ''));
			$colName = str_replace('-', '_', $paramName);
			$ofd->set($colName, $value);
		}
		$when = strtotime(trim($this->_getParam('when', '')));
		$ofd->set('when', $when);
		$via = trim($this->_getParam('via', ''));
		$viaArray = explode("\n", $via);
		foreach ($viaArray as $k=>$v) {
			$viaArray[$k] = trim($v);
			if (empty($viaArray[$k])) {
				unset($viaArray[$k]);
			}
		}
		$ofd->set('via', json_encode($viaArray));
		$viaCoordinates = trim($this->_getParam('via-coordinates', ''));
		$viaCoordinatesArray = explode("\n", $viaCoordinates);
		foreach ($viaCoordinatesArray as $k=>$v) {
			$viaCoordinatesArray[$k] = trim($v);
			if (empty($viaCoordinatesArray[$k])) {
				unset($viaCoordinatesArray[$k]);
			}
		}
		$ofd->set('via_coordinates', json_encode($viaCoordinatesArray));
		$ofd->save();
		if ($this->_getParam('driver-code', '') && $order->get('status')<Model_Order::STATUS_DRIVER_ASSIGNED) {
			$order->set('status', Model_Order::STATUS_DRIVER_ASSIGNED);
			$order->save();
		}
		$this->_redirect('order-detail?sn=' . $sn);
	}

	public function vehiclesAction() {
		$this->view->activeMenu = 'vehicles';
		$vehicle = new Model_Vehicle();
		$result = $vehicle->fetchPagedList(1, 999999);
		$this->view->vehicles = $vehicles = $result['rows'];
	}

	public function editVehicleAction() {
		$id = $this->_getParam('id', '');
		$vehicle = new Model_Vehicle($id);
		$availableParams = array('lpn', 'model', 'color', 'seats');
		foreach ($availableParams as $paramName) {
			$value = trim($this->_getParam($paramName, ''));
			$colName = str_replace('-', '_', $paramName);
			$vehicle->set($colName, $value);
		}
		$vehicle->save();
		$this->_redirect('vehicles');
	}

	public function deleteVehicleAction() {
		$id = $this->_getParam('id', '');
		$vehicle = new Model_Vehicle($id);
		$vehicle->delete();
		$this->_redirect('vehicles');
	}

	public function driversAction() {
		$this->view->activeMenu = 'drivers';
		$driver = new Model_Driver();
		$result = $driver->fetchPagedList(1, 999999);
		$this->view->drivers = $drivers = $result['rows'];
	}

	public function editDriverAction() {
		$code = $this->_getParam('code', '');
		$driver = new Model_Driver($code);
		$availableParams = array('name', 'phone');
		foreach ($availableParams as $paramName) {
			$value = trim($this->_getParam($paramName, ''));
			$colName = str_replace('-', '_', $paramName);
			$driver->set($colName, $value);
		}
		if (empty($code)) {
			$code = substr(str_shuffle('12345678901234567890123456789012345678901234567890'), 0, 8);
			$verify = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZABCDEFGHJKLMNPQRSTUVWXYZABCDEFGHJKLMNPQRSTUVWXYZABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 4);
			$driver->set('code', $code);
			$driver->set('code_verify', $verify);
		}
		$driver->save();
		$this->_redirect('drivers');
	}

	public function deleteDriverAction() {
		$code = $this->_getParam('code', '');
		$driver = new Model_Driver($code);
		$driver->delete();
		$this->_redirect('drivers');
	}

	public function vehicleTypesAction() {
		$this->view->activeMenu = 'vehicle-types';
		$vehicleType = new Model_VehicleType();
		$result = $vehicleType->fetchPagedList(1, 999999);
		$this->view->vehicleTypes = $vehicleTypes = $result['rows'];
	}

	public function editVehicleTypeAction() {
		$id = $this->_getParam('id', '');
		$vehicleType = new Model_VehicleType($id);
		$availableParams = array('name');
		foreach ($availableParams as $paramName) {
			$value = trim($this->getParam($paramName, ''));
			$colName = str_replace('-', '_', $paramName);
			$vehicleType->set($colName, $value);
		}
		$vehicleType->save();
		$this->_redirect('vehicle-types');
	}

	public function deleteVehicleTypeAction() {
		$id = $this->_getParam('id', '');
		$vehicleType = new Model_VehicleType($id);
		$vehicleType->delete();
		$this->_redirect('vehicle-types');
	}

	public function commonPhrasesAction() {
		$modelPhrase = new Model_CommonPhrase();
		$this->view->phrases = $modelPhrase->fetchPagedList(1, 99999)['rows'];
	}

	public function addCommonPhraseAction() {
		$zh = trim($this->_getParam('zh'));
		$en = trim($this->_getParam('en'));
		$modelPhrase = new Model_CommonPhrase();
		$modelPhrase->set('zh', $zh);
		$modelPhrase->set('en', $en);
		$modelPhrase->save();
		$this->_redirect('common-phrases');
	}

	public function deleteCommonPhraseAction() {
		$id = intval($this->_getParam('id', ''));
		$modelPhrase = new Model_CommonPhrase($id);
		$modelPhrase->delete();
		$this->_redirect('common-phrases');
	}

	public function orderConversationAction() {
		$this->view->sn = $sn = trim($this->_getParam('sn', ''));
		$lastId = intval(trim($this->_getParam('lastId', 0)));
		$getConversation = $this->_getParam('getConversation');
		if (empty($getConversation)) {
			return;
		}
		$oc = new Model_OrderConversation();
		$lastId = intval($lastId);
		$result = $oc->fetchPagedList(1, 999999, array(Db::RAW_WHERE_KEY=>'id>'.$lastId, 'sn'=>$sn), 'id ASC')['rows'];
		$arp = new AjaxResponse();
		$arp->setStatus(AjaxResponse::STATUS_OK);
		$arp->setMessage($result);
		$this->json($arp);
	}

	public function addOrderConversationAction() {
		$sn = trim($this->_getParam('sn'));
		$message = trim($this->_getParam('message'));
		$imageUrl = $this->_getParam('imageUrl');
		$oc = new Model_OrderConversation();
		$oc->set('sn', $sn);
		$oc->set('sender', Model_OrderConversation::SENDER_CS);
		$oc->set('message', $message);
		$oc->set('image_url', $imageUrl);
		$oc->set('created_time', time());
		$oc->save();
		$arp = new AjaxResponse();
		$arp->setStatus(AjaxResponse::STATUS_OK);
		$this->json($arp);
		$c = new ApiControllerAbstract();
		$c->sendPush(Model_OrderConversation::SENDER_CS, $sn, $message);
	}

	public function logoutAction() {
		setcookie(self::AUTH_COOKIE_NAME, '', null, '/');
		$this->_redirect('/private-car-management/');
	}

	protected function setLoginCookie($email, $password) {
		$data = array('name'=>$email, 'password'=>$password);
		$auth = StringHelper::crypt($data);
		setcookie(self::AUTH_COOKIE_NAME, $auth, null, '/');
	}

}