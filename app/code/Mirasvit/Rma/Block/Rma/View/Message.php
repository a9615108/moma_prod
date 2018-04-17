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



namespace Mirasvit\Rma\Block\Rma\View;

class Message extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Mirasvit\Rma\Helper\Message\Url $rmaMessageUrl,
        \Mirasvit\Rma\Helper\Attachment\Html $rmaAttachmentHtml,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Rma\Api\Config\AttachmentConfigInterface $attachmentConfig,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->strategy          = $strategyFactory->create($context->getRequest());
        $this->rmaMessageUrl     = $rmaMessageUrl;
        $this->rmaAttachmentHtml = $rmaAttachmentHtml;
        $this->attachmentConfig  = $attachmentConfig;
        $this->registry          = $registry;
        $this->context           = $context;

        parent::__construct($context, $data);
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
    public function getMessagePostUrl()
    {
        return $this->rmaMessageUrl->getPostUrl();
    }

    /**
     * @return string
     */
    public function getFileInputHtml()
    {
        return $this->rmaAttachmentHtml->getFileInputHtml($this->getStoreId());
    }

    /**
     * @return int
     */
    public function getAttachmentLimits()
    {
        return $this->attachmentConfig->getFileSizeLimit($this->getStoreId());
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->context->getStoreManager()->getStore()->getId();
    }

    /**
     * @return int
     */
    public function getRmaId()
    {
        $rma = $this->getRma();

        return $this->strategy->getRmaId($rma);
    }

}
