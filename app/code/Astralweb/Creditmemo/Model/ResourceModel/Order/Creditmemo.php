<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Astralweb\Creditmemo\Model\ResourceModel\Order;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\SalesSequence\Model\Manager;
use Magento\Sales\Model\ResourceModel\Attribute;
use Magento\Sales\Model\ResourceModel\EntityAbstract as SalesResource;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Sales\Model\Spi\CreditmemoResourceInterface;

/**
 * Flat sales order creditmemo resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Creditmemo extends \Magento\Sales\Model\ResourceModel\Order\Creditmemo
{

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $object */
        if (!$object->getOrderId() && $object->getOrder()) {
            $object->setOrderId($object->getOrder()->getId());
            $object->setBillingAddressId($object->getOrder()->getBillingAddress()->getId());
        }

        $creditmemo = $object->getCreditmemo();
        $creditmemoItem = $object->getData('items');
        $product = NULL;
        foreach ($creditmemoItem as $value) {
        if(intval($value->getData('price')) > 0){
                $product = $product.'Sku : '.$value->getData('sku').', Qty : '.$value->getData('qty').', Price : '.$value->getData('price').'||';
            }
        }
        if(isset($product)){
            $object->setProductCustom($product);
        }
        $telephoneBilling =  $object->getOrder()->getBillingAddress()->getData('telephone');
        if($object->getOrder()->getShippingAddress()){
            $telephoneShipping =  $object->getOrder()->getShippingAddress()->getData('telephone');    
        }
        
        if(isset($telephoneBilling)){
            $object->setTelephoneBilling($telephoneBilling);
        }
        if(isset($telephoneShipping)){
            $object->setTelephoneShipping($telephoneShipping);
        }

        return parent::_beforeSave($object);
    }
}
