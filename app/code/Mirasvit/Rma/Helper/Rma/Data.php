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



namespace Mirasvit\Rma\Helper\Rma;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Service\Config\RmaNumberConfig $numberConfig,
        \Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->storeFactory         = $storeFactory;
        $this->storeManager         = $storeManager;
        $this->rmaManagement        = $rmaManagement;
        $this->numberConfig         = $numberConfig;
        $this->rmaCollectionFactory = $rmaCollectionFactory;

        parent::__construct($context);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function generateIncrementId(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        $id = $rma->getId();
        $storeId = (string)$rma->getStoreId();

        $format = $this->numberConfig->getFormat();
        $maxLen = $this->numberConfig->getCounterLength();
        if ($this->numberConfig->isResetCounter($storeId)) {
            $rmas = $this->countOrderRmas($rma);
            $counter = $rmas ?: 1;
        } else {
            $counter = $this->numberConfig->getCounterStart() + $id * $this->numberConfig->getCounterStep() - 1;
        }

        if ($maxLen > strlen($counter)) {
            $counter = str_repeat('0', $maxLen - strlen($counter)) . $counter;
        }

        $result = str_replace('[counter]', $counter, $format);
        $result = str_replace('[store]', $storeId, $result);
        $result = str_replace('[order]', $this->rmaManagement->getOrder($rma)->getIncrementId(), $result);

        $collection = $this->rmaCollectionFactory->create()
            ->addFieldToFilter('main_table.increment_id', ['like' => $result . '%']);

        if ($collection->count()) {
            $result .= '-' . ($collection->count() + 1);
        }

        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStoreByOrder($order)
    {
        return ($order) ? $this->storeFactory->create()->load($order->getStoreId()) : $this->storeManager->getStore();
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return int
     */
    protected function countOrderRmas(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->rmaCollectionFactory->create()->addFieldToFilter('order.entity_id', $rma->getOrderId())->count();
    }
}