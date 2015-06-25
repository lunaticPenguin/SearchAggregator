<?php

namespace App\Tools\SEStrategy;

use App\Config;
use App\Observers\ObserverHandler;

class SearchHandler
{
    const TYPE_SUGGEST  = 0;
    const TYPE_SEARCH   = 1;

    /**
     * @var ISEStrategy[]
     */
    protected static $arraySEStrategyInstances = array();

    /**
     * Effectue une recherche par les différentes stratégies de moteurs de recherche et leur délègue le parsing du résultat
     * @param string $strSearchedParameters paramètres de recherche
     * @return array données de chaque stratégie, formatées uniformément
     */
    public static function search($strSearchedParameters)
    {
        return self::commonProcess(self::TYPE_SEARCH, $strSearchedParameters);
    }

    /**
     * Effectue une recherche par les différentes stratégies de moteurs de recherche et leur délègue le parsing du résultat
     * @param string $strSearchedParameters paramètres de recherche
     * @return array données de chaque stratégie, formatées uniformément
     */
    public static function suggest($strSearchedParameters)
    {
        return self::commonProcess(self::TYPE_SUGGEST, $strSearchedParameters);
    }

    /**
     * Factorise les processus communs entre suggestions et recherche réelle
     * @param integer $intType type du processus à effectuer
     * @param string $strSearchedParameters paramètres de recherche
     * @return array données de chaque stratégie, formatées uniformément
     */
    protected static function commonProcess($intType, $strSearchedParameters)
    {
        $hashResults = array();
        try {
            $arrayEngines = Config::getValue('registered_engines', array());
        } catch (\ErrorException $e) {
            error_log($e->getMessage());
            $arrayEngines = array();
        }
        foreach ($arrayEngines as $strSEName => $hashSEInfos) {
            if (!isset($hashSEInfos['active']) || !$hashSEInfos['active']) {
                continue;
            }
            $strStrategyName = sprintf('App\Tools\SEStrategy\%sSEStrategy', $strSEName);
            if (!class_exists($strStrategyName)) {
                continue;
            }

            self::$arraySEStrategyInstances[$strSEName] = new $strStrategyName();
            if ($intType === self::TYPE_SEARCH) {
                $hashResults[$strSEName] = self::$arraySEStrategyInstances[$strSEName]->search($strSearchedParameters);
            } else {
                $hashResults[$hashSEInfos['label']] = self::$arraySEStrategyInstances[$strSEName]->suggest($strSearchedParameters);
            }
        }
        return ObserverHandler::applyMHook('result_search', $hashResults, array('type' => $intType));
    }
}
