<?php

namespace App\Tools;

/**
 * Memcache wrapper
 */
class MemCache
{
    /**
     * @var \Memcache
     */
    private $objMemCache = null;

    /**
     * @var MemCache
     */
    private static $objInstance = null;

    /**
     * Returns Cache instance
     * @return \Memcache
     */
    public static function getInstance()
    {
        if (self::$objInstance === null) {
            self::$objInstance = new self();
        }
        return self::$objInstance;
    }

    /**
     * Instancie l'object MemCache interne
     * @throws \ErrorException
     */
    protected function __construct()
    {
        $this->objMemCache = new \Memcache();
        if (!$this->objMemCache->connect('127.0.0.1', '11211')) {
            throw new \ErrorException('Unable to connect mem_cache server.');
        }
    }

    /**
     * Stocke une valeur dans le serveur MemCache
     *
     * @param string $strKey clé correspondant à la valeur à stocker
     * @param mixed $mixedValue valeur à stocker
     * @param mixed $mixedFlag flag de compression zlib
     * @param int $intDuration durée de validité du stockage (sec). 0 <=> période indéfinie
     * @throws \ErrorException
     *
     * @see \MemCache::set()
     */
    public function set($strKey, $mixedValue, $mixedFlag = false, $intDuration = 600)
    {
        if (!$this->objMemCache->set($strKey, $mixedValue, $mixedFlag, $intDuration)) {
            throw new \ErrorException('Unable to set data to mem_cache server.');
        }
    }

    /**
     * Retourne la valeur attaché a la clé
     *
     * @param string $strKey
     * @param mixed $mixedFlag flag de compression zlib
     * @return mixed|false en cas de failure
     *
     * @see \Memcache::get()
     */
    public function get($strKey, $mixedFlag = false)
    {
        return $this->objMemCache->get($strKey, $mixedFlag);
    }

    /**
     * Efface tout ou une partie des données stockées
     * @param string|null $strKey efface la donnée correspondant à la clé, ou tout si la clé vaut null
     *
     * @see \MemCache::flush(), \MemCache::delete()
     */
    public function flush($strKey = null)
    {
        if ($strKey === null) {
            $this->objMemCache->flush();
        } else {
            $this->objMemCache->delete($strKey);
        }
    }
}
