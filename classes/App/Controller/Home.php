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
        $strSearchParameter = $this->request->get('q', '');
        $this->hashViewVariables['strSearchedParameter'] = $strSearchParameter;

        $hashTmp = array();
        foreach (array('GoogleRaw', 'BingRaw') as $strEngine) {
            for ($i = 0 ; $i < 15 ; ++$i) {
                $hashTmp[$strEngine][] = array(
                    ISEStrategy::FIELD_TITLE => $strEngine . ' ' . $i . 'coucou lolildfjdsk',
                    ISEStrategy::FIELD_URL => 'http://perdu.com',
                    ISEStrategy::FIELD_DESCRIPTION => $strEngine . ' ' . $i . ':;wsdfhklsdnfg!lksnfd,glùksghj'
                );
            }
        }

        $strSearchKey = md5($strSearchParameter);
        if (($mixedValue = MemCache::getInstance()->get($strSearchKey)) === false) {
            MemCache::getInstance()->set($strSearchKey, $hashTmp); // SearchHandler::search($strSearchParameter));
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
