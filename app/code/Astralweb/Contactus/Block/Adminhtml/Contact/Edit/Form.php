<?php

namespace Astralweb\Contactus\Block\Adminhtml\Contact\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic {

    protected $_systemStore;
    protected $_wysiwygConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('contact_form');
        $this->setTitle(__('Email <span class="sp-highlight-term">Information</span>'));
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('contact_data');

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post', 'enctype' => 'multipart/form-data']]
        );

        $form->setHtmlIdPrefix('contact_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General <span class="sp-highlight-term">Information</span>'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Name'), 'title' => __('Name'), 'required' => true]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'title' => __('Email'),
                'required' => true,

            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'name' => 'is_active',
                'label' => __('Status'),
                'title' => __('Status'),
                'values'    => array(
                    array(
                        'value'     => 0,
                        'label'     => __('Disabled'),
                    ),
                    array(
                        'value'     => 1,
                        'label'     => __('Enabled'),
                    ),
                ),
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
