<?php
/**
 * 框架主入口文件，所有的页面请求均定为到该页面，并根据 url 地址来确定调用合适的方法并显示输出
 */



/**
 * --------------------------------------------------------------------
 * 获取 uri ，并通过 uri 调用相应的方法
 * --------------------------------------------------------------------
 */

function detect_uri() {
	
	if ( ! isset($_SERVER['REQUEST_URI']) OR ! isset($_SERVER['SCRIPT_NAME'])) {
		return '';
	}

	$uri = $_SERVER['REQUEST_URI'];
	if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
		$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
	}

	if ($uri == '/' || empty($uri)) {
		return '/';
	}

	$uri = parse_url($uri, PHP_URL_PATH);

	// 将路径中的 '//' 或 '../' 等进行清理
	return str_replace(array('//', '../'), '/', trim($uri, '/'));
}

$uri = detect_uri();
// echo $uri;


function explode_uri($uri) {

	foreach (explode('/', preg_replace("|/*(.+?)/*$|", "\\1", $uri)) as $val) {
		$val = trim($val);
		if ($val != '') {
			$segments[] = $val;
		}
	}

	return $segments;
}

$uri_segments = explode_uri($uri);
// print_r($uri_segments);

// 获取要调用的类和方法
$class = '';
$method = '';
$rsegments = array();

include('routes.php');


function parse_routes() {
	global $uri_segments, $routes, $rsegments;

	$uri = implode('/', $uri_segments);	

	if (isset($routes[$uri])) {
		$rsegments = explode('/', $routes[$uri]);

		return set_request($rsegments);		
	}
}

function set_request($segments = array()) {
	global $class, $method;

	$class = $segments[0];

	if (isset($segments[1])) {
		$method = $segments[1];
	} else {
		$method = 'index';
	}
}

parse_routes();

$CI = new $class();

call_user_func_array(array(&$CI, $method), array_slice($rsegments, 2));

class Welcome {

	function hello() {
		echo 'My first Php Framework!';
	}

	function saysomething($str) {
		echo $str.", I'am the php framework you created!";
	}
}
