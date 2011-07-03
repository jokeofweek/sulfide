<?php

class ViewController extends Controller {

	protected $redirect_bad_actions = 'index';
	
	public function doIndex() {
		$calls = Plugins::get('EventLogger')->fetch();
		$serverUrl = 'http://'.$_SERVER['SERVER_ADDR'].(($_SERVER['SERVER_PORT'] != '80') ? ':'.$_SERVER['SERVER_PORT'] : '');
		$parameters = print_r($this->getParameters(), TRUE);
		
		$template = new Template();

		$template->assign('headerTitle', 'EventLogger/View/Index');
		$template->assign('pageTitle', 'EventLogger/View/Index');
		
		$content = <<<CONTENT
		<p>This is the EventLogger plugin. It displays the basic plugin functionality as well as using Plugin controllers
		and seeing how they map to URLs.</p>
		
		<p>Below is a list of all the events that were called in rendering this page, as well as the arguments which 
		were passed to them:
		
		<ul>
CONTENT;
		foreach ($calls as $call) {
			$content .= '<li>'.$call['name'].'<ul>';
			foreach ($call['args'] as $arg) {
				if (is_object($arg))
					$content .= '<li>Object</li>';
				else if (is_array($arg))
					$content .= '<li>'.htmlspecialchars(print_r($arg, TRUE)).'</li>';
				else
					$content .= '<li>'.htmlspecialchars($arg).'</li>';
			}
			$content .= '</ul	></li>';
		}
		
		$content .= <<<CONTENT
		</ul></p>
		
		<p>If you wish to see how parameters get passed in events, feel free to add parameters to the current URL.</p>
		
		<p>You can also return to the <a href="{$serverUrl}">home page</a>.</p>
		
		<p>For debugging and testing purposes, here are the parameters which were recieved by this page:
			{$parameters}
		</p>

CONTENT;
		
		
		$template->assign('pageContent', $content);
		
		$template->display('page.tpl');
	}
}