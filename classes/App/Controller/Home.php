<?php

namespace App\Controller;

use App\Page;
use App\Tools;
use App\Tools\MemCache;
use App\Tools\SEStrategy\ISEStrategy;
use App\Tools\SEStrategy\SearchHandler;

class Home extends Page
{
    /**
     * Main page
     */
	public function action_index()
    {
        $strSearchParameter = $this->request->get('q', $this->pixie->session->get('q', ''));
        if ($strSearchParameter !== $this->pixie->session->get('q')) {
            $this->pixie->session->set('q', $strSearchParameter);
        }
        $this->hashViewVariables['strSearchedParameter'] = $strSearchParameter;

        $strSearchKey = md5(trim($strSearchParameter));
        if (($mixedValue = MemCache::getInstance()->get($strSearchKey)) === false) {
            MemCache::getInstance()->set($strSearchKey, SearchHandler::search($strSearchParameter));
        }

        $hashPaging = $this->pixie->session->get('paging');
        $this->hashViewVariables['hashContent'] = Tools::paginate(
            MemCache::getInstance()->get($strSearchKey),
            $hashPaging,
            $this->pixie->session->get('engine'),
            $this->pixie->session->get('page')
        );
        $this->pixie->session->set('paging', $hashPaging);
	}

    /**
     * Suggested data
     */
    public function action_suggest()
    {
        if (!$this->request->is_ajax()) {
            exit;
        }
        $strSearchParameter = $this->request->get('q', '');
        $this->hashViewVariables['hashContent'] = SearchHandler::suggest($strSearchParameter);
    }
}
