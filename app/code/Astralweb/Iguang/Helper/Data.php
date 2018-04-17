<?php
namespace Astralweb\Iguang\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const SITE            = 2 ;           // 導購媒體代號
    const SHOPID          = 251 ;         // 商城代號
    const AUTHKEY         = '9a3607c7' ;  // 認證登入代碼
    const ORDERINFO_URL   = 'https://buy.line.me/tracking/orderinfo';   // 訂單追蹤設定回傳 url
    const ORDERFINISH_URL = 'https://buy.line.me/tracking/orderfinish'; // 訂單記錄與銷售完成記錄 url

    public function get_orderItems_data($orderItems, $Product_model, $Category_model ){

        $itemQty = array(
            'parent' => array(),
            'son' => array(),
        );

        foreach ($orderItems as $item) {
            if( empty( $item->getParentItemId() ) ){

                $productId = $item->getProductId();
                $product = $Product_model->load($productId);
                $CategoryIds = $product->getCategoryIds();

                $sub_category1 = '';
                $sub_category2 = '';

                // 由於商品的類別可能不只一個 這邊只抓第一個
                foreach( $CategoryIds as $id){
                    $category = $Category_model->load($id);
                    if( $category->getLevel() == 3 ){
                        $sub_category2 = $category->getName();
                            if( $sub_category1 == '' ){
                                $category = $Category_model->load($category->getParentId());
                                $sub_category1 = $category->getName();
                            }
                        break;
                    }
                }
                // 商品的類別或許沒有 level 3 只有 level 2 的 (不確定)
                if( $sub_category1 == ''){
                    foreach( $CategoryIds as $id){
                        $category = $Category_model->load($id);
                        if( $category->getLevel() == 2 ){
                            $sub_category1 = $category->getName();
                            break;
                        }
                    }
                }

                $tmp = array(
                    'product_name' => $item->getName(),
                    //'product_amount' => (int)$item->getPrice(),
                    'product_amount' => (int)$item->getRowTotal(),  // price * qty
                    'sub_category1' => $sub_category1,
                );
                if( $sub_category2 != '' ){
                    $tmp['sub_category2'] = $sub_category2;
                }

                $itemQty['parent'][$item->getItemId()] = $tmp;
            }else{
                $itemQty['son'][$item->getItemId()] = array(
                    'product_type' => 'normal',
                    'parent' => $item->getParentItemId(),
                );
            }
        }

        return $itemQty;
    }
}