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



namespace Mirasvit\Rma\Block\Adminhtml\Rma;

class Create extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_rma';
        $this->_mode = 'create';
        $this->_blockGroup = 'Mirasvit_Rma';

        parent::_construct();

        $this->setId('rma_rma_create');
        $this->removeButton('save');
        $this->removeButton('reset');
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderText()
    {
        return __('Create New RMA');
    }
}
