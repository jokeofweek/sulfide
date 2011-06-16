<?php defined('APP_DIR') or die('Cannot access file.');	

class HomeController extends Controller {

	protected $redirect_bad_actions = 'view';
	
	public function doIndex() {
		echo _('Hello there, :user', array(':user' => 'World')).'<br/>';
	}
	
	public function doView() {
		echo 'These parameters were passed: ';
		var_dump($this->getParameters());
	}
}