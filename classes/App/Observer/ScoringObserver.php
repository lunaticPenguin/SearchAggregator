<?php

namespace App\Observer;

use App\Observers\ObserverHandler;
use App\Observers\AbstractObserver;
use App\Tools\SEStrategy\ISEStrategy;
use App\Tools\SEStrategy\SearchHandler;

/**
 * Class ScoringObserver
 * Assure la gestion complète du scoring entre résultats de moteurs de recherche
 *
 * @package App\Observer
 */
class ScoringObserver extends AbstractObserver
{
    const FACTOR_NO_FLAG            = 0;
    const FACTOR_DUPLICATED         = 2;
    const FACTOR_FULLY_DUPLICATED   = 4;

    private static $intCountNbEngine = 0;

    /**
     * Register observer's methods to specified hooks
     */
    public function load()
    {
        ObserverHandler::addMHook('init_view_registered_engines', $this->getName(), 'init_view_registered_engines');
        ObserverHandler::addMHook('result_search', $this->getName(), 'result_search');
    }

    /**
     * Appelée lorsque l'on assigne à la vue les moteurs enregistrés afin d'ajouter le moteur factice "Scoring" (créé un onglet supplémentaire)
     * @param array $hashRegisteredEngines
     * @param array $hashParameters paramètres potentiels, facultatifs
     *
     * @return array liste des moteurs mise à jour (ajout du scoring)
     */
    public function init_view_registered_engines($hashRegisteredEngines, array $hashParameters = array())
    {
        $hashRegisteredEngines['Scoring'] = array('active' => true, 'label' => 'Scoring');
        return $hashRegisteredEngines;
    }

    /**
     * Appelée lors de la fin d'une recherche, afin d'ajouter les résultats scorés
     *
     * @param array $hashResults
     * @param array $hashParameters paramètres potentiels, facultatifs
     *
     * @return array
     */
    public function result_search($hashResults, array $hashParameters = array())
    {
        if ($hashParameters['type'] !== SearchHandler::TYPE_SEARCH) {
            return $hashResults;
        }

        $hashUrlFactors = array();
        $intNbEngine = count($hashResults);
        foreach ($hashResults as $strEngine => $arrayResult) {
            ++self::$intCountNbEngine;
            $intCountResults = count($arrayResult);
            foreach ($arrayResult as $intIndex => $hashRow) {
                if (!array_key_exists($hashRow[ISEStrategy::FIELD_URL], $hashUrlFactors)) {
                    $hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]] =
                        array(
                            'flag' => self::FACTOR_NO_FLAG,
                            'count' => 1,
                            'index' => array(
                                $strEngine => $intCountResults - $intIndex
                            )
                        );
                } else {

                    $hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]]['index'][$strEngine] = $intCountResults - $intIndex;

                    ++$hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]]['count'];

                    if (($hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]]['flag'] & self::FACTOR_DUPLICATED) === 0) {
                        $hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]]['flag'] += self::FACTOR_DUPLICATED;
                    }
                    if ($hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]]['count'] === $intNbEngine) {
                        $hashUrlFactors[$hashRow[ISEStrategy::FIELD_URL]]['flag'] += self::FACTOR_FULLY_DUPLICATED;
                    }
                }

            }
        }

        // tri des résultats scorés
        uasort($hashUrlFactors, array('App\Observer\ScoringObserver', 'sortScoring'));

        $arrayDuplicata = array();
        foreach ($hashUrlFactors as $strUrlKey => $hashResultInfos) {
            foreach ($hashResults as $strEngine => $arrayResults) {
                foreach ($arrayResults as $hashResultFields) {
                    if ($strUrlKey === $hashResultFields[ISEStrategy::FIELD_URL] && !in_array($hashResultFields[ISEStrategy::FIELD_URL], $arrayDuplicata, true)) {
                        $hashResults['Scoring'][] = array_merge($hashResultFields, array('scoring' => $hashResultInfos['scoring']));
                        $arrayDuplicata[] = $hashResultFields[ISEStrategy::FIELD_URL];
                    }
                }
            }
        }

        return $hashResults;
    }

    /**
     * Permet de comparer les résultats des recherches selon un scoring
     *
     * @param array $hashRowA
     * @param array $hashRowB
     * @return bool
     */
    public static function sortScoring(&$hashRowA, &$hashRowB)
    {
        $intAvgIndexA = (int) (array_sum($hashRowA['index']) / self::$intCountNbEngine);
        $intAvgIndexB = (int) (array_sum($hashRowB['index']) / self::$intCountNbEngine);


        $hashRowA['scoring'] = self::computeRowWeight($intAvgIndexA, $hashRowA);
        $hashRowB['scoring'] = self::computeRowWeight($intAvgIndexB, $hashRowB);

        return $hashRowA['scoring'] < $hashRowB['scoring'];
    }

    /**
     *
     * @param $intAvgIndex
     * @param array $hashRowInfos
     * @return int
     */
    protected static function computeRowWeight($intAvgIndex, array &$hashRowInfos)
    {
        if (self::FACTOR_FULLY_DUPLICATED & $hashRowInfos['flag'] !== 0) {
            $intAvgIndex *= self::FACTOR_FULLY_DUPLICATED;
        } else if (self::FACTOR_DUPLICATED & $hashRowInfos['flag'] !== 0) {
            $intAvgIndex *= self::FACTOR_DUPLICATED;
        }
        return $intAvgIndex;
    }
}
