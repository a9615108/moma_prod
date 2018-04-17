<?php

namespace Astralweb\Contactus\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_collectionFactory;

    public function __construct(Context $context, \Astralweb\Contactus\Model\ResourceModel\Contact\CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function getEmail()
    {
        return $this->getCollections();
    }

    private function getCollections() {

        $Collection = $this->_collectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('is_active',1);


        return $Collection;
    }
}
