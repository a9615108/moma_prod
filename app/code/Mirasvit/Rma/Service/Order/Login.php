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



namespace Mirasvit\Rma\Service\Order;

/**
 * Autorization of guest customer
 */
class Login implements \Mirasvit\Rma\Api\Service\Order\LoginInterface
{
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder($orderIncrementId, $emailOrLastname)
    {
        if ($orderIncrementId && $emailOrLastname) {
            $orderIncrementId = trim($orderIncrementId);
            $orderIncrementId = str_replace('#', '', $orderIncrementId);

            $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $orderIncrementId);
            $items = $this->orderRepository->getList($searchCriteria->create())->getItems();

            if (count($items)) {
                $order = array_pop($items);
                $emailOrLastname = trim(strtolower($emailOrLastname));
                if ($emailOrLastname != strtolower($order->getCustomerEmail())
                    && $emailOrLastname != strtolower($order->getCustomerLastname())) {
                    return false;
                }
                return $order;
            }
        }
        return false;
    }
}