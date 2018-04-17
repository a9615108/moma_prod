<?php
namespace Astralweb\Cronjob\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper{

    public $baseUrl_arr = array();
    public $additional_image_link_arr = array();

    public function set_baseUrl_arr($arr){
        $this->baseUrl_arr = $arr;
    }

    public function set_additional_image_link_arr($arr){
        $this->additional_image_link_arr = $arr;
    }

    // input  $product  Magento\Catalog\Model\Product
    // output 愛逛街 的 商品欄位 格式
    public function get_iguang_product($product){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $Configurable_model = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable');
        // $Category_model     = $objectManager->create('Magento\Catalog\Model\Category');

        $storeManager       = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore       = $storeManager->getStore();
        $mediaUrl           = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $baseUrl            = $currentStore->getBaseUrl();

        $obj = array();

        // $output->writeln("SKU :". $product->getSku() .' : '. $product->getData('type_id') );
        $product_tmp = $Configurable_model->getParentIdsByChild($product->getId());

        $parent_id = 0;
        if(isset($product_tmp[0])){
            // $output->writeln("parent :". $product_tmp[0] );
            $parent_id = $product_tmp[0];
        }

        // 商品編號
        // $output->writeln("product_id :". $product->getSku());
        $obj['product_id'] = $product->getSku();

        // 商品名稱
        // $output->writeln("product_name :". $product->getName());
        $obj['product_name'] = $product->getName();

        // 商品簡要說明或特色
        // $output->writeln("description :". $product->getMetaDescription());

        $description = $product->getMetaDescription();
        $obj['description'] = ($description)?$description:'';

        // 商品詳細說明（允許使用 html 格式）
        //$output->writeln("l_description :". $product->getDescription());
        $Description = $product->getDescription();
        $Description = str_replace('"}}', '', $Description);
        $Description = str_replace('{{media url="', $mediaUrl, $Description);
        //$obj['l_description'] = $Description;

        // landing page  // 子商品須抓主商品的資料
        // $output->writeln("link :".  $product->getUrlKey() );
        $obj['link'] = '';
        if( isset($this->baseUrl_arr[$parent_id]) ){
            $obj['link'] = $this->baseUrl_arr[$parent_id];
        }

        // 商品主圖網址
        // $output->writeln("image_link :". $product->getImage());
        /*
            /8/1/81km27-40_1_1_1.jpg
            http://local.astralweb.com.tw/pub/media/catalog/product/cache/image/1000x1500/e9c3970ab036de70892d86c6d221abfe/8/1/81km27-40_1_1_1.jpg
            http://local.astralweb.com.tw/pub/media/catalog/product/8/1/81km27-40_1_1_1.jpg
        */
        $obj['image_link'] = $mediaUrl .'catalog/product'. $product->getImage();

        // 更多圖        // 子商品須抓主商品的資料
        //$output->writeln("additional_image_link :". $product->getDescription());
        if( isset($this->additional_image_link_arr[$parent_id]) ){
            $obj['additional_image_link'] = $this->additional_image_link_arr[$parent_id];
        }

        // 供應情況
        // $output->writeln("availability :". $product->getStatus());               // 1 : 現貨 : in stock
        if( $product->getStatus() == 1
            && $product->getQty() >0
        ){
            $obj['availability'] = 'in stock';
        }else{
            $obj['availability'] = 'discontinued';
        }

        // 商品價格
        // $output->writeln("price :". $product->getData('price'));
        $obj['price'] = $product->getData('price');

        // 商品分類，僅填兩種類型(一般商品/3C商品)
        // product_type : normal
        $obj['product_type'] = 'normal';

        // 該商品對應到目錄更新檔之分類目錄編號
        // $output->writeln("product_category_value :". implode(",", $product->getCategoryIds()) );
    /*
        $categories_arr = array();
        $categories = $product->getCategoryIds();
        foreach($categories as $category){

            if( ! isset($categories_arr[$category]) ){
                $cat = $Category_model->load($category);
                $categories_arr[$category] = $cat->getName();
            }

            $output->writeln("getName :". $categories_arr[$category]);
        }
    */
        $obj['product_category_value'] = implode(",", $product->getCategoryIds());

        // 商品折扣價
        // sale_price     不填寫

        // 商品是否為成人商品
        // age_group      normal
        $obj['age_group'] = 'normal';

        // 商品顏色
        // $output->writeln("color :". $product->getData('c_color'));   // 16
        // $output->writeln("color :". $product->getResource()->getAttribute('c_color')->getFrontend()->getValue($product));
        $obj['color'] = $product->getResource()->getAttribute('c_color')->getFrontend()->getValue($product);

        // 商品尺寸
        // $output->writeln("size :". $product->getData('size'));       // 66
        // $output->writeln("size :". $product->getResource()->getAttribute('size')->getFrontend()->getValue($product));
        $obj['size'] = $product->getResource()->getAttribute('size')->getFrontend()->getValue($product);

        // 運費說明
        // shipping : TWN:NWT:Ground:80
        $obj['shipping'] = 'TWN:NWT:Ground:80';

        return $obj;
    }

