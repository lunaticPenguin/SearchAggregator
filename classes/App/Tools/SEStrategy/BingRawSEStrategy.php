<?php

namespace App\Tools\SEStrategy;

use App\Tools;

class BingRawSEStrategy extends AbstractSEStrategy
{
    /**
     * @inheritdoc
     */
    protected function init()
    {
        $this->strSearchUrl             = 'https://www.bing.com/search';
        $this->strSearchFieldName       = 'q';


        $this->strSuggestUrl            = 'https://www.bing.com/AS/Suggestions';
        $this->strSuggestFieldName      = 'qry';
        $this->arraySuggestFieldNames   = array(
            'cvid'  => md5(time())  // avec une valeur random, à priori ça fonctionne :)
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

        if (preg_match_all('/<div class="sa_tm">(.*?)<\/div>/mi', $mixedResult, $arraySuggestionsMatches) !== false) {
            foreach ($arraySuggestionsMatches[1] as $strSuggestion) {
                $arrayResults[] = html_entity_decode(strip_tags($strSuggestion));
            }
        }
        return $arrayResults;
    }

    /**
     * @inheritdoc
     */
    public function parseSearch($mixedResult)
    {
        $arrayResults = array();

        $boolStatus = preg_match_all('/<li class="b_algo"><h2><a.*>(.*)<\/a><\/h2>/Uim', $mixedResult, $arrayTitleMatches) !== false;
        $boolStatus = preg_match_all('/<li class="b_algo"><h2><a href="(.*)".*>/U', $mixedResult, $arrayUrlMatches) !== false && $boolStatus;
        $boolStatus = preg_match_all('/<li class="b_algo">.*<div class="b_caption">.*<p>(.*)<\/p>/Uim', $mixedResult, $arrayDescriptionMatches) !== false && $boolStatus;


        if (!$boolStatus) {
            return $arrayResults;
        }

        $intNbEntry = count($arrayTitleMatches[1]);
        for ($intIndex = 0 ; $intIndex < $intNbEntry ; ++$intIndex) {
            $arrayResults[] = array(
                ISEStrategy::FIELD_TITLE        => html_entity_decode(strip_tags($arrayTitleMatches[1][$intIndex])),
                ISEStrategy::FIELD_URL          => html_entity_decode(strip_tags($arrayUrlMatches[1][$intIndex])),
                ISEStrategy::FIELD_DESCRIPTION  => html_entity_decode(strip_tags($arrayDescriptionMatches[1][$intIndex])),
            );
        }
        return $arrayResults;
    }
}