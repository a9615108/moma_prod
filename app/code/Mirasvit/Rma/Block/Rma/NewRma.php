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


namespace Mirasvit\Rma\Block\Rma;

use \Magento\Framework\View\Element\Template;

class NewRma extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Magento\Customer\Model\Session $customerSession,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->customerSession = $customerSession;
        $this->context         = $context;
        $this->strategy        = $strategyFactory->create();
    }

    /**
     * @return bool|int
     */
    public function getIsStep2()
    {
        $order = null;
        if ($orderId = $this->context->getRequest()->getParam('order_id')) {
            $items = $this->strategy->getAllowedOrderList($this->getCustomer());
            if (isset($items[$orderId])) {
                $order = $items[$orderId];
            }
        }

        return (bool)$order;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

}
