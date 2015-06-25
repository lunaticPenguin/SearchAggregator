<?php

namespace App\Tools\CacheStrategy;

use PHPixie\Session;

/**
 * Class PixieSessionCacheStrategy
 * Cache utilisant la session
 *
 * @package App\Tools\CacheStrategy
 */
class PixieSessionCacheStrategy implements ICacheStrategy
{
    /**
     * @var Session
     */
    protected $objectSession;

    /**
     * Permet de construire la stratégie de cache
     * @param array $hashOptions
     * @throws \ErrorException
     */
    public function __construct(array $hashOptions = array())
    {
        if (session_name() === '') {
            throw new \ErrorException('No current session available. Check for session_start()');
        }

        if (!isset($hashOptions['object'])) {
            throw new \ErrorException('Session object invalid');
        }

        $this->objectSession = $hashOptions['object'];
    }

    /**
     * @inheritdoc
     */
    public function set($strKey, $mixedValue, array $hashOptions = array())
    {
        $hashTemp[$strKey] = $mixedValue;
        $this->objectSession->set('pixie_session_cache', $hashTemp);
    }

    /**
     * @inheritdoc
     */
    public function get($strKey, array $hashOptions = array())
    {
        $hashTemp = $this->objectSession->get('pixie_session_cache', array());
        if (array_key_exists($strKey, $hashTemp)) {
            return $hashTemp[$strKey];
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function flush($strKey = null)
    {
        if ($strKey === null) {
            $this->objectSession->set('pixie_session_cache', array());
        } else {
            $hashTemp = $this->objectSession->get('pixie_session_cache', array());
            if (array_key_exists($strKey, $hashTemp)) {
                unset($hashTemp[$strKey]);
                $this->objectSession->set('pixie_session_cache', $hashTemp);
            }
        }
    }
}
