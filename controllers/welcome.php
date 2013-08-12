<?php

class welcome extends CI_Controller {

	function hello() {
		echo 'My first Php Framework!';
	}

	function saysomething($str) {
		$this->load->model('test_model');

		$info = $this->test_model->get_test_data();

		echo $info;
	}
}
