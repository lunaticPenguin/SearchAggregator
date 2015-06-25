<?php

namespace App\Tools\CacheStrategy;

interface ICacheStrategy
{
    /**
     * Permet de construire la stratégie de cache
     * @param array $hashOptions
     * @throws \ErrorException
     */
    public function __construct(array $hashOptions = array());

    /**
     * Assigne une valeur en cache
     * @param string $strKey
     * @param mixed $mixedValue
     * @param array $hashOptions
     *
     * @throws \ErrorException
     *
     * @return mixed
     */
    public function set($strKey, $mixedValue, array $hashOptions = array());

    /**
     * Renvoit une donnée stockée en mémoire
     * @param string $strKey
     * @param array $hashOptions = array()
     * @return mixed|false on failure
     */
    public function get($strKey, array $hashOptions = array());

    /**
     * Permet de vider les données en cache. Présence d'une clé pour supprimer au cas par cas
     * @param string $strKey
     */
    public function flush($strKey = null);
}
