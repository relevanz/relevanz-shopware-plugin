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

    public function testClientAction() {
        $config = $this->Request()->getParams();
        $snippets = $this->plugin->getLocalizationHelper()->readTranslations();
        $readData = $this->plugin->getDataHelper()->verifyApiKey($config['relevanzApiKey']);
        $userId = $readData['userId'];
        $message = $readData['data']['Message'];
        if ($readData['userId']) {
            if ($userId) {
                $data = array(
                    'Code' => $snippets['ok'],
                    'Message' => $message,
                    'Id' => $userId,
                );
            } else {
                $data = array(
                    'Code' => $snippets['error'],
                    'Message' => $message,
                );
            }
        } else {
            $data = array(
                'Code' => $snippets['error'],
                'Message' => $message,
            );
        }
        
        $this->View()->assign($data);
    }

}
