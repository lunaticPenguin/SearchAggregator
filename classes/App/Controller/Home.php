<?php

namespace App\Controller;

use App\Page;
use App\Tools\SEStrategy\SearchHandler;

class Home extends Page {

	public function action_index() {
        SearchHandler::search('lolilalul');
	}
}
