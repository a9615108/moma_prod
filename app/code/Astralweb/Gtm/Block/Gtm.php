<?php

namespace Astralweb\Gtm\Block;

class Gtm extends \Magento\Framework\View\Element\Template {

    const HEADER_SCRIPT = 'astralweb_gtm/general/header_script';
    const BODY_SCRIPT = 'astralweb_gtm/general/body_script';

    public function getHeaderScript(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue(self::HEADER_SCRIPT, $storeScope);
    }

    public function getBodyScript(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue(self::BODY_SCRIPT, $storeScope);
    }
}