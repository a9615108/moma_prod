<?php
/*
    php /var/www/html/as_moma/bin/magento ps:IguangProductHot

    熱銷商品檔案
*/
namespace Astralweb\Cronjob\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;


class IguangProductHot extends Command
{
    protected $CollectionFactory;
    protected $StoreManagerInterface;
    protected $CategoryFactory;
    protected $Collection;
    protected $state;

    protected function configure()
    {
        $this->setName("ps:IguangProductHot");
        $this->setDescription("A command the programmer was too lazy to enter a description for.");
        parent::configure();
    }

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $CollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $StoreManagerInterface,
        \Magento\Catalog\Model\CategoryFactory $CategoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $Collection,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $Configurable,
        \Magento\Framework\App\State $state
    )
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->CollectionFactory     = $CollectionFactory;
        $this->StoreManagerInterface = $StoreManagerInterface;
        $this->CategoryFactory       = $CategoryFactory;
        $this->Collection            = $Collection;
        $this->Configurable          = $Configurable;
        $this->state                 = $state;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->get('Magento\Variable\Model\Variable')->loadByCode('DEBUG_MODEL');
        $DEBUG_MODEL = $model->getName();
        if( $DEBUG_MODEL ){
            $this->state->setAreaCode('adminhtml');
        }

        $product_model      = $objectManager->get('Magento\Catalog\Model\Product');
        $Configurable_model = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable');
// 熱銷分類
        $model = $objectManager->get('Magento\Variable\Model\Variable')->loadByCode('PRODUCT_HOT_CATAGORY_ID');
        $categoryId = $model->getName();

        $storeManager       = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore       = $storeManager->getStore();
        $mediaUrl           = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $baseUrl            = $currentStore->getBaseUrl();

        $directoryList      = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $file               = $objectManager->get('Magento\Framework\Filesystem\Io\File');

        $asArray = true;
        $category = $this->CategoryFactory->create();
        $category->load($categoryId);
        $category_arr =$category->getAllChildren($asArray); // 沒上架的 類別 不會抓

        $parent_pkg_arr = array();
        $fileContent = array();
        $availability_arr = array('','in stock','discontinued');

        $Astralweb_Cronjob_Helper = $objectManager->create('Astralweb\Cronjob\Helper\Data');

// exit;

        $sku_arr = array();

// $output->writeln(json_encode( $category_arr ));
// exit;

        foreach( $category_arr as $category_id ){
            $category = $this->CategoryFactory->create();
            $category->load($category_id);

            $collection = $category
                            ->getProductCollection()
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter([['attribute'=>'type_id','='=>'configurable']])
                            ->addAttributeToFilter([['attribute'=>'status' ,'='=>Status::STATUS_ENABLED]])  // 已啟用
                            ->load();

            if( empty($collection) ){
                continue;
            }

            foreach($collection as $product){
                $product->setStoreId(ScopedAttributeInterface::SCOPE_STORE);
                $product_id = $product->getId();
                $sku_arr[$product->getSku()] = $product->getSku();

                if( empty($fileContent[$product_id]) ){
                    $fileContent[$product_id] = $Astralweb_Cronjob_Helper->get_my_obj_by_parent($product);
                }
            }
        }

        $filter = [];
        foreach( $sku_arr as $sku ){
            $filter[] = ['attribute'=>'sku',['like' => $sku.'%']];
        }

        $fileContent_tmp = array();
        if( ! empty($filter) ){
            $productCollection  = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            $collection = $productCollection->create()
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter([['attribute' => 'type_id','=' => 'simple']])
                            ->addAttributeToFilter([['attribute' => 'status' ,'=' => Status::STATUS_ENABLED ]]) // 已啟用
                            ->addAttributeToFilter($filter)
                            //->joinField('stock_item', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '1')
                            // ->load()
                            ;

            foreach($collection as $product){
                $product->setStoreId(ScopedAttributeInterface::SCOPE_STORE);
                $product_id = $product->getId();

                $product_model->load($product_id);
                $qty =  $product_model->getQuantityAndStockStatus()['qty'];
                $product->setQty($qty);

                $product_tmp = $Configurable_model->getParentIdsByChild($product_id);
                if(isset($product_tmp[0])){
                    $parent_id = $product_tmp[0];
                    $fileContent[$parent_id] = $Astralweb_Cronjob_Helper->get_my_obj_by_sun($fileContent[$parent_id],$product);
                }
            }

            foreach ($fileContent as $key => $product){
                $product['color'] = implode( ',',$product['color'] );
                $product['size'] = implode( ',',$product['size'] );

                $fileContent_tmp[] = $product;
            }
        }
//*
        $fileContent = json_encode($fileContent_tmp);

        $filePath = "/iguang/";
        $pdfPath = $directoryList->getPath('pub').$filePath;
        if (!is_dir($pdfPath)) {
            $file->mkdir($pdfPath, 0775);
        }

        $fileName = 'product_hot';
        $file->open(array('path'=>$pdfPath));
        $file->write($fileName, $fileContent, 0666);
//*/
    }
}