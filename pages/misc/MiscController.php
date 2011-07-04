<?php defined('APP_DIR') or die('Cannot access file.');	

class MiscController extends Controller {

	protected $redirectBadActions = 'index';
	
	public function doIndex() {
		$fileLocation = __FILE__;
		$serverUrl = 'http://'.$_SERVER['SERVER_ADDR'].(($_SERVER['SERVER_PORT'] != '80') ? ':'.$_SERVER['SERVER_PORT'] : '');
		$parameters = print_r($this->getParameters(), TRUE);
		
		$template = new Template();
		$template->assign('headerTitle', 'Misc/Index');
		$template->assign('pageTitle', 'Misc/Index');
		$template->assign('pageContent', <<<CONTENT
		
			<p>You are currently in the Misc controller in the Index action. The code for this can be seen in the 'doIndex' function
			of this class <a href="{$fileLocation}">{$fileLocation}</a>. This is a second controller and is created to show you the
			structures of controllers and how the controllers and actions map to a URL. Note that this is the default action for this
			controller, and can therefore be accessed by the following URLs:
			
			<ul>
				<li><a href="{$serverUrl}/misc">{$serverUrl}/misc</a></li>
				<li><a href="{$serverUrl}/misc/bad-action">{$serverUrl}/misc/bad-action</a></li>
				<li><a href="{$serverUrl}/misc/param1/param2/param3">{$serverUrl}/misc/param1/param2/param3</a></li>
			</ul>
			</p>
			
			
			</p>You may see other controllers and actions here:
			<ul>
				<li><a href="{$serverUrl}">{$serverUrl}</a></li>
				<li><a href="{$serverUrl}/home/otherAction">{$serverUrl}/home/otherAction</a></li>
				<li><a href="{$serverUrl}/misc/add/5/8">{$serverUrl}/misc/add/5/8</a></li>
			</ul>
			</p>
			
			<p>By default, the Sulfide framework includes a basic plugin called the 'eventlogger', which tracks all events which are called
			in the processing of a page. You may see just the basic events raised when calling the eventlogger controller itself at 
			<a href="{$serverUrl}/EventLogger/view">{$serverUrl}/EventLogger/view</a>
			</p>
			
			<p>For debugging and testing purposes, here are the parameters which were recieved by this page:
				{$parameters}
			</p>
CONTENT
);
		$template->display('page.tpl');
		
	}
	
	public function doAdd() {
		$fileLocation = __FILE__;
		$serverUrl = 'http://'.$_SERVER['SERVER_ADDR'].(($_SERVER['SERVER_PORT'] != '80') ? ':'.$_SERVER['SERVER_PORT'] : '');
		$parameters = $this->getParameters();
		
		$first = (isset($parameters[0]) && is_numeric($parameters[0])) ? $parameters[0] : 0;
		$second = (isset($parameters[1]) && is_numeric($parameters[1])) ? $parameters[1] : 0;
		$total = $first + $second;
		$parameters = print_r($parameters, TRUE);
		
		
		$template = new Template();
		$template->assign('headerTitle', 'Misc/Add');
		$template->assign('pageTitle', 'Misc/Add');
		$template->assign('pageContent', <<<CONTENT
			<p>You are currently in the Misc controller in theAdd action. The code for this can be seen in the 'doAdd' function
			of this class <a href="{$fileLocation}">{$fileLocation}</a>.
			</p>
			
			<p>This action is meant primarily to display passing parameters to a controller and action. It accepts two numerical parameters,
			adds them and then displays the result. You can test it out through the following URL (feel free to change the numbers!): 
			<a href="{$serverUrl}/misc/add/1/2">{$serverUrl}/misc/add/1/2</a>. Note that any parameter which is not passed, or is not
			a number will be set to zero.</p>
			
			<p>The two numbers passed are {$first} and {$second}, which when added produce a total of {$total}.</p>
			
			<p>You can also return to the <a href="{$serverUrl}">home page</a>.</p>
			
			<p>For debugging and testing purposes, here are the parameters which were recieved by this page:
				{$parameters}
			</p>
CONTENT
);
		$template->display('page.tpl');
	}
}