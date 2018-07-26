<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Extends the default CI_Exception class and logs the errors into logs/class/log */
class MY_Exception extends CI_Exceptions {
	private $type, $message, $debug_stack;

	public function __construct($type = 'DEBUG', $message = 'Generic Unspecified Exception') {
		parent::__construct($message, 0, null);
		$this->type = $type;
		$this->message = $message;
		$this->debug_stack = debug_backtrace();
		//Remove My_Exception do backtrace
		array_pop($this->debug_stack);
	}

	/** Exception Logger Logs PHP generated error messages with backtrace
	 * @param bool $arg If the message should show the arguments the functions received on the backtrace */
	public function log_exception($arg = null) {
		log_message($this->exception_message($arg));
	}

	/** ECHO the Exception generated with backtrace
	 * @param bool $arg If the message should show the arguments the functions received on the backtrace */
	public function show_exception($arg = null) {
		pre($this->exception_message($arg));
	}

	private function exception_message($message, $type = 'DEBUG', $arg = null) {
		$backtrace = '';
		foreach($this->debug_stack as $error){
			$backtrace .= 'file: ' . $error['file'] . ', function: ' . $error['function'] . ' line:' . $error['line'] . PHP_EOL;
			if($arg != null){
				ob_start();
				var_dump($error['args']);
				$content = ob_get_contents();
				ob_end_clean();
				$backtrace .= '      args: ' . $content . PHP_EOL;
			}
		}
		return date("d/m/Y") . ' [' . $type . '] Exception: ' . $message . PHP_EOL . '   at: ' . $backtrace;
	}
}
