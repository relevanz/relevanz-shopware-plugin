<?php
namespace Releva\Retargeting\Shopware\Internal;

abstract class AbstractHelper {
    
    protected function getPlugin () {
        return \Shopware()->Container()->get('plugins')->Backend()->Relevanz();
    }
    
}
