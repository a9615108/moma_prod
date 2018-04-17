<?php

namespace Astralweb\ShippingStorePickUp\Block\Adminhtml;
 
use Magento\Backend\Block\Widget\Context;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    public function __construct(Context $context,
                                array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->removeButton('back')->removeButton('reset')->removeButton('save');

        $this->_objectId = 'order_export_id';
        $this->_blockGroup = 'Astralweb\ShippingStorePickUp';
        $this->_controller = 'adminhtml';
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {


        return __('Import');
    }
}
