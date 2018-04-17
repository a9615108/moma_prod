<?php
namespace Astralweb\ShippingStorePickUp\Model\Shop;

use Astralweb\ShippingStorePickUp\Model\ResourceModel\shop\CollectionFactory as postCollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $request;

    protected $loadedData;

    public function __construct(
        postCollectionFactory $postCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        // \ThankIT\UIcomponents\Model\PostFactory $postFactory,
        \Astralweb\ShippingStorePickUp\Model\shopFactory $postFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection  = $postCollectionFactory->create();
        $this->postFactory = $postFactory;
        $this->request     = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {

        $requestId = $this->request->getParam($this->requestFieldName);

        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $post = $this->postFactory->create();
        // 如果是 update 操作
        if ($requestId) {
            $post->load($requestId);
            if (!$post->getId()) {
                throw NoSuchEntityException::singleField('id', $requestId);
            }
            $postData = $post->getData();

            $this->loadedData[$requestId]['post_form'] = $postData;
        } else {
            // new 操作
            $this->loadedData = [];
        }

        return $this->loadedData;
    }
}