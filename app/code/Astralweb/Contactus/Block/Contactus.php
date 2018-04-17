<?php
namespace Astralweb\Contactus\Block;
class Contactus extends \Magento\Framework\View\Element\Template
{
    protected $_helper;
    protected $_collectionFactory;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Astralweb\Contactus\Model\ResourceModel\Contact\CollectionFactory $collectionFactory,
        array $data = []
    ) {

        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }


    private function getCollections() {

        $Collection = $this->_collectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('is_active',1);


        return $Collection;
    }

    public function getEmail()
    {
        return $this->getCollections();
    }



}
