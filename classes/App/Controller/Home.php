<?php

namespace App\Controller;

use App\Page;
use App\Tools\SEStrategy\SearchHandler;

class Home extends Page
{
    /**
     * Main page
     */
	public function action_index()
    {
        $strSearchParameter = $this->request->get('q', '');
        $this->hashViewVariables['hashContent'] = SearchHandler::search($strSearchParameter);
	}

    /**
     * Suggested data
     */
    public function action_suggest()
    {
        $strSearchParameter = $this->request->get('q', '');
        $this->hashViewVariables['hashContent'] = SearchHandler::suggest($strSearchParameter);
        $this->setCustomTemplate('global/ajax_call.html.twig');
    }
}
