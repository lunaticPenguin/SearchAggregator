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
        $this->strSearchUrl             = 'https://www.google.fr/search';
        $this->strSearchFieldName       = 'q';


        $this->strSuggestUrl            = 'https://www.google.fr/s';
        $this->strSuggestFieldName      = 'q';
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
        $arrayResults = array();

        if (!isset($mixedResult[1])) {
            return $arrayResults;
        }

        foreach ($mixedResult[1] as $hashSuggestion) {
            $arrayResults[] = Tools::removeAccents(strip_tags($hashSuggestion[0]));
        }
        return $arrayResults;
    }

    /**
     * @inheritdoc
     */
    public function parseSearch($mixedResult)
    {
        $arrayResults = array();

        $boolStatus = preg_match_all('/<h3 class="r"><a.*>(.*?)<\/a><\/h3>/Uim', $mixedResult, $arrayTitleMatches) !== false;
        $boolStatus = preg_match_all('/<h3 class="r"><a href="\/url\?q=(.*?)&amp;.*">.*<\/a><\/h3>/im', $mixedResult, $arrayUrlMatches) !== false && $boolStatus;
        $boolStatus = preg_match_all('/<\/div><span class="st">([\w\W]*?)<\/span><br>/', $mixedResult, $arrayDescriptionMatches) !== false && $boolStatus;

        if (!$boolStatus) {
            return $arrayResults;
        }

        $intNbEntry = count($arrayTitleMatches[0]);
        for ($intIndex = 0 ; $intIndex < $intNbEntry ; ++$intIndex) {
            $arrayResults[] = array(
                ISEStrategy::FIELD_TITLE        => isset($arrayTitleMatches[1][$intIndex])
                    ? Tools::removeAccents(utf8_encode(strip_tags($arrayTitleMatches[1][$intIndex])))
                    : '',
                ISEStrategy::FIELD_URL          => isset($arrayUrlMatches[1][$intIndex])
                    ? html_entity_decode(strip_tags($arrayUrlMatches[1][$intIndex]), ENT_QUOTES)
                    : '',
                ISEStrategy::FIELD_DESCRIPTION  => isset($arrayDescriptionMatches[1][$intIndex])
                    ? utf8_encode(html_entity_decode(strip_tags($arrayDescriptionMatches[1][$intIndex]), ENT_QUOTES))
                    : ''
            );
        }
        return $arrayResults;
    }
}
