<?php

namespace Astralweb\TaiXinBank\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Transaction;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Checkout\Model\Cart as CustomerCart;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_stockInterface;
    protected $_objectManager;
    protected $_transaction;
    protected $_eavConfig;

    const MERCHAN_ID = 'payment/firstbank/merchant_id';
    const TERMINAL_ID = 'payment/firstbank/terminal_id';
    const MER_ID = 'payment/firstbank/mer_id';
    const MERCHAN_NAME = 'payment/firstbank/merchant_name';
    const AUTO_CAP = 'payment/firstbank/aut_cap';
    const RETURN_URL = 'payment/firstbank/return_url';
    const CUSTOMIZE = 'payment/firstbank/customize';

    const ORDER_STATUS ='sales_email/order/enabled';
    const ORDER_INDENTIFY ='sales_email/order/identity';
    const ORDER_TEMPLATE ='sales_email/order/template';
    const ORDER_GUEST_TEMPLATE ='sales_email/order/guest_template';
    const ORDER_COPY_TO ='sales_email/order/copy_to';
    const ORDER_COPY_METHOD ='sales_email/order/copy_method';
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Transaction $transaction,
        CustomerCart $cart,
        EavConfig $eavConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectmanager;
        $this->_storeManager = $storeManager;
        $this->_transaction = $transaction;
        $this->_eavConfig = $eavConfig;
        $this->cart = $cart;
    }

    public function getConfig($config){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue($config);
    }
  }
