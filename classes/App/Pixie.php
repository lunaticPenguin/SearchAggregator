<?php

namespace App;
use PHPixie\Exception\PageNotFound;

/**
 * Pixie dependency container
 */
class Pixie extends \PHPixie\Pixie {

    /**
     * @var \Twig_Environment $view View module
     */
    public $view;

    protected function after_bootstrap() {
        // VIEW ENGINE instanciation
        $this->view = new \Twig_Environment(
            new \Twig_Loader_Filesystem(
                Config::getValue('template_engine_templates_path', '../assets/templates')),
            array(
                'cache' => (Config::getValue('environment', 'prod') !== 'dev' ? Config::getValue('template_engine_cache_path', '../cache/templates') : false),
                'optimizations' => 0
            )
        );
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
        }
    }
}
