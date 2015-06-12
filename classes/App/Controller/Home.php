<?php

namespace App\Controller;

use App\Config;
use App\Page;
use App\Tools\SEStrategy\SearchHandler;

class Home extends Page {

	public function action_index() {
        $this->hashViewVariables['hashRegisteredEngines'] = Config::getValue('registered_engines', array());
        $this->hashViewVariables['hashContent'] = SearchHandler::search('rfgdfg');
	}
}
