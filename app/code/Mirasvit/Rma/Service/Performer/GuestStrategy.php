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


namespace Mirasvit\Rma\Service\Performer;


class GuestStrategy implements \Mirasvit\Rma\Api\Service\Performer\PerformerInterface
{

    public function __construct(
        \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig
    ) {
        $this->rmaConfig = $rmaConfig;
    }
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * {@inheritdoc}
     */
    public function setPerfomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessageAttributesBeforeAdd($message, $params = [])
    {
        if (!isset($params['isNotified'])) {
            $params['isNotified'] = 1;
        }
        if (!isset($params['isVisible'])) {
            $params['isVisible'] = 1;
        }
        $message->setIsCustomerNotified($params['isNotified']);
        $message->setIsVisibleInFrontend($params['isVisible']);
        $message->setCustomerId($this->getId())
            ->setCustomerName($this->customer->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function setRmaAttributesBeforeSave($rma)
    {
        $rma->setStatusId($this->rmaConfig->getDefaultStatus());
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->customer ? $this->customer->getId() : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->customer->getName();
    }
}