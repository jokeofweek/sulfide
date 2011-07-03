<?php defined('APP_DIR') or die('Cannot access file.');	

class HomeController extends Controller {

	protected $redirect_bad_actions = 'index';
	
	public function doIndex() {
		$fileLocation = __FILE__;
		$serverUrl = 'http://'.$_SERVER['SERVER_ADDR'].(($_SERVER['SERVER_PORT'] != '80') ? ':'.$_SERVER['SERVER_PORT'] : '');
		$parameters = print_r($this->getParameters(), TRUE);
		
		$template = new Template();
		$template->assign('headerTitle', 'Home/Index');
		$template->assign('pageTitle', 'Home/Index');
		$template->assign('pageContent', <<<CONTENT
			<p>Welcome to the Sulfide framework! The Sulfide framework is a simple yet powerful PHP5-compliant framework which 
			allows you to rapidly create applications without worrying about basic functionality like database connections
			and URL routing.</p>
			
			<p>You are currently in the Home controller in the Index action. The code for this can be seen in the 'doIndex' function
			of this class <a href="{$fileLocation}">{$fileLocation}</a>. This is the default controller and action, and therefore any
			request which isn't properly formatted will lead to this action. This includes the following requests:
			
			<ul>
				<li><a href="{$serverUrl}">{$serverUrl}</a></li>
				<li><a href="{$serverUrl}/home">{$serverUrl}/home</a></li>
				<li><a href="{$serverUrl}/home/bad-action">{$serverUrl}/home/bad-action</a></li>
				<li><a href="{$serverUrl}/home/param1/param2/param3">{$serverUrl}/home/param1/param2/param3</a></li>
			</ul>
			
			</p>You may see other controllers and actions here:
			<ul>
				<li><a href="{$serverUrl}/home/otherAction">{$serverUrl}/home/otherAction</a></li>
				<li><a href="{$serverUrl}/misc/">{$serverUrl}/misc/</a></li>
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
	
	public function doOtherAction() {
		$fileLocation = __FILE__;
		$parameters = print_r($this->getParameters(), TRUE);
		$serverUrl = 'http://'.$_SERVER['SERVER_ADDR'].(($_SERVER['SERVER_PORT'] != '80') ? ':'.$_SERVER['SERVER_PORT'] : '');
		
		$template = new Template();
		$template->assign('headerTitle', 'Home/OtherAction');
		$template->assign('pageTitle', 'Home/OtherAction');
		$template->assign('pageContent', <<<CONTENT
			<p>You are currently in the Home controller in the OtherAction action. The code for this can be seen in the 'doOtherAction' function
			of this class <a href="{$fileLocation}">{$fileLocation}</a>.
			</p>
			
			<p>As you can see, the mapping of actions and controllers to URLs allows you to create clean URLs which map directly to a function
			within a controller. From a coding perspective, this provides maintainability as it is easy to determine what each URL does by keeping
			a consistent structure throughout your controllers. It also provides a basic level of search engine optimization.</p>
			
			<p>The routing engine can be very powerful, as it allows you to re-route requests, dispatch other actions within a controller as well as
			forward parameters to another controller and action. Note that the routing engine is however completely optional, and it's functionality
			can simply be removed by removing it from the list of includes in the 'requires()' call of the index.php file located in the root. You must
			also delete the .htaccess file included with sulfide, as this re-routes every request to the index.php file. Note however that we reccomend
			keeping some of the .htaccess rules to hide your application structure and prevent related vulnerabilities.</p>
			
			<p>You can also return to the <a href="{$serverUrl}">home page</a>.</p>
			
			<p>For debugging and testing purposes, here are the parameters which were recieved by this page:
				{$parameters}
			</p>
CONTENT
);
		$template->display('page.tpl');
	}
}