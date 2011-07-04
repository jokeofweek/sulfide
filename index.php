<?php

include_once('core/core.php');
Core::requires('database', 'i18n', 'smarty.template', 'routing');

Plugins::load('EventLogger');

Plugins::get('EventLogger')
	->hook(Core::getHookable())
	->hook(Database::getFactory())
	->hook(Language::getHookable())
	->hook(Routing::getHookable());

Language::load('en');

Routing::route($_SERVER['REQUEST_URI']);