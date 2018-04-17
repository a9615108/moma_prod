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



namespace Mirasvit\Rma\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Notification extends Form
{
    public function __construct(
        FormFactory $formFactory,
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create();
        $this->setForm($form);
        /** @var \Mirasvit\Rma\Model\Rule $rule */
        $rule = $this->registry->registry('current_rule');

        $fieldset = $form->addFieldset('notification_fieldset', ['legend' => __('Notifications')]);
        if ($rule->getId()) {
            $fieldset->addField('rule_id', 'hidden', [
                'name'  => 'rule_id',
                'value' => $rule->getId(),
            ]);
        }
        $fieldset->addField('is_send_user', 'select', [
            'label'  => __('Send email to RMA owner'),
            'name'   => 'is_send_owner',
            'value'  => $rule->getIsSendOwner(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('is_send_customer', 'select', [
            'label'  => __('Send email to customer'),
            'name'   => 'is_send_user',
            'value'  => $rule->getIsSendUser(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('other_email', 'text', [
            'label' => __('Send email to other email addresses'),
            'name'  => 'other_email',
            'value' => $rule->getOtherEmail(),
        ]);
        $fieldset->addField('email_subject', 'text', [
            'label' => __('Email subject'),
            'name'  => 'email_subject',
            'value' => $rule->getEmailSubject(),
        ]);
        $fieldset->addField('email_body', 'textarea', [
            'label' => __('Email body'),
            'name'  => 'email_body',
            'value' => $rule->getEmailBody(),
        ]);
        $fieldset->addField('is_send_attachment', 'select', [
            'label'  => __('Attach files which were attached to the last message'),
            'name'   => 'is_send_attachment',
            'value'  => $rule->getIsSendAttachment(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);

        return parent::_prepareForm();
    }
}
