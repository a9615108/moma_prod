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


namespace Mirasvit\Rma\Controller\Adminhtml;

abstract class Address extends \Magento\Backend\App\Action
{
    public function __construct(
        \Mirasvit\Rma\Model\AddressFactory $addressFactory,
        \Mirasvit\Rma\Api\Repository\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->addressFactory    = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->localeDate        = $localeDate;
        $this->registry          = $registry;
        $this->context           = $context;
        $this->backendSession    = $context->getSession();
        $this->resultFactory     = $context->getResultFactory();

        parent::__construct($context);
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Sales::sales_operation');
        $resultPage->getConfig()->getTitle()->prepend(__('RMA Return Address'));
        return $resultPage;
    }

    /**
     * @return \Mirasvit\Rma\Model\Address
     */
    public function _initAddress()
    {
        $address = $this->addressFactory->create();
        if ($this->getRequest()->getParam('id')) {
            $address->load($this->getRequest()->getParam('id'));
        }

        $this->registry->register('current_address', $address);

        return $address;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Rma::rma_return_addresses');
    }

    /************************/
}
