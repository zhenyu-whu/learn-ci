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

	return $uri;
}


echo detect_uri();