<?php

class welcome extends CI_Controller {

	function hello() {
		echo 'My first Php Framework!';
	}

	function saysomething($str) {

		$this->load->library('email');

		$info = $this->email->test();

		$data['info'] = $info;

		$this->load->view('test_view', $data);
	}
}
