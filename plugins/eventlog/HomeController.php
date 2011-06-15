<?php

class HomeController extends Controller {
	public function doIndex() {
		var_dump(Plugins::get('eventlog')->fetch());
	}
}