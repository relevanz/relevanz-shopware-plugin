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
        $snippets = $this->getPlugin()->getLocalizationHelper()->readTranslations();
        if ($apiKey) {
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
                $userId = $credentials->getUserId();
                $data = array(
                    'Code' => $snippets['ok'],
                    'Message' => $snippets['succesfullySaveData'],
                    'Id' => $userId,
                );
            } catch (\Releva\Retargeting\Base\Exception\RelevanzException $exception) {//@todo use translations
                $userId = null;
                $data = array(
                    'Code' => __LINE__,
                    'Message' => vsprintf($exception->getMessage(), $exception->getSprintfArgs()),
                );
            } catch (\Exception $exception) {
                $userId = null;
                $data = array(
                    'Code' => $exception->getCode(),
                    'Message' => $exception->getMessage(),
                );
            }
        } else {
            $userId = null;
            $data = array(
                'Code' => $snippets['error'],
                'Message' => $snippets['messageApiKeyCanNotBeEmpty'],
                'Id' => $userId,
            );
        }
        if ($shopId !== null) {
            $form = $this->getPlugin()->Form();
            $relevanzUserElementId = $form->getElement('relevanzUserID')->getId();
            if ($userId === null) {
                $sql = "DELETE FROM `s_core_config_values` WHERE element_id = ? AND shop_id = ?";
                \Shopware()->Db()->query($sql, array($relevanzUserElementId, $shopId));
            } else {
                $sql = "SELECT * FROM `s_core_config_values` WHERE element_id = ? AND shop_id = ?";
                $result = \Shopware()->Db()->fetchRow($sql, array($relevanzUserElementId, $shopId));
                if (isset($result['id'])) {
                    $sql = "UPDATE `s_core_config_values` SET `value`= ? WHERE id = ?";
                    \Shopware()->Db()->query($sql, array(serialize($userId), $result['id']));
                } else {
                    $sql = "INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`) VALUES (?, ?, ?)";
                    \Shopware()->Db()->query($sql, array($relevanzUserElementId, $shopId, serialize($userId)));
                }
            }
        }
        return array(
            'userId' => $userId,
            'data' => $data,
        );
        
    }
    
}