<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Posts extends MY_Controller {

	public function index() {
		parent::_load('posts');
	}
}
