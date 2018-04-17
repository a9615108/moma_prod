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



namespace Mirasvit\Rma\Model\Config\Source\Order;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        \Magento\Sales\Model\Order\ConfigFactory $orderConfigFactory,
        \Magento\Framework\Model\Context $context
    ) {
        $this->orderConfigFactory = $orderConfigFactory;
        $this->context            = $context;
    }

    /**
     * @var array
     */
    protected $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];
            $statuses = $this->orderConfigFactory->create()->getStatuses();
            foreach ($statuses as $id => $status) {
                $this->options[] = ['value' => $id, 'label' => $status];
            }
        }

        return $this->options;
    }
}
