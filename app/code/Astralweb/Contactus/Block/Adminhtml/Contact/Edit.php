<?php

namespace Astralweb\Contactus\Block\Adminhtml\Contact;

class Edit extends \Magento\Backend\Block\Widget\Form\Container {

    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Astralweb\Contactus';
        $this->_controller = 'adminhtml_contact';
        parent::_construct();

        if ($this->_isAllowedAction('Astralweb_Contactus::save')) {
            $this->buttonList->update('save', 'label', __('Save Item'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Astralweb_Contactus::contact_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Item'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('contact_data')->getId()) {
            return __("Edit Item '%1'", $this->escapeHtml($this->_coreRegistry->registry('contact_data')->getFileName()));
        } else {
            return __('New Item');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('contactus/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }
}
