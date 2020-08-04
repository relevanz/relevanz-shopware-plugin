<?php

use Releva\Retargeting\Shopware\Internal\ProductExporter;

class Shopware_Controllers_Frontend_Relevanz extends Enlight_Controller_Action
{
    const ITEMS_PER_PAGE = 50;
    
    public function preDispatch()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
    }
    
    public function productExportAction () {
        if ($this->checkCredentials()) {
            $request = $this->Request();
            $page = (int) $request->get('page') < 1 ? null : (int) $request->get('page') - 1;
            $context = $this->container->get('shopware_storefront.context_service')->getShopContext();
            $criteria = $this->container->get('shopware_search.store_front_criteria_factory')->createListingCriteria($this->Request(), $context);
            try {
                $productExporter = new ProductExporter();
                $exporter = $productExporter->export($context, $criteria,
                    $request->get('format') === 'json' ? ProductExporter::FORMAT_JSON : ProductExporter::FORMAT_CSV,
                    $page === null ? null : self::ITEMS_PER_PAGE,
                    $page === null ? 0 : $page * self::ITEMS_PER_PAGE
                );
                $this->setShopwareCompatibilityResponse(200, $exporter->getContents(), $exporter->getHttpHeaders());
            } catch (\Exception $exception) {
                $this->setShopwareCompatibilityResponse($exception instanceof \Releva\Retargeting\Base\Exception\RelevanzException && $exception->getCode() === 1585554289 ? 400 : 500);
            }
        }
    }
    
    public function callbackAction () {
        if ($this->checkCredentials()) {
            $shopInfo = new \Releva\Retargeting\Shopware\Internal\ShopInfo();
            $this->setShopwareCompatibilityResponse(200, json_encode([
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
            ], JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION), array('Content-Type' => 'application/json; charset="utf-8"'));
        }
    }
    
    private function checkCredentials() {
        $dataHelper = Shopware()->Container()->get('plugins')->Backend()->Relevanz()->getDataHelper();
        $credentials = new Releva\Retargeting\Base\Credentials($dataHelper->getData('relevanzApiKey'), $dataHelper->getData('relevanzUserID'));
        if ($credentials->isComplete() && $credentials->getAuthHash() === $this->Request()->get('auth')) {
            return true;
        } else {
            $this->setShopwareCompatibilityResponse(401);
            Shopware()->Container()->get('plugins')->Backend()->Relevanz()->getMessageBridge()->addError('Wrong Credentials', array('code' => 1595659947, 'auth' => $this->Request()->get('auth')));
            return false;
        }
    }
    
    private function setShopwareCompatibilityResponse ($status, $content = '', $headers = array()) {
        if ($this->Response() instanceof \Symfony\Component\HttpFoundation\Response) {
            $this->Response()->setStatusCode($status)->setContent($content);
        } else {
            $this->Response()->setHttpResponseCode($status)->setBody($content);
        }
        foreach ($headers as $key => $value) {
            $this->Response()->setHeader($key, $value);
        }
        return $this;
    }
    
}
