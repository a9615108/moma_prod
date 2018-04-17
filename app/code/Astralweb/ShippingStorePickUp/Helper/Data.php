<?php
namespace Astralweb\ShippingStorePickUp\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $shopFactory;

    public function __construct(
        \Astralweb\ShippingStorePickUp\Model\shopFactory $shopFactory,
        \Astralweb\ShippingStorePickUp\Model\ResourceModel\shop\Collection $shopCollection
    )
    {
        $this->shopFactory = $shopFactory;
        $this->shopCollection = $shopCollection;
    }

    // 回傳所有店家資訊
    public function getShops()
    {
        $this->shopCollection->addFieldToSelect('*')
            ->addFieldToFilter(
                'is_active',
                ['=' => 1]
            )
            ->load();
        $shop = array();

        foreach($this->shopCollection as $item){
            $shop[] = $item->getData();
        }

        $rearr = array();
        foreach($shop as $item){
            $rearr[$item['county']] = array();
        }

        foreach($shop as $item){
            $rearr[$item['county']][$item['region']] = array();
        }
        foreach($shop as $item){
            $rearr[$item['county']][$item['region']][] = array(
                'name'      => $item['name'],
                'street'    => $item['street'],
                'code'      => $item['code'],
                'postcode'  => $item['postcode'],
                'telephone' => $item['telephone'],
            );
        }

        return json_encode($rearr);
    }

    // 輸入 抵達門市時間
    // 回傳 取貨期限     Y/m/d
    function get_get_date($arrival_datetime){
        $time = strtotime($arrival_datetime);
        return date('Y/m/d',mktime(0,0,0,date('m',$time),date('d',$time)+10,date('Y',$time)));
    }
}