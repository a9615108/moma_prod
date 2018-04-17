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

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException as ModelException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

use Mirasvit\Rma\Model\Rma;
use Mirasvit\Rma\Model\Resolution;

class ResolutionRepository implements \Mirasvit\Rma\Api\Repository\ResolutionRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var Resolution[]
     */
    protected $instances = [];

    public function __construct(
        \Mirasvit\Rma\Model\ResolutionFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Resolution $resolutionResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Model\ResourceModel\Resolution\CollectionFactory $resolutionCollectionFactory,
        \Mirasvit\Rma\Api\Data\ResolutionSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory               = $objectFactory;
        $this->resolutionResource          = $resolutionResource;
        $this->storeManager                = $storeManager;
        $this->resolutionCollectionFactory = $resolutionCollectionFactory;
        $this->searchResultsFactory        = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\ResolutionInterface $resolution)
    {
        $this->resolutionResource->save($resolution);
        return $resolution;
    }

    /**
     * {@inheritdoc}
     */
    public function get($resolutionId)
    {
        if (!isset($this->instances[$resolutionId])) {
            /** @var Resolution $resolution */
            $resolution = $this->objectFactory->create();
            $resolution->load($resolutionId);
            if (!$resolution->getId()) {
                throw NoSuchEntityException::singleField('id', $resolutionId);
            }
            $this->instances[$resolutionId] = $resolution;
        }
        return $this->instances[$resolutionId];
    }

    /**
     * {@inheritdoc}
     */
    public function getByCode($code)
    {
        if (!isset($this->instances[$code])) {
            /** @var \Mirasvit\Rma\Model\Resolution $resolution */
            $resolution = $this->objectFactory->create()->getCollection()
                ->addFieldToFilter('code', $code)
                ->getFirstItem();

            if (!$resolution->getId()) {
                throw NoSuchEntityException::singleField('code', $code);
            }
            $this->instances[$code] = $resolution;
        }
        return $this->instances[$code];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\ResolutionInterface $resolution)
    {
        try {
            $resolutionId = $resolution->getId();
            $this->resolutionResource->delete($resolution);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete resolution with id %1',
                    $resolution->getId()
                ),
                $e
            );
        }
        unset($this->instances[$resolutionId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($resolutionId)
    {
        $resolution = $this->get($resolutionId);
        return  $this->delete($resolution);
    }

    /**
     * Validate resolution process
     *
     * @param  Resolution $resolution
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateResolution(Resolution $resolution)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->resolutionCollectionFactory->create();
    }
}
