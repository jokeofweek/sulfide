<?php defined('APP_DIR') or die('Cannot access file.');

/**
 * Class used by the Routing class to provide the same basic level 
 * of functionality to all user-defined controllers. It permits
 * basic forwarding and dispatching. It is compliant with
 * the plugin system as it extends the {@link Observable} class.
 *
 * @package core
 * @author Dominic Charley-Roy
 */
abstract class Controller extends Observable {
	private $parameters = array();
	
	/**
	 * This variable can be overridden to redirect any
	 * erroneous action, along with the parameters, to
	 * another action. Note that if you leave it empty,
	 * or do not override it, the {@link Controller::dispatch}
	 * method will call the {@link Routing::routeError}
	 * method. You should set this to the action name, not including
	 * the 'do'.
	 *
	 * Note that this can also be used to do things such as
	 * passing parameters without passing an action. For example,
	 * if you have a controller called posts, and you want
	 * to be able to call /posts/2 to view the second post, you
	 * could set this variable to the 'view' or 'list' action,
	 * and the parameter 2 would be passed to that action along with
	 * any other parameters.
	 *
	 * @access protected
	 * @type mixed
	 */
	protected $redirectBadActions = FALSE;
	
	/**
	 * This variable can be overridden to change the default
	 * action when there is no action passed as a route, or the
	 * action passed as a route is empty.
	 *
	 * @access protected
	 * @type string
	 */
	protected $defaultAction = 'index';
	
	/**
	 * Function which allows you to pass a variable
	 * to a Controller based on a key and value system. These
	 * variables can be fetched using:
	 *   - {@link Controller::getParameter}
	 *   - {@link Controller:getParameters}
	 *
	 * @param mixed $key The key to represent the value
	 * @param mixed $val The value to store
	 */
	public function setParameter($key, $val) {
		$this->parameters[$key] = $val;
	}
	
	/**
	 * Function which allows you to pass an array of variables
	 * to a Controller which can then be used within the controller.
	 * These variables can be fetched using:
	 *   - {@link Controller::getParameter}
	 *   - {@link Controller:getParameters}
	 *
	 * @param array $values 
	 *		An array of values to store in the Controller
	 */
	public function setParameters($values) {
		$this->parameters = $values;
	}

	/**
	 * Fetches a controller parameter associated with a key
	 *
	 * @param mixed $key The key representing the value
	 * @return mixed 
	 *		If the key is valid, the value will be returned
	 *		or else false is returned.
	 */
	public function getParameter($key) {
		return (isset($this->parameters[$key])) ? $this->parameters[$key] : false;
	}
	
	/**
	 * Fetches all the current stored parameters in the controller
	 *
	 * @return array
	 *		An array of all the parameters held by the Controller
	 */
	public function getParameters() {
		return $this->parameters;
	}
	
	/**
	 * Fetches the default action specified by {@link Controller::defaultAction}
	 *
	 * @return string
	 *		The default action for the controller
	 */
	public function getDefaultAction() {
		return $this->defaultAction;
	}
	
	/**
	 * This function does a basic mapping from an action name
	 * to a function. The default mapping is that passing an action,
	 * such as 'view', will call the function 'doView'. 
	 *
	 * Any action must have an accompanying method, which follows the naming 
	 * standard:
	 *		do{$action}
	 * Where the first letter of $action is capitalized.
	 *
	 * If there is no valid function for a passed action, the method
	 * will then check if the {@link Controller::$redirectBadActions}
	 * variable has been set. If it has been, it will add the passed
	 * action to the front of the parameter list and call the dispatch
	 * method on the action defined by {@link Controller::$redirectBadActions}.
	 * However, if it has not been overridden, the {@link Routing::routeError()}
	 * method is called.	
	 *
	 * @param string $action The action to execute
	 */
	public function dispatch($action) {
		$action = ucfirst(strtolower($action));
		$this->setParameter('action', $action);
		$method = 'do'.$action;
		
		if (!method_exists($this, $method)){
			if ($this->redirectBadActions) {
				array_unshift($this->parameters, $action);
				$this->dispatch($this->redirectBadActions);
			} else {
				Routing::routeError();
			}
		} else {
			$this->$method();
		}
	}
	
