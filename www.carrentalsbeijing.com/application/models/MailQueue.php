<?php
class Model_MailQueue extends Model_Base {

	protected $tableName = 'mail_queue';
	const TABLE_NAME = 'mail_queue';

	public function __construct($code=0) {
		$db = Application::getDb();
		if (!empty($code)) {
			$res = $db->fetchRow("select * from {$this->tableName} where id=?", array($code));
		} else {
			$res = '';
		}
		if (!empty($res)) {
			$this->data = $res;
		}
	}

	public static function getUnprocessedItem() {
		$db = Application::getDb();
		return $db->fetchAll('SELECT * FROM ' . self::TABLE_NAME . ' WHERE processed=0 ORDER BY id');
	}

	public static function setItemAsProcessedBatch(array $ids) {
		if (empty($ids)) {
			return;
		}
		$db = Application::getDb();
		$paramValue = implode(',', $ids);
		$time = time();
		return $db->query('UPDATE ' . self::TABLE_NAME . " SET processed=1, processed_time={$time} WHERE id IN ({$paramValue})");
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