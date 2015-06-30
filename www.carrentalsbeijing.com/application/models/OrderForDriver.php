<?php
class Model_OrderForDriver extends Model_Base {

	protected $tableName = 'orders_for_driver';

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

	public static function isSnExists($sn) {
		$db = Application::getDb();
		$res = $db->fetchOne("select count(*) from {$this->tableName} where sn=?", array($sn));
		return $res != 0;
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