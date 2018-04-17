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



namespace Mirasvit\Rma\Api\Service\Order;

use \Magento\Sales\Api\Data\OrderInterface;

interface OrderManagementInterface
{
    /**
     * @param \Magento\Customer\Model\Customer|false $customer
     * @return OrderInterface[]
     */
    public function getAllowedOrderList(\Magento\Customer\Model\Customer $customer);

    /**
     * @param OrderInterface|int $order
     *
     * @return bool
     */
    public function isReturnAllowed($order);

}