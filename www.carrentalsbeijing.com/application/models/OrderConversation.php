<?php
class Model_OrderConversation extends Model_Base {

	const SENDER_DRIVER = 'driver';
	const SENDER_PASSENGER = 'passenger';
	const SENDER_CS = 'cs';

	protected $tableName = 'order_conversations';

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
}