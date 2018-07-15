<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Extends the default CI_Controller class to template pages with menu, footer, etc */
abstract class MY_Controller extends CI_Controller {
	// Essential page vars
	private $full_page, $menu_vars, $header_vars, $footer_vars;
	// Paths to essential files
	private $menu_view, $header_view, $footer_view;
	// Data
	private $_data;
	// Var to sinalize is custom, set from child class on abstract functions
	private $custom_menu = FALSE, $custom_header = FALSE, $custom_footer = FALSE;

	final private function initialize_vars() {
		// Arrays for views
		$this->menu_vars = array();
		$this->header_vars = array();
		$this->_data = array();
		$this->footer_vars = array();
		// Config files
		$this->load_config();
		// View names
		$this->menu_view = $this->config->item('menu_view');
		$this->header_view = $this->config->item('header_view');
		$this->footer_view = $this->config->item('footer_view');
		// Load default or custom views
		$this->get_menu();
		$this->get_header();
		$this->get_footer();
	}

	/** Load vars from header and common config files */
	final private function load_config() {
		$this->config->load('my_controller');
		$this->config->load('header', TRUE);
		$this->config->load('common', TRUE);
		//GET ALL COMMON CONFIGS
		$this->_register_variables($this->config->config['common']);
		//GET ALL HEADER CONFIGS
		foreach($this->config->config['header'] as $key=>$value)
			$this->header_vars[$key] = $value;
	}

	/** Make variables available between MVC
	 * @param receives associative array or var to send to view, if var the key will be the var name */
	final public function _register_variables($var) {
		if(is_array($var) && $this->isAssoc($var)){
			foreach($var as $key=>$value)
				$this->_data[$key] = $value;
		}
	}

	/** Loads a page with content
	 * @param View to be loaded */
	final protected function _load($view = '') {
		if($view == ''){
			throw new InvalidArgumentException('Invalid view name');
		}
		$this->initialize_vars();
		$this->check_config();
		ECHO $this->full_page['header'];
		ECHO $this->full_page['menu'];
		ECHO $this->full_page['content'] = $this->parser->parse($view, $this->_data, TRUE);
		ECHO $this->full_page['footer'];
	}

	/** Validates all the essential config files of this controller */
	final private function check_config() {
		// Paths to essential views
		$menu_path = APPPATH . 'views\\' . $this->config->item('menu_view') . '.php';
		$header_path = APPPATH . 'views\\' . $this->config->item('header_view') . '.php';
		$footer_path = APPPATH . 'views\\' . $this->config->item('footer_view') . '.php';
		// Check config for my_controller
		if(! file_exists($menu_path))
			throw new InvalidArgumentException("Invalid configuration for my controller variables at my_controller, the menu file don\'t exist at {$menu_path}");
		if(! file_exists($header_path))
			throw new InvalidArgumentException("Invalid configuration for my controller variables at my_controller, the header file don\'t exist at {$menu_path}");
		if(! file_exists($footer_path))
			throw new InvalidArgumentException("Invalid configuration for my controller variables at my_controller, the footer file don\'t exist at {$menu_path}");
	}

	/** Gets Default menu view or custom one if setted */
	final private function get_menu() {
		$this->custom_menu = $this->_custom_menu();
		$this->full_page['menu'] = $this->custom_menu;
		if(! $this->full_page['menu']){
			$this->menu_vars = array_merge($this->menu_vars, $this->_data);
			$this->full_page['menu'] = $this->parser->parse($this->menu_view, $this->menu_vars, TRUE);
		}
	}

	/** Gets Default header view or custom one if setted */
	final private function get_header() {
		$this->custom_header = $this->_custom_header();
		$this->full_page['header'] = $this->custom_header;
		if(! $this->full_page['header']){
			$this->header_vars = array_merge($this->header_vars, $this->_data);
			$this->full_page['header'] = $this->parser->parse($this->header_view, $this->header_vars, TRUE);
		}
	}

	/** Gets Default footer view or custom one if setted */
	final private function get_footer() {
		$this->custom_footer = $this->_custom_footer();
		$this->full_page['footer'] = $this->custom_footer;
		if(! $this->full_page['footer']){
			$this->header_vars = array_merge($this->header_vars, $this->_data);
			$this->full_page['footer'] = $this->parser->parse($this->footer_view, $this->footer_vars, TRUE);
		}
	}

	/** Defines a custom menu
	 * @return String Need to return a parsed view to ECHO
	 * @example return $this->parser->parse('view', $view_data); */
	public function _custom_menu() {
		return false;
	}

	/** Defines a custom <head> tag
	 * @return String Need to return a parsed view to ECHO
	 * @example return $this->parser->parse('header', $view_data); */
	public function _custom_header() {
		return false;
	}

	/** Defines a custom footer
	 * @return String Need to return a parsed view to ECHO
	 * @example return $this->parser->parse('footer', $view_data); */
	public function _custom_footer() {
		return false;
	}

	/** Checks is the array has associative keys
	 * @param Array
	 * @return Boolean */
	final protected function isAssoc(array $arr) {
		if(array() === $arr){
			return false;
		}
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
}