	/**
	 * This function allows you to dispatch an action on another controller
	 * in the same style as the {@link Routing::route} function. 
	 *
	 * @param string $controller 
	 *		This is the name of the controller which contains the action 
	 * @param string $action
	 *		This is the action to be dispatched on the $controller
	 * @param string $plugin
	 *		This is an optional parameter allowing you to specify the name
	 *		of the plugin which contains the controller. Note that if you
	 *		wish to call a normal controller and not a plugin one, you must
	 *		leave this parameter empty
	 * @param array $parameters
	 *		This is an optional parameter allowing you to pass a set of 
	 *		parameters to the controller.
	 * @throws ControllerException
	 *		A ControllerException is thrown if you attempt to forward
	 *		an action to a plugin controller and the plugin is not currently loaded.
	 * @throws FileNotFoundException
	 *		A FileNotFoundException is thrown if you attempt to load
	 *		a controller which does not exist, or does not have the
	 *		appropriately named class inside it.
	 */
	public function forward($controller, $action, $plugin = '', array $parameters = NULL) {
		$class = ucfirst($controller).'Controller';
		
		$path = DIRECTORY_SEPARATOR.$class.'.php';
		
		// Build the path based on whether it is a plugin or not
		if ($plugin == '') {
			$path = Config::get('routing', 'controller_dir').$controller.$path;
		} else {
			if (!Plugins::get($plugin)) {
				throw new ControllerException('A '.__CLASS__.' object attempted to forward the action "'.
											  $action.'" to the controller "'.$class.'" in the plugin "'.
											  $plugin.'", however the plugin is currently not loaded.');
			}
			
			$path = Config::get('plugins', 'dir').$plugin.$path;
		}
		
		if (!file_exists($path)) {
			throw new FileNotFoundException('Could not forward request to the '.$class.' class with action '.$action.
											(($plugin != '') ? ' (plugin: '.$plugin.') ' : ' ').
											' as the controller does not exist.'); 
		}
		
		require_once $path;
		
		if (!class_exists($class)){
			throw new FileNotFoundException('Could not forward request to the '.$class.' class with action '.$action.
											(($plugin != '') ? ' (plugin: '.$plugin.') ' : ' ').
											' as there is no class named '.$class.' in the controller file.'); 
		}
		
		$obj = new $class();
		
		if (!($obj instanceof Controller)) {
			throw new FileNotFoundException('Could not forward request to the '.$class.' class with action '.$action.
											(($plugin != '') ? ' (plugin: '.$plugin.') ' : ' ').
											' as the class '.$class.' does not extend Controller.');
		}
		
		$obj->setParameters($parameters);
		$obj->dispatch($action);
	}
}

/**
 * This is a global singleton object which provides
 * URL routing functionality and interacts with the 
 * Controller system.
 *
 * @package core
 * @author Dominic Charley-Roy
 */
class Routing extends Observable {

	protected $package = 'core';
	protected $class = 'routing';
	private static $instance;
	
	/**
	 * This function fetches a hookable instance of the Routing class
	 * in order to allow {@link Observer} objects to watch the
	 * routing system for any events.
	 *
	 * @return Observable 
	 *		An instance of the class is returned as an Observable
	 *		object to allow plugins to hook to the Routing object.
	 * 
	 */
	public static function getHookable() { 
		if (!isset(self::$instance))
			self::$instance = new Routing();
		return self::$instance;
	}

