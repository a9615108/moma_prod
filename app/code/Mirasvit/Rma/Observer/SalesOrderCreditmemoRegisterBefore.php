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



namespace Mirasvit\Rma\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderCreditmemoRegisterBefore implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->request        = $request;
        $this->backendSession = $backendSession;
    }

    /**
     * Save rma id to session when create credit memo in the backend.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($rmaId = $this->request->getParam('rma_id')) {
            $this->backendSession->setRmaId($rmaId);
        }
    }
}
