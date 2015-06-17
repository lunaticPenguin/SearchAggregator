<?php

namespace App;

use PHPixie\Controller;

/**
 * Base controller
 *
 * @property-read \App\Pixie $pixie Pixie dependency container
 */
class Page extends Controller {

    /**
     * @var \Twig_Environment
     */
    protected $view;

    protected $strControllerName = '';
    protected $strActionName = '';
    protected $strCustomTemplate = '';

    protected $hashViewVariables = array();

    /**
     * @inheritdoc
     */
    public function before() {
        $this->view = $this->pixie->view;
        $this->strControllerName    = strtolower($this->request->param('controller'));
        $this->strActionName        = strtolower($this->request->param('action'));

        $this->hashViewVariables['title'] = ucfirst(strtolower($this->strControllerName));
        $hashRegisteredEngines = Config::getValue('registered_engines', array());
        if (count($this->pixie->session->get('paging', array())) === 0) {
            $hashPagingInfos = array();
            foreach ($hashRegisteredEngines as $strSearchEngine => $hashSEInfos) {
                if (isset($hashSEInfos['active']) && $hashSEInfos['active']) {
                    $hashPagingInfos[$strSearchEngine] = 1; // page 1 pour chaque moteur
                }
            }
            $this->pixie->session->set('paging', $hashPagingInfos);
        }

        $strEngine = $this->request->get('engine', '');
        $hashPagingInfos = $this->pixie->session->get('paging');
        if (!array_key_exists($strEngine, $hashPagingInfos)) {
            $strEngine = current(array_keys($hashPagingInfos));
        }
        $this->pixie->session->set('engine', $strEngine);
        $this->hashViewVariables['current_engine'] = $strEngine; // résultats du moteur en cours de visualisation

        $intPage = (int) $this->request->get('p', $this->pixie->session->get('paging')[$strEngine]);
        if ($intPage <= 0) {
            $intPage = 1;
        }
        $this->pixie->session->set('page', $intPage);


        $this->hashViewVariables['hashRegisteredEngines'] = $hashRegisteredEngines;

        if ($this->request->is_ajax()) {
            header('Content-type: application/json');
            $this->setCustomTemplate('global/ajax_call.html.twig');
        }
    }

    /**
     * @inheritdoc
     */
    public function after()
    {
        $strPathToTemplate = !empty($this->strCustomTemplate)
            ? $this->strCustomTemplate
            : sprintf('%s/%s.html.twig', $this->strControllerName, $this->strActionName);
        $this->response->body = $this->view->render($strPathToTemplate, $this->hashViewVariables);
    }

    /**
     * Allows to set a custom template path in order to render it.
     * This path must be valid
     * @param string $strPathToCustomTemplate
     */
    protected function setCustomTemplate($strPathToCustomTemplate)
    {
        $this->strCustomTemplate = $strPathToCustomTemplate;
    }
}
