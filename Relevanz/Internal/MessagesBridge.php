<?php
namespace Releva\Retargeting\Shopware\Internal;

class MessagesBridge {
    
    /**
     * @var \Shopware\Components\Logger
     */
    private $logger;
    
    public function getLogger () {
        if ($this->logger === null) {
            $this->logger = \Shopware()->Container()->get('pluginlogger')->withName('relvanzRetargeting');
        }
        return $this->logger;
    }
    
    public function addError($message, $context = array()) {
        $this->getLogger()->addError($message, $context);
    }
    
    public function addInfo($message, $context = array()) {
        $this->getLogger()->addInfo($message, $context);
        return $this;
    }
    
    public function addFatal ($message, $context) {
        $this->getLogger()->addCritical($message, $context);
        return $this;
    }
}