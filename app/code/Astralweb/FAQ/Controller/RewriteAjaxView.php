<?php

namespace Astralweb\FAQ\Controller;

use Magestore\Faq\Controller\Index\Ajaxview;
use Magento\Framework\Controller\ResultFactory;

class RewriteAjaxView extends Ajaxview
{

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context)
    {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $block = $resultLayout->getLayout()->createBlock('Magestore\Faq\Block\Listfaq')
            ->setTemplate('list.phtml')->toHtml();
        echo $block;
        exit;
    }
}