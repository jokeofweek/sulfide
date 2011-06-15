<?php

include_once('core/core.php');
requires('database', 'i18n', 'template', 'routing', 'db.session');

Plugins::load('eventlog');

Plugins::get('eventlog')
	->hook(Core::getHookable())
	->hook(Database::getFactory())
	->hook(Language::getHookable())
	->hook(Routing::getHookable());

Language::load('en');

Routing::route($_SERVER['REQUEST_URI']);