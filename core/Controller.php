<?php

class CI_Controller {

	private static $instance;

	public function __construct() {
		self::$instance =& $this;

		foreach (is_loaded() as $var => $class) {
			$this->$var =& load_class($class);
		}

		$this->load =& load_class('Loader');

		$this->load->initialize();
	}

	public static function &get_instance() {
		return self::$instance;
	}


}