	/**
	 * This function maps a given URL to the appropriate controller
	 * and action, passing along any extra parameters and executing
	 * the action.
	 *
	 * The basic structure for a URL is :
	 *		/controller/action/param1/param2/.../paramN
	 *
	 * For a controller to be valid, there must be a folder in the directory
	 * referenced in the Config key 'routing' => 'controller_dir'. This 
	 * folder must have the exact same name as the controller in the URL.
	 * In this folder, there must be a file named {controller}Controller.php,
	 * where {controller} represents the name of the controller <b>with the first
	 * letter capitalized.</b>
	 *
	 * In this file there must be a class with the same name as this file (not 
	 * including the .php), and this class must extend the {@link Controller} 
	 * class. 
	 *
	 * For details on mapping actions, see the {@link Controller} documentation.
	 *
	 * Note that the Routing system also ties in with the plugin system,
	 * and allows for URLs structured like so:
	 *		/plugin/controller/action/param1/param2/.../paramN
	 *
	 * In this case, the {controller}Controller.php file must be in the same
	 * folder as the plugin.php file of the designated plugin, and the Plugin
	 * must be loaded in the {@link Plugins} global object.
	 *
	 * Note that every URL fragment is optional, so you may have the following
	 * url structures:
	 * 		/
	 *		/controller
	 *		/controller/action
	 *		/plugin
	 *		etc..
	 *
	 * If the controller is  missing, the routing engine
	 * will use the default one, which are held in the {@link Config} key:
	 *		'routing' => 'default_controller'
	 *
	 * If no action is defined, the routing engine will dispatch the
	 * action defined by {@link Controller::defaultAction}.
	 *
	 * Events raised by the route function:
	 *		requested(url) - This event is raised before a url is
	 *					     process and signals that routing has
	 *					     been requested.
	 *		dispatching(controller, action, arguments)
	 *					   - This event is raised after routing is
	 *						 complete and before the action is dispatched.
	 *
	 * @param string $url The url which is going to be processed for routing
	 */
	public static function route($url) {
		self::getHookable()->raiseEvent('requested', array($url));
		
		$parts = explode('/', $url);
		$plugin = false;
		
		array_shift($parts); // Get rid of the first empty one
		
		// Check if we are accessing a plugin controller
		if (isset($parts[0]) && $parts[0] != '' && Plugins::get($parts[0])) {
			$plugin = $parts[0];
			array_shift($parts); // Remove the plugin name
		}
		
		// Check if we have a controller and if not, set to default
		$controller = (isset($parts[0]) && $parts[0] != '') ? $parts[0] : Config::get('routing', 'default_controller');
		
		// Validate the controller name to avoid any exploits
		if (!preg_match('/^[A-Za-z0-9_.\-]+$/', $controller)) {
			self::routeError();
			return;
		}
		
		$class = ucfirst($controller).'Controller';
		$path = DIRECTORY_SEPARATOR.$class.'.php';
		
		if ($plugin) {
			$path = Config::get('plugins', 'dir').$plugin.$path;
		} else {
			$path = Config::get('routing', 'controller_dir').$controller.$path;
		}
		
		// Check if there is a valid controller, if not route it to the error controller
		if (!file_exists($path)) {
			self::routeError();
			return;
		}
		
		// Include the controller, make sure it is a valid Controller object 
		// and dispatch the action
		include_once($path);
		
		if (!class_exists($class)){
			self::routeError();
			return;
		}
		
		$controller = new $class();
		
		if (!($controller instanceof Controller)) {
			self::routeError();
			return;
		}
		
		// Check if the action is defined, if not dispatch to default
		$action = (isset($parts[1]) && $parts[1] != '') ? $parts[1] : $controller->getDefaultAction();
		
		// Set the parameters
		$parameters = array_slice($parts, 2);
		$controller->setParameters($parameters);
		self::getHookable()->raiseEvent('dispatching', array($controller, $action, $parameters));
		$controller->dispatch($action);	
		
	}
	
	/**
	 * This function is called when there is an error in routing.
	 */
	public static function routeError() {
		self::getHookable()->raiseEvent('error');
		die('Routing error.');
	}	

}	

/**
 * Exception used to specify there was a problem with the Controller functionality.
 * @package core
 * @author Dominic Charley-Roy
 */
class ControllerException extends Exception { }