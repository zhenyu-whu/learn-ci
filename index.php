<?php
/**
 * 框架主入口文件，所有的页面请求均定为到该页面，并根据 url 地址来确定调用合适的方法并显示输出
 */
require('Common.php');

$URI =& load_class('URI');
$RTR =& load_class('Router');

$RTR->set_routing();


$class = $RTR->fetch_class();
$method = $RTR->fetch_method();

$CI = new $class();

call_user_func_array(array(&$CI, $method), array_slice($URI->rsegments, 2));

class Welcome {

	function hello() {
		echo 'My first Php Framework!';
	}

	function saysomething($str) {
		echo $str.", I'am the php framework you created!";
	}
}
