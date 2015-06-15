<?php

namespace App\Tools\SEStrategy;

use App\Tools;

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

        preg_match_all('/<h3 class="r"><a.*>(.*?)<\/a><\/h3>/Uim', $mixedResult, $arrayTitleMatches);
        preg_match_all('/<h3 class="r"><a href="\/url\?q=(.*?)&amp;.*">.*<\/a><\/h3>/im', $mixedResult, $arrayUrlMatches);
        preg_match_all('/<\/div><span class="st">([\w\W]*?)<\/span><br>/', $mixedResult, $arrayDescriptionMatches);

        $intNbEntry = count($arrayTitleMatches[0]);
        for ($intIndex = 0 ; $intIndex < $intNbEntry ; ++$intIndex) {
            $arrayResults[] = array(
                ISEStrategy::FIELD_TITLE        => isset($arrayTitleMatches[1][$intIndex])
                    ? utf8_encode(strip_tags($arrayTitleMatches[1][$intIndex]))
                    : '',
                ISEStrategy::FIELD_URL          => isset($arrayUrlMatches[1][$intIndex])
                    ? strip_tags($arrayUrlMatches[1][$intIndex])
                    : '',
                ISEStrategy::FIELD_DESCRIPTION  => isset($arrayDescriptionMatches[1][$intIndex])
                    ? utf8_encode(html_entity_decode(strip_tags($arrayDescriptionMatches[1][$intIndex]), ENT_QUOTES))
                    : ''
            );
        }
        return $arrayResults;
    }
}
