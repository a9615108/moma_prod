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



namespace Mirasvit\Rma\Block\Adminhtml\Field;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Extended as GridExtended;
use Magento\Backend\Helper\Data as BackendHelper;
use Mirasvit\Rma\Model\FieldFactory;

class Grid extends GridExtended
{
    public function __construct(
        FieldFactory $fieldFactory,
        Context $context,
        BackendHelper $backendHelper,
        array $data = []
    ) {
        $this->fieldFactory = $fieldFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('grid');
        $this->setDefaultSort('field_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->fieldFactory->create()
            ->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('field_id', [
            'header'       => __('ID'),
            'index'        => 'field_id',
            'filter_index' => 'main_table.field_id',
        ]);
        $this->addColumn('name', [
            'header'         => __('Title'),
            'index'          => 'name',
            'frame_callback' => [$this, '_renderCellName'],
            'filter_index'   => 'main_table.name',
        ]);
        $this->addColumn('sort_order', [
            'header'       => __('Sort Order'),
            'index'        => 'sort_order',
            'filter_index' => 'main_table.sort_order',
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Reindex name
     *
     * @param string $renderedValue
     * @param object $item
     * @param string $column
     * @param bool   $isExport
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _renderCellName($renderedValue, $item, $column, $isExport)
    {
        return $item->getName();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('field_id');
        $this->getMassactionBlock()->setFormFieldName('field_id');
        $statuses = [
            ['label' => '', 'value' => ''],
            ['label' => __('Disabled'), 'value' => 0],
            ['label' => __('Enabled'), 'value' => 1],
        ];
        $this->getMassactionBlock()->addItem('is_active', [
            'label'      => __('Change status'),
            'url'        => $this->getUrl('*/*/massChange', ['_current' => true]),
            'additional' => [
                'visibility' => [
                    'name'   => 'is_active',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => __('Status'),
                    'values' => $statuses,
                ],
            ],
        ]);
        $this->getMassactionBlock()->addItem('delete', [
            'label'   => __('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure?'),
        ]);
        $this->getMassactionBlock()->addItem('export', array(
        'label'=> __('Export Selected'),
        'url'  => $this->getUrl('*/*/ExportCsv'),   //an action defined in the controller
        'selected' => 'selected',
        'confirm' => __('Are you sure?')
        ));

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Mirasvit\Rma\Model\Field $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
