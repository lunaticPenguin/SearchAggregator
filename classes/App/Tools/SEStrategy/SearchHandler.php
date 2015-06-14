<?php

namespace App\Tools\SEStrategy;

use App\Config;

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
     * @throws \ErrorException
     * @return array données de chaque stratégie, formatées uniformément
     */
    public static function search($strSearchedParameters)
    {
        return self::commonProcess(self::TYPE_SEARCH, $strSearchedParameters);
    }

    /**
     * Effectue une recherche par les différentes stratégies de moteurs de recherche et leur délègue le parsing du résultat
     * @param string $strSearchedParameters paramètres de recherche
     * @throws \ErrorException
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
        foreach (Config::getValue('registered_engines', array()) as $strSEName => $hashFieldsToFetch) {
            $strStrategyName = sprintf('App\Tools\SEStrategy\%sSEStrategy', $strSEName);
            if (!class_exists($strStrategyName)) {
                continue;
            }

            self::$arraySEStrategyInstances[$strSEName] = new $strStrategyName();
            $hashResults[$strSEName] =
                $intType === self::TYPE_SEARCH
                    ? self::$arraySEStrategyInstances[$strSEName]->search($strSearchedParameters)
                    : self::$arraySEStrategyInstances[$strSEName]->suggest($strSearchedParameters);

            return $hashResults;
        }
    }
}
