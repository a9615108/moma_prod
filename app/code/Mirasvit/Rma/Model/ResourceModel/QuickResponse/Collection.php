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



namespace Mirasvit\Rma\Model\ResourceModel\QuickResponse;

/**
 * @method \Mirasvit\Rma\Model\QuickResponse getFirstItem()
 * @method \Mirasvit\Rma\Model\QuickResponse getLastItem()
 * @method \Mirasvit\Rma\Model\ResourceModel\QuickResponse\Collection addFieldToFilter
 * @method \Mirasvit\Rma\Model\ResourceModel\QuickResponse\Collection setOrder
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\QuickResponse', 'Mirasvit\Rma\Model\ResourceModel\QuickResponse');
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
        /** @var \Mirasvit\Rma\Model\QuickResponse $item */
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
        /** @var \Mirasvit\Rma\Model\QuickResponse $item */
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
    public function addStoreFilter($storeId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('mst_rma_template_store')}`
                AS `template_store_table`
                WHERE main_table.template_id = template_store_table.ts_template_id
                AND template_store_table.ts_store_id in (?))", [0, $storeId]);

        return $this;
    }

     /************************/
}
