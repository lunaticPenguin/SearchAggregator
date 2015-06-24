<?php

namespace App;
use App\Observers\ObserverHandler;
use App\Tools\CacheStrategy\ICacheStrategy;
use DebugBar\StandardDebugBar;
use PHPixie\Exception\PageNotFound;

/**
 * Pixie dependency container
 */
class Pixie extends \PHPixie\Pixie {

    /**
     * @var \Twig_Environment $view View module
     */
    public $view;

    /**
     * @var ICacheStrategy
     */
    public $cache = null;

    /**
     * @var StandardDebugBar
     */
    public $objDebugBar = null;

    protected function after_bootstrap() {
        // VIEW ENGINE instanciation

        $boolProdEnvironment = Config::getValue('environment', 'prod') === 'prod';
        $this->view = new \Twig_Environment(
            new \Twig_Loader_Filesystem(
                Config::getValue('template_engine_templates_path', '../assets/templates')),
            array(
                'debug'         => !$boolProdEnvironment,//,
                'cache'         => ($boolProdEnvironment ? Config::getValue('template_engine_cache_path', '../cache/templates') : false),
                'optimizations' => 0
            )
        );

        if (!$boolProdEnvironment) {
            $this->view->addExtension(new \Twig_Extension_Debug());
        }

        $strStrategyName = sprintf('App\Tools\CacheStrategy\%sCacheStrategy', Config::getValue('cache', 'PixieSession'));
        if (!class_exists($strStrategyName)) {
            throw new \ErrorException(sprintf('Unable to find %s\'s class', $strStrategyName));
        }
        $this->cache = new $strStrategyName(array('object' => $this->session));

        if (!$boolProdEnvironment) {
            $this->objDebugBar = new StandardDebugBar();
        }

        ObserverHandler::load(Config::getValue('registered_observers', array()));
    }

    /**
     * Intercepte les exceptions pour gérer les erreurs d'une manière relativement douce pour les utilisateurs finaux
     *
     * @param \Exception $exception
     * @throws \Exception
     */
    public function handle_exception($exception)
    {
        // Handle 404 error
        if ($exception instanceof PageNotFound) {
            header(sprintf('Location: %s', $this->router->get('default')->url()));
        } else {
            if (Config::getValue('environment', 'prod') === 'dev') {
                var_dump($exception);
                exit;
            }
        }
    }
}
