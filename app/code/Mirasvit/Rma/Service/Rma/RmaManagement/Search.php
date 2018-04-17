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


namespace Mirasvit\Rma\Service\Rma\RmaManagement;

use Mirasvit\Rma\Api\Data\RmaInterface;

/**
 *  We put here only methods directly connected with RMA properties
 */
class Search implements \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
{
    public function __construct(
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Mirasvit\Rma\Api\Repository\MessageRepositoryInterface $messageRepository,
        \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository,
        \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->messageRepository     = $messageRepository;
        $this->itemRepository        = $itemRepository;
        $this->attachmentManagement  = $attachmentManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder         = $filterBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(RmaInterface $rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
        ;
        return $this->itemRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaItemsByOrderItem($orderItemId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_item_id', $orderItemId)
            ->addFilter('qty_requested', 0, 'gt')
        ;

        return $this->itemRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestedItems(RmaInterface $rma)
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('qty_requested')
                    ->setConditionType('gt')
                    ->setValue(0)
                    ->create(),
            ]
        )->addFilters(
            [
                $this->filterBuilder
                    ->setField('rma_id')
                    ->setValue($rma->getId())
                    ->create(),
            ]
        );

        return $this->itemRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages(RmaInterface $rma)
    {
        $order = $this->sortOrderBuilder
            ->setField('message_id')
            ->setDirection(\Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
            ->addSortOrder($order)
        ;

        return $this->messageRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastMessage(RmaInterface $rma)
    {
        $messages = $this->getMessages($rma);

        return array_pop($messages);
    }

    /**
     * {@inheritdoc}
     */
    public function getRead(RmaInterface $rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
            ->addFilter('is_read', 1)
        ;

        return $this->messageRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getUnread(RmaInterface $rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
            ->addFilter('is_read', 0)
        ;

        return $this->messageRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function hasRmaReason(RmaInterface $rma, $reasonId)
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('rma_id')
                    ->setValue($rma->getId())
                    ->create(),
            ]
        )->addFilters(
            [
                $this->filterBuilder
                    ->setField('main_table.reason_id')
                    ->setValue($reasonId)
                    ->create(),
            ]
        );

        return $this->itemRepository->getList($searchCriteria->create())->getTotalCount();
    }

    /**
     * {@inheritdoc}
     */
    public function hasRmaCondition(RmaInterface $rma, $conditionId)
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('rma_id')
                    ->setValue($rma->getId())
                    ->create(),
            ]
        )->addFilters(
            [
                $this->filterBuilder
                    ->setField('main_table.condition_id')
                    ->setValue($conditionId)
                    ->create(),
            ]
        );

        return $this->itemRepository->getList($searchCriteria->create())->getTotalCount();
    }

    /**
     * {@inheritdoc}
     */
    public function hasRmaResolution(RmaInterface $rma, $resolutionId)
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('rma_id')
                    ->setValue($rma->getId())
                    ->create(),
            ]
        )->addFilters(
            [
                $this->filterBuilder
                    ->setField('main_table.resolution_id')
                    ->setValue($resolutionId)
                    ->create(),
            ]
        );

        return $this->itemRepository->getList($searchCriteria->create())->getTotalCount();
    }
}

