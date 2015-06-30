<?php
abstract class ControllerAbstract extends Controller {

	public function init() {
		parent::init();
	}

	protected function setNoCacheHeaders() {
		$this->_response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate', true);
		$this->_response->setHeader('Pragma', 'no-cache', true);
		$this->_response->setHeader('Expires', '0', true);
	}

	protected function json($obj) {
		$this->setNoRender();
		$this->setNoCacheHeaders();
		$this->_response->setHeader('Content-Type', 'application/json', true);
		//echo json_encode($obj);
		echo $obj;
		return;
	}
	
	protected function redirect($msg, $link, $waitTime=1) {
		if ($this->_request->isXmlHttpRequest()) {
			$ret['type'] = 'success';
			$ret['message'] = $msg;
			$this->json($ret);
			return;
		}
		$this->setNoRender();
		$this->view->msg = $msg;
		$this->view->link = $link;
		$this->view->waitTime = $waitTime;
		$this->renderScript('common/redirect.phtml');
	}
	
	protected function error($msg) {
		$this->setNoRender();
		$this->_request->setDispatched(true);
		if ($this->_request->isXmlHttpRequest()) {
			$arp = new AjaxResponse();
			$arp->setStatus(AjaxResponse::STATUS_FAILED);
			$arp->setMessage($msg);
			$this->json($arp);
		} else {
			$this->view->msg = $msg;
			$this->renderScript('common/error.phtml');
		}
	}

	protected function message($msg) {
		if ($this->_request->isXmlHttpRequest()) {
			$ret['type'] = 'success';
			$ret['message'] = $msg;
			$this->json($ret);
			return;
		}
		$this->setNoRender();
		$this->view->msg = $msg;
		$this->renderScript('common/msg.phtml');
	}
	
	public static function pager($currpage, $perpage, $nums, $q, $currPageStyle='', $othersPageStyle='',$dp = 10)
	{
		$nums = intval($nums);
		$maxPages = ceil($nums/$perpage);
		$pageStart=1;
		if ($maxPages==0) {
			$maxPages = 1;
		}
		if ($currpage>$maxPages) {
			$currpage=$maxPages;
		}
		if ($currpage<=1) {
			$s = "<span class=\"{$currPageStyle}\">Previous </span>";
			$pageStart = 1;
			$currpage=1;
			$pageEnd=$dp;
		} else {
			$tmp = $currpage-1;
			$s = "<a href=\"".str_replace('{page}', $tmp, $q)."\" class=\"{$othersPageStyle}\">Previous</a> ";
			/*** 下面开始计算 1--$dp 以后的 $pageStart ***/
			$rangeOrder = floor(($currpage-2)/($dp-2));
			$pageStart = $rangeOrder*($dp-2)+1;
			$pageEnd=$pageStart+$dp-1;
		}
	
		for ($i=$pageStart; $i<=$pageEnd; $i++) {
			if ($i>$maxPages) {
				break;
			}
			if ($i!=$currpage) {
				$s.= '<a href="'.str_replace('{page}', $i, $q).'" class="'.$othersPageStyle.'">'.$i.'</a> ';
			}
			else {
				$s.= '<span class="'.$currPageStyle.'">'.$i.'</span> ';
			}
		}
	
		if ($currpage>=$maxPages) {
			$s.= "<span class=\"{$currPageStyle}\">Next </span>";
		} else {
			$tmp = $currpage+1;
			$s.= "<a href=\"".str_replace('{page}', $tmp, $q)."\" class=\"{$othersPageStyle}\">Next</a>";
		}
		return $s;
	}
}