<?php
class Model_Order extends Model_Base {

	protected $tableName = 'orders';

	const STATUS_NEW = 0;
	const STATUS_CS_CONFIRMED = 8;
	const STATUS_DRIVER_ASSIGNED = 12;
	const STATUS_DRIVER_CONFIRMED = 16;
	const STATUS_ON_SERVICE = 32;
	const STATUS_SERVICE_COMPLATE = 64;
	
	public function __construct($sn=0) {
		$db = Application::getDb();
		if (!empty($sn)) {
			$res = $db->fetchRow("select * from {$this->tableName} where sn=?", array($sn));
		} else {
			$res = '';
		}
		if (!empty($res)) {
			$this->data = $res;
		}
	}

	public static function generateSn() {
		return substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789ABCDEFGHJKLMNPQRSTUVWXYZ23456789ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8);
	}

	public static function isSnExists($sn) {
		$db = Application::getDb();
		$res = $db->fetchOne("select count(*) from {$this->tableName} where sn=?", array($sn));
		return $res != 0;
	}

	public static function getStatusDesc($status) {
		$statusDesc = array(
			self::STATUS_NEW => 'Waiting for customer service to confirm',
			self::STATUS_CS_CONFIRMED => 'Confirmed, waiting for assigning a vehicle',
			self::STATUS_DRIVER_ASSIGNED => 'Driver assigned, waiting for driver to confirm',
			self::STATUS_DRIVER_CONFIRMED => 'Confirmed by driver',
			self::STATUS_ON_SERVICE => 'Service in progress',
			self::STATUS_SERVICE_COMPLATE => 'Service completed',
		);
		return $statusDesc[$status];
	}

	/* ======= Getters and Setters ======= */
	public function getId() {
		return $this->get('id');
	}
	public function setId($value) {
		if (empty($value)) {
			$value = 0;
		}
		$this->set('id', $value);
	}
	public function getCreatedTime() {
		return $this->get('created_time');
	}
	public function setCreatedTime($value) {
		if (empty($value)) {
			$value = 0;
		}
		$this->set('created_time', $value);
	}
}