<?php

class Shopware_Plugins_Backend_Relevanz_Bootstrap extends Shopware_Components_Plugin_Bootstrap {

    private $messageBridge;
    
    private $localizationHelper;
    
    private $formHelper;
    
    private $dataHelper;
    
    
    public function getMessageBridge () {
        if ($this->messageBridge === null) {
            $this->messageBridge = new \Releva\Retargeting\Shopware\Internal\MessagesBridge();
        }
        return $this->messageBridge;
    }
    
    public function getLocalizationHelper () {
        if ($this->localizationHelper === null) {
            $this->localizationHelper = new \Releva\Retargeting\Shopware\Internal\LocalizationHelper();
        }
        return $this->localizationHelper;
    }
    
    public function getFormHelper () {
        if ($this->formHelper === null) {
            $this->formHelper = new \Releva\Retargeting\Shopware\Internal\FormHelper();
        }
        return $this->formHelper;
    }
    
    public function getDataHelper () {
        if ($this->dataHelper === null) {
            $this->dataHelper = new \Releva\Retargeting\Shopware\Internal\DataHelper();
        }
        return $this->dataHelper;
    }
    
    /*
     * -------------------------
     * General setup
     * -------------------------
     */

    public function install() {
        $this
            ->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend', 'onEnlightControllerActionPostDispatchSecureFrontend')
            ->subscribeEvent('Theme_Compiler_Collect_Plugin_Javascript', 'onThemeCompilerCollectPluginJavascript')
            ->subscribeEvent('CookieCollector_Collect_Cookies', 'onCookieCollectorCollectCookies')
            ->subscribeEvent('Enlight_Controller_Action_PostDispatch_Backend_Index', 'onEnlightControllerActionPostDispatchBackendIndex')
            ->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_Relevanz', 'onEnlightControllerDispatcherControllerPathBackendRelevanz')
            ->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_Relevanz', 'onEnlightControllerDispatcherControllerPathFrontendRelevanz')
            ->subscribeEvent('Enlight_Controller_Action_PostDispatch_Backend_Relevanz', 'onEnlightControllerActionPostDispatchBackendRelevanz')
            ->subscribeEvent('Shopware_Controllers_Backend_Config_After_Save_Config_Element', 'onShopwareControllersBackendConfigAfterSaveConfigElement')
            ->registerController('Backend', 'Relevanz')
        ;
        if($this->Menu()->findOneBy(array('controller' => 'Relevanz')) === null) {
            $this->createMenuItem(array(
                'label' => 'releva.nz',
                'controller' => 'Relevanz',
                'action' => 'Index',
                'class' => 'sprite-globe',
                'active' => 1,
                'parent' => $this->Menu()->findOneBy(array('label' => 'Marketing')),
            ));
        }
        $this->getFormHelper()->setConfigForm($this->Form());
        $this->addFormTranslations($this->getFormHelper()->getFormTranslations($this->Form(), $this->getLocalizationHelper()));
        return true;
    }

    public function uninstall() {
        return true;
    }

    public function update($oldVersion) {
        $this->install();
        return $this->enable();
    }

    public function enable() {
        return array(
            'success' => true,
            'invalidateCache' => array('config', 'backend', 'proxy', 'theme')
        );
    }

    public function disable() {
        return array(
            'success' => true,
            'invalidateCache' => array('config', 'backend', 'theme')
        );
    }
    
    public function afterInit()
    {
        !file_exists(__DIR__ . '/vendor/autoload.php') || require_once __DIR__ . '/vendor/autoload.php';
        $this->get('Loader')->registerNamespace('Releva\Retargeting\Shopware\Internal', $this->Path().'Internal/');
    }
    
    public function getInfo() {
        return array(
            'version' => '1.1.1',
            'author' => 'releva.nz',
            'label' => 'releva.nz retargeting',
            'description' => '<p style="font-size:12px; font-weight: bold;">releva.nz retargeting<br /><a href="http://www.releva.nz" target="_blank">Not registered yet? Now catch up</a></p>',
            'copyright' => 'Copyright © 2016-2018, releva.nz',
            'support' => 'support@releva.nz',
            'link' => 'http://www.releva.nz',
        );
    }

    public function getVersion() {
        return $this->info->get('version');
    }
    
