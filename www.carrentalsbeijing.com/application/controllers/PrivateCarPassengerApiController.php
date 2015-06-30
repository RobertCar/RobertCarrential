<?php
class PrivateCarPassengerApiController extends ApiControllerAbstract {

	protected $sn;
	protected $order;

	public function init() {
		parent::init();
		$sn = strtoupper(trim($this->_getParam('sn', '')));
		$order = new Model_Order($sn);
		if (empty($sn) || !$order->exists()) {
			$this->error('Order Not Found.');
		}
		$this->sn = $sn;
		$this->order = $order;
	}

	public function indexAction() {
	}

	public function updateDeviceIdAction() {
		$deviceId = trim($this->_getParam('deviceId', ''));
		$this->order->set('passenger_device_id', $deviceId);
		$this->order->save();
		$this->sendResult();
	}

	public function updateCoordinatesAction() {
		$lng = trim($this->_getParam('lng', ''));
		$lat = trim($this->_getParam('lat', ''));
		$coordinates = "{$lng},{$lat}";
		$now = time();
		$this->order->set('passenger_coordinates', $coordinates);
		$this->order->set('passenger_locate_time', $now);
		$this->order->save();
		$this->sendResult();
	}

	public function orderDetailAction() {
		$db = Application::getDb();
		$order = $db->fetchRow('SELECT o.*, od.vehicle_id, od.driver_code, od.pickup_coordinates, od.dropoff_coordinates, od.via_coordinates, od.when, v.lpn, v.model, v.color, v.seats FROM orders o LEFT JOIN orders_for_driver od ON od.sn=o.sn LEFT JOIN vehicles v ON v.id=od.vehicle_id WHERE o.sn=?', array($this->sn));
		$order['via'] = json_decode($order['via']);
		$order['via_coordinates'] = json_decode($order['via_coordinates']);
		$driver = new Model_Driver($order['driver_code']);
		$order['driver_name'] = $driver->get('name');
		$order['driver_phone'] = $driver->get('phone');
		//$ofd = new Model_OrderForDriver();
		//$orders = $ofd->fetchPagedList(1, 999999, array('driver_code'=>$this->code), '`when` desc')['rows'];
		$this->sendResult($order);
	}

	public function commonPhrasesAction() {
		parent::commonPhrasesAction();
	}

	/***
	params: sn, lastId
	***/
	public function orderConversationsAction() {
		$lastId = intval(trim($this->_getParam('lastId')));
		$this->sendResult(parent::orderConversations($this->sn, $lastId));
	}

	/***
	params: sn, message, commonPhraseId, imageUrl
	***/
	public function addOrderConversationAction() {
		parent::addOrderConversation(Model_OrderConversation::SENDER_PASSENGER);
		$this->sendResult();
	}

	public function driverCoordinatesAction() {
		$ofd = new Model_OrderForDriver($this->sn);
		if (!$ofd->exists() || empty($ofd->get('driver_code'))) {
			$this->sendResult(array());
			return;
		}
		$driver = new Model_Driver($ofd->get('driver_code'));
		if (!$driver->exists()) {
			$this->sendResult(array());
			return;
		}
		$result = [];
		$result['coordinates'] = $driver->get('coordinates');
		$result['time'] = $driver->get('locate_time');
		$this->sendResult($result);
	}
}