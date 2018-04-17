<?php

namespace Astralweb\ShareSocial\Block;

class Sharesocial extends \Magento\Framework\View\Element\Template
{
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }
}