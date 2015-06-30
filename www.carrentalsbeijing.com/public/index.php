<?php
@ini_set('memory_limit', '512M');
@set_magic_quotes_runtime(0);
@ini_set('magic_quotes_gpc', 0);
date_default_timezone_set('Asia/Shanghai');

if (!defined('APPLICATION_ENV')) {
	define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
}

define('APPLICATION_PATH', __DIR__ . '/../application/');
define('IN_CLI', isset($argv) && !isset($_SERVER['SERVER_NAME']));

if (in_array(APPLICATION_ENV, array('development', 'testing')) || isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'ZHIYI7')!==false) {
	define('SHOW_ERROR', true);
	@ini_set('display_errors', 1);
	error_reporting(E_ALL);
	set_include_path(get_include_path() . PATH_SEPARATOR . APPLICATION_PATH . PATH_SEPARATOR . '/Users/zhiyi/workspace/library');
} else {
	define('SHOW_ERROR', false);
	@ini_set('display_errors', 0);
	error_reporting(0);
	set_include_path(get_include_path() . PATH_SEPARATOR . APPLICATION_PATH);
}

if (IN_CLI) {
	if (isset($argv[1])) {
		$_SERVER['HTTP_X_REWRITE_URL'] = $argv[1];
	}
}

require APPLICATION_PATH . '/libs/core/Application.php';

try {
	Application::run(APPLICATION_PATH.'/config.php', APPLICATION_ENV);
} catch (Exception $e) {
	if (SHOW_ERROR || IN_CLI) {
		echo '<pre>';
		var_dump($e);
		echo '</pre>';
	} else {
		echo '<h1>500 Internal Server Error</h1>';
	}
}
