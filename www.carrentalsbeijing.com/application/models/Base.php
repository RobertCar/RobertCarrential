<?php
class Model_Base {
	
	const OK = 1;
	
	const ERROR_MISSING_PARAMS = -1;
	const ERROR_INVALID_PARAMS = -2;
	const ERROR_EXISTS = -3;
	const ERROR_SAVING = -4;
	const ERROR_UNKNOWN = -65536;
	
	protected $primaryKey = array('id');
	protected $tableName = '';
	
	protected $changedData = array();
	protected $data = array();

	protected $fresh = false;
	
	public function exists() {
		foreach ($this->primaryKey as $field) {
			if (!isset($this->data[$field]) || empty($this->data[$field])) {
				return false;
			}
		}
		return true;
	}
	
	public function get($key) {
		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		}
		return null;
	}
	
	public function set($key, $value) {
		if (!isset($this->data[$key]) || $this->data[$key] != $value) {
			$this->changedData[$key] = $value;
		}
		$this->data[$key] = $value;
	}
	
	public function getRawData() {
		return $this->data;
	}

	public function getChangedData() {
		return $this->changedData;
	}
	
	public function save() {
		if (empty($this->data)) {
			return false;
		}
		$db = Application::getDb();
		$data = array();
		if (!$this->exists()) {
			foreach ($this->data as $key=>$value) {
				if ($value === null) {
					$value = '';
				}
				$data[$key] = $value;
			}
			$ret = $db->insert($this->tableName, $data);
			if (array_search('id', $this->primaryKey)!==false) {
				$this->set('id', $db->lastInsertId());
			}
			$this->fresh = true;
			return $ret;
		}
		if (empty($this->changedData)) {
			return true;
		}
		foreach ($this->changedData as $key=>$value) {
			if ($value === null) {
				$value = '';
			}
			$data[$key] = $value;
		}
		$this->changedData = array();
		$where = array();
		foreach ($this->primaryKey as $field) {
			$where[] = "`{$field}`=" . Db::quote($this->data[$field]);
		}
		$where = implode(' AND ', $where);
		return $db->update($this->tableName, $data, $where);
	}

	public function fetchPagedList($page=1, $itemsPerPage=0, array $where=array(), $orderBy='', array $fields=array(), $sqlNoCache=false) {
		$tableName = Db::quoteIdentifier($this->tableName);
		if (empty($fields)) {
			$field = '*';
		} else {
			$cols = array();
			foreach ($fields as $name) {
				$cols[] = Db::quoteIdentifier($name);
			}
			$field = implode(',', $cols);
		}
		if (empty($where)) {
			$where = '';
		} else {
			$where = 'WHERE ' . Db::buildWhere($where);
		}
		if (!empty($orderBy)) {
			$orderBy = 'ORDER BY ' . $orderBy;
		}
		if ($itemsPerPage > 0) {
			$page = intval($page);
			$page < 1 && $page = 1;
			$offset = ($page-1) * $itemsPerPage;
			$limit = "LIMIT {$offset},{$itemsPerPage}";
		} else {
			$limit = '';
		}
		if ($sqlNoCache) {
			$sqlNoCache = 'SQL_NO_CACHE';
		} else {
			$sqlNoCache = '';
		}
		$countSql = "SELECT {$sqlNoCache} count(*) FROM {$tableName} {$where}";
		$sql = "SELECT {$sqlNoCache} {$field} FROM {$tableName} {$where} {$orderBy} {$limit}";
		$db = Application::getDb();
		$total = $db->fetchOne($countSql);
		$rows = $db->fetchAll($sql);
		return array('total'=>$total, 'rows'=>$rows);
	}
}