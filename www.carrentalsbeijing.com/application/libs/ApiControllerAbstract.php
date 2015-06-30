<?php
class ApiControllerAbstract extends ControllerAbstract {

	public function init() {
		parent::init();
		$this->setNoRender();
	}

	protected function commonPhrasesAction() {
		$mcp = new Model_CommonPhrase();
		$rows = $mcp->fetchPagedList(1, 999999)['rows'];
		$this->sendResult($rows);
	}

	protected function orderConversations($sn, $lastId) {
		$oc = new Model_OrderConversation();
		$lastId = intval($lastId);
		return $oc->fetchPagedList(1, 999999, array(Db::RAW_WHERE_KEY=>'id>'.$lastId, 'sn'=>$sn), 'id ASC')['rows'];
	}

	protected function addOrderConversation($sender) {
		$sn = trim($this->_getParam('sn'));
		$message = trim($this->_getParam('message'));
		$commonPhraseId = intval(trim($this->_getParam('commonPhraseId')));
		$imageUrl = $this->_getParam('imageUrl');
		if ($commonPhraseId) {
			$mcp = new Model_CommonPhrase($commonPhraseId);
			$zh = $mcp->get('zh');
			$en = $mcp->get('en');
			if ($sender == Model_OrderConversation::SENDER_DRIVER) {
				$message = "{$zh} ({$en})";
			} else {
				$message = "{$en} ({$zh})";
			}
		}
		$oc = new Model_OrderConversation();
		$oc->set('sn', $sn);
		$oc->set('sender', $sender);
		$oc->set('message', $message);
		$oc->set('image_url', $imageUrl);
		$oc->set('created_time', time());
		$oc->save();

		$this->sendPush($sender, $sn, $message);
	}

	public function sendPush($sender, $sn, $message) {
		return;
		//发送推送通知
		include APPLICATION_PATH . 'libs/ApnsPHP/Log/Interface.php';
		include APPLICATION_PATH . 'libs/ApnsPHP/Log/Embedded.php';
		include APPLICATION_PATH . 'libs/ApnsPHP/Log/Null.php';
		include APPLICATION_PATH . 'libs/ApnsPHP/Abstract.php';
		include APPLICATION_PATH . 'libs/ApnsPHP/Exception.php';
		include APPLICATION_PATH . 'libs/ApnsPHP/Feedback.php';
		include APPLICATION_PATH . 'libs/ApnsPHP/Message.php';
		include APPLICATION_PATH . 'libs/ApnsPHP/Push.php';
		include APPLICATION_PATH . 'libs/ApnsPHP/Message/Custom.php';
		include APPLICATION_PATH . 'libs/ApnsPHP/Message/Exception.php';
		include APPLICATION_PATH . 'libs/ApnsPHP/Push/Server.php';
		include APPLICATION_PATH . 'libs/ApnsPHP/Push/Exception.php';
		include APPLICATION_PATH . 'libs/ApnsPHP/Push/Server/Exception.php';

		$order = new Model_Order($sn);
		$passengerDeviceId = $order->get('passenger_device_id');
		$ofd = new Model_OrderForDriver($sn);
		$driver = new Model_Driver($ofd->get('driver_code'));
		$driverDeviceId = $driver->get('device_id');
		//$certSuffix = APPLICATION_ENV == 'development' ? '-dev.pem' : '.pem';
		$certSuffix = '-dev.pem';
		$passengerCert = APPLICATION_PATH . '/data/passenger' . $certSuffix;
		$driverCert = APPLICATION_PATH . '/data/driver' . $certSuffix;
		if ($sender == Model_OrderConversation::SENDER_DRIVER) {
			$this->sendPushToDevice($passengerCert, $passengerDeviceId, $message, $sn);
		} elseif ($sender == Model_OrderConversation::SENDER_PASSENGER) {
			$this->sendPushToDevice($driverCert, $driverDeviceId, $message, $sn);
		} else {
			$this->sendPushToDevice($passengerCert, $passengerDeviceId, $message, $sn);
			$this->sendPushToDevice($driverCert, $driverDeviceId, $message, $sn);
		}
	}

	protected function sendPushToDevice($cert, $deviceId, $message, $sn='') {
		if (strlen($deviceId)<64) {
			return;
		}
		$env = APPLICATION_ENV == 'development' ? ApnsPHP_Abstract::ENVIRONMENT_SANDBOX : ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION;
		$env = ApnsPHP_Abstract::ENVIRONMENT_SANDBOX;
		$push = new ApnsPHP_Push($env, $cert);
		$push->setLogger(new ApnsPHP_Log_Null());
		$push->setConnectTimeout(4000);
		$push->setRootCertificationAuthority(APPLICATION_PATH . '/data/entrust_root_certification_authority.pem');
		$push->connect();
		$apnsMessage = new ApnsPHP_Message($deviceId);
		$apnsMessage->setBadge(1);
		$apnsMessage->setText($message);
		$apnsMessage->setSound();
		$apnsMessage->setCustomProperty('sn', $sn);
		$push->add($apnsMessage);
		$push->send();
		//$push->disconnect();
		$aErrorQueue = $push->getErrors();
		if (!empty($aErrorQueue)) {
			var_dump($aErrorQueue);
		}
	}

	protected function error($msg) {
		$this->setNoRender();
		$this->_request->setDispatched(true);
		$arp = new AjaxResponse();
		$arp->setStatus(AjaxResponse::STATUS_FAILED);
		$arp->setMessage($msg);
		$this->json($arp);
	}

	protected function sendResult($result='') {
		$arp = new AjaxResponse();
		$arp->setStatus(AjaxResponse::STATUS_OK);
		$arp->setMessage($result);
		$this->json($arp);
	}
}