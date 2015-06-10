<?php

namespace App\Tools\SEStrategy;

use App\Config;

class SearchHandler
{
    /**
     * @var ISEStrategy[]
     */
    protected static $arraySEStrategyInstances = array();

    /**
     * Effectue une recherche par les différentes stratégies de moteurs de recherche et leur délègue le parsing du résultat
     * @param string $strSearchedParameters paramètres de recherche
     * @throws \ErrorException
     * @return array données de chaque stratégie, formatées uniformément
     */
    public static function search($strSearchedParameters)
    {
        $hashResults = array();
        foreach (Config::getValue('registered_engines', array()) as $strSEName => $hashFieldsToFetch) {
            $strStrategyName = sprintf('App\Tools\SEStrategy\%sSEStrategy', $strSEName);
            if (!class_exists($strStrategyName)) {
                continue;
            }

            self::$arraySEStrategyInstances[$strSEName] = new $strStrategyName();
            $hashResults[$strSEName] = self::$arraySEStrategyInstances[$strSEName]->search($strSearchedParameters);
            return $hashResults;
        }
    }
}
