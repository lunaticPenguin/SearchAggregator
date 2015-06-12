<?php

namespace App\Tools\SEStrategy;

use Sunra\PhpSimple\HtmlDomParser;
use Symfony\Component\DomCrawler\Crawler;

class GoogleRawSEStrategy extends AbstractSEStrategy
{
    /**
     * @inheritdoc
     */
    protected function init()
    {
        $this->strUrl = 'http://www.google.fr/search';
        $this->strQueryType = 'GET';
        $this->strSearchFieldName = 'q';
        $this->hashFieldsMapping = array(
            ISEStrategy::FIELD_TITLE        => 'blopblop',
            ISEStrategy::FIELD_DESCRIPTION  => 'blipblip',
            ISEStrategy::FIELD_URL          => 'blupblup'
        );
    }

    /**
     * @inheritdoc
     */
    public function parse($mixedResult)
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
