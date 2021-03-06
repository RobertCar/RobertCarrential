<?php
class Model_Driver extends Model_Base {

	protected $tableName = 'drivers';

	public function __construct($code=0) {
		$db = Application::getDb();
		if (!empty($code)) {
			$res = $db->fetchRow("select * from {$this->tableName} where code=?", array($code));
		} else {
			$res = '';
		}
		if (!empty($res)) {
			$this->data = $res;
		}
	}

	public static function isCodeExists($code) {
		$db = Application::getDb();
		$res = $db->fetchOne("select count(*) from {$this->tableName} where code=?", array($code));
		return $res != 0;
	}

	public function delete() {
		$db = Application::getDb();
		if ($this->get('id')) {
			return $db->query("DELETE FROM {$this->tableName} WHERE id=?", array($this->get('id')));
		} else {
			return false;
		}
	}

	/* ======= Getters and Setters ======= */
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