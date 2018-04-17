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




namespace Mirasvit\Rma\Service\Rma\RmaManagement;


class Add implements \Mirasvit\Rma\Api\Service\Rma\RmaManagement\AddInterface
{

    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
    ) {
        $this->rmaManagement = $rmaManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function initFromOrder(\Mirasvit\Rma\Api\Data\RmaInterface $rma, $orderId)
    {
        $rma->setOrderId($orderId);
        $order = $this->rmaManagement->getOrder($rma);

        if ($order->getCustomerId()) {
            $rma->setCustomerId($order->getCustomerId());

            $customer = $this->rmaManagement->getCustomer($rma);
            $rma->setFirstname($customer->getFirstname());
            $rma->setLastname($customer->getLastname());
            $rma->setEmail($customer->getEmail());
        } else {
            $rma->setEmail($order->getCustomerEmail());
        }

        $address = $order->getShippingAddress();
        if (!$address) {
            $address = $order->getBillingAddress();
        }
        $data = $address->getData();
        if (!$address->getEmail() || trim($address->getEmail()) == '') {
            unset($data['email']);
        }
        unset($data['increment_id']);
        $rma->addData($data);
    }

}