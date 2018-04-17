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



namespace Mirasvit\Rma\Block\Rma;

class View extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface $messageManagement,
        \Mirasvit\Rma\Api\Service\Message\MessageManagement\SearchInterface $messageSearchManagement,
        \Mirasvit\Rma\Helper\Rma\Html $rmaHtmlHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement,
        array $data = []
    ) {
        $this->messageManagement       = $messageManagement;
        $this->messageSearchManagement = $messageSearchManagement;
        $this->rmaHtmlHelper           = $rmaHtmlHelper;
        $this->registry                = $registry;
        $this->rmaManagement           = $rmaManagement;
        $this->fieldManagement         = $fieldManagement;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($rma = $this->getRma()) {
            $this->pageConfig->getTitle()->set(__('RMA #%1', $rma->getIncrementId()));
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle(
                    __('RMA #%1 - %2', $rma->getIncrementId(),
                        $this->rmaManagement->getStatus($rma)->getName())
                );
            }
        }
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->registry->registry('current_rma');
    }
}
