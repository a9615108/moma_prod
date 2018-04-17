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



namespace Mirasvit\Rma\Helper\Order;

/**
 * Helper which creates different html code
 */
class Html extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->orderFactory = $orderFactory;
        $this->localeDate = $localeDate;
        $this->context = $context;
        parent::__construct($context);
    }


    /**
     * @param int|\Magento\Sales\Api\Data\OrderInterface        $order
     * @param bool|false $url
     * @return string
     */
    public function getOrderLabel($order, $url = false)
    {
        if (!is_object($order)) {
            $order = $this->orderFactory->create()->load($order);
        }
        $res = "#{$order->getRealorderId()}";
        if ($url) {
            $res = "<a href='{$url}' target='_blank'>$res</a>";
        }
        $res .= __(
            ' at %1 (%2)',
            $this->localeDate->formatDate($order->getCreatedAt(), \IntlDateFormatter::MEDIUM),
            strip_tags($order->formatPrice($order->getGrandTotal()))
        );

        return $res;
    }

}