<?php

class CI_Loader {


	protected $_ci_model_paths = array();

	protected $_ci_models = array();

	function __construct() {
		$this->_ci_model_paths = array('');
	}


	public function model($model, $name = '') {

		if (is_array($model)) {
			foreach ($model as $babe) {
				$this->model($babe);
			}
			return;
		}

		if ($model == '') {
			return;
		}

		$path = '';

		// model 是否在一个文件夹中,如果是的话，则分析路径和文件名
		if (($last_slash = strrpos($model, '/')) !== FALSE) {
			$path = substr($model, 0, $last_slash + 1);

			$model = substr($model, $last_slash + 1);
		}

		if ($name == '') {
			$name = $model;
		}

		if (in_array($name, $this->_ci_models, TRUE)) {
			return;
		}

		$model = strtolower($model);

		$CI =& get_instance();

		foreach ($this->_ci_model_paths as $mod_path) {
			if ( ! file_exists($mod_path.'models/'.$path.$model.'.php')) {
				continue;
			}

			if ( ! class_exists('CI_Model')) {
				load_class('Model', 'core');
			}

			require_once($mod_path.'models/'.$path.$model.'.php');
			$model = ucfirst($model);

			$CI->$name = new $model();

			$this->_ci_models[] = $name;
			return;
		}

		// 找不到模型
		exit('Unable to locate the model you have specified: '.$model);

	}


	function initialize() {
		return;
	}



}