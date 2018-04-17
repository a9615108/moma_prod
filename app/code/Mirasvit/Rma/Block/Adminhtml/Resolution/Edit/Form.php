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



namespace Mirasvit\Rma\Block\Adminhtml\Resolution\Edit;

class Form extends \Magento\Backend\Block\Widget\Form
{
    public function __construct(
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry    = $registry;
        $this->context     = $context;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create()->setData(
            [
                'id' => 'edit_form',
                'action' => $this->getUrl(
                    '*/*/save',
                    [
                        'id' => $this->getRequest()->getParam('id'),
                        'store' => (int) $this->getRequest()->getParam('store')
                    ]
                ),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ]
        );

        $resolution = $this->registry->registry('current_resolution');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($resolution->getId()) {
            $fieldset->addField('resolution_id', 'hidden', [
                'name' => 'resolution_id',
                'value' => $resolution->getId(),
            ]);
        }
        $fieldset->addField('store_id', 'hidden', [
            'name' => 'store_id',
            'value' => (int) $this->getRequest()->getParam('store'),
        ]);

        $fieldset->addField('name', 'text', [
            'label' => __('Title'),
            'name' => 'name',
            'value' => $resolution->getName(),
            'scope_label' => __('[STORE VIEW]'),
        ]);
        $fieldset->addField('code', 'text', [
            'label' => __('Code'),
            'required' => true,
            'name' => 'code',
            'value' => $resolution->getCode(),
            'disabled' => $resolution->getCode() == '' ? '' : 'disabled',
        ]);
        $fieldset->addField('sort_order', 'text', [
            'label' => __('Sort Order'),
            'name' => 'sort_order',
            'value' => $resolution->getSortOrder(),
        ]);
        $fieldset->addField('is_active', 'select', [
            'label' => __('Is Active'),
            'name' => 'is_active',
            'value' => $resolution->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
