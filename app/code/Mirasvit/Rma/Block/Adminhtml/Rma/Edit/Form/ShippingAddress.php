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


namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form;

class ShippingAddress extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->formFactory = $formFactory;

        parent::__construct($context, $data);
    }


    /**
     * General information form
     *
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFormHtml(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        $form = $this->formFactory->create();

        $fieldset = $form->addFieldset('customer_fieldset', ['legend' => __('Contact Information')]);
        $fieldset->addField('firstname', 'text', [
            'label' => __('First Name'),
            'name'  => 'firstname',
            'value' => $rma->getFirstname(),
        ]);
        $fieldset->addField('lastname', 'text', [
            'label' => __('Last Name'),
            'name'  => 'lastname',
            'value' => $rma->getLastname(),
        ]);
        $fieldset->addField('company', 'text', [
            'label' => __('Company'),
            'name'  => 'company',
            'value' => $rma->getCompany(),
        ]);
        $fieldset->addField('telephone', 'text', [
            'label' => __('Telephone'),
            'name'  => 'telephone',
            'value' => $rma->getTelephone(),
        ]);
        $fieldset->addField('email', 'text', [
            'label' => __('Email'),
            'name'  => 'email',
            'value' => $rma->getEmail(),
        ]);
        $street = explode("\n", $rma->getStreet());
        $fieldset->addField('street', 'hidden', [
            'label' => __('Street Address'),
            'name'  => 'street',
            'value' => $street[0],
        ]);
        $fieldset->addField('street2', 'hidden', [
            'name'  => 'street2',
            'value' => isset($street[1]) ? $street[1] : '',
        ]);
        $fieldset->addField('city', 'hidden', [
            'label' => __('City'),
            'name'  => 'city',
            'value' => $rma->getCity(),
        ]);
        $fieldset->addField('postcode', 'hidden', [
            'label' => __('Zip/Postcode'),
            'name'  => 'postcode',
            'value' => $rma->getPostcode(),
        ]);

        return $form->toHtml();
    }
}