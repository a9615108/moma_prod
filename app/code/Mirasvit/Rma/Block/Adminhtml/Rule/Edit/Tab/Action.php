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
use Mirasvit\Rma\Helper\User\Html as RmaHelper;
use Mirasvit\Rma\Model\ResourceModel\Status\CollectionFactory as StatusCollectionFactory;

class Action extends Form
{
    public function __construct(
        StatusCollectionFactory $statusCollectionFactory,
        RmaHelper $rmaHelper,
        FormFactory $formFactory,
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->rmaHelper = $rmaHelper;
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

        $fieldset = $form->addFieldset('action_fieldset', ['legend' => __('Actions')]);
        if ($rule->getId()) {
            $fieldset->addField('rule_id', 'hidden', [
                'name'  => 'rule_id',
                'value' => $rule->getId(),
            ]);
        }
        $fieldset->addField('status_id', 'select', [
            'label'  => __('Set Status'),
            'name'   => 'status_id',
            'value'  => $rule->getStatusId(),
            'values' => $this->statusCollectionFactory->create()->toOptionArray(true),
        ]);
        $fieldset->addField('user_id', 'select', [
            'label'  => __('Set Owner'),
            'name'   => 'user_id',
            'value'  => $rule->getUserId(),
            'values' => $this->rmaHelper->toAdminUserOptionArray(true),
        ]);

        return parent::_prepareForm();
    }
}
