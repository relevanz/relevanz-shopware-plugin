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
        $credentials = \Releva\Retargeting\Base\RelevanzApi::verifyApiKey($apiKey, $params);
        return $credentials->getUserId();
    }
    
}