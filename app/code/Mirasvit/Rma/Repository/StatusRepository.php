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



namespace Mirasvit\Rma\Repository;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Mirasvit\Rma\Model\Rma;
use Mirasvit\Rma\Model\Status;

class StatusRepository implements \Mirasvit\Rma\Api\Repository\StatusRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var Status[]
     */
    protected $instances = [];

    public function __construct(
        \Mirasvit\Rma\Model\StatusFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Status $statusResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Api\Data\StatusSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory        = $objectFactory;
        $this->statusResource       = $statusResource;
        $this->storeManager         = $storeManager;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\StatusInterface $status)
    {
        $this->statusResource->save($status);
        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public function get($statusId)
    {
        if (!isset($this->instances[$statusId])) {
            /** @var Status $status */
            $status = $this->objectFactory->create();
            $status->load($statusId);
            if (!$status->getId()) {
                throw NoSuchEntityException::singleField('id', $statusId);
            }
            $this->instances[$statusId] = $status;
        }
        return $this->instances[$statusId];
    }

    /**
     * {@inheritdoc}
     */
    public function getByCode($code)
    {
        if (!isset($this->instances[$code])) {
            /** @var Rma $rma */
            $rma = $this->objectFactory->create()->getCollection()
                ->addFieldToFilter('code', $code)
                ->getFirstItem();

            if (!$rma->getId()) {
                throw NoSuchEntityException::singleField('code', $code);
            }
            $this->instances[$code] = $rma;
        }
        return $this->instances[$code];
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerMessageForStore($status, $storeId)
    {
        $customerMessages = $status->getCustomerMessage();

        return isset($customerMessages[$storeId]) ? $customerMessages[$storeId] : $customerMessages[0];
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminMessageForStore($status, $storeId)
    {
        $adminMessages = $status->getAdminMessage();

        return isset($adminMessages[$storeId]) ? $adminMessages[$storeId] : $adminMessages[0];
    }

    /**
     * {@inheritdoc}
     */
    public function getHistoryMessageForStore($status, $storeId)
    {
        $historyMessages = $status->getHistoryMessage();

        return isset($historyMessages[$storeId]) ? $historyMessages[$storeId] : $historyMessages[0];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\StatusInterface $status)
    {
        try {
            $statusId = $status->getId();
            $this->statusResource->delete($status);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete status with id %1',
                    $status->getId()
                ),
                $e
            );
        }
        unset($this->instances[$statusId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($statusId)
    {
        $status = $this->get($statusId);
        return  $this->delete($status);
    }

    /**
     * Validate status process
     *
     * @param  Status $status
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateStatus(Status $status)
    {

    }
}
