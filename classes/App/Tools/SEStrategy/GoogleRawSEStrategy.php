<?php

namespace App\Tools\SEStrategy;

use App\Tools;
use Symfony\Component\DomCrawler\Crawler;

class GoogleRawSEStrategy extends AbstractSEStrategy
{
    /**
     * @inheritdoc
     */
    protected function init()
    {
        $this->strSearchUrl         = 'http://www.google.fr/search';
        $this->strSearchFieldName   = 'q';


        $this->strSuggestUrl            = 'https://www.google.fr/s';
        $this->arraySuggestFieldNames   = array(
            'gs_ri' => 'psy-ab'
        );

        $this->hashFieldsMapping = array(
            ISEStrategy::FIELD_TITLE        => 'blopblop',
            ISEStrategy::FIELD_DESCRIPTION  => 'blipblip',
            ISEStrategy::FIELD_URL          => 'blupblup'
        );
    }

    /**
     * @inheritdoc
     */
    public function parseSuggest($mixedResult)
    {
        $hashResult = array();

        if (!isset($mixedResult[1])) {
            return $hashResult;
        }

        foreach ($mixedResult[1] as $hashSuggestion) {
            $hashResult[] = Tools::removeAccents(strip_tags($hashSuggestion[0]));
        }

        return $hashResult;
    }

    /**
     * @inheritdoc
     */
    public function parseSearch($mixedResult)
    {
        $arrayResults = array();
        try {
            $objDocument = new Crawler($mixedResult);
            foreach ($objDocument->filter('LI[class=g]') as $objChildNode) {
                /**
                 * @var $objChildNode \DOMElement
                 * @var $objSubChildNode \DOMElement
                 */

                $hashTempResult = array();
                foreach ($objChildNode->childNodes as $objSubChildNode) {
//                    var_dump($objSubChildNode->nodeName);
                    switch($objSubChildNode->nodeName) {
                        case 'h3':
                            $hashTempResult[ISEStrategy::FIELD_TITLE] = $objSubChildNode->nodeValue;
                            break;
                    }
                }

                if (!empty($hashTempResult)) {
                    $arrayResults[] = $hashTempResult;
                }
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
        return $arrayResults;
    }
}
