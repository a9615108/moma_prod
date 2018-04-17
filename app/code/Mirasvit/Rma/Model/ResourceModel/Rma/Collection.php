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



namespace Mirasvit\Rma\Model\ResourceModel\Rma;

/**
 * @method \Mirasvit\Rma\Model\Rma getFirstItem()
 * @method \Mirasvit\Rma\Model\Rma getLastItem()
 * @method \Mirasvit\Rma\Model\ResourceModel\Rma\Collection|\Mirasvit\Rma\Model\Rma[] addFieldToFilter
 * @method \Mirasvit\Rma\Model\ResourceModel\Rma\Collection|\Mirasvit\Rma\Model\Rma[] setOrder
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
        $this->fetchStrategy = $fetchStrategy;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->connection = $connection;
        $this->resource = $resource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\Rma', 'Mirasvit\Rma\Model\ResourceModel\Rma');
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray($emptyOption = false)
    {
        $arr = [];
        if ($emptyOption) {
            $arr[0] = ['value' => 0, 'label' => __('-- Please Select --')];
        }
        /** @var \Mirasvit\Rma\Model\Rma $item */
        foreach ($this as $item) {
            $arr[] = ['value' => $item->getId(), 'label' => $item->getName()];
        }

        return $arr;
    }

    /**
     * @param string|false $emptyOption
     *
     * @return array
     */
    public function getOptionArray($emptyOption = false)
    {
        $arr = [];
        if ($emptyOption) {
            $arr[0] = __('-- Please Select --');
        }
        /** @var \Mirasvit\Rma\Model\Rma $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    /**
     * @param string $storeId
     *
     * @return $this
     */
    public function addStoreIdFilter($storeId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('mst_rma_rma_store')}`
                AS `rma_store_table`
                WHERE main_table.rma_id = rma_store_table.rs_rma_id
                AND rma_store_table.rs_store_id in (?))", [0, $storeId]);

        return $this;
    }

    /**
     * @param string $exchangeOrderId
     *
     * @return $this
     */
    public function addExchangeOrderFilter($exchangeOrderId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('mst_rma_rma_order')}`
                AS `rma_order_table`
                WHERE main_table.rma_id = rma_order_table.re_rma_id
                AND rma_order_table.re_exchange_order_id in (?))", [-1, $exchangeOrderId]);

        return $this;
    }

    /**
     * @param string $creditMemoId
     *
     * @return $this
     */
    public function addCreditMemoFilter($creditMemoId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('mst_rma_rma_creditmemo')}`
                AS `rma_creditmemo_table`
                WHERE main_table.rma_id = rma_creditmemo_table.rc_rma_id
                AND rma_creditmemo_table.rc_credit_memo_id in (?))", [-1, $creditMemoId]);

        return $this;
    }

    /**
     *
     */
    protected function initFields()
    {
        /* @noinspection PhpUnusedLocalVariableInspection */
        $select = $this->getSelect();
        $select->joinLeft(
            ['order' => $this->getTable('sales_order')],
            'main_table.order_id = order.entity_id',
            ['order_increment_id' => 'order.increment_id']
        );
        $select->joinLeft(
            ['status' => $this->getTable('mst_rma_status')],
            'main_table.status_id = status.status_id',
            ['status_name' => 'status.name']
        );
        $select->columns(['name' => new \Zend_Db_Expr("CONCAT(main_table.firstname, ' ', main_table.lastname)")]);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->initFields();
    }

     /************************/
}
