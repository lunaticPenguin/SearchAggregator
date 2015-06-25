<?php

namespace App\Tools\CacheStrategy;

/**
 * Class MemCacheCacheStrategy
 * Cache utilisant la technologie MemCache
 * @package App\Tools\CacheStrategy
 */
class MemCacheCacheStrategy implements ICacheStrategy
{
    /**
     * @var \Memcache
     */
    private $objMemCache = null;

    /**
     * @inheritdoc
     */
    public function __construct(array $hashOptions = array())
    {
        $this->objMemCache = new \Memcache();
        $strIPHost = isset($hashOptions['host']) ? $hashOptions['host'] : '127.0.0.1';
        $strIPPort = isset($hashOptions['port']) ? $hashOptions['port'] : '11211';
        if (!$this->objMemCache->connect($strIPHost, $strIPPort)) {
            throw new \ErrorException('Unable to connect mem_cache server.');
        }
    }

    /**
     * @inheritdoc
     */
    public function set($strKey, $mixedValue, array $hashOptions = array())
    {
        $mixedFlag      = (int) isset($hashOptions['flag']) ? $hashOptions['flag'] : false;
        $intDuration    = (int) isset($hashOptions['duration']) ? $hashOptions['duration'] : 600;
        if (!$this->objMemCache->set($strKey, $mixedValue, $mixedFlag, $intDuration)) {
            throw new \ErrorException('Unable to set data to mem_cache server.');
        }
    }

    /**
     * @inheritdoc
     */
    public function get($strKey, array $hashOptions = array())
    {
        $mixedFlag = (int) isset($hashOptions['flag']) ? $hashOptions['flag'] : false;
        return $this->objMemCache->get($strKey, $mixedFlag);
    }

    /**
     * @inheritdoc
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
