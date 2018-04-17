<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   1.1.22
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Helper\Item;

/**
 * Helper which creates different html code
 */
class Html extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);

        $this->itemManagement = $itemManagement;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getItemSku($item)
    {
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        $orderItem = $this->itemManagement->getOrderItem($item);

        return $orderItem->getSku();
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getItemLabel($item)
    {
        $orderItem = $this->itemManagement->getOrderItem($item);
        $name = $orderItem->getName();
        $options = $this->getItemOptions($orderItem);
        if (count($options)) {
            $name .= ' (';
            foreach ($options as $option) {
                $name .= $option['label'] . ': ' . $option['value'] . ', ';
            }
            $name = substr($name, 0, -2); //remove last ,
            $name .= ')';
        }

        return $name;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return array
     */
    protected function getItemOptions($item)
    {
        $result = [];
        if ($options = $item->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

}