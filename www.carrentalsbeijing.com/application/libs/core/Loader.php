<?php
class Loader {
	public static function registerAutoload() {
		spl_autoload_register(array(__CLASS__, 'autoload'), false, true);
	}

	public static function unregisterAutoload() {
		spl_autoload_unregister(array(__CLASS__, 'autoload'));
	}

	public static function autoload($className) {
		if (StringHelper::strEndsWith($className, 'Controller')) {
			require APPLICATION_PATH . '/controllers/' . $className . '.php';
		} elseif (StringHelper::strStartsWith($className, 'Model_')) {
			list(,$name) = explode('_', $className);
			require APPLICATION_PATH . '/models/' . $name . '.php';
		} else {
			require APPLICATION_PATH . '/libs/' . $className . '.php';
		}
	}
}