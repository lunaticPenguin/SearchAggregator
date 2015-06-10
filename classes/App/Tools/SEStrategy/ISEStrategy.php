<?php

namespace App\Tools\SEStrategy;

/**
 * Interface ISEStrategy.
 * Cadre le comportement des stratégies dédiées à chaque fonctionnement de chaque moteur de recherche
 * @package App\Tools\SEStrategy
 */
interface ISEStrategy
{
    const FIELD_TITLE = 0;
    const FIELD_URL = 1;
    const FIELD_DESCRIPTION = 2;


    /**
     * Lance l'éxecution de la recherche
     * @param string $strSearchedParameters paramètres de la recherche
     * @return array résultat de l'exécution de la recherche, avec résultats formatés
     */
    public function search($strSearchedParameters);

    /**
     * Défini le comportement à effectuer pour parser les résultats d'une recherche
     * @param mixed|array|string $mixedResult résultat du webservice à parser
     * @return mixed
     */
    public function parse($mixedResult);
}