    public function onEnlightControllerActionPostDispatchSecureFrontend(Enlight_Controller_ActionEventArgs $args) {
        $subject = $args->getSubject();
        $request = $subject->Request();
        $view = $subject->View();
        $view->addTemplateDir($this->Path() . 'Views/');
        if ($request->isXmlHttpRequest()) {
            return;
        }
        $view->baseURLRT = \Releva\Retargeting\Base\RelevanzApi::RELEVANZ_TRACKER_URL . '?t=d&';
        $view->baseURLConv = \Releva\Retargeting\Base\RelevanzApi::RELEVANZ_CONV_URL . 'Netw?';
        $view->CampaignID = $this->getDataHelper()->getData('relevanzUserID');
    }
    
    public function onCookieCollectorCollectCookies(Enlight_Event_EventArgs $event) {
        $collection = new \Shopware\Bundle\CookieBundle\CookieCollection();
        $collection->add(new \Shopware\Bundle\CookieBundle\Structs\CookieStruct(
            'relevanz',
            '/^relevanz/',
            'releva.nz Retargeting',
            Shopware\Bundle\CookieBundle\Structs\CookieGroupStruct::STATISTICS
        ));
        return $collection;
    }

    public function onThemeCompilerCollectPluginJavascript(Enlight_Event_EventArgs $args) {
        return new Doctrine\Common\Collections\ArrayCollection(array(__DIR__ . '/Views/frontend/_public/src/js/relevanz.js'));
    }
    
    public function onEnlightControllerActionPostDispatchBackendRelevanz(Enlight_Event_EventArgs $args) {
        $view = $args->getSubject()->View();
        $view->waveCdnSnippets = json_encode($this->getLocalizationHelper()->readTranslations());
        $view->addTemplateDir($this->Path() . 'Views/');
        $view->extendsTemplate('backend/relevanz/marketing_statistic.js');
        $view->waveConfigJson = json_encode(array(
            'relevanzApiKey' => $this->getDataHelper()->getData('relevanzApiKey'),
        ));
    }

    public function onEnlightControllerActionPostDispatchBackendIndex(Enlight_Event_EventArgs $args) {
        $controller = $args->getSubject();
        $view = $controller->View();
        $view->addTemplateDir($this->Path() . 'Views/');
    }
    
    public function onEnlightControllerDispatcherControllerPathFrontendRelevanz(Enlight_Event_EventArgs $args) {
        return $this->Path() . '/Controllers/Frontend/Relevanz.php';
    }

    public function onEnlightControllerDispatcherControllerPathBackendRelevanz(Enlight_Event_EventArgs $args) {
        $this->Application()->Template()->addTemplateDir($this->Path() . 'Views/');
        return $this->Path() . '/Controllers/Backend/Relevanz.php';
    }
    
    public function onShopwareControllersBackendConfigAfterSaveConfigElement($params) {
        if ($params['element']->getName() == 'relevanzApiKey') {
            $form = $this->Form();
            $relevanzUserElementId = $form->getElement('relevanzUserID')->getId();
            $config = $params['subject']->Request()->getParams();
            foreach ($config['elements'] as $configElement) {
                if ($configElement['name'] === 'relevanzApiKey') {
                    foreach ($configElement['values'] as $configScopeValue) {
                        try {
                            $userId = $this->getDataHelper()->verifyApiKey($configScopeValue['value'], $configScopeValue['shopId']);// saves user-id
                            $sql = "SELECT * FROM `s_core_config_values` WHERE element_id = ? AND shop_id = ?";
                            $result = \Shopware()->Db()->fetchRow($sql, array($relevanzUserElementId, $configScopeValue['shopId']));
                            if (isset($result['id'])) {
                                $sql = "UPDATE `s_core_config_values` SET `value`= ? WHERE id = ?";
                                \Shopware()->Db()->query($sql, array(serialize($userId), $result['id']));
                            } else {
                                $sql = "INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`) VALUES (?, ?, ?)";
                                \Shopware()->Db()->query($sql, array($relevanzUserElementId, $configScopeValue['shopId'], serialize($userId)));
                            }
                        } catch (\Exception $exception) {
                            $sql = "DELETE FROM `s_core_config_values` WHERE element_id = ? AND shop_id = ?";
                            \Shopware()->Db()->query($sql, array($relevanzUserElementId, $configScopeValue['shopId']));
                        }
                    }
                    break;
                }
            }
        }
    }
    
}
