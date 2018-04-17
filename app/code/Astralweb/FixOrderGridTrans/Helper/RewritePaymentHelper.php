<?php


namespace Astralweb\FixOrderGridTrans\Helper;


class RewritePaymentHelper
{
    /**
     * @param \Magento\Payment\Helper\Data $subject
     * @param array $result
     * @return array
     */
    public function afterGetPaymentMethods(\Magento\Payment\Helper\Data $subject, $result){
        //translate payment title
            foreach ($result as $key=>$paymentInfo){
            if (isset($paymentInfo['title'])){
                $result[$key]['title'] = __($paymentInfo['title']);
            }
        }

        return $result;
    }
}