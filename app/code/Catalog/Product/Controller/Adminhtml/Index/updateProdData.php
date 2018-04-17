<?php
namespace Catalog\Product\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class updateProdData extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;
    protected $_storeManager;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,  
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;  
        parent::__construct($context, $productBuilder);
    }

    /*  英吋轉公分
        輸入 '14 1/2'
        輸出 36.83    // 14.5 * 2.54
    */
    function inch2cm($i){
        if( $i == '' ){
            return '';
        }

        $a = explode(' ',$i);
        $re = $a[0];

        if( isset($a[1]) ){
            $f = explode('/',$a[1]) ;
            $re += ( $f[0] / $f[1] );
        }

        return round($re * 2.54);
    }


    /**
     * Get options fieldset block
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $store_id = $this->_storeManager->getStore()->getId(); 
 
        $productRepository  = $this->_objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product');

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/POS_PROD_prodInfo.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        // 整理選取的商品 
        $sku_arr = array();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        foreach ($collection->getItems() as $product) {
            $sku = $product->getSku();
            $sku = explode( '-', $sku )[0];
            $sku_arr[$sku] = $sku;
        }

        // 統計更新筆數
        $productUpdate = 0;
        foreach ($sku_arr as $sku) {

            /*  $sku 要記 log */
            $logger->info('sku : '.$sku);

            $model = $this->_objectManager->get('Magento\Variable\Model\Variable')->loadByCode('POS_PROD_prodInfo');
            $POS_PROD_prodInfo = $model->getName();
            $url = $POS_PROD_prodInfo . '?Cloth=' . $sku;
            
            /*  $url 要記 log */
            $logger->info('url : '.$url);

            $s = curl_init();
            curl_setopt($s,CURLOPT_URL,$POS_PROD_prodInfo . '?Cloth=' . $sku);
            curl_setopt($s, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($s, CURLOPT_POST, 1);
            curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query(array('Cloth' => $sku)));
            curl_setopt($s, CURLOPT_RETURNTRANSFER, true); 
            $result = curl_exec($s);
             
            /* $result 要記 log */
            $logger->info($result);

            $result = json_decode($result,true);
            $result = $result[0];

            // 第一部分：商品狀態
            if( ! $result['Cloth_status'] ){
                // 查無資料
                continue;
            }

            // 第二部份：商品基本資料(單筆)
            $product_data[ $result['Cloth'] ] = array(
                'name'      => $result['Spec'],     // 品名
                'weight'    => $result['Net_w'],    // 淨重
            );

            // Content 商品成分
            $cont_short_desc_arr = [];
            for( $i=1;$i<7;$i++ ){
                if( $result['Material'.$i] ){
                    $cont_short_desc_arr[] = $result['Material'.$i] .' - '. $result['Rate'.$i] .'%';
                }
            }
            $cont_short_desc = join( $cont_short_desc_arr , '<br/>' );
            $product_data[ $result['Cloth'] ]['cont_short_desc'] = '<p>'. $cont_short_desc. '</p>';

            // 第三部份：商品明細資料(多筆)
            $sub_product = [];
            foreach( $result['Item'] as $arr ){
                $sub_product[ $arr['Cloth_c_s'] ] = array(
                    'Color'      => $arr['Color'],      // 顏色
                    'Color_name' => $arr['Color_name'], // 顏色名稱
                    'Size'       => $arr['Size'],       // 尺寸
                    'Sprice'     => $arr['Sprice'],     // 定價
                );
            }
            $product_data[ $result['Cloth'] ]['sub_product'] = $sub_product;

            // 第四部份：商品系列資料(多筆)             // Content 洗滌方式
            $Cloth_m_set = '';
            foreach( $result['Cloth_m_set'] as $arr ){
                $Cloth_m_set .= '<p><img title="MOMA" src="{{media url="wysiwyg/Wash/'. $arr['Set_no'] .'.jpg"}}" /></p>';
            }
            $product_data[ $result['Cloth'] ]['Cloth_m_set'] = $Cloth_m_set;

            // 第五部份：商品尺寸表資料(多筆)
            $tbody = '';
            foreach( $result['Cloth_m_size'] as $arr ){
                if( $arr['Shop_name'] == '淨重' ){
                    continue;
                }

                $tbody .= '<tr>';
                $tbody .= '<td class="xl65" width="93" height="18">'. $arr['Shop_name'] .'</td>';
                for($i=1;$i<=8;$i++){

                    if( $arr['Shop_name'] == '尺碼' ){
                        $Size_num = $arr['Size_'.$i];
                    }else{
                        $Size_num = $this->inch2cm($arr['Size_'.$i]);
                    }

                    if($arr['Size_'.$i] != ''){
                        $tbody .= '<td class="xl65" width="93">'. $Size_num .'</td>';
                    }
                }
                $tbody .= '</tr>';
            }
            $Size = '<table style="width: 279px;" border="0" cellspacing="0" cellpadding="0">
                        <colgroup><col span="3" width="93" /></colgroup>
                        <tbody>'. $tbody .'</tbody>
                    </table>
                    <table style="width: 0px;" border="0" align="r">
                        <tbody>
                            <tr>
                                <td>'. __('size table note') .'</td>
                            </tr>
                        </tbody>
                    </table>';
        //  $Size.= '<p><img src="{{media url="wysiwyg/new-size/newjumpsuit.jpg"}}" alt="" /></p>';

            $product_data[ $result['Cloth'] ]['Size'] = $Size;

            foreach( $product_data as $sku => $data){
                // 主商品

                if( $product->getIdBySku($sku) ){
                    // 存在
                    $product = $productRepository->get($sku);
                }else{
                    // 不存在
                    $product->setSku($sku);
                }
                
                $product
                    // 第二部份
                    ->setName(  $data['name'])
                    ->setWeight($data['weight'])
                ->save();

                // $product->addAttributeUpdate('description',         $data['description'], $store_id);    // 商品介紹
                $product->addAttributeUpdate('short_description',   $data['Size'], $store_id);              // 尺寸表
                $product->addAttributeUpdate('product_ingredient',  $data['cont_short_desc'],   $store_id);   // 商品成分
                $product->addAttributeUpdate('washing_instruction', $data['Cloth_m_set'],       $store_id);   // 洗滌方式
                // $product->addAttributeUpdate('fitting_report',      $data['name'], $store_id);           // 試穿報告
                
                $productUpdate++;

                foreach($data['sub_product'] as $sku => $subdata){      // 上面的 $sku 應該不會再用到
                    if( $product->getIdBySku($sku) ){
                        // 存在
                        $product = $productRepository->get($sku);
                    }else{
                        // 不存在
                        $product->setSku($sku);
                    }

                    $product
                        // 第二部份
                        ->setPrice($subdata['Sprice'])      // 商品子項目只能改價錢 因為顏色、尺寸都已經跟 sku 綁定了
                    ->save();

                    $productUpdate++;
                }
            }
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been update.', $productUpdate)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/product');
    }
}