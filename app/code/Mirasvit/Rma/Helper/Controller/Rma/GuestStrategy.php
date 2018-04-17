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

class GuestStrategy extends AbstractStrategy
{

    public function __construct(
        \Mirasvit\Rma\Helper\Rma\Url $rmaUrl,
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Api\Service\Strategy\SearchInterface $strategySearch,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface $performerFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->rmaUrl           = $rmaUrl;
        $this->rmaRepository    = $rmaRepository;
        $this->strategySearch   = $strategySearch;
        $this->orderRepository  = $orderRepository;
        $this->customerSession  = $customerSession;
        $this->performerFactory = $performerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequireCustomerAutorization()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaId(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $rma->getGuestId();
    }

    /**
     * {@inheritdoc}
     */
    public function initRma(\Magento\Framework\App\RequestInterface $request)
    {
        $id = $request->getParam('id');
        $rma = $this->rmaRepository->getByGuestId($id);

        return $rma;
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaList(\Magento\Sales\Model\Order $order = null)
    {
        return $this->strategySearch->getRmaList(
            $this->getOrder()->getCustomerId(),
            $order
        );
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        $orderId = $this->customerSession->getRMAGuestOrderId();
        $order = $this->orderRepository->get($orderId);

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerformer()
    {
        $order = $this->getOrder();
        $name = implode(
            ' ',
            [$order->getCustomerFirstname(), $order->getCustomerMiddlename(), $order->getCustomerLastname()]
        );

        return $this->performerFactory->create(
            \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface::GUEST,
            new \Magento\Framework\DataObject(
                [
                    'name' => $name,
                    'id'   => $order->getCustomerId(),
                ]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOrderList()
    {
        return [$this->getOrder()->getId() => $this->getOrder()];
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaUrl(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->rmaUrl->getGuestUrl($rma);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRmaUrl()
    {
        return $this->rmaUrl->getCreateUrl($this->getOrder());
    }

    /**
     * {@inheritdoc}
     */
    public function getPrintUrl(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->rmaUrl->getGuestPrintUrl($rma);
    }
}