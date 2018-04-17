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



namespace Mirasvit\Rma\Helper\Controller\Rma;

class Grid
{

    public function __construct(
        \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement,
        \Mirasvit\Rma\Helper\Store $rmaStoreHelper,
        \Mirasvit\Rma\Model\StatusFactory $statusFactory,
        \Mirasvit\Rma\Helper\User\Html $rmaUserHtml,
        \Mirasvit\Rma\Api\Config\BackendConfigInterface $config
    ) {
        $this->fieldManagement = $fieldManagement;
        $this->rmaStoreHelper  = $rmaStoreHelper;
        $this->statusFactory   = $statusFactory;
        $this->rmaUserHtml     = $rmaUserHtml;

        $this->columns = $config->getRmaGridColumns();
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getIncrementId($grid)
    {
        if (in_array('increment_id', $this->columns)) {
            $grid->addColumn('increment_id', [
                'header'       => __('RMA #'),
                'index'        => 'increment_id',
                'filter_index' => 'main_table.increment_id',
            ]);
        }
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getOrderIncrementId($grid)
    {
        if (in_array('order_increment_id', $this->columns)) {
            $grid->addColumn('order_increment_id', [
                'header'       => __('Order #'),
                'index'        => 'order_increment_id',
                'filter_index' => 'order.increment_id',
            ]);
        }
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getCustomerEmail($grid)
    {
        if (in_array('customer_email', $this->columns)) {
            $grid->addColumn('email', [
                'header' => __('Email'),
                'index'  => 'email',
            ]);
        }
    }

      public function getCustomerPhone($grid)
    {
        if (in_array('customer_phone', $this->columns)) {
            $grid->addColumn('phone', [
                'header' => __('電話'),
                'index'  => 'telephone',
            ]);
        }
    }

    public function getCustomerAddress($grid)
    {
        if (in_array('customer_address', $this->columns)) {
            $grid->addColumn('address', [
                'header'       => __('地址'),
                'index'        => ['street', 'city' , 'region'],
                'type'         => 'concat',
                'separator'    => ',',
                'filter_index' => new \Zend_Db_Expr("CONCAT(street, ' ', city , ' ', region)"),
            ]);
        }
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getCustomerName($grid)
    {
        if (in_array('customer_name', $this->columns)) {
            $grid->addColumn('name', [
                'header'       => __('Customer Name'),
                'index'        => ['firstname', 'lastname'],
                'type'         => 'concat',
                'separator'    => ' ',
                'filter_index' => new \Zend_Db_Expr("CONCAT(firstname, ' ', lastname)"),
            ]);
        }
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getUserId($grid)
    {
        if (in_array('user_id', $this->columns)) {
            $grid->addColumn('user_id', [
                'header'       => __('建立者'),
                'index'        => 'user_id',
                'filter_index' => 'main_table.user_id',
                'type'         => 'options',
                'options'      => $this->rmaUserHtml->getAdminUserOptionArray(),
            ]);
        }
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getLastReplyName($grid)
    {
        if (in_array('last_reply_name', $this->columns)) {
            $grid->addColumn('last_reply_name', [
                'header'         => __('最後修改者'),
                'index'          => 'last_reply_name',
                'filter_index'   => 'main_table.last_reply_name',
                'frame_callback' => [$grid, '_lastReplyFormat'],
            ]);
        }
    }

    //  public function getSkus($grid)
    // {
    //     if (in_array('skus', $this->columns)) {
    //         $grid->addColumn('skus', [
    //             'header'         => __('Refund SKU Product'),
    //             'index'          => 'skus',
    //             'filter_index'   => 'main_table.skus',
    //         ]);
    //     }
    // }


    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getStatusId($grid)
    {
        if (in_array('status_id', $this->columns)) {
            $grid->addColumn('status_id', [
                'header'       => __('Status'),
                'index'        => 'status_id',
                'filter_index' => 'main_table.status_id',
                'type'         => 'options',
                'options'      => $this->statusFactory->create()->getCollection()->getOptionArray(),
            ]);
        }
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getCreatedAt($grid)
    {
        if (in_array('created_at', $this->columns)) {
            $grid->addColumn('created_at', [
                'header'       => __('Created Date'),
                'index'        => 'created_at',
                'filter_index' => 'main_table.created_at',
                'type'         => 'datetime',
            ]);
        }
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getUpdatedAt($grid)
    {
        if (in_array('updated_at', $this->columns)) {
            $grid->addColumn('updated_at', [
                'header'         => __('Last Activity'),
                'index'          => 'updated_at',
                'filter_index'   => 'main_table.updated_at',
                'type'           => 'datetime',
                'frame_callback' => [$grid, '_lastActivityFormat'],
            ]);
        }
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getStoreId($grid)
    {
        if (in_array('store_id', $this->columns)) {
            $grid->addColumn('store_id', [
                'header'       => __('Store'),
                'index'        => 'store_id',
                'filter_index' => 'main_table.store_id',
                'type'         => 'options',
                'options'      => $this->rmaStoreHelper->getCoreStoreOptionArray(),
            ]);
        }
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getItems($grid)
    {
        if (in_array('items', $this->columns)) {
            $grid->addColumn('items', [
                'header'           => __('Items'),
                'column_css_class' => 'nowrap',
                'type'             => 'text',
                'frame_callback'   => [$grid, 'itemsFormat'],
            ]);
        }
    }
        public function getSkus($grid)
    {
        if (in_array('skus', $this->columns)) {
            $grid->addColumn('skus', [
                'header'           => __('商品貨號'),
                'column_css_class' => 'nowrap',
                'type'             => 'text',
                'frame_callback'   => [$grid, 'skuFormat'],
            ]);
        }
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getAction($grid)
    {
        if ($grid->getTabMode() || in_array('action', $this->columns)) {
            $grid->addColumn(
                'action',
                [
                    'header'   => __('Action'),
                    'width'    => '50px',
                    'type'     => 'action',
                    'getter'   => 'getId',
                    'is_system' => true,
                    'actions'  => [
                        [
                            'caption' => __('View'),
                            'url'     => [
                                'base' => 'rma/rma/edit',
                            ],
                            'field'   => 'id',
                        ],
                    ],
                    'filter'   => false,
                    'sortable' => false,
                ]
            );
        }
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getCustomFields($grid)
    {
        $collection = $this->fieldManagement->getStaffCollection();
        foreach ($collection as $field) {
            if (in_array($field->getCode(), $this->columns)) {
                $grid->addColumn($field->getCode(), [
                    'header'  => __($field->getName()),
                    'index'   => $field->getCode(),
                    'type'    => $field->getGridType(),
                    'options' => $field->getGridOptions(),
                ]);
            }
        }
    }
}