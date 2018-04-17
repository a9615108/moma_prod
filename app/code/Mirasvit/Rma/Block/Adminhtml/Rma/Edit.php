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



namespace Mirasvit\Rma\Block\Adminhtml\Rma;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Helper\Rma\Url $rmaUrl,
        \Mirasvit\Rma\Helper\Order\Creditmemo $creditmemoHelper,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $orderInvoiceCollectionFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->rmaManagement                 = $rmaManagement;
        $this->rmaUrl                        = $rmaUrl;
        $this->creditmemoHelper              = $creditmemoHelper;
        $this->orderInvoiceCollectionFactory = $orderInvoiceCollectionFactory;
        $this->wysiwygConfig                 = $wysiwygConfig;
        $this->registry                      = $registry;
        $this->context                       = $context;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_objectId = 'rma_id';
        $this->_controller = 'adminhtml_rma';
        $this->_blockGroup = 'Mirasvit_Rma';

        $this->buttonList->remove('save');

        $this->getToolbar()->addChild(
            'update-split-button',
            'Magento\Backend\Block\Widget\Button\SplitButton',
            [
                'id'           => 'update-split-button',
                'label'        => __('Save'),
                'class_name'   => 'Magento\Backend\Block\Widget\Button\SplitButton',
                'button_class' => 'widget-button-update',
                'options'      => [
                    [
                        'id'             => 'update-button',
                        'label'          => __('Save'),
                        'default'        => true,
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => [
                                    'event'  => 'save',
                                    'target' => '#edit_form',
                                ],
                            ],
                        ],
                    ],
                    [
                        'id'             => 'update-continue-button',
                        'label'          => __('Save & Continue Edit'),
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => [
                                    'event'  => 'saveAndContinueEdit',
                                    'target' => '#edit_form',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $rma = $this->getRma();
        if ($rma) {
            $this->buttonList->add('print', [
                'label'   => __('Print'),
                'onclick' => 'var win = window.open(\'' .
                    $this->rmaUrl->getGuestPrintUrl($rma) . '\', \'_blank\');win.focus();',
            ]);
            $order = $this->rmaManagement->getOrder($rma);
            if ($this->creditmemoHelper->canCreateCreditmemo($rma, $order)) {
                $this->buttonList->add('order_creditmemo_manual', [
                    'label'   => __('Credit Memo'),
                    'onclick' => 'var win = window.open(\'' .
                        $this->creditmemoHelper->getCreditmemoUrl($rma, $order) . '\', \'_blank\');win.focus();',
                ]);
            }

            $this->buttonList->add('order_exchange', [
                'label'   => __('Exchange Order'),
                'onclick' => 'var win = window.open(\'' .
                    $this->getCreateOrderUrl($rma) . '\', \'_blank\');win.focus();',
            ]);
        }

        return $this;
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma $rma
     *
     * @return string
     */
    public function getCreateOrderUrl($rma)
    {
        return $this->getUrl(
            'sales/order_create/start/',
            [
                'customer_id' => $rma->getCustomerId(),
                'store_id'    => $rma->getStoreId(),
                'rma_id'      => $rma->getId()
            ]
        );
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma $rma
     *
     * @return string
     */
    public function getCreditmemoUrl($rma)
    {
        $orderId = $rma->getOrderId();
        $collection = $this->orderInvoiceCollectionFactory->create()
            ->addFieldToFilter('order_id', $orderId);
        // echo $collection->getSelect();die;
        if ($collection->count() == 1) {
            $invoice = $collection->getFirstItem();

            return $this->getUrl(
                'sales/order_creditmemo/new',
                [
                    'order_id'   => $orderId,
                    'invoice_id' => $invoice->getId(),
                    'rma_id'     => $rma->getId()
                ]
            );
        } else {
            return $this->getUrl(
                'sales/order_creditmemo/new',
                [
                    'order_id' => $orderId,
                    'rma_id'   => $rma->getId()]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->wysiwygConfig->isEnabled()) {
        }
    }

    /**
     * @return \Mirasvit\Rma\Model\Rma
     */
    public function getRma()
    {
        if ($this->registry->registry('current_rma') && $this->registry->registry('current_rma')->getId()) {
            return $this->registry->registry('current_rma');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderText()
    {
        if ($rma = $this->getRma()) {
            $status = $this->rmaManagement->getStatus($rma);
            return __('RMA #%1 - %2', $rma->getIncrementId(), $status->getName());
        } else {
            return __('Create New RMA');
        }
    }

    /************************/
}
