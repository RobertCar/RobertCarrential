<?php
class Model_User extends Model_Base {
	
	const ROLE_ADMIN = 'admin';
	const ROLE_CUSTOMER_SERVICE = 'cs';
	const ROLE_ENDUSER = 'eu';

	protected $tableName = 'users';
	
	public function __construct($id=0) {
		$db = Application::getDb();
		if (!filter_var($id, FILTER_VALIDATE_EMAIL)) {
			$res = $db->fetchRow("select * from {$this->tableName} where id=?", array($id));
		} elseif (!empty($id)) {
			$res = $db->fetchRow("select * from {$this->tableName} where email=?", array($id));
		} else {
			$res = '';
		}
		if (!empty($res)) {
			$this->data = $res;
		}
	}

	public static function isEmailExists($email) {
		$db = Application::getDb();
		$res = $db->fetchOne("select count(*) from users where email=?", array($email));
		return $res != 0;
	}
	
	public static function getRoles() {
		return array(
			self::ROLE_ADMIN => '管理员',
			self::ROLE_CUSTOMER_SERVICE => '客服',
			self::ROLE_ENDUSER => '普通用户'
		);
	}
	
	public static function getRoleDisplayName($role) {
		$roles = self::getRoles();
		return isset($roles[$role]) ? $roles[$role] : '未知角色';
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
	public function getPassword() {
		return $this->get('password');
	}
	public function setPassword($value) {
		$this->set('password', $value);
	}
	public function getEmail() {
		return $this->get('email');
	}
	public function setEmail($value) {
		$this->set('email', $value);
	}
	public function getStatus() {
		return $this->get('status');
	}
	public function setStatus($value) {
		if (empty($value)) {
			$value = 0;
		}
		$this->set('status', $value);
	}
	public function getLastLoginIp() {
		return $this->get('last_login_ip');
	}
	public function setLastLoginIp($value) {
		$this->set('last_login_ip', $value);
	}
	public function getLastLoginTime() {
		return $this->get('last_login_time');
	}
	public function setLastLoginTime($value) {
		if (empty($value)) {
			$value = 0;
		}
		$this->set('last_login_time', $value);
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