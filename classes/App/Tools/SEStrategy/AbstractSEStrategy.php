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
    protected $strUrl = '';

    /**
     * Nom du paramètre utilisé pour transmettre les critères de la recherche
     * @var string
     */
    protected $strSearchFieldName = '';

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
    final public function search($strSearchedParameters)
    {
        // ressource curl
        $resCurl = curl_init();

        $hashCurlParameters = array(
            CURLOPT_CUSTOMREQUEST   => $this->strQueryType,
            CURLOPT_CONNECTTIMEOUT  => 5, // timeout de 5 secondes
            CURLOPT_HEADER          => false,
            CURLOPT_RETURNTRANSFER  => true // résultat mis en tampon
        );

        $strUrl = $this->strUrl;
        switch ($this->strQueryType) {
            default:
            case 'GET':
                $hashCurlParameters[CURLOPT_HTTPGET] = true;
                $strUrl = sprintf('%s?%s=%s', $this->strUrl, $this->strSearchFieldName, urlencode($strSearchedParameters));
                break;
            case 'POST':
                $hashCurlParameters[CURLOPT_POST] = true;
                $hashCurlParameters[CURLOPT_POSTFIELDS] = array($this->strSearchFieldName => $strSearchedParameters);
                break;
        }

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

        return $this->parse($mixedResult);
    }
}
