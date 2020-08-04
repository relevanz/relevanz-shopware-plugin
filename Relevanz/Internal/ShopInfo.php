<?php
namespace Releva\Retargeting\Shopware\Internal;
class ShopInfo extends \Releva\Retargeting\Base\AbstractShopInfo {
    
    public static function getShopSystem() {
        return 'Shopware';
    }
    
    public static function getShopVersion() {
        if (method_exists(\Shopware()->Container()->get('kernel'), 'getRelease')) {
            $release = \Shopware()->Container()->get('kernel')->getRelease();
            return $release['version'].' - r'.$release['revision'];
        } else {//older shopware
            return \Shopware\Kernel::VERSION.' - r'.\Shopware\Kernel::REVISION;
        }
    }

    public static function getPluginVersion()
    {
        return \Shopware()->Container()->get('plugins')->Backend()->Relevanz()->getVersion();
    }
    
    public static function getDbVersion() {
        $result = \Shopware()->Db()->fetchRow("SELECT @@version AS `version`, @@version_comment AS `server`;");
        return [
            'version' => $result['version'],
            'server' => $result['server'],
        ];
    }
    
    public static function getUrlCallback() {
        return \Shopware()->Front()->Router()->assemble(array('module' => 'frontend', 'controller' => 'relevanz', 'action' => 'callback'));
    }

    public static function getUrlProductExport() {
        return \Shopware()->Front()->Router()->assemble(array('module' => 'frontend', 'controller' => 'relevanz', 'action' => 'productexport'));
    }

}