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



namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit;


class Form extends \Magento\Backend\Block\Widget\Form
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form\GeneralInfo $generalInfo,
        \Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form\ShippingAddress $shippingAddressForm,
        \Mirasvit\Rma\Helper\Rma\Calculate $calculateHelper,
        \Mirasvit\Rma\Helper\Module $moduleHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->generalInfo         = $generalInfo;
        $this->shippingAddressForm = $shippingAddressForm;
        $this->rmaManagement       = $rmaManagement;
        $this->calculateHelper     = $calculateHelper;
        $this->moduleHelper        = $moduleHelper;
        $this->pricingHelper       = $pricingHelper;
        $this->registry            = $registry;
        $this->context             = $context;

        parent::__construct($context, $data);
    }

    /**
     * Old exchange amount.
     *
     * @var int
     */
    protected $oldAmount;

    /**
     * New exchange amount.
     *
     * @var int
     */
    protected $newAmount;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('rma/edit/form.phtml');
        $amounts = $this->calculateHelper->calculateExchangeAmounts($this->getRma());

        $this->oldAmount = $amounts['oldAmount'];
        $this->newAmount = $amounts['newAmount'];
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->registry->registry('current_rma');
    }

    /**
     * @return string
     */
    public function getGeneralInfoFormHtml()
    {
        return $this->generalInfo->getGeneralInfoFormHtml($this->getRma());
    }

    /**
     * @return bool|\Magento\Framework\Data\Form
     */
    public function getFieldForm()
    {
        return $this->generalInfo->getFieldForm($this->getRma());
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getShippingAddressFormHtml()
    {
        return $this->shippingAddressForm->getFormHtml($this->getRma());
    }

    /**
     * Items html
     *
     * @return string
     */
    public function getItemsHtml()
    {
        return $this->getLayout()->createBlock('\Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form\Items')
            ->setRma($this->getRma())
            ->setTemplate('rma/edit/form/items.phtml')
            ->toHtml();
    }

    /**
     * Add message html
     *
     * @return string
     */
    public function getAddMessageHtml()
    {
        return $this->getLayout()->createBlock('\Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form\Message')
            ->setRma($this->getRma())
            ->setTemplate('rma/edit/form/add_message.phtml')
            ->toHtml();
    }

    /**
     * History html
     *
     * @return string
     */
    public function getHistoryHtml()
    {
        return $this->getLayout()->createBlock('\Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form\History')
            ->setRma($this->getRma())
            ->setTemplate('rma/edit/form/history.phtml')
            ->toHtml();
    }

    /**
     * @return float
     */
    public function getExchangeOldAmount()
    {
        return $this->oldAmount;
    }

    /**
     * @return float
     */
    public function getExchangeNewAmount()
    {
        return $this->newAmount;
    }

    /**
     * @return float
     */
    public function getExchangeDiffAmount()
    {
        return $this->newAmount - $this->oldAmount;
    }

    /**
     * @return bool|int
     */
    public function getIsCreditEnabled()
    {
        return $this->moduleHelper->isCreditEnable();
    }

    /**
     * @return \Magento\Framework\Pricing\Helper\Data
     */
    public function getPricingHelper()
    {
        return $this->pricingHelper;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return float
     */
    public function getCreditAmount($rma)
    {
        $balance = 0;
        $credit = $this->moduleHelper->getCredit();
        if ($credit) {
            $balance = $credit->getBalanceFactory()
                ->loadByCustomer($this->rmaManagement->getCustomer($rma))
                ->getAmount();
        }

        return $balance;
    }
}
