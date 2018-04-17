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



namespace Mirasvit\Rma\Block\Adminhtml\Template\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form as WidgetForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory as StoreCollectionFactory;

class Form extends WidgetForm
{
    public function __construct(
        StoreCollectionFactory $storeCollectionFactory,
        FormFactory $formFactory,
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->formFactory = $formFactory;
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create()->setData([
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ]);

        /** @var \Mirasvit\Rma\Model\QuickResponse $template */
        $template = $this->registry->registry('current_template');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($template->getId()) {
            $fieldset->addField('template_id', 'hidden', [
                'name'  => 'template_id',
                'value' => $template->getId(),
            ]);
        }

        $fieldset->addField('name', 'text', [
            'label' => __('Internal Title'),
            'name'  => 'name',
            'value' => $template->getName(),
        ]);

        $fieldset->addField('is_active', 'select', [
            'label'  => __('Is Active'),
            'name'   => 'is_active',
            'value'  => $template->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);

        $fieldset->addField('template', 'textarea', [
            'label' => __('Template'),
            'name'  => 'template',
            'value' => $template->getTemplate(),
            'note'  => __(
                'You can use variables:
                [rma_increment_id],
                [rma_firstname],
                [rma_lastname],
                [rma_email],
                [store_name],
                [store_phone],
                [store_address],
                [user_firstname],
                [user_lastname],
                [user_email]'
            ),
        ]);

        $fieldset->addField('store_ids', 'multiselect', [
            'label'    => __('Stores'),
            'required' => true,
            'name'     => 'store_ids[]',
            'value'    => $template->getStoreIds(),
            'values'   => $this->storeCollectionFactory->create()->toOptionArray(),
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
