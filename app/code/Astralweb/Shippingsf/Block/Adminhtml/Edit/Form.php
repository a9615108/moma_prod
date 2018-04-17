<?php

namespace Astralweb\Shippingsf\Block\Adminhtml\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form before rendering HTML.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/order/index'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ],
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Import CSV')]);

        $fieldset->addField('file', 'file', array(
            'label'     => 'CSV',
            'class'     => 'disable',
            'required'  => true,
            'name'      => 'file'
        ));



        $fieldset->addField(
            'submit',
            'note',
            [
                'text' => $this->getLayout()->createBlock(
                    'Magento\Backend\Block\Widget\Button'
                )->setData(
                    ['label' => __('Submit'), 'onclick' => 'this.form.submit();', 'class' => 'add']
                )->toHtml()
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
