<?php

namespace Astralweb\ShippingStorePickUp\Model\ResourceModel\shop;


use Magento\Framework\Data\OptionSourceInterface;

class is_active implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Enabled')],
            ['value' => 0, 'label' => __('Disabled')]
        ];
    }
}
