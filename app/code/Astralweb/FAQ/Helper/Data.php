<?php
namespace Astralweb\FAQ\Helper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $scopeConfig;
    protected $_directoryList;
    const BANNER_IMG = 'faq_banner/parameters/slider_image_1';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->_directoryList = $directoryList;
    }
    
    public function getFaqBanner()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::BANNER_IMG, $storeScope);
    }

    public function getDirectoryList() {
    
        return $this->_directoryList;
    
    }
}
