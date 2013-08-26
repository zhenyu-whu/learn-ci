<?php

class CI_Loader {

	/**
	 * Nesting level of the output buffering mechanism
	 * @var int
	 * @access protected
	 */
	protected $_ci_ob_level;

	/**
	 * List of paths to load views from
	 * @var array
	 * @access  protected
	 */
	protected $_ci_view_paths = array();

	protected $_ci_library_paths = array();

	protected $_ci_model_paths = array();

	protected $_ci_helper_paths = array();

	protected $_base_classes = array();

	protected $_ci_cached_vars = array();

	protected $_ci_classes = array();

	protected $_ci_loaded_files = array();

	protected $_ci_models = array();

	protected $_ci_helpers = array();

	protected $_ci_varmap = array('unit_test' => 'unit',
								'user_agent' => 'agent'
							);


	function __construct() {
		$this->_ci_ob_level = ob_get_level();

		$this->_ci_model_paths = array('');
		$this->_ci_view_paths = array('views/' => TRUE);
	}

	public function library($library = '', $params = NULL, $object_name = NULL) {

		if (is_array($library)) {

			foreach ($library as $class) {
				$this->library($class, $params);
			}

			return;
		}

		if ($library == '' OR isset($this->_base_classes[$library])) {
			return FALSE;
		}

		if ( ! is_null($params) && ! is_array($params)) {
			$params = NULL;
		}

		$this->_ci_load_class($library, $params, $object_name);
	}

	protected function _ci_load_class($class, $params = NULL, $object_name = NULL) {

		$class = str_replace('.php', '', trim($class, '/'));

		$subdir = '';
		if (($last_slash = strrpos($class, '/')) !== FALSE) {

			// 提取出路径
			$subdir = substr($class, 0, $last_slash + 1);

			// 提取出 class
			$class = substr($class, $last_slash + 1);
		}

		foreach (array(ucfirst($class), strtolower($class)) as $class) {
			$subclass = APPPATH.'libraries/'.$subdir.config_item('subclass_prefix').$class.'.php';

			// class 扩展
			if (file_exists($subclass)) {
				$baseclass = BASEPATH.'libraries/'.ucfirst($class).'.php';

				if ( ! file_exists($baseclass)) {
					log_message('error', 'Unable to load the requested class: '.$class);
					show_error('Unable to load the requested class: '.$class);
				}

				// 检查 class 是否加载过
				if (in_array($subclass, $this->_ci_loaded_files)) {
					if (! is_null($object_name)) {
						$CI =& get_instance();
						if ( ! isset($CI->$object_name)) {
							return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
						}
					}

					$is_duplicate = TRUE;
					log_message('debug', $class." class already loaded. Second attempt ignored.");
					return;

				}

				include_once($baseclass);
				include_once($subclass);

				$this->_ci_loaded_files[] = $subclass;

				return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name);
			}
		}

		if ($is_duplicate == FALSE) {
			log_message('error', 'Unable to load the requested class: '.$class);
			show_error("Unable to load the requested class: ".$class);
		}
	}

	public function _ci_init_class($class, $prefix = '', $config = FALSE, $object_name = NULL) {

		if ($prefix == '') {
			$name = $class;
		} else {
			$name = $prefix.$class;
		}

		if ( ! class_exists($name)) {
			log_message('error', "Non-existent class: ".$name);
			show_error("Non-existent class: ".$class);
		}

		$class = strtolower($class);

		$classvar = $class;

		$CI =& get_instance();
		if ($config !== NULL) {
			$CI->$classvar = new $name($config);
		} else {
			$CI->$classvar = new $name;
		}
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

		// model
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

		// 鎵句笉鍒版ā鍨�
		exit('Unable to locate the model you have specified: '.$model);

	}


	public function initialize() {
		$this->_ci_classes = array();
		$this->_ci_loaded_files = array();
		$this->_ci_models = array();
		$this->_base_classes =& is_loaded();

		return;
	}

	public function view($view, $vars = array(), $return = FALSE) {
		return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_objects_to_array($vars), '_ci_return' => $return));
	}


	/**
	 *
	 *
	 * @param  object $object
	 * @return array
	 */
	public function _ci_objects_to_array($object) {
		return (is_object($object)) ? get_object_vars($object) : $object;
	}

	public function _ci_load($_ci_data) {


		// 閫氳繃 foreach 寰幆寤虹珛鍥涗釜灞�儴鍙橀噺锛屼笖鏍规嵁浼犲叆鐨勬暟缁勮繘琛岃祴鍊硷紙濡傛灉娌℃湁锛屽垯涓篎ALSE)
		foreach (array('_ci_view', '_ci_vars', '_ci_path', '_ci_return') as $_ci_val) {
			$$_ci_val = ( ! isset($_ci_data[$_ci_val])) ? FALSE : $_ci_data[$_ci_val];
		}

		$file_exists = FALSE;

		// 璁剧疆璺緞, 鍗曠函鍔犺浇瑙嗗浘鐨勬椂鍊�锛宊ci_path 涓虹┖锛屼細鐩存帴鎵ц涓嬮潰鐨�else 璇彞
		if ($_ci_path != '') {
			$_ci_x = explode('/', $_ci_path);
			$_ci_file = end($_ci_x);
		} else {

			// 鍒ゆ柇 鎵╁睍鍚嶏紝濡傛灉娌℃湁鍒欏姞涓�php 鍚庣紑
			$_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
			$_ci_file = ($_ci_ext == '') ? $_ci_view.'.php' : $_ci_view;

			// 鎼滅储瀛樻斁 view 鏂囦欢鐨勮矾寰�
			foreach ($this->_ci_view_paths as $view_file => $cascade) {
				if (file_exists($view_file.$_ci_file)) {
					$_ci_path = $view_file.$_ci_file;
					$file_exists = TRUE;
					break;
				}

				if ( ! $cascade) {
					break;
				}
			}
		}

		if ( ! $file_exists && ! file_exists($_ci_path)) {
			exit('Unable to load the requested file: '.$_ci_file);
		}

		$_ci_CI =& get_instance();
		foreach (get_object_vars($_ci_CI) as $_ci_key => $_ci_var) {
			if (! isset($this->$_ci_key)) {
				$this->$_ci_key =& $_ci_CI->$_ci_key;
			}
		}

		// 非常重要
		if (is_array($_ci_vars)) {
			$this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $_ci_vars);
		}
		extract($this->_ci_cached_vars);

		ob_start();

		include($_ci_path);

		// 如果需要返回数据，则从缓冲区中返回数据
		if ($_ci_return === TRUE) {
			$buffer = ob_get_contents();
			@ob_end_clean();
			return $buffer;
		}

		// 如果是嵌套的视图中的输出，则直接 flush, 以便外层视图可以得到 buffer 中的内容，
		// 而最外层的 buffer 则导出到 output 类中进行最后的处理
		if (ob_get_level() > $this->_ci_ob_level + 1) {
			ob_end_flush();
		} else {
			$_ci_CI->output->append_output(ob_get_contents());
			@ob_end_clean();
		}

	}


}