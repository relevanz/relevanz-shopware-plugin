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
        try {
            $this->plugin->getDataHelper()->verifyApiKey($config['relevanzApiKey']);//throws exception
            $data = array(
                'Code' => $this->plugin->getLocalizationHelper()->translate('successCode'),
                'Message' => $this->plugin->getLocalizationHelper()->translate('successMessage'),
                'Id' => $userId,
            );
        } catch (\Releva\Retargeting\Base\Exception\RelevanzException $exception) {
            $data = array(
                'Code' => $this->plugin->getLocalizationHelper()->translate('errorCode'),
                'Message' => $this->plugin->getLocalizationHelper()->translateRelevanzException($exception).' ('.$exception->getCode().')',
            );
        } catch (\Exception $exception) {
            $userId = null;
            $data = array(
                'Code' => $this->plugin->getLocalizationHelper()->translate('errorCode'),
                'Message' => $exception->getMessage().' ('.$exception->getCode().')',
            );
        }
        $this->View()->assign($data);
    }

}
