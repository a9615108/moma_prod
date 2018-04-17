<?php

namespace Astralweb\Invoicetype\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;

class TaxIdBlockConfigProvider implements ConfigProviderInterface
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfiguration;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
    ) {
        $this->scopeConfiguration = $scopeConfiguration;
    }

    /**
     * @return array() $showHide
     */
    public function getConfig()
    {
		/** @var array() $showHide */
        $showHide = [];
		/** @var boolean $enabled */
        $enabled = $this->scopeConfiguration
			->getValue('checkout/options/onepage_checkout_invoicetype_enabled', ScopeInterface::SCOPE_STORE);
        $showHide['show_hide_taxid_block'] = ($enabled)?true:false;
        return $showHide;
    }
}