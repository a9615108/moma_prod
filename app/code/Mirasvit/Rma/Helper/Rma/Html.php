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



namespace Mirasvit\Rma\Helper\Rma;

class Html extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->rmaManagement = $rmaManagement;

        parent::__construct($context);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getReturnAddressHtml(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return nl2br($this->rmaManagement->getReturnAddressHtml($rma));
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getShippingAddressHtml(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return nl2br($this->rmaManagement->getShippingAddressHtml($rma));
    }
}