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


namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form;


class GeneralInfo extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form\Generalinfo\CustomFields $customFields,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Helper\User\Html $rmaUserHtml,
        \Mirasvit\Rma\Helper\Rma\Url $rmaUrl,
        \Mirasvit\Rma\Helper\Rma\Option $rmaOption,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Convert\DataObject $convertDataObject,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->customFields         = $customFields;
        $this->rmaManagement        = $rmaManagement;
        $this->rmaUserHtml          = $rmaUserHtml;
        $this->rmaUrl               = $rmaUrl;
        $this->rmaOption            = $rmaOption;
        $this->formFactory          = $formFactory;
        $this->convertDataObject    = $convertDataObject;

        parent::__construct($context, $data);
    }


    /**
     * General information form
     *
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return string
     */
    public function getGeneralInfoFormHtml(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        $form = $this->formFactory->create();
        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);

        if ($rma->getId()) {
            $fieldset->addField('rma_id', 'hidden', [
                'name'  => 'rma_id',
                'value' => $rma->getId(),
            ]);
        }

        $element = $fieldset->addField('increment_id', 'text', [
            'label' => __('RMA #'),
            'name'  => 'increment_id',
            'value' => $rma->getIncrementId(),
        ]);

        if (!$rma->getId()) {
            $element->setNote('will be generated automatically, if empty');
        }

        $this->customFields->addCustomerLink($fieldset, $rma);

        $order = $this->rmaManagement->getOrder($rma);
        $fieldset->addField('order_id', 'link', [
            'label' => __('Order #'),
            'name'  => 'order_id',
            'value' => '#' . $order->getIncrementId(),
            'href'  => $this->getUrl('sales/order/view', ['order_id' => $rma->getOrderId()]),
        ]);

        if ($rma->getTicketId()) {
            $fieldset->addField('ticket_id', 'hidden', [
                'name'  => 'ticket_id',
                'value' => $rma->getTicketId(),
            ]);
            $ticket = $this->rmaManagement->getTicket($rma);
            $fieldset->addField('ticket_link', 'link', [
                'label'  => __('Created From Ticket'),
                'name'   => 'ticket_link',
                'value'  => '#' . $ticket->getCode(),
                'href'   => $ticket->getBackendUrl(),
                'target' => '_blank',
            ]);
        }

        $fieldset->addField('user_id', 'select', [
            'label'  => __('RMA Owner'),
            'name'   => 'user_id',
            'value'  => $rma->getUserId(),
            'values' => $this->rmaUserHtml->toAdminUserOptionArray(true),
        ]);

        $fieldset->addField('status_id', 'select', [
            'label'  => __('Status'),
            'name'   => 'status_id',
            'value'  => $rma->getStatusId(),
            'values' => $this->convertDataObject->toOptionArray($this->rmaOption->getStatusList(), "id", "name")
        ]);

        $fieldset->addField('return_label', 'Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form\Element\File', [
            'label'      => __('Upload Return Label'),
            'name'       => 'return_label',
            'attachment' => $this->rmaManagement->getReturnLabel($rma),
        ]);

        if ($rma->getId()) {
            $fieldset->addField('guest_link', 'link', [
                'label'  => __('External Link'),
                'name'   => 'guest_link',
                'class'  => 'guest-link',
                'value'  => __('open'),
                'href'   => $this->rmaUrl->getGuestUrl($rma),
                'target' => '_blank',
            ]);
        }


        $this->customFields->addExchangeOrders($fieldset, $rma);
        $this->customFields->addCreditmemos($fieldset, $rma);

        $this->customFields->getReturnAddress($fieldset, $rma);

        return $form->toHtml();
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return bool|\Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFieldForm(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->customFields->getFieldForm($rma);
    }
}