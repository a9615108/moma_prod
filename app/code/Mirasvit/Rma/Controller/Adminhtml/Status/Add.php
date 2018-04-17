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



namespace Mirasvit\Rma\Controller\Adminhtml\Status;

use Magento\Framework\Controller\ResultFactory;

class Add extends \Mirasvit\Rma\Controller\Adminhtml\Status
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->_initStatus();

        $this->initPage($resultPage);
        $resultPage->getConfig()->getTitle()->prepend(__('New Status'));
        $this->_addBreadcrumb(
            __('Status  Manager'),
            __('Status Manager'),
            $this->getUrl('*/*/')
        );
        $this->_addBreadcrumb(__('Add Status '), __('Add Status'));

        $resultPage->getLayout()
            ->getBlock('head')
            ;
        $this->_addContent($resultPage->getLayout()->createBlock('\Mirasvit\Rma\Block\Adminhtml\Status\Edit'));

        return $resultPage;
    }
}
