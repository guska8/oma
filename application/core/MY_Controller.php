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
	// Common vars
	public $class_name;
	// Resources
	private $resources;
	
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
		// Resources for header
		$this->header_vars["css_list"] = '';
		$this->header_vars["js_list"] = '';
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
		if(! file_exists($menu_path)) throw new Exception("Invalid configuration for my controller, the menu file don\'t exist at {$menu_path}");
		if(! file_exists($header_path)) throw new Exception("Invalid configuration for my controller, the header file don\'t exist at {$menu_path}");
		if(! file_exists($footer_path)) throw new Exception("Invalid configuration for my controller, the footer file don\'t exist at {$menu_path}");
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

	/** Checks if a resource type is accepted
	 * @param $type string The type of resource (css, js) */
	private function is_resource_type($type = "") {
		$accepted = array('css', 'js');
		if(in_array(strtolower($type), $accepted)){
			return true;
		}
		throw new Exception('Invalid resource type');
	}

	/** Add a resource (css/js) to the template, searches resourses/class_name and then resources/common
	 * @param $type string The type of resource (css, js)
	 * @param $file_name string Resource to be added */
	protected function add_resource($type, $file_name) {
		if(! $this->is_resource_type($type)) return false;
		if(is_array($file_name)){
			foreach($file_name as $value){
				$this->resources[] = array($type, $this->find_resource($type, $value));
			}
		} else{
			$this->resources[] = array($type, $this->find_resource($type, $file_name));
		}

		foreach($this->resources as $key=>$value){
			//Para cada tipo adiciona o recurso com a tag
			switch($key){
				case 'css':
					$this->header_vars['css_list'] .= '<link rel="stylesheet" type="text/css" href="' . $value . '"/>';
					break;
				case 'js':
					$this->header_vars['js_list'] .= '<script type="text/javascript" src="' . $value . '"/>';
					break;
			}
		}
	}

	/** Find the css or js file in the correct folder depending on the type
	 * @param $type string The type of resource (css, js)
	 * @param $file_name string Resource to be added */
	private function find_resource($type, $file_name) {
		//Path to
		$path1 = base_url() . 'resources/' . $this->class_name . '/' . $type . '/' . $file_name;
		$path2 = base_url() . 'resources/common/' . $type . '/' . $file_name;
		//Se achou em resources
		if(file_exists($path1))
			return $path1;
		//Se achou em common
		else if(file_exists($path2))
			return $path2;
		else return false;
	}

	/** Defines a custom menu
	 * @return String Need to return a parsed view to ECHO
	 * @example return $this->parser->parse('view', $view_data); */
	function _custom_menu() {
		return false;
	}

	/** Defines a custom <head> tag
	 * @return String Need to return a parsed view to ECHO
	 * @example return $this->parser->parse('header', $view_data); */
	function _custom_header() {
		return false;
	}

	/** Defines a custom footer
	 * @return String Need to return a parsed view to ECHO
	 * @example return $this->parser->parse('footer', $view_data); */
	function _custom_footer() {
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
