<?php
class PrivateCarDriverApiController extends ApiControllerAbstract {

	protected $code;

	public function init() {
		parent::init();
		$code = trim($this->_getParam('vehicleCode', ''));
		$verify = strtoupper(trim($this->_getParam('vehicleCodeVerify', '')));
		$driver = new Model_Driver($code);
		if (empty($code) || empty($verify) || !$driver->exists() || $driver->get('code_verify') != $verify) {
			$this->error('Verify Failed. Unauthorized access.');
		}
		$this->code = $code;
	}

	public function indexAction() {
	}

	public function updateDeviceIdAction() {
		$deviceId = trim($this->_getParam('deviceId', ''));
		$driver = new Model_Driver($this->code);
		$driver->set('device_id', $deviceId);
		$driver->save();
		$this->sendResult();
	}

	public function updateCoordinatesAction() {
		$lng = trim($this->_getParam('lng', ''));
		$lat = trim($this->_getParam('lat', ''));
		$coordinates = "{$lng},{$lat}";
		$now = time();
		$driver = new Model_Driver($this->code);
		$driver->set('coordinates', $coordinates);
		$driver->set('locate_time', $now);
		$driver->save();
		$this->sendResult();
	}

	public function ordersAction() {
		$db = Application::getDb();
		$orders = $db->fetchAll('SELECT o.*, od.status, v.lpn, v.model, v.color, v.seats FROM orders_for_driver o LEFT JOIN vehicles v ON v.id=o.vehicle_id LEFT JOIN orders od ON od.sn=o.sn WHERE o.driver_code=? ORDER BY o.when DESC', array($this->code));
		foreach ($orders as &$order) {
			$order['via'] = json_decode($order['via']);
			$order['via_coordinates'] = json_decode($order['via_coordinates']);
		}
		unset($order);
		//$ofd = new Model_OrderForDriver();
		//$orders = $ofd->fetchPagedList(1, 999999, array('driver_code'=>$this->code), '`when` desc')['rows'];
		$this->sendResult($orders);
	}

	public function setOrderConfirmAction() {
		$sn = strtoupper(trim($this->_getParam('sn')));
		$result = 0;
		$ofd = new Model_OrderForDriver($sn);
		$order = new Model_Order($sn);
		if ($ofd->exists() && $ofd->get('driver_code')==$this->code && $order->get('status')<Model_Order::STATUS_DRIVER_CONFIRMED) {
			$order->set('status', Model_Order::STATUS_DRIVER_CONFIRMED);
			$result = $order->save();
		}
		$this->sendResult($result);
	}

	public function setOrderOnServiceAction() {
		$sn = strtoupper(trim($this->_getParam('sn')));
		$result = 0;
		$ofd = new Model_OrderForDriver($sn);
		$order = new Model_Order($sn);
		if ($ofd->exists() && $ofd->get('driver_code')==$this->code && $order->get('status')<Model_Order::STATUS_ON_SERVICE) {
			$order->set('status', Model_Order::STATUS_ON_SERVICE);
			$result = $order->save();
		}
		$this->sendResult($result);
	}

	public function setOrderServiceCompleteAction() {
		$sn = strtoupper(trim($this->_getParam('sn')));
		$result = 0;
		$ofd = new Model_OrderForDriver($sn);
		$order = new Model_Order($sn);
		if ($ofd->exists() && $ofd->get('driver_code')==$this->code && $order->get('status')<Model_Order::STATUS_SERVICE_COMPLATE) {
			$order->set('status', Model_Order::STATUS_SERVICE_COMPLATE);
			$result = $order->save();
		}
		$this->sendResult($result);
	}

	public function commonPhrasesAction() {
		parent::commonPhrasesAction();
	}

	/***
	params: sn, lastId
	***/
	public function orderConversationsAction() {
		$sn = strtoupper(trim($this->_getParam('sn')));
		$lastId = intval(trim($this->_getParam('lastId')));
		$ofd = new Model_OrderForDriver($sn);
		if (!$ofd->exists() || $ofd->get('driver_code')!=$this->code) {
			$this->sendResult(array());
			return;
		}
		$this->sendResult(parent::orderConversations($sn, $lastId));
	}

	/***
	params: sn, message, commonPhraseId, imageUrl
	***/
	public function addOrderConversationAction() {
		parent::addOrderConversation(Model_OrderConversation::SENDER_DRIVER);
		$this->sendResult();
	}

	public function passengerCoordinatesAction() {
		$sn = strtoupper(trim($this->_getParam('sn')));
		$ofd = new Model_OrderForDriver($sn);
		$order = new Model_Order($sn);
		if (!$ofd->exists() || $ofd->get('driver_code')!=$this->code) {
			$this->sendResult(array());
			return;
		}
		$result = [];
		$result['coordinates'] = $order->get('passenger_coordinates');
		$result['time'] = $order->get('passenger_locate_time');
		$this->sendResult($result);
	}
}