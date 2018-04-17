<?php
/*
    php /var/www/html/as_moma/bin/magento ps:IguangProductPartial

    每日商品增刪修檔案(更新商品檔案)
*/
namespace Astralweb\Cronjob\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class IguangProductPartial extends Command
{
    protected function configure()
    {
        $this->setName("ps:IguangProductPartial");
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

        $Ymd = date('Y-m-d');

        $sku_arr_when_subEdit = array();  // 子商品有更動的話 要抓父商品的 sku
// parent_sku_arr

        $sku_arr_when_edit = array();     // 父商品有更動的話 要抓 sku

        $Astralweb_Cronjob_Helper = $objectManager->create('Astralweb\Cronjob\Helper\Data');


        // 抓子商品 (今日有改)
        //   找出 父商品 sku => sku_arr_when_subEdit
        $collection = $productCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter([['attribute' => 'type_id','=' => 'simple']])
            //->addAttributeToFilter([['attribute' => 'status' ,'=' => Status::STATUS_ENABLED ]]) // 已啟用
            ->addAttributeToFilter('updated_at',['from' => $Ymd.' 00:00:00','to'   => $Ymd.' 23:59:59',])      // 只抓當天有修改的資料
            //->load()
            ;

        // 紀錄要抓的父商品的 sku

        $fileContent_tmp = array();

        if( ! empty($collection) ){
                foreach ($collection as $product){
                    $product_exp = explode('-',$product->getSku());
                    $sku_arr_when_subEdit[$product_exp[0]] = $product_exp[0];
                }

    // $output->writeln("1 : ========== ");
    // $output->writeln("sku_arr_when_subEdit : ". json_encode($sku_arr_when_subEdit) );

            $productCollection  = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            // $productCollection->clear()->getSelect()->reset('where');

            // 抓父商品 (今日有改)
            $collection = $productCollection->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter([['attribute'=>'type_id','='=>'configurable']])
                //->addAttributeToFilter([['attribute'=>'status' ,'='=>Status::STATUS_ENABLED]])  // 已啟用
                ->addAttributeToFilter([
                    // ['attribute'=>'sku',array('in' => implode(',',$sku_arr_when_subEdit) )],
                    ['attribute'=>'updated_at',['from' => $Ymd.' 00:00:00','to'   => $Ymd.' 23:59:59']]            // 只抓當天有修改的資料
                ])
                //->load()
                ;

            foreach($collection as $product){
                $sku_arr_when_edit[$product->getSku()] = $product->getSku();   // 今日有改
            }
    // $output->writeln("2 : ========== ");
    // $output->writeln("sku_arr_when_edit : ". json_encode($sku_arr_when_edit) );

            //$productCollection->clear()->getSelect()->reset('where');
            $productCollection  = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            // 抓父商品 (今日有改 + 子商品今日有改)
            $collection = $productCollection->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter([['attribute'=>'type_id','='=>'configurable']])
                //->addAttributeToFilter([['attribute'=>'status' ,'='=>Status::STATUS_ENABLED]])  // 已啟用
                ->addAttributeToFilter([
                    ['attribute'=>'sku',array('in' => implode(',',$sku_arr_when_subEdit) )],
                    ['attribute'=>'sku',array('in' => implode(',',$sku_arr_when_edit) )],
                    // ['attribute'=>'updated_at',['from' => $Ymd.' 00:00:00','to'   => $Ymd.' 23:59:59',]]            // 只抓當天有修改的資料
                ])
                //->load()
                ;

            $fileContent = array();

    // $output->writeln("3 : ========== ");
            // 只抓主類別的  // landing page
            //               // 更多圖
            foreach($collection as $product){
                $product->setStoreId(ScopedAttributeInterface::SCOPE_STORE);
                $fileContent[$product->getId()] = $Astralweb_Cronjob_Helper->get_my_obj_by_parent($product);
            }

            // $productCollection->clear()->getSelect()->reset('where');
            $productCollection  = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            $filter = [['attribute'=>'updated_at',['from' => $Ymd.' 00:00:00','to' => $Ymd.' 23:59:59']]];  // 只抓當天有修改的資料
            $sku_like = [];
            foreach( $sku_arr_when_edit as $sku ){
                $sku_like[] = ['attribute'=>'sku',['like' => $sku.'%']];
            }
            $filter = array_merge( $filter, $sku_like );

            // 抓子商品 (今日有改 + 父商品今日有改)
            $collection = $productCollection->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter([['attribute' => 'type_id','=' => 'simple']])
                //->addAttributeToFilter([['attribute' => 'status' ,'=' => Status::STATUS_ENABLED ]]) // 已啟用
                ->addAttributeToFilter($filter)
                ->joinField('stock_item', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '1')
                //->load()
                ;

            $availability_arr = array('','in stock','discontinued');

    // $output->writeln("4 : ========== ");
            // 紀錄要抓的父商品的 sku
            foreach ($collection as $product){
                //$product->setStoreId(ScopedAttributeInterface::SCOPE_STORE);

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

        $fileName = 'product_partial';
        $file->open(array('path'=>$pdfPath));
        $file->write($fileName, $fileContent, 0666);
//*/
    }
}