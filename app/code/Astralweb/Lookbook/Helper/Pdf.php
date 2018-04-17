<?php
/**
 * EaDesgin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eadesign.ro so we can send you a copy immediately.
 *
 * @category    eadesigndev_pdfgenerator
 * @copyright   Copyright (c) 2008-2016 EaDesign by Eco Active S.R.L.
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Astralweb\Lookbook\Helper;

use Eadesigndev\Pdfgenerator\Model\Pdfgenerator;
use Eadesigndev\Pdfgenerator\Model\Source\TemplatePaperOrientation;
use Eadesigndev\Pdfgenerator\Model\Template\Processor;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Block\Adminhtml\Items\AbstractItems as Invoiceitem;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order\Invoice;
use mPDF;

/**
 * Class Pdf
 * @package Eadesigndev\Pdfgenerator\Helper
 * @SuppressWarnings("CouplingBetweenObjects")
 */
class Pdf extends \Eadesigndev\Pdfgenerator\Helper\Pdf
{

    public function _transport()
    {
        // die('---');

        $invoice = $this->invoice;
        $order = $this->order;
        $invoiceTotal = $this->getInvoiceVariables();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $phoneBilling ='';
        $cityBilling ='';
        $regionBilling ='';
        $streetBilling ='';
        $nameBilling ='';
        $phoneShipping ='';
        $cityShipping ='';
        $regionShipping ='';
        $streetShipping ='';
        $nameShipping ='';


        $cityBilling =  $order->getBillingAddress()->getCity();
        $regionBilling =  $order->getBillingAddress()->getRegion();
        $streetBilling =  $order->getBillingAddress()->getData('street');
        $nameBilling =  $order->getBillingAddress()->getFirstname().$order->getBillingAddress()->getLastname();

        $a = $order->getShippingAddress();
        if(isset($a)){
          $phoneShipping = $order->getShippingAddress()->getTelephone();
          $cityShipping =  $order->getShippingAddress()->getCity();
          $regionShipping =  $order->getShippingAddress()->getRegion();
          $streetShipping =  $order->getShippingAddress()->getData('street');
          $nameShipping =  $order->getShippingAddress()->getFirstname().$order->getShippingAddress()->getLastname();

        }

        $totalqty = 0;
        $iTime = 0;
        if ($order->hasInvoices()) {
            foreach ($invoice->getItemsCollection() as $item) {
                //check if item is not a child item
                if(!$item->isDeleted() && $item->getRowTotal()){
                    $totalqty = $totalqty + $item->getQty();
                    $iTime = $iTime + 1;
                }
            }
        }


        $orderdate = $order->getCreatedAt();
        $orderformatdate = date('Y/m/d',strtotime($orderdate));
        $htmlProduct = $this->getProductHtml();

        $transport = [
        	  'phoneBilling' =>$phoneBilling,
        	  'cityBilling' =>$cityBilling,
        	  'regionBilling' =>$regionBilling,
        	  'streetBilling' =>$streetBilling,
        	  'nameBilling' => $nameBilling,
        	  'phoneShipping' =>$phoneShipping,
        	  'cityShipping' =>$cityShipping,
        	  'regionShipping' =>$regionShipping,
        	  'streetShipping' =>$streetShipping,
            'nameShipping' => $nameShipping,
            'date_order' => $orderformatdate,
            'htmlProduct' => $htmlProduct,
            'order' => $order,
            'invoice' => $invoice,
            'totalqty' => number_format($totalqty, 0),
            'i_time'=>$iTime,
            'invoicetotal_subtotal' => $invoiceTotal['invoice_subtotal']['value'],
            'invoicetotal_shipping_amount' => $invoiceTotal['invoice_shipping_amount']['value'],
            'invoicetotal_discount_amount' => $invoiceTotal['invoice_discount_amount']['value'],
            'invoicetotal_grand_total' => $invoiceTotal['invoice_grand_total']['value'],
            'comment' => $invoice->getCustomerNoteNotify() ? $invoice->getCustomerNote() : '',
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order)
        ];
        $processor = $this->processor;
        $processor->setVariables($transport);
        $processor->setTemplate($this->template);
        $parts = $processor->processTemplate();

        return $parts;
    }
     public function getProductHtml(){
        $html = '';
        $orderAll = $this->_objectManager->create('Magento\Sales\Model\Order')->load($this->order->getId());
        $oprionsbun = array();
        $oprionsku = array();
        $i = 0;
        foreach ($orderAll->getAllItems() as $item) {
            $options = $item->getProductOptions();
            if(isset($options['attributes_info'])){

                $i++;
                $oprionsbun[$i]['sku'] = $options['simple_sku'];
                $oprionsku[$i] = $item->getSku();
                $oprionsbun[$i]['options'] = $options['attributes_info'];
            }
        }

        foreach ($this->getRenderingEntity()->getAllItems() as $item) {

            if($item->getRowTotal() && intval($item->getRowTotal()) > 0){
                $htmloption = '';
                $check = array_search($item->getSku(),$oprionsku);
                if($check){
                    $arrayOprion = $oprionsbun[$check]['options'];
                    $htmloption.= "<p></p>";
                    for ($i=0; $i < count($arrayOprion) ; $i++) { 
                    $htmloption.= "<span >".$arrayOprion[$i]['label'] .": ".$arrayOprion[$i]['value']."|</span>";
                    }
                    // $htmloption = "</p>";


                }
                $invoice = $this->invoice;
                $order = $invoice->getOrder();

                 $html.="<tr>";
                 $html.=    "<td class=\"name\"><p>".$item->getName().'</p><p></p>'.$htmloption."</td>";
                 $html.=    "<td class=\"sku\">".$item->getSku()."</td>";
                 $html.=    "<td>".$this->numberFormat2($item->getPrice())."</td>";
                 $html.=    "<td class=\"qty\">".$this->numberFormat($item->getQty())."</td>";
                 $html.=    "<td>".$this->numberFormat2($item->getTaxAmount())."</td>";
                 $html.=    "<td>".$this->numberFormat2($item->getRowTotal())."</td>";
                 $html.= "</tr>";
            }
        }
        return $html;
    }
    public function numberFormat2($number){
        return number_format($number, 2);
    }
 
}
