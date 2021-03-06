<?php

namespace App\Observer;

use App\Config;
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
     * Nombre de résultats par moteur
     * @var array
     */
    private static $hashNbEngineResults = array();

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

        $hashRegisteredEngines = Config::getValue('registered_engines');

        $hashUrlFactors = array();
        $intNbEngine = count($hashResults);
        foreach ($hashResults as $strEngine => $arrayResult) {
            $intCountResults = count($arrayResult);
            ++self::$intCountNbEngine;
            self::$hashNbEngineResults[$strEngine] = $intCountResults;
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

                        // résolution du nom des moteurs concernés par le résultat
                        $arrayConcernedEngines = array();
                        foreach ($hashResultInfos['index'] as $strEngine => $intUnusedIndex) {
                            $arrayConcernedEngines[] = $hashRegisteredEngines[$strEngine]['label'];
                        }
                        $hashResults['Scoring'][] = array_merge($hashResultFields, array('scoring' => $hashResultInfos['scoring'], 'engines' => $arrayConcernedEngines));
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
        $intAvgIndexA = self::computeAveragePercentPosition($hashRowA['index']);
        $intAvgIndexB = self::computeAveragePercentPosition($hashRowB['index']);

        $hashRowA['scoring'] = self::computeRowWeight($intAvgIndexA, $hashRowA);
        $hashRowB['scoring'] = self::computeRowWeight($intAvgIndexB, $hashRowB);

        return $hashRowA['scoring'] < $hashRowB['scoring'];
    }


    /**
     * Calcule la moyenne de la somme des % des index d'un résultat pour chaque moteur
     * @param array $hashIndexes
     * @return float|int
     */
    protected static function computeAveragePercentPosition(array $hashIndexes)
    {
        $intSumPercent = 0;
        foreach ($hashIndexes as $strEngine => $intIndex) {
            $intSumPercent += $intIndex / self::$hashNbEngineResults[$strEngine];
        }
        return (int) ($intSumPercent / self::$intCountNbEngine * 100);
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
