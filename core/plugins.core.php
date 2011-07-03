<?php defined('APP_DIR') or die('Cannot access file.');

/**
 * This class provides a base class for any class which
 * wants to allow Plugins hooking to the class and
 * receiving events. A class must extend this in order to
 * connect with the Plugin system.
 *
 * Note that, for the event system of a given object to work
 * properly, an object must override the protected variables
 * {@link $package} and {@link $class}.
 *
 * @package core
 * @author Dominic Charley-Roy
 */
class Observable {
	/**
	 * String which designates the object's package
	 * which is passed when raising events. This must be
	 * overridden. For info on how the package is used, see 
	 * {@link Observable::raiseEvent}.
	 * @access protected
	 * @type string
	 */
	protected $package;
	/**
	 * String which designates the object's class
	 * which is passed when raising events. This must be
	 * overridden. For info on how the package is used, see 
	 * {@link Observable::raiseEvent}.
	 * @access protected
	 * @type string
	 */
	protected $class;
	
	private $observers = array();
	private $hasObservers = false;
	
	/**
	 * This function attached a Plugin object to the class
	 * in order to be notified of any events raised by the
	 * class.
	 *
	 * @param Plugin The plugin to be hooked to the class
	 * @throws PluginException 
	 *		A PluginException is thrown if there is already
	 *		a plugin hooked to this class with that name.
	 */
	public function addPlugin(Plugin $plugin) {
		if (array_key_exists($plugin->getName(), $this->observers))
			throw new PluginException('The plugin '.$plugin->getName().' was already hooked to the '.__CLASS__.' object.');
		else {
			$this->observers[$plugin->getName()] = $plugin;
			$this->hasObservers = true;
			call_user_func(array($plugin, $this->package.'_'.$this->class.'_hooked'), $this);
		}
	}
	
	/**
	 * This function detaches a named Plugin object from
	 * the class in order to prevent it from receiving
	 * event notifications.
	 * 
	 * @param string $name The name of the Plugin
	 * @throws PluginException
	 *		A PluginException is thrown if there is no
	 *		plugin currently hooked to the object with the
	 *		passed name.
	 */
	public function removePlugin($name) {
		if (array_key_exists($name, $this->observers)) {
			call_user_func(array($this->observers[$name],  $this->package.'_'.$this->class.'_unhooked'), $this);
			unset($this->observers[$name]);
			$this->hasObservers = (count($this->observers) == 0);
		} else 			
			throw new PluginException('The plugin '.$name.' cannot be unhooked from the '.__CLASS__.' object as it is not currently hooked to it..');
	}	
	
	/**
	 * This function raises an event and signals all the
	 * Plugins currently hooked to the object of the event.
	 * The event which is called on the Plugin follows the
	 * following format:
	 *		package_class_event(...)
	 *
	 * As an event handler can return a value, you can also
	 * speciy the raised event to collect the return values.
	 *
	 * @param string $event The name of the event
	 * @param array $args
	 *		The args parameter is optional and allows
	 *		you to pass one or more arguments along with
	 * 		the event through an array.
	 * @param boolean $collect
	 *		The collect parameter is optional (default is false)
	 *		and states whether the return values should be 
	 *		collected in an array. 
	 * @return mixed
	 *		Either nothing will be returned if $collect is set to false,
	 *		or an array of all the returned data will be returned.
	 */
	public function raiseEvent($event, array $args = NULL, $collect = FALSE) {
		// If there are no observers, quit early
		if (!$this->hasObservers) return;
		
		// If no args are being passed, use call_user_func as it gives a performance
		// boost
		if ($args) {
			if (!$collect) {
				foreach ($this->observers as $plugin)
					call_user_func_array(array($plugin,  $this->package.'_'.$this->class.'_'.$event), $args);
			} else {
				$data = array();
				$method = $this->package.'_'.$this->class.'_'.$event;
				
				// Loop through each hooked plugin and cache the result
				foreach ($this->observers as $plugin) 
					$data[] = call_user_func_array(array($plugin, $method), $args);
		
				return $data;
			}
		} else {
			if (!$collect) {
				foreach ($this->observers as $plugin)
					call_user_func(array($plugin,  $this->package.'_'.$this->class.'_'.$event));
			} else {
				$data = array();
				$method = $this->package.'_'.$this->class.'_'.$event;
				
				// Loop through each hooked plugin and cache the result
				foreach ($this->observers as $plugin) 
					$data[] = call_user_func(array($plugin, $method));
		
				return $data;
			}
		}
	}
}

