<?php
namespace Releva\Retargeting\Shopware\Internal;

class DataHelper extends AbstractHelper {
    
    public function getData ($key = null) {
        $data = $this->getPlugin()->Config()->toArray();
        if ($key === null) {
            return $data;
        } else {
            return array_key_exists($key, $data) ? $data[$key] : null;
        }
    }
    
    public function verifyApiKey ($apiKey, $shopId = null) {
        if ($shopId) {
            $shop = \Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext($shopId)->getShop();
            $routerContext = Shopware()->Front()->Router()->getContext();
            \Shopware()->Front()->Router()->setContext(new \Shopware\Components\Routing\Context($shop->getHost(), $shop->getUrl(), $shop->getSecure()));
            $params = ['callback-url' => \Releva\Retargeting\Shopware\Internal\ShopInfo::getUrlCallback(), ];
            \Shopware()->Front()->Router()->setContext($routerContext);
        } else {
            $params = [];
        }
        try {
            $credentials = \Releva\Retargeting\Base\RelevanzApi::verifyApiKey($apiKey, $params);
            $this->getPlugin()->getMessageBridge()->addInfo('Success', ['method' => __METHOD__, 'apiKey' => $apiKey, 'params' => $params, ]);
            return $credentials->getUserId();
        } catch (\Releva\Retargeting\Base\Exception\RelevanzException $exception) {
            $this->getPlugin()->getMessageBridge()->addError(vsprintf($exception->getMessage(), $exception->getSprintfArgs()), ['method' => __METHOD__, 'code' => $exception->getCode(), 'apiKey' => $apiKey, 'params' => $params, ]);
            throw($exception);
        } catch (\Exception $exception) {
            $this->getPlugin()->getMessageBridge()->addFatal($exception->getMessage(), ['method' => __METHOD__, 'code' => $exception->getCode(), 'apiKey' => $apiKey, 'params' => $params, ]);
            throw($exception);
        }
    }
    
}