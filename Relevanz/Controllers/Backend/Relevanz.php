<?php

class Shopware_Controllers_Backend_Relevanz extends Shopware_Controllers_Backend_Application {

    /**
     * @var Shopware_Plugins_Backend_Relevanz_Bootstrap $plugin
     */
    private $plugin;

    /**
     * @var Enlight_Components_Session_Namespace $session
     */
    private $session;

    /**
     * {@inheritdoc}
     */
    public function init() {
        $this->plugin = Shopware()->Container()->get('plugins')->Backend()->Relevanz();
    }

    /**
     * Returns if the current user is logged in
     *
     * @return bool
     */
    public function isUserLoggedIn() {
        return (isset($this->session->sUserId) && !empty($this->session->sUserId));
    }

    /**
     * Index action method.
     *
     * Forwards to correct the action.
     */
    public function indexAction() {
        
    }

    public function loadAction() {
        
    }

    public function requestretargetingdataAction() {
        exit;
    }

    public function saveFormAction() {
        $relevanzApiKey = $this->Request()->getParam('relevanzApiKey', '');
        $relevanzBudget = $this->Request()->getParam('relevanzBudget', 0);
        $data = $this->plugin->getUserIdAction($relevanzApiKey, $relevanzBudget);
        $this->View()->assign($data);
    }

    public function testClientAction() {
        $config = $this->Request()->getParams();
        $data = $this->plugin->getUserIdAction($config['relevanzApiKey']);
        $this->View()->assign($data);
    }

}
