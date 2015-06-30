<?php
class Model_CommonPhrase extends Model_Base {

	protected $tableName = 'common_phrases';

	public function __construct($id=0) {
		$db = Application::getDb();
		if (!empty($id)) {
			$res = $db->fetchRow("select * from {$this->tableName} where id=?", array($id));
		} else {
			$res = '';
		}
		if (!empty($res)) {
			$this->data = $res;
		}
	}

	public function delete() {
		$db = Application::getDb();
		if ($this->get('id')) {
			return $db->query("DELETE FROM {$this->tableName} WHERE id=?", array($this->get('id')));
		} else {
			return false;
		}
	}
}