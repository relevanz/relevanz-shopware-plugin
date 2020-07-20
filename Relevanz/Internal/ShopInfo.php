<?php
namespace Releva\Retargeting\Shopware\Internal;
class ShopInfo extends \Releva\Retargeting\Base\AbstractShopInfo {
    
    public static function getShopSystem(): string {
        return 'Shopware';
    }
    
    public static function getShopVersion(): string {
        $release = \Shopware()->Container()->get('kernel')->getRelease();
        return $release['version'].' - r'.$release['revision'];
    }

    public static function getPluginVersion(): string
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
    
    public static function getUrlCallback(): string {
        return \Shopware()->Front()->Router()->assemble(array('module' => 'frontend', 'controller' => 'relevanz', 'action' => 'callback'));
    }

    public static function getUrlProductExport(): string {
        return \Shopware()->Front()->Router()->assemble(array('module' => 'frontend', 'controller' => 'relevanz', 'action' => 'productexport'));
    }

}