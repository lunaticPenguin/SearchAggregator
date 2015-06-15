<?php

namespace App\Tools\SEStrategy;

/**
 * Interface ISEStrategy.
 * Cadre le comportement des stratégies dédiées à chaque fonctionnement de chaque moteur de recherche
 * @package App\Tools\SEStrategy
 */
interface ISEStrategy
{
    const FIELD_TITLE       = 0;
    const FIELD_URL         = 1;
    const FIELD_DESCRIPTION = 2;
    const FIELD_SUGGESTION  = 3;

    /**
     * Lance la récupération des suggestions de termes recherchés
     * @param string $strSearchedParameters paramètres de la recherche
     * @return array suggestions des termes, avec résultats formatés
     */
    public function suggest($strSearchedParameters);

    /**
     * Lance la recherche
     * @param string $strSearchedParameters paramètres de la recherche
     * @return array résultat de la recherche, avec résultats formatés
     */
    public function search($strSearchedParameters);

    /**
     * Défini le comportement à effectuer pour parser les résultats d'une recherche
     * @param mixed|array|string $mixedResult résultat du webservice à parser
     * @return mixed
     */
    public function parseSearch($mixedResult);

    /**
     * Défini le comportement à effectuer pour parser les suggestions
     * @param mixed|array|string $mixedResult résultat du webservice à parser
     * @return mixed
     */
    public function parseSuggest($mixedResult);
}
