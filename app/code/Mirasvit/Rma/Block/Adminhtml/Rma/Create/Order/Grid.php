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



namespace Mirasvit\Rma\Block\Adminhtml\Rma\Create\Order;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    public function __construct(
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Mirasvit\Rma\Helper\Mage $rmaMage,
        \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface $policyConfig,
        array $data = []
    ) {
        $this->addressRenderer = $addressRenderer;
        $this->context         = $context;
        $this->request         = $context->getRequest();
        $this->rmaMage         = $rmaMage;
        $this->policyConfig    = $policyConfig;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('rma_rma_create_order_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $allowedStatuses = $this->policyConfig->getAllowRmaInOrderStatuses();
        $collection = $this->rmaMage->getOrderCollection();
        $collection->addFieldToFilter('status', ['in' => $allowedStatuses]);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', [
            'header' => __('Order #'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'increment_id',
            'filter_index' => 'main_table.increment_id',
        ]);

        if (!$this->context->getStoreManager()->isSingleStoreMode()) {
            $this->addColumn('store_id', [
                'header' => __('Purchased From (Store)'),
                'index' => 'store_id',
                'type' => 'store',
                'store_view' => true,
                'display_deleted' => true,
            ]);
        }

        $this->addColumn('created_at', [
            'header' => __('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ]);

        $this->addColumn('billing_name', [
            'header' => __('Bill to Name'),
            'index' => 'billing_address_id',
            'frame_callback' => [$this, 'renderBillingAddress'],
        ]);

        $this->addColumn('shipping_name', [
            'header' => __('Ship to Name'),
            'index' => 'shipping_address_id',
            'frame_callback' => [$this, 'renderShippingAddress'],
        ]);

        $this->addColumn('base_grand_total', [
            'header' => __('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
        ]);

        $this->addColumn('grand_total', [
            'header' => __('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @param int                                                $renderedValue
     * @param \Magento\Sales\Model\Order                         $item
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @param bool                                               $isExport
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function renderBillingAddress($renderedValue, $item, $column, $isExport)
    {
        if ($renderedValue) {
            echo $this->addressRenderer->format($item->getBillingAddress(), 'html');
        }
    }

    /**
     * @param int                                                $renderedValue
     * @param \Magento\Sales\Model\Order                         $item
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @param bool                                               $isExport
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function renderShippingAddress($renderedValue, $item, $column, $isExport)
    {
        if ($renderedValue) {
            echo $this->addressRenderer->format($item->getShippingAddress(), 'html');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/add',
            [
                'order_id' => $row->getId(),
                'ticket_id' => $this->request->getParam('ticket_id')
            ]
        );
    }
}
