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


namespace Mirasvit\Rma\Service\Rma;

/**
 *  We put here only methods directly connected with RMA properties
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RmaAdapter extends \Mirasvit\Rma\Model\Rma
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Message\Url $urlMessageHelper,
        \Mirasvit\Rma\Helper\Rma\Url $rmaUrl,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Mirasvit\Rma\Helper\Rma\Data $rmaData,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($rmaData, $orderFactory, $context, $registry, $resource, $resourceCollection, $data);

        $this->urlMessageHelper    = $urlMessageHelper;
        $this->rmaUrl              = $rmaUrl;
        $this->rmaManagement       = $rmaManagement;
        $this->rmaSearchManagement = $rmaSearchManagement;
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\StatusInterface
     */
    public function getStatus()
    {
        return $this->rmaManagement->getStatus($this);
    }


    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->rmaManagement->getOrder($this);
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->rmaManagement->getCustomer($this);
    }

    /**
     * @return \Magento\User\Api\Data\UserInterface
     */
    public function getUser()
    {
        return $this->rmaManagement->getUser($this);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore()
    {
        return $this->rmaManagement->getStore($this);
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->rmaManagement->getFullName($this);
    }


    /**
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getItems()
    {
        return $this->rmaSearchManagement->getItems($this);
    }


    /**
     * @return void
     */
    public function markAsRead()
    {
        $this->rmaManagement->markAsRead($this);
    }

    /**
     * @return void
     */
    public function markAsUnread()
    {
        $this->rmaManagement->markAsUnread($this);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface
     */
    public function getReturnLabel()
    {
        return $this->rmaManagement->getReturnLabel($this);
    }

    /**
     * @return string
     */
    public function getShippingAddressHtml()
    {
        return $this->rmaManagement->getShippingAddressHtml($this);
    }

    /**
     * @return string
     */
    public function getReturnAddressHtml()
    {
        return $this->rmaManagement->getReturnAddressHtml($this);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->rmaManagement->getCode($this);
    }

    /**
     * @return string
     */
    public function getCreatedAtFormated()
    {
        return $this->rmaManagement->getCreatedAtFormated($this);
    }

    /**
     * @return string
     */
    public function getUpdatedAtFormated()
    {
        return $this->rmaManagement->getUpdatedAtFormated($this);
    }

    /**
     * @return string
     */
    public function getGuestPrintUrl()
    {
        return $this->rmaUrl->getGuestPrintUrl($this);
    }

    /**
     * @return bool|string
     */
    public function getGuestPrintLabelUrl()
    {
        if (!$this->getReturnLabel()) {
            return false;
        }

        return $this->rmaUrl->getGuestPrintLabelUrl($this);
    }

    /**
     * @return string
     */
    public function getConfirmationUrl()
    {
        return $this->urlMessageHelper->getConfirmationUrl($this);
    }
}