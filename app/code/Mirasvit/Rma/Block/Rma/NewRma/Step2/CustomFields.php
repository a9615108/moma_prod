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



namespace Mirasvit\Rma\Block\Rma\NewRma\Step2;

class CustomFields extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement,
        \Mirasvit\Rma\Service\Rma\RmaAdapter $rmaAdapter,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->fieldManagement    = $fieldManagement;
        $this->rmaAdapter         = $rmaAdapter;
        $this->registry           = $registry;
        $this->context            = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        $rma =  $this->registry->registry('current_rma');
        $this->rmaAdapter->setData($rma->getData());

        return $rma;
    }

    /**
     * @return \Mirasvit\Rma\Model\Field[]
     */
    public function getCustomFields()
    {
        return $this->fieldManagement->getVisibleCustomerCollection('initial', true);
    }

    /**
     * @param \Mirasvit\Rma\Model\Field $field
     *
     * @return string
     */
    public function getFieldInputHtml(\Mirasvit\Rma\Model\Field $field)
    {
        return $this->fieldManagement->getInputHtml($field);
    }

    /**
     * @return \Mirasvit\Rma\Service\Rma\RmaAdapter
     */
    public function getRmaAdapter()
    {
        return $this->rmaAdapter;
    }
}