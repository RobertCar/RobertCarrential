<?php
require APPLICATION_PATH . '/libs/core/StringHelper.class.php';
require APPLICATION_PATH . '/libs/core/Request.php';
require APPLICATION_PATH . '/libs/core/Response.php';
require APPLICATION_PATH . '/libs/core/Controller.php';
require APPLICATION_PATH . '/libs/core/View.php';
require APPLICATION_PATH . '/libs/core/Db.php';
require APPLICATION_PATH . '/libs/core/Loader.php';

class Application {
	
	private static $config;
	
	/**
	 * @var Db
	 */
	private static $db;
	
	/**
	 * @var Request
	 */
	private static $request;
	
	/**
	* @var Response
	*/
	private static $response;
	
	/**
	* @var View
	*/
	private static $view;
	private static $outputBuffer = true;
	private static $shouldRender = true;
	
	public static function run($configFile, $applicationEnv) {
		Loader::registerAutoload();
		self::$request = new Request();
		self::$response = new Response();
		include $configFile;
		self::$config = $config[$applicationEnv];
		StringHelper::setCryptKey(self::$config['crypt_key']);
		self::$db = new Db(self::$config['db']['params']);
		self::initView();
		self::startDispatch();
	}
	
	public static function setNoOutputBuffer() {
		self::$outputBuffer = false;
	}
	
	public static function setNoRender() {
		self::$shouldRender = false;
	}

	private static function initView() {
		self::$view = new View();
	}
	
	private static function startDispatch() {
		$buffering = !IN_CLI && self::$outputBuffer;
		if ($buffering) {
			ob_start();
		}
		try {
			self::dispatch();
			$controllerPath = self::$request->getControllerPath();
			$actionFile = self::$request->getActionFile();
		} catch(Exception $e) {
			if (IN_CLI) {
				throw $e;
				return;
			}
			$controllerPath = 'error';
			self::$request->setUserParam('exception', $e);
			$controller = new ErrorController();
			if ($e instanceof PageNotFoundException) {
				$actionFile = 'page-not-found';
				self::$response->setHttpResponseCode(404);
				$controller->pageNotFoundAction();
			} else {
				$actionFile = 'internal-error';
				self::$response->setHttpResponseCode(500);
				$controller->internalErrorAction();
			}
		}
		if (self::$shouldRender && !self::$response->isRedirect()) {
			self::$view->render($controllerPath, $actionFile);
		}
		if ($buffering && !self::$response->isRedirect()) {
			self::$response->setBody(ob_get_clean());
		}
		self::$response->sendResponse();
	}
	
	private static function dispatch() {
		$controllerClassName = self::$request->getControllerClassName();
		$actionFuncName = self::$request->getActionFuncName();
		if (!is_callable(array($controllerClassName, $actionFuncName))) {
			throw new PageNotFoundException("{$controllerClassName}->{$actionFuncName} not found.");
			return;
		}
		$controller = new $controllerClassName();
		if (!self::$request->isDispatched()) {
			$controller->$actionFuncName();
		}
	}

	/**
	 * Return Request instance
	 * 
	 * @return Request
	 */
	public static function getRequest() {
		return self::$request;
	}
	
	/**
	 * Return Response instance
	 * 
	 * @return Response
	 */
	public static function getResponse() {
		return self::$response;
	}
	
	/**
	 * Get config array.
	 * @return array
	 */
	public static function getConfig() {
		return self::$config;
	}
	
	/**
	 * Get Db instance.
	 * @return Db
	 */
	public static function getDb() {
		return self::$db;
	}
	
	/**
	 * Get View
	 * @return View
	 */
	public static function getView() {
		return self::$view;
	}
}

class PageNotFoundException extends Exception{}