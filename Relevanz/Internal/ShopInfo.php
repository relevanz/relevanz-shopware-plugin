<?php
namespace Releva\Retargeting\Shopware\Internal;
class ShopInfo extends \Releva\Retargeting\Base\AbstractShopInfo {
    
    public static function getShopSystem(): string {
        return 'Shopware';
    }
    
    public static function getShopVersion(): string {
        $release = Shopware()->Container()->get('kernel')->getRelease();
        return $release['version'].' - r'.$release['revision'];
    }

    public static function getPluginVersion(): string
    {
        return Shopware()->Container()->get('plugins')->Backend()->Relevanz()->getInfo()['version'];
    }
    
    public static function getDbVersion() {
        $result = Shopware()->Db()->fetchRow("SELECT @@version AS `version`, @@version_comment AS `server`;");
        return [
            'version' => $result['version'],
            'server' => $result['server'],
        ];
    }
    
    /**
     * @todo actually works only in frontend
     */
    public static function getUrlCallback(): string {
        return Shopware()->Front()->Router()->assemble(array('controller' => 'relevanz', 'action' => 'callback'));
    }

    /**
     * @todo actually works only in frontend
     */
    public static function getUrlProductExport(): string {
        return Shopware()->Front()->Router()->assemble(array('controller' => 'relevanz', 'action' => 'productexport'));
    }

}