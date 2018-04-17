<?php
/*
    php /var/www/html/as_moma/bin/magento ps:iguangallprod

    全部商品檔案
*/
namespace Astralweb\Cronjob\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class IguangAllProd extends Command
{
    protected function configure(){
        $this->setName("ps:iguangallprod");
        $this->setDescription("A command the programmer was too lazy to enter a description for.");
        parent::configure();
    }

    public function __construct(
        \Magento\Framework\App\State $state
    )
    {
        $this->state  = $state;
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
        //$productCollection  = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $productCollection  = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $Category_model     = $objectManager->create('Magento\Catalog\Model\Category');
        //$ProductRepository  = $objectManager->create('Magento\Catalog\Model\ProductRepository');
        $Configurable_model = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable');
        $storeManager       = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore       = $storeManager->getStore();
        $mediaUrl           = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $baseUrl            = $currentStore->getBaseUrl();

        $directoryList      = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $file               = $objectManager->get('Magento\Framework\Filesystem\Io\File');

        $Astralweb_Cronjob_Helper = $objectManager->create('Astralweb\Cronjob\Helper\Data');

// 測試用
//$sku = '71P090';
// $sku = '71M047';
//$sku = '72kj49';
//$sku = '81m003';
//$sku = '71N009';
$sku = '52J001';

        // 先抓父商品來跑
        $collection =   $productCollection->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter([['attribute'=>'type_id','='=>'configurable']])
                // ->addAttributeToFilter([['attribute'=>'status' ,'='=>Status::STATUS_ENABLED]])  // 已啟用
/*
// 測試用
->addAttributeToFilter(
    array(
        array(
            'attribute' => 'sku',
            'like' =>  $sku.'%',
        ),
    )
)
//*/
                //->load();
                ;

        $fileContent = array();

        // 只抓主類別的  // landing page
        //               // 更多圖

        $fileContent_tmp = array();
        if( ! empty($collection) ){
            foreach($collection as $product){
                $product->setStoreId(ScopedAttributeInterface::SCOPE_STORE);
                $fileContent[$product->getId()] = $Astralweb_Cronjob_Helper->get_my_obj_by_parent($product);
            }

            // 清除 父商品 的搜尋條件
            //$productCollection->clear()->getSelect()->reset('where');
            $productCollection  = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

            // 抓子商品
            $collection = $productCollection->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter([['attribute'=>'type_id','='=>'simple']])
                    //->addAttributeToFilter([['attribute'=>'status' ,'='=>Status::STATUS_ENABLED]])  // 已啟用
                    //->joinField('stock_item', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', 'qty>0')
                    ->joinField('stock_item', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '1')
/*
// 測試用
->addAttributeToFilter(
    array(
        array(
            'attribute' => 'sku',
            'like' =>  $sku.'%',

        ),
    )
)
//*/
                // ->load();
                ;
            //$collection->setStoreId(ScopedAttributeInterface::SCOPE_STORE);

            foreach ($collection as $product){
                // $product->setStoreId(ScopedAttributeInterface::SCOPE_STORE);

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

        $fileName = 'product_full';
        $file->open(array('path'=>$pdfPath));
        $file->write($fileName, $fileContent, 0666);
//*/
    }
}