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



namespace Mirasvit\Rma\Helper;

class Module extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Module\Credit|null
     */
    protected $credit = null;

    public function __construct(
        \Mirasvit\Rma\Helper\Module\Credit $credit,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->credit        = $credit;
        $this->moduleManager = $context->getModuleManager();
        $this->context       = $context;

        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isCreditEnable()
    {
        return $this->moduleManager->isEnabled('Mirasvit_Credit');
    }

    /**
     * @return bool
     */
    public function isFoomanEmailAttachmentsEnable()
    {
        return $this->moduleManager->isEnabled('Fooman_EmailAttachments');
    }

    /**
     * @return object
     */
    public function getCredit()
    {
        return $this->credit->getBalanceFactory();
    }

    /************************/
}
