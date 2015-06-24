<?php

namespace App\Observer;

use App\Observers\ObserverHandler;
use App\Observers\AbstractObserver;
use App\Tools\SEStrategy\ISEStrategy;

class ScoringObserver extends AbstractObserver
{
    const FACTOR_DUPLICATED = 1;
    const FACTOR_FULLY_DUPLICATED = 2;

    /**
     * Register observer's methods to specified hooks
     */
    public function load()
    {
        ObserverHandler::addMHook('result_search', $this->getName(), 'result_search');
    }

    /**
     * Appelé lors de la fin d'une recherche, afin de scorer les résultats
     *
     * @param array $hashResults
     * @param array $hashParameters
     *
     * @return array
     */
    public function result_search($hashResults, array $hashParameters)
    {
        $hashUrlFactors = array();
        $intNbEngine = count($hashResults);
        $hashScoredResults = array();
        foreach ($hashResults as $strEngine => $arrayResult) {
            $hashScoredResults[$strEngine] = array();
            $intCountResults = count($arrayResult);
            foreach ($arrayResult as $intIndex => $hashRow) {
                if (!array_key_exists($hashRow[ISEStrategy::FIELD_URL], $hashUrlFactors)) {
                    $hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]] =
                        array(
                            'factor' => 0,
                            'count' => 1,
                            'index' => array(
                                $strEngine => $intCountResults - $intIndex
                            )
                        );
                } else {

                    $hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]]['index'][$strEngine] = $intCountResults - $intIndex;

                    ++$hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]]['count'];

                    if (($hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]]['factor'] & self::FACTOR_DUPLICATED) === 0) {
                        $hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]]['factor'] += self::FACTOR_DUPLICATED;
                    }
                    if ($hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]]['count'] === $intNbEngine) {
                        $hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]]['factor'] += self::FACTOR_FULLY_DUPLICATED;
                    }
                }

            }
        }

        foreach ($hashUrlFactors as $strUrl => $hashInfos) {
//            $hashUrlFactors[$strUrl]
        }


//        var_dump('---------------------', $intNbEngine, $hashUrlFactors);
//        exit;


        return $hashScoredResults;
    }
}
