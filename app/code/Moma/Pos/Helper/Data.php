<?php

namespace Moma\Pos\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_posfactory;
    protected $_orderCollectionFactory;

    const XML_PATH_POS_USER = 'moma_pos_section/general/user';
    const XML_PATH_POS_TOKEN = 'moma_pos_section/general/token';
    const XML_PATH_POS_METHOD = 'moma_pos_section/general/method';

    public function __construct(Context $context, \Moma\Pos\Model\PosFactory $posfactory, \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory)
    {
        $this->_posfactory =  $posfactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    public function saveOrderPOS() {
        $orderPaymentTaixinCollection = $this->_orderCollectionFactory->create()
            ->join(
                array('invoice' => 'sales_invoice'),
                'main_table.entity_id=invoice.order_id',
                array('invoiced_id' => 'entity_id', 'order_id' => 'order_id')
            );
        $orderPaymentTaixinCollection->addFieldToSelect('increment_id');
        foreach ($orderPaymentTaixinCollection as $taixin_item) {
            $pos_model_taixin = $this->_posfactory->create()->load($taixin_item->getData('increment_id'), 'order_id');
            if (!$pos_model_taixin->getData()) {
                $pos_model_taixin->setData('order_id', $taixin_item->getData('increment_id'));
                $pos_model_taixin->save();
            }
        }
    }

    public function getPosUser(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_POS_USER, $storeScope);
    }

    public function getPosToken(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_POS_TOKEN, $storeScope);
    }

    public function getPosMethod(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_POS_METHOD, $storeScope);
    }

}
