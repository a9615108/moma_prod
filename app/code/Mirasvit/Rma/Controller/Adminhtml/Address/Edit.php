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


namespace Mirasvit\Rma\Controller\Adminhtml\Address;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Mirasvit\Rma\Controller\Adminhtml\Address
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $address = $this->_initAddress();

        if ($address->getId()) {
            $this->initPage($resultPage);
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Return Address '%1'", $address->getName()));
            $this->_addBreadcrumb(
                __('Return Addresses'),
                __('Return Addresses'),
                $this->getUrl('*/*/')
            );
            $this->_addBreadcrumb(
                __('Edit Return Address '),
                __('Edit Return Address ')
            );

            $resultPage->getLayout()
                ->getBlock('head')
                ;
            $this->_addContent($resultPage->getLayout()->createBlock('\Mirasvit\Rma\Block\Adminhtml\Address\Edit'));

            return $resultPage;
        } else {
            $this->messageManager->addError(__('The Return Address does not exist.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }
    }
}
