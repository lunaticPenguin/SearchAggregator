<?php

namespace App\Tools\SEStrategy;

use App\Tools;

class YahooRawSEStrategy extends AbstractSEStrategy
{
    /**
     * Initialise les données de la stratégie
     * @return void
     */
    protected function init()
    {
        $this->strSearchUrl             = 'https://fr.search.yahoo.com/search';
        $this->strSearchFieldName       = 'p';


        $this->strSuggestUrl            = 'https://fr.search.yahoo.com/sugg/gossip/gossip-fr-ura';
        $this->strSuggestFieldName      = 'command';
        $this->arraySuggestFieldNames   = array(
            'output'  => 'sd1'
        );

        $this->hashFieldsRegexp = array(
            ISEStrategy::FIELD_TITLE        => '/<div class="compTitle.*><h3 class="title"><a class="[ ]{0,}td-u".*>(.*)<\/a>/Uim',
            ISEStrategy::FIELD_URL          => '/<div class="compTitle.*"><h3 class="title"><a class="[ ]{0,}td-u" href="(.*)".*>/Uim',
            ISEStrategy::FIELD_DESCRIPTION  => '/<p class="lh\-18[ ]{0,3}">(.*)<\/p>/Uim',
            ISEStrategy::FIELD_SUGGESTION   => ''
        );
    }

    /**
     * Défini le comportement à effectuer pour parser les résultats d'une recherche
     * @param mixed|array|string $mixedResult résultat du webservice à parser
     * @return mixed
     */
    public function parseSearch($mixedResult)
    {
        $arrayResults = array();
        $boolStatus = preg_match_all($this->hashFieldsRegexp[ISEStrategy::FIELD_TITLE], $mixedResult, $arrayTitleMatches);
        $boolStatus = preg_match_all($this->hashFieldsRegexp[ISEStrategy::FIELD_URL], $mixedResult, $arrayUrlMatches) && $boolStatus;
        $boolStatus = preg_match_all($this->hashFieldsRegexp[ISEStrategy::FIELD_DESCRIPTION], $mixedResult, $arrayDescriptionMatches) && $boolStatus;

        if (!$boolStatus) {
            return $arrayResults;
        }

        $intNbEntry = count($arrayTitleMatches[0]);
        for ($intIndex = 0 ; $intIndex < $intNbEntry ; ++$intIndex) {
            $arrayResults[] = array(
                ISEStrategy::FIELD_TITLE        => isset($arrayTitleMatches[1][$intIndex])
                    ? html_entity_decode(strip_tags($arrayTitleMatches[1][$intIndex]))
                    : '',
                ISEStrategy::FIELD_URL          => isset($arrayUrlMatches[1][$intIndex])
                    ? html_entity_decode(strip_tags($arrayUrlMatches[1][$intIndex]))
                    : '',
                ISEStrategy::FIELD_DESCRIPTION  => isset($arrayDescriptionMatches[1][$intIndex])
                    ? html_entity_decode(strip_tags($arrayDescriptionMatches[1][$intIndex]))
                    : ''
            );
        }
        return $arrayResults;
    }

    /**
     * Défini le comportement à effectuer pour parser les suggestions
     * @param mixed|array|string $mixedResult résultat du webservice à parser
     * @return mixed
     */
    public function parseSuggest($mixedResult)
    {
        $arrayResults = array();
        if (isset($mixedResult['r']) && is_array($mixedResult['r'])) {
            foreach ($mixedResult['r'] as $hashRow) {
                $arrayResults[] = $hashRow['k'];
            }
        }
        return $arrayResults;
    }
}
