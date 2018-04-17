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


namespace Mirasvit\Rma\Service\Item;

/**
 *  We put here only methods directly connected with Item properties
 */
class ItemManagement implements \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface
{
    public function __construct(
        \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository,
        \Mirasvit\Rma\Api\Repository\ResolutionRepositoryInterface $resolutionRepository,
        \Mirasvit\Rma\Api\Repository\ReasonRepositoryInterface $reasonRepository,
        \Mirasvit\Rma\Api\Repository\ConditionRepositoryInterface $conditionRepository,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->itemRepository       = $itemRepository;
        $this->resolutionRepository = $resolutionRepository;
        $this->reasonRepository     = $reasonRepository;
        $this->conditionRepository  = $conditionRepository;
        $this->rmaManagement        = $rmaManagement;
        $this->orderItemRepository  = $orderItemRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderItem(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->orderItemRepository->get($item->getOrderItemId());
    }

    /**
     * {@inheritdoc}
     */
    public function isRefund(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $resolution = $this->resolutionRepository->getByCode(\Mirasvit\Rma\Api\Data\ResolutionInterface::REFUND);

        return $item->getResolutionId() == $resolution->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function isExchange(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $resolution = $this->resolutionRepository->getByCode(\Mirasvit\Rma\Api\Data\ResolutionInterface::EXCHANGE);

        return $item->getResolutionId() == $resolution->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function isCredit(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $resolution = $this->resolutionRepository->getByCode(\Mirasvit\Rma\Api\Data\ResolutionInterface::CREDIT);

        return $item->getResolutionId() == $resolution->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getResolutionName(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        if ($item->getResolutionId()) {
            return $this->resolutionRepository->get($item->getResolutionId())->getName();
        } else {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonName(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        if ($item->getReasonId()) {
            return $this->reasonRepository->get($item->getReasonId())->getName();
        } else {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionName(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        if ($item->getConditionId()) {
            return $this->conditionRepository->get($item->getConditionId())->getName();
        } else {
            return '';
        }
    }
}