<?php
/* zhiyi, since about 2012/03/15 20:00 */

class Db {

	const FETCH_ASSOC = 1;
	const FETCH_NUM = 2;
	const FETCH_BOTH = 3;

	const RAW_WHERE_KEY = '___rawwherekey___';
	
	protected static $numQueries = 0;

	private $config = array('host'=>'localhost', 'username'=>null, 'password'=>null, 'dbname'=>null, 'port'=>3306, 'socket'=>null, 'charset'=>'utf8');
	private $isConnected = false;
	private $fetchMode = self::FETCH_ASSOC;
	private $inTransaction = false;
	private $autoCommit;
	private $connection = null;
	private $lastSql = null;

	public function __construct(array $config) {
		foreach ($this->config as $key=>$val) {
			if (isset($config[$key])) {
				$this->config[$key] = $config[$key];
			}
		}
	}
	
	public function connect() {
		if ($this->isConnected) {
			return true;
		}
		$this->connection = new mysqli($this->config['host'], $this->config['username'], $this->config['password'], $this->config['dbname'], $this->config['port'], $this->config['socket']);
		if ($this->connection->connect_error) {
			throw new DbException('Error connecting database: ' . $this->connection->connect_error);
		}
		$this->isConnected = true;
		$this->setAutoCommit(true);
		if (!empty($this->config['charset'])) {
			$this->setCharset($this->config['charset']);
		}
		return true;
	}
	
	public function disconnect() {
		if ($this->isConnected) {
			$this->connection->close();
		}
		return true;
	}
	
	public function isConnected() {
		return $this->isConnected;
	}
	
	public function getConnection() {
		$this->connect();
		return $this->connection;
	}
	
	public function setCharset($charset='utf8') {
		$ret = $this->getConnection()->set_charset($charset);
		$this->checkError();
		return $ret;
	}
	
	public function setAutoCommit($mode=true) {
		$this->autoCommit = $mode;
		$ret = $this->getConnection()->autocommit($mode);
		$this->checkError();
		return $ret;
	}
	
	public function beginTransaction() {
		if ($this->inTransaction) {
			return true;
		}
		$ret = $this->getConnection()->autocommit(true);
		$this->checkError();
		if ($ret) {
			$this->inTransaction = true;
		}
		return $ret;
	}
	
	public function commit() {
		$ret = $this->getConnection()->commit();
		$this->getConnection()->autocommit($this->autoCommit);
		$this->checkError();
		$this->inTransaction = !$this->autoCommit;
		return $ret;
	}
	
	public function rollBack() {
		$ret = $this->getConnection()->rollback();
		$this->getConnection()->autocommit($this->autoCommit);
		$this->checkError();
		$this->inTransaction = !$this->autoCommit;
		return $ret;
	}
	
	public function insert($table, array $data) {
		$fields = array();
		$values = array();
		foreach ($data as $field=>$val) {
			$fields[] = self::quoteIdentifier($field);
			$values[] = self::quote($val);
		}
		$field = implode(',', $fields);
		$value = implode(',', $values);
		$table = self::quoteIdentifier($table);
		$sql = "INSERT INTO {$table} ({$field}) VALUES({$value})";
		$ret = $this->getConnection()->query($sql);
		$this->lastSql = $sql;
		++self::$numQueries;
		$this->checkError();
		return $ret;
	}
	
	public function update($table, array $data, $where) {
		$up = array();
		foreach ($data as $field=>$val) {
			$up[] = self::quoteIdentifier($field) . '=' . self::quote($val);
		}
		$update = implode(',', $up);
		$where = self::buildWhere($where);
		$table = self::quoteIdentifier($table);
		$sql = "UPDATE {$table} SET {$update} WHERE {$where}";
		$ret = $this->getConnection()->query($sql);
		$this->lastSql = $sql;
		++self::$numQueries;
		$this->checkError();
		return $ret;
	}
	
	public function delete($table, $where) {
		$where = self::buildWhere($where);
		$table = self::quoteIdentifier($table);
		$sql = "DELETE FROM {$table} WHERE {$where}";
		$ret = $this->getConnection()->query($sql);
		$this->lastSql = $sql;
		++self::$numQueries;
		$this->checkError();
		return $ret;
	}
	
