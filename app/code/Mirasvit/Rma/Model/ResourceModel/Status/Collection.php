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



namespace Mirasvit\Rma\Model\ResourceModel\Status;

/**
 * @method \Mirasvit\Rma\Model\Status getFirstItem()
 * @method \Mirasvit\Rma\Model\Status getLastItem()
 * @method \Mirasvit\Rma\Model\ResourceModel\Status\Collection|\Mirasvit\Rma\Model\Status[] addFieldToFilter
 * @method \Mirasvit\Rma\Model\ResourceModel\Status\Collection|\Mirasvit\Rma\Model\Status[] setOrder
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\Status', 'Mirasvit\Rma\Model\ResourceModel\Status');
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
        /** @var \Mirasvit\Rma\Model\Status $item */
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
        /** @var \Mirasvit\Rma\Model\Status $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    /**
     * @return $this
     */
    public function addActiveFilter()
    {
        $this->getSelect()
            ->where('is_active', 1);

        return $this;
    }

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @param string $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function _afterLoad()
    {
        if ($this->storeId) {
            foreach ($this as $item) {
                $item->setStoreId($this->storeId);
            }
        }
    }

     /************************/
}
