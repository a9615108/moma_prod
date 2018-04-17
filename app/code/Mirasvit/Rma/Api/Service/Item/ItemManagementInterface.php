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



namespace Mirasvit\Rma\Api\Service\Item;


interface ItemManagementInterface
{
    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return \Magento\Sales\Api\Data\OrderItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderItem(\Mirasvit\Rma\Api\Data\ItemInterface $item);

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return bool
     */
    public function isRefund(\Mirasvit\Rma\Api\Data\ItemInterface $item);

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return bool
     */
    public function isExchange(\Mirasvit\Rma\Api\Data\ItemInterface $item);

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return bool
     */
    public function isCredit(\Mirasvit\Rma\Api\Data\ItemInterface $item);

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getResolutionName(\Mirasvit\Rma\Api\Data\ItemInterface $item);

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getReasonName(\Mirasvit\Rma\Api\Data\ItemInterface $item);

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getConditionName(\Mirasvit\Rma\Api\Data\ItemInterface $item);
}