/**
 * This class provides a base class for a plugin which can
 * hook to an {@link Observable} object and receive events
 * from it.  
 *
 * Note that a plugin must override the protected variable
 * {@link $pluginName} in order to fnction properly.
 *
 * @package core
 * @author Dominic Charley-Roy
 */
class Plugin {
	/**
	 * String which designates the plugin's name. This is an important
	 * setting and must be overridden.
	 * @access protected
	 * @type string
	 */
	protected $pluginName = '';
	
	private $observing = array();

	/**
	 * This function attaches the plugin to an appropriate object
	 * in order to receive event notifications from the object.
	 *
	 * @param Observable $observable
	 * 		This is the object to attach to. Note that it must
	 *		extend the Observable object.
	 * @return Plugin
	 *		The plugin will be returned in order to allow method
	 *		chaining.
	 * @throws PluginException
	 *		A PluginException will be thrown if the plugin is 
	 *		already hooked to the passed Observable object.
	 */
	public function hook(Observable $observable) {
		$observable->addPlugin($this);
		$this->observing[] = $observable;
		return $this;
	}
	
	/**
	 * This function detaches the plugin from an {@link Observable}
	 * object it was previously hooked to. Once it has been detached,
	 * the plugin will no longer receive events from the object.
	 
	 * @param Observable $observable
	 * 		This is the object to detach from. Note that it must
	 *		have been hooked to previously.
	 * @return Plugin
	 *		The plugin will be returned in order to allow method
	 *		chaining.
	 * @throws PluginException
	 *		A PluginException will be thrown if the plugin is not
	 *		hooked to the passed Observable object.
	 */
	public function unhook(Observable $observable) {
		foreach ($this->observing as $key => $obj)
			if ($obj === $observable) {
				$observable->removePlugin($this->getName());
				unset($this->observing[$key]);
				return $this;
			}
		
		return $this;
	}
	
	/**
	 * This function unhooks the plugin from all {@link Observable}
	 * objects it is currently hooked to.
	 *
	 * @return Plugin
	 *		The plugin will be returned in order to allow method
	 *		chaining.
	 */
	public function unhookAll() {
		$name = $this->getName();
		
		foreach ($this->observing as $obj)
			$obj->removePlugin($name);
			
		unset($this->observing);
		
		return $this;
	}
	
	/**
	 * This function returns the proper name of the plugin.
	 *
	 * @return string The name of the plugin
	 */
	public function getName() {
		return $this->pluginName;
	}

	public function __call($function, $args) { }
}

/**
 * This class provides a basic manager for Plugins. It allows
 * the code to cache plugins and interact with them in order 
 * to keep only one copy of the plugin. It also makes it easier
 * to access the plugins as they are available globally 
 * through this singleton manager.
 * @package core
 * @author Dominic Charley-Roy
 */
class Plugins {
	private static $plugins = array();
	
	/**
	 * This function adds a {@link Plugin} object to the
	 * plugin manager.
	 *
	 * @param Plugin $plugin The plugin object to add
	 * @throws PluginException
	 *		A PluginException is thrown if there is already
	 *		a plugin with the same name ({@link Plugin->getName()})
	 *		being managed by the Plugins manager
	 */
	public static function add(Plugin $plugin) {
		if (array_key_exists($plugin->getName(), self::$plugins))
			throw new PluginException('The plugin '.$plugin->getName().' was already loaded. This could either be a conflict in plugin names (see the pluginName property) or an error in the code.');
		
		self::$plugins[$plugin->getName()] = $plugin;
		$plugin->load();
	}
	
	/**
	 * This function removes a {@link Plugin} object from the
	 * manager, and it can no longer be accessed through the manager.
	 *
	 * @param string $name
	 *		This is the name of the plugin to be removed. The name
	 *		of a plugin can be obtained through {@link Plugin->getName()}
	 * @throws PluginException
	 *		A PluginException is thrown if there is no plugin
	 *		being managed with the given name.
	 */
	public static function remove($name) {
		if (!array_key_exists($name, self::$plugins))
			throw new PluginException('An attempt was made to remove the plugin \''.$name.'\' , however it is not currently loaded in the Plugin Manager.');
		
		self::$plugins[$name]->unhookAll();
		self::$plugins[$name]->unload();
		
		unset(self::$plugins[$name]);		
	}
	