	public function fetchAll($sql, $bind=array(), $fetchMode=null) {
		switch ($fetchMode) {
			case self::FETCH_NUM:
				$mode = MYSQLI_NUM;
				break;
			case self::FETCH_BOTH:
				$mode = MYSQLI_BOTH;
				break;
			case self::FETCH_ASSOC:
				$mode = MYSQLI_ASSOC;
				break;
			default:
				$mode = $this->fetchMode;
				break;
		}
		$result = $this->query($sql, $bind);
		/* SAE 不知道什么时候改的不再是 mysqlnd，只好改成自己循环一遍 */
		// $ret = $results->fetch_all($mode);
		$ret = array();
		while ($row = $result->fetch_assoc()) {
			$ret[] = $row;
		}
		$result->free();
		return $ret;
	}
	
	public function fetchCol($sql, $bind=array(), $col=0) {
		$results = $this->query($sql, $bind);
		$cols = array();
		while ($row = $results->fetch_row()) {
			$cols[] = $row[$col];
		}
		$results->free();
		return $cols;
	}
	
	public function fetchOne($sql, $bind=array()) {
		$results = $this->query($sql, $bind);
		$row = $results->fetch_row();
		if (!empty($row) && isset($row[0])) {
			$ret = $row[0];
		} else {
			$ret = null;
		}
		$results->free();
		return $ret;
	}
	
	public function fetchPair($sql, $bind=array()) {
		$results = $this->query($sql, $bind);
		$pairs = array();
		while ($row = $results->fetch_row()) {
			$pairs[$row[0]] = $row[1];
		}
		$results->free();
		return $pairs;
	}
	
	public function fetchRow($sql, $bind=array(), $fetchMode=null) {
		switch ($fetchMode) {
			case self::FETCH_NUM:
				$mode = MYSQLI_NUM;
				break;
			case self::FETCH_BOTH:
				$mode = MYSQLI_BOTH;
				break;
			case self::FETCH_ASSOC:
				$mode = MYSQLI_ASSOC;
				break;
			default:
				$mode = $this->fetchMode;
				break;
		}
		$results = $this->query($sql, $bind);
		$ret = $results->fetch_array($mode);
		$results->free();
		return $ret;
	}
	
	public function query($sql, $bind=array()) {
		$sql = $this->modifyQuery($sql, $bind);
		$ret = $this->getConnection()->query($sql);
		$this->lastSql = $sql;
		++self::$numQueries;
		$this->checkError();
		return $ret;
	}
	
	public function lastInsertId() {
		return $this->getConnection()->insert_id;
	}
	
	public function affectedRows() {
		return $this->getConnection()->affected_rows;
	}
	
	public function getServerVersion() {
		return $this->getConnection()->server_version;
	}
	
	public function setFetchMode($mode) {
		$this->fetchMode = $mode;
	}
	
	public function getFetchMode() {
		return $this->fetchMode;
	}
	
	public function getNumQueries() {
		return self::$numQueries;
	}

	public function getLastSql() {
		return $this->lastSql;
	}

	public static function quote($value, $type=null) {
		//return "'" . $this->getConnection()->real_escape_string($value) . "'";
		return "'" . addslashes($value) . "'";
	}
	
	public static function quoteIdentifier($ident) {
		return '`' . trim($ident, '`') . '`';
	}

	public static function buildWhere($where) {
		if (is_array($where)) {
			$conds = array();
			foreach ($where as $field=>$val) {
				if ($field == self::RAW_WHERE_KEY) {
					$conds[] = $val;
				} else {
					$conds[] = self::quoteIdentifier($field) . '=' . self::quote($val);
				}
			}
			return implode(' AND ', $conds);
		}
		return $where;
	}
	
	private function modifyQuery($query, $bind=array()) {
		if (empty($bind)) {
			return $query;
		}
		if (substr_count($query, '?') != count($bind)) {
			throw new DbException('placeholders('.substr_count($query, '?').') and params('.count($bind).') do not match.');
		}
		$tokens = explode('?', $query);
		$i=0;
		$sql = $tokens[0];
		foreach ($bind as $val) {
			$sql .= self::quote($val);
			$sql .= $tokens[++$i];
		}
		return $sql;
	}
	
	private function checkError() {
		if ($this->connection && $this->connection->errno) {
			throw new DbException('Database Error:' . $this->connection->error . "\nSQL:".$this->lastSql);
		}
	}
}

class DbException extends exception{}