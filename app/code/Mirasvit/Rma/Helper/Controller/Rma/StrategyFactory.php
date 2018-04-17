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



namespace Mirasvit\Rma\Helper\Controller\Rma;

class StrategyFactory
{

    public function __construct(
        \Mirasvit\Rma\Helper\Controller\Rma\CustomerStrategy $customerStrategy,
        \Mirasvit\Rma\Helper\Controller\Rma\GuestStrategy $guestStrategy,
        \Mirasvit\Rma\Helper\Controller\Rma\NoAccessStrategy $noAccessStrategy,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerStrategy = $customerStrategy;
        $this->guestStrategy    = $guestStrategy;
        $this->customerSession  = $customerSession;
        $this->noAccessStrategy = $noAccessStrategy;
    }


    /**
     * @param \Magento\Framework\App\RequestInterface|false $request
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return AbstractStrategy
     */
    public function create($request = false)
    {
        try {
            if ($this->customerSession->getId()) {
                return $this->customerStrategy;
            } elseif (
                $this->customerSession->getRMAGuestOrderId() ||
                ($request && $this->guestStrategy->initRma($request))
            ) {
                return $this->guestStrategy;
            } else {
                return $this->noAccessStrategy;
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->noAccessStrategy;
        }
    }
}