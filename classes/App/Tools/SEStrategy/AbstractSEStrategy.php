<?php

namespace App\Tools\SEStrategy;

/**
 * Class AbstractSEStrategy
 * Factorise les comportements communs des différentes stratégies de moteurs de recherche
 * @package App\Tools\SEStrategy
 */
abstract class AbstractSEStrategy implements ISEStrategy
{
    /**
     * Définis le mapping des champs contenant les données parsées à relever pour chaque résultat de moteur de recherche
     * @var array
     */
    protected $hashFieldsMapping = array();

    /**
     * Url de recherche
     * @var string
     */
    protected $strSearchUrl = '';

    /**
     * Nom du paramètre utilisé pour transmettre les critères de la recherche
     * @var string
     */
    protected $strSearchFieldName = '';


    /**
     * Url de recherche des suggestions (autocomplete)
     * @var string
     */
    protected $strSuggestUrl = '';

    /**
     * Noms des paramètres utilisés pour obtenir les suggestions
     * @var array
     */
    protected $arraySuggestFieldNames = array();

    /**
     * Type de la requête HTTP à effectuer (GET|POST|PUT..)
     * @var string
     */
    protected $strQueryType = '';

    /**
     * Initialise les données de la stratégie
     * @return void
     */
    abstract protected function init();

    /**
     * Constructeur de la stratégie. Commentaire assez inutile ma foi.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @inheritdoc
     */
    final public function suggest($strSearchedParameters)
    {
        $hashParameters = $this->arraySuggestFieldNames;
        $hashParameters[$this->strSearchFieldName] = $strSearchedParameters;
        $strUrl = sprintf('%s?%s', $this->strSuggestUrl, http_build_query($hashParameters));
        return $this->parseSuggest($this->doCall($strUrl, $strSearchedParameters));
    }

    /**
     * @inheritdoc
     */
    final public function search($strSearchedParameters)
    {
        $strUrl = sprintf('%s?%s=%s', $this->strSearchUrl, $this->strSearchFieldName, urlencode($strSearchedParameters));
        return $this->parseSearch($this->doCall($strUrl, $strSearchedParameters));
    }

    /**
     * Effectue un appel curl sur le moteur de recherche correspondant
     * @param string $strUrl
     * @param string $strSearchedParameters
     * @return array|string $mixedResult
     */
    private function doCall($strUrl, $strSearchedParameters)
    {
        $resCurl = curl_init();

        $hashCurlParameters = array(
            CURLOPT_CONNECTTIMEOUT  => 5, // timeout de 5 secondes
            CURLOPT_HEADER          => false,
            CURLOPT_RETURNTRANSFER  => true // résultat mis en tampon
        );

        $hashCurlParameters[CURLOPT_URL] = $strUrl;

        curl_setopt_array($resCurl, $hashCurlParameters);

        $mixedResult = curl_exec($resCurl);
        $intError = curl_errno($resCurl);

        if ($intError === 0) {
            $mixedTempResult = json_decode($mixedResult, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $mixedResult = $mixedTempResult;
            }
        } else {
            $mixedResult = '';
        }

        return $mixedResult;
    }
}
