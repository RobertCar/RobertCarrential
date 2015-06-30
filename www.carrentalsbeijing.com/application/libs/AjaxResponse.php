<?php
class AjaxResponse {
	const STATUS_OK = '1';
	const STATUS_FAILED = '0';

	protected $body;

	public function __construct() {
		$this->body = array();
	}

	public function setStatus($status) {
		$this->body['status'] = $status;
	}

	public function getStatus() {
		return $this->body['status'];
	}

	public function setMessage($msg) {
		$this->body['message'] = $msg;
	}

	public function addErrorItem($id, $desc) {
		$this->body['errors'][$id] = $desc;
	}

	public function __toString() {
		return json_encode($this->body);
	}
}