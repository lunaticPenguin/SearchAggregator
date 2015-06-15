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
        $this->hashViewVariables['hashRegisteredEngines'] = Config::getValue('registered_engines', array());

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