	/**
	 * This function fetches the copy of a {@link Plugin} object
	 * being managed through the manager by its name.
	 *
	 * @param string $name
	 *		This is the name of the plugin to be fetched. The name
	 *		of a plugin can be obtained through {@link Plugin->getName()}
	 * @return mixed 
	 *		The copy of the Plugin object is returned if is found, or else 
	 *		false is returns
	 */
	public static function get($name) {
		return (array_key_exists($name, self::$plugins)) ? 
			self::$plugins[$name] :
			false;
	}
	
	
	/**
	 * This function loads the plugin file for a designated plugin
	 * and adds it to the manager. For a plugin to be loaded properly,
	 * it must be placed in a file in the following structure:
	 *		 
	 * 		path to application/plugins/{$name}/plugin.php
	 *
	 * where {$name} represents the name which was passed to the function.
	 * In the plugin.php file, there must be a class which is named
	 * the same as the name passed, and must extend the Plugin class.
	 *
	 * For example, if you wanted to load a plugin named Logger, you must 
	 * have a plugin.php file placed in the following location:
	 *
	 *		path to application/plugins/Logger/plugin.php
	 *
	 * And in that file, there must be a class which is described like so:
	 *	
	 *		class Logger extends Plugin { ... }
	 *
	 * Note that this function automatically loads any plugin configuration settings
	 * to the {@link Config} object. If a config.php file is present in the plugin
	 * folder, it will attempt to load it. Note that the config.php file must
	 * contain the following variable:
	 *
	 *		$config = array(...)
	 *
	 * The $config array should contain all your configuration settings. These settings
	 * will be added to the following Config key:
	 *		'loaded_plugins' => 'plugin_name'
	 *
	 * The 'plugin_name' part of the key is automatically fetched from the Plugin through 
	 * the {@link Plugin::getName()} method.
	 *
	 * @param string $name 
	 *		This is the name of the plugin to be loaded. This describes both
	 *		the directory of the plugin and the name of the class.
	 * @throws FileNotFoundException
	 *		A FileNotFoundException is thrown if there was no plugin.php found
	 *		for this class
	 * @throws PluginException
	 *		A PluginException is thrown if there is no valid class in the
	 *		plugin.php file or if it does not extend the Plugin object.
	 */
	public static function load($name) {
		$cfg = Config::get('plugins');
		$dir = $cfg['dir'];
	
		if (!file_exists($dir.$name.DIRECTORY_SEPARATOR.'plugin.php')) {
			throw new FileNotFoundException('No plugin.php file found for the '.$name.' plugin.');
		}
		
		require_once $dir.$name.DIRECTORY_SEPARATOR.'plugin.php';
		
		if (!class_exists($name)) {
			throw new PluginException('The plugin.php file for the '.$name.' plugin must contain a class named '.$name.' which must extend the Plugin class.');
		}
		
		$obj = new $name();
		
		if (!($obj instanceof Plugin)) {
			throw new PluginException('The plugin.php file for the '.$name.' plugin must contain a class named '.$name.' which must extend the Plugin class.');
		}
		
		self::add($obj);
		
		// Load any configuration options
		if (file_exists($dir.$name.DIRECTORY_SEPARATOR.'config.php')) {
			require_once $dir.$name.DIRECTORY_SEPARATOR.'config.php';
			
			if (isset($config))
				Config::add($config, array('loaded_plugins', $obj->getName()));
		}
	}
}

/**
 * The Core class is a singleton object which is globally available
 * and allows system events to be triggered from external code
 * through the use of the {@link Observable->raiseEvent()} function.
 *
 * @author Dominic Charley-Roy
 * @package core
 */
class Core extends Observable {
	protected $package = 'core';
	protected $class = 'core';
	
	private static $instance;
	
	/**
	 * This function fetches a hookable instance of the Core class
	 * in order to allow {@link Observer} objects to watch the
	 * system for any events.
	 *
	 * @return Observable 
	 *		An instance of the class is returned as an Observable
	 *		object to allow plugins to hook to the Core system.
	 * 
	 */
	public static function getHookable() { 
		if (!isset(self::$instance))
			self::$instance = new Core();
		return self::$instance;
	}
	
	/**
	 * Acts as a convience method which calls the internal raiseEvent
	 * method of the Observable object.
	 *
	 * @see Observable::raiseEvent()
	 */
	public static function raise($event, array $args = NULL, $collect = FALSE)  {
		self::getHookable()->raiseEvent($event, $args, $collect);
	}
}	

/**
 * Exception used to specify there was a problem with Plugin functionality
 * @package core
 * @author Dominic Charley-Roy
 */
class PluginException extends Exception { }