<?php

namespace App\Tools\SEStrategy;

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
        echo $mixedResult;
    }
}
