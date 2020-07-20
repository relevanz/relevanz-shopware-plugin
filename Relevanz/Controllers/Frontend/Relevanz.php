<?php

class Shopware_Controllers_Frontend_Relevanz extends Enlight_Controller_Action
{
    private const ITEMS_PER_PAGE = 50;
    /**
     * Pre dispatch method
     *
     * Sets the scope
     */
    public function preDispatch()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
    }
    
    /**
     * @todo
     */
    public function productExportAction () {
        
    }
    
    public function callbackAction () {
        $response = new Symfony\Component\HttpFoundation\JsonResponse();
        if (!$this->checkCredentials()) {
            $response->setStatusCode(401)->setData([]);
        } else {
            $shopInfo = new \Releva\Retargeting\Shopware\Internal\ShopInfo();
            $response->setData([
                'plugin-version' => $shopInfo->getPluginVersion(),
                'shop' => ['system' => $shopInfo->getShopSystem(), 'version' => $shopInfo->getShopVersion(), ],
                'environment' => $shopInfo->getServerEnvironment(),
                'callbacks' => [
                    'callback' => ['url' => $shopInfo->getUrlCallback(), 'parameters' => [], ],
                    'export' => [
                        'url' => $shopInfo->getUrlProductExport(),
                        'parameters' => [
                            'format' => ['values' => ['csv', 'json'], 'default' => 'csv', 'optional' => true, ],
                            'page' => ['type' => 'integer', 'optional' => true, 'info' => ['items-per-page' => self::ITEMS_PER_PAGE, ], ],
                        ],
                    ],
                ]
            ]);
        }
        $response->send();
    }
    
    private function checkCredentials() {
        $dataHelper = Shopware()->Container()->get('plugins')->Backend()->Relevanz()->getDataHelper();
        $credentials = new Releva\Retargeting\Base\Credentials($dataHelper->getData('relevanzApiKey'), $dataHelper->getData('relevanzUserID'));
        return $credentials->isComplete() && $credentials->getAuthHash() === $this->Request()->get('auth');
    }

}