    // input  $product  Magento\Catalog\Model\Product
    // output 我 的 商品欄位 格式
    public function get_my_obj_by_parent($product){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $Configurable_model = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable');
        // $Category_model     = $objectManager->create('Magento\Catalog\Model\Category');

        $storeManager       = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore       = $storeManager->getStore();
        $mediaUrl           = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $baseUrl            = $currentStore->getBaseUrl();

        $obj = array();

        // $output->writeln("SKU :". $product->getSku() .' : '. $product->getData('type_id') );
        $product_tmp = $Configurable_model->getParentIdsByChild($product->getId());

        // 商品編號
        // $output->writeln("product_id :". $product->getSku());
        $obj['product_id'] = $product->getSku();

        // 商品名稱
        // $output->writeln("product_name :". $product->getName());
        $obj['product_name'] = $product->getName();

        // 商品簡要說明或特色
        // $output->writeln("description :". $product->getMetaDescription());

        $description = $product->getMetaDescription();
        $obj['description'] = ($description)?$description:'';

        // 商品詳細說明（允許使用 html 格式）
        //$output->writeln("l_description :". $product->getDescription());
        $Description = $product->getDescription();
        $Description = str_replace('"}}', '', $Description);
        $Description = str_replace('{{media url="', $mediaUrl, $Description);
        //$obj['l_description'] = $Description;

        // landing page  // 子商品須抓主商品的資料
        // $output->writeln("link :".  $product->getUrlKey() );
        $obj['link'] = $baseUrl . $product->getUrlKey();;


        // 商品主圖網址
        // $output->writeln("image_link :". $product->getImage());
        /*
            /8/1/81km27-40_1_1_1.jpg
            http://local.astralweb.com.tw/pub/media/catalog/product/cache/image/1000x1500/e9c3970ab036de70892d86c6d221abfe/8/1/81km27-40_1_1_1.jpg
            http://local.astralweb.com.tw/pub/media/catalog/product/8/1/81km27-40_1_1_1.jpg
        */
        $obj['image_link'] = $mediaUrl .'catalog/product'. $product->getImage();



        // 更多圖        // 子商品須抓主商品的資料
        //$output->writeln("additional_image_link :". $product->getDescription());

$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);

// $logger->info('Description : '. $product->getDescription() );

        preg_match_all('/{{media url="([\d\w\/_\.-]+)"}}/',$product->getDescription(), $arrayout);

// $logger->info('arrayout : '.  json_encode($arrayout) );

        if( isset($arrayout[1]) ){
            foreach( $arrayout[1] as $key =>$image_url  ){
                if( strpos($image_url,'/size/') ){
                    continue;
                }
                $arrayout[1][$key] = $mediaUrl . $image_url;
            }
            $obj['additional_image_link'] = implode(",", $arrayout[1]);
        }

        // 供應情況
        // $output->writeln("availability :". $product->getStatus());               // 1 : 現貨 : in stock
        $obj['availability'] = 'discontinued';

        // 商品價格
        // $output->writeln("price :". $product->getData('price'));
        $obj['price'] = (int)$product->getData('price');

        // 商品分類，僅填兩種類型(一般商品/3C商品)
        // product_type : normal
        $obj['product_type'] = 'normal';

        // 該商品對應到目錄更新檔之分類目錄編號
        // $output->writeln("product_category_value :". implode(",", $product->getCategoryIds()) );

        $obj['product_category_value'] = implode(",", $product->getCategoryIds());

        // 商品折扣價
        // sale_price     不填寫

        // 商品是否為成人商品
        // age_group      normal
        $obj['age_group'] = 'normal';

        // 商品顏色
        // $output->writeln("color :". $product->getData('c_color'));   // 16
        // $output->writeln("color :". $product->getResource()->getAttribute('c_color')->getFrontend()->getValue($product));
        $obj['color'] = array();

        // 商品尺寸
        // $output->writeln("size :". $product->getData('size'));       // 66
        // $output->writeln("size :". $product->getResource()->getAttribute('size')->getFrontend()->getValue($product));
        $obj['size'] = array();

        // 運費說明
        // shipping : TWN:NWT:Ground:80
        $obj['shipping'] = 'TWN:NWT:Ground:80';

        return $obj;
    }

    public function get_my_obj_by_sun($my_obj,$product){

        $color = $product->getResource()->getAttribute('c_color')->getFrontend()->getValue($product);
        if( $color != 'No' ){
            $my_obj['color'][$color] = $color;
        }

        $size = $product->getResource()->getAttribute('size')->getFrontend()->getValue($product);
        if( $size != 'No' ){
            $my_obj['size'][$size] = $size;
        }

        if( $my_obj['availability'] == 'discontinued' ){
            if( $product->getStatus() == 1
                && $product->getQty() >0
            ){
                $my_obj['availability'] = 'in stock';
            }
        }

        return $my_obj;
    }
}