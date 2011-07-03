<?php

include_once('core/core.php');
requires('database', 'i18n', 'template', 'routing');

Plugins::load('EventLogger');

Plugins::get('EventLogger')
	->hook(Core::getHookable())
	->hook(Database::getFactory())
	->hook(Language::getHookable())
	->hook(Routing::getHookable());

Language::load('en');

Routing::route($_SERVER['REQUEST_URI']);