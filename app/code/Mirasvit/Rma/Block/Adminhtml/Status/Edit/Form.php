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



namespace Mirasvit\Rma\Block\Adminhtml\Status\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form as WidgetForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\Rma\Api\Repository\StatusRepositoryInterface;

class Form extends WidgetForm
{
    public function __construct(
        StatusRepositoryInterface $statusRepository,
        FormFactory $formFactory,
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->statusRepository = $statusRepository;
        $this->formFactory      = $formFactory;
        $this->registry         = $registry;
        $this->context          = $context;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        $form = $this->formFactory->create()->setData([
            'id'      => 'edit_form',
            'action'  => $this->getUrl(
                '*/*/save',
                [
                    'id'    => $this->getRequest()->getParam('id'),
                    'store' => $storeId,
                ]
            ),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ]);

        $status = $this->registry->registry('current_status');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($status->getId()) {
            $fieldset->addField('status_id', 'hidden', [
                'name'  => 'status_id',
                'value' => $status->getId(),
            ]);
        }

        $fieldset->addField('store_id', 'hidden', [
            'name'  => 'store_id',
            'value' => $storeId,
        ]);

        $fieldset->addField('name', 'text', [
            'label'       => __('Title'),
            'name'        => 'name',
            'value'       => $status->getName(),
            'required'    => true,
            'scope_label' => __('[STORE VIEW]'),
        ]);

        $fieldset->addField('code', 'text', [
            'label'    => __('Code'),
            'name'     => 'code',
            'value'    => $status->getCode(),
            'disabled' => $status->getCode() == '' ? '' : 'disabled',
            'required' => true,
        ]);

        $fieldset->addField('sort_order', 'text', [
            'label' => __('Sort Order'),
            'name'  => 'sort_order',
            'value' => $status->getSortOrder(),
        ]);

        $fieldset->addField('is_active', 'select', [
            'label'  => __('Is Active'),
            'name'   => 'is_active',
            'value'  => $status->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);

        $fieldset->addField('is_show_shipping', 'select', [
            'label'  => __('Show buttons \'Print RMA Packing Slip\','.
                ' \'Print RMA Shipping Label\' and \'Confirm Shipping\' in the customer account'),
            'name'   => 'is_show_shipping',
            'value'  => $status->getIsShowShipping(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);

        $fieldset = $form->addFieldset('notification_fieldset', ['legend' => __('Notifications')]);
        $fieldset->addField('customer_message', 'textarea', [
            'label'       => __('Email Notification for customer'),
            'name'        => 'customer_message',
            'value'       => $this->statusRepository->getCustomerMessageForStore($status, $storeId),
            'note'        => __('leave blank to not send'),
            'scope_label' => __('[STORE VIEW]'),
        ]);

        $fieldset->addField('history_message', 'textarea', [
            'label'       => __('Message for RMA history'),
            'name'        => 'history_message',
            'value'       => $this->statusRepository->getHistoryMessageForStore($status, $storeId),
            'scope_label' => __('[STORE VIEW]'),
        ]);

        $fieldset->addField('admin_message', 'textarea', [
            'label'       => __('Email Notification for administrator'),
            'name'        => 'admin_message',
            'value'       => $this->statusRepository->getAdminMessageForStore($status, $storeId),
            'note'        => __('leave blank to not send'),
            'scope_label' => __('[STORE VIEW]'),
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
