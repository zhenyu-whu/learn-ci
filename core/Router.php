<?php

class CI_Router {

	var $uri;

	var $routes;

	var $class;

	var $method;

	var $default_controller;

	function __construct() {
		$this->uri =& load_class('URI');
	}

	function set_routing() {

		if (is_file('routes.php')) {
			include('routes.php');
		}

		$this->routes = ( ! isset($route) OR ! is_array($route)) ? array() : $route;
		unset($route);

		$this->default_controller = ( ! isset($this->routes['default_controller']) OR $this->routes['default_controller'] == '') ? FALSE : $this->routes['default_controller'];

		$this->uri->fetch_uri_string();

		if ($this->uri->uri_string == '') {
			return $this->set_default_controller();
		}

		$this->uri->explode_uri();

		$this->parse_routes();
	}


	function set_default_controller() {
		
	}

	function parse_routes() {
		$uri = implode('/', $this->uri->segments);	

		if (isset($this->routes[$uri])) {
			$rsegments = explode('/', $this->routes[$uri]);

			return $this->set_request($rsegments);		
		}
	}

	function set_request($segments = array()) {

		if (count($segments) == 0) {
			return $this->set_default_controller();
		}

		$this->set_class($segments[0]);

		if (isset($segments[1])) {
			$this->set_method($segments[1]);
		} else {
			$method = 'index';
		}

		$this->uri->rsegments = $segments;
	}

	function set_class($class) {
		$this->class = str_replace(array('/', '.'), '', $class);
	}

	/**
	 * Set the method
	 * 
	 * @param string $method the method to execute 
	 */
	function set_method($method) {
		$this->method = $method;
	}

	/**
	 * Fetch the class
	 *
	 * @return string the class 
	 */
	function fetch_class() {
		return $this->class;
	}

	/**
	 * Fetch the method
	 * 
	 * @return string the method 
	 */
	function fetch_method() {
		return $this->method;
	}
}