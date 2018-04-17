<?php
namespace Astralweb\Shippingsf\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeManager;
    protected $_directory;
    protected $_ioFile;
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,\Magento\Framework\App\Filesystem\DirectoryList $directory_list,\Magento\Store\Model\StoreManagerInterface $storeManager,\Magento\Framework\Filesystem\Io\File $ioFile)
    {
        $this->_directory = $directory_list;
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_ioFile = $ioFile;
    }
    public function getHtmlPdfSF($barcode,$destcode,$dataLine3,$paymentMethod,$subtotal,$dataLine5){

      if($barcode != ''){
            $generatorHTML = new \Picqer\Barcode\BarcodeGeneratorHTML();
            $htmlBarcode1 = $generatorHTML->getBarcode($barcode,$generatorHTML::TYPE_CODE_128_C,'2.20px','41');
            $htmlBarcode2 = $generatorHTML->getBarcode($barcode,$generatorHTML::TYPE_CODE_128_C,'1.75px','32');
            $textBarcode = '運單號: '.substr($barcode,0,3).' '.substr($barcode,3,3).' '.substr($barcode,6,3).' '.substr($barcode,9,3);
        }else{
            $htmlBarcode1 = '';
            $htmlBarcode2 = '';
            $textBarcode = '';
        }
        if($destcode == ''){
            $destcode = '';
        }

            //Get Data
        $logoLink = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'/sf/label-logo.png';
        $phoneweb = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'/sf/label-contact.png';
        $servicetype = $this->getServiceType();
        $creditcardNo = $this->getCreditNo();
        $creditAccount = $this->getCreditAccount();
        if($paymentMethod == 'cashondelivery'){
            $textPayment = '代收货款';
            $textCard = $creditcardNo;
            $textSubtotal = $subtotal;
        }else{
            $textPayment = '';
            $textSubtotal = '';
            $textCard ='';
        }
        $pickipCourier = $this->getPickupCourier();
        $dataLine4 = $this->getCompany().' '.$this->getTel().' '.$this->getCity().' '.$this->getProvince().' '.$this->getAdress();

        $pickupDate = date("d.m.Y", time());
        $html ='<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta charset="utf-8" />
		<title>Label</title>
		<link rel="stylesheet" href="styles-label.css">
	</head>
		<style>
@page { size: 100mm 150mm; margin-right: 9; margin-top: 0; margin-bottom: 0; margin-left: 0; }

html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, embed, 
figure, figcaption, footer, header, hgroup, 
menu, nav, output, ruby, section, summary,
time, mark, audio, video {
	margin: 0;
	padding: 0;
	border: 0;
	font-size: 100%;
	font: inherit;
	vertical-align: baseline;
}
/* HTML5 display-role reset for older browsers */
article, aside, details, figcaption, figure, 
footer, header, hgroup, menu, nav, section {
	display: block;
}
body {
	line-height: 1;
}
body *{
	box-sizing: border-box;
}
ol, ul {
	list-style: none;
}
blockquote, q {
	quotes: none;
}
blockquote:before, blockquote:after,
q:before, q:after {
	content: \'\';
	content: none;
}
table {
	border-collapse: collapse;
	border-spacing: 0;
}

body{
	font-family: \'Arial\', Helvetica, Arial, sans-serif;
	font-family: Tahoma, Arial, "Microsoft Yahei","微软雅黑", STXihei, "华文细黑", sans-serif;
	text-align: center;
}
p{
    width: 100%;
    margin: 0;
    padding: 0;
}


/* New */

.label-wrapper{
	width: 100mm;
	height: 150mm;
	border: 1pt solid #c9c9c9;
	display: inline-block;
	margin: 0 auto;
	text-align: left;
}
.label-wrapper *{
	float: left;
	display: inline-block;
}
.line-1{
	height: 13mm;
	padding: 0 2mm;
}

.line-1-inner{
	border-bottom: 1pt solid #c9c9c9;
	height: 100%;
}

.block1-1{
	width: 40mm;
	content: \'\';
	position: relative;
	height: 100%;
}
.block1-1 img, .block1-1 .img{
	width:15.5mm;
	height: 5.6mm;
	border: none;
	float: left;
	margin-top: 3.5mm;
}
.block1-3 img, .block1-3 .img{
	width:15.3mm;
	height: 5.6mm;
	border: none;
	float: left;
	margin-top: 3.3mm;
}
.block1-2{
	width: 39mm;
	font-weight: 800;
	line-height: 1;
	font-size: 36pt;
	letter-spacing
}
.block1-2 .e-text{
	font-size: 45pt;
	position: relative;
	top: -6.5pt;
	margin-right: 3mm;
}
.block1-2 span{
	margin-top: -1mm;
}
.block1-3{
	width: 16mm;
	float: right;
}

/* Line 2 */

.line-2{
	height: 22mm;
	padding: 0 2mm;
}

.line-2-inner{
	border-bottom: 1pt solid #c9c9c9;
	height: 100%;
}

.block2-1{
	width: 68mm;
	height: 100%;
	border-right:  1pt solid #c9c9c9;
	text-align: center;
}
.block2-2{
	width: 27mm;
}

.block2-1 img, .block2-1 .img{
	width: 70mm;
	height: 11mm;
	display:block;
	padding-left: 10px;
	margin: 0 auto;
	margin-top: 3mm;
	overflow: hidden;
	float: none;
}

.subline2-2-1{
	height: 6mm;
	text-align: center;
	width: 100%;
	position: relative;
	top: -1pt;
	border-bottom: 1pt solid #c9c9c9;
	font-size: 15pt;
}
.subline2-2-2{
	width: 100%;
	text-align: center;
	font-size: 18pt;
	font-weight: 600;
	padding-top: 1mm;
}
.subline2-2-3{
	width: 100%;
	text-align: center;
	font-size: 8pt;
	padding-top: 1mm;
	letter-spacing: -0.5pt;
}
.subline2-2-3 .title{
	font-weight: 500;
	margin-right: 2pt;
}

.subline2-2-4{
	font-size: 10pt;
	font-weight: 600;
	width: 100%;
	padding: 1mm;
}
.block2-1 .barcode-label{
	font-size: 11pt;
	margin-left: 2mm;
	margin-top: 0mm;
	width: 100%;
	text-align: center;
}


/* Line 3 */

.line-3{
	height: 14mm;
	padding: 0 2mm;
}

.line-3-inner{
	border-bottom: 1pt solid #c9c9c9;
	height: 100%;
	width: 100%;
}
.block3-1{
	width: 5mm;
	font-size: 8pt;
	line-height: 10pt;
	padding-top: 1.7mm;
	border-right: 1pt solid #c9c9c9;
	height: 100%;
}
.block3-2{
	width: 90mm;
	font-size: 52pt;
	font-weight: 800;
	line-height: 36pt;
}

/* Line 4 */

.line-4{
	height: 13mm;
	padding: 0 2mm;	
}


.line-4-inner{
	border-bottom: 1pt solid #c9c9c9;
	height: 100%;
	width: 100%;
}
.block4-1{
	width: 5mm;
	font-size: 8pt;
	line-height: 10pt;
	padding-top: 1.2mm;
	border-right: 1pt solid #c9c9c9;
	height: 100%;
}
.block4-2{
	padding-left: 1mm;
	width: 90mm;
	font-size: 10pt;
	font-weight: 600;
	line-height: 13pt;
}
/* Line 5 */

.line-5{
	height: 9mm;
	padding: 0 2mm;
}

.line-5-inner{
	border-bottom: 1pt solid #c9c9c9;
	height: 100%;
	width: 100%;
}
.block5-1{
	width: 5mm;
	font-size: 8pt;
	line-height: 8pt;
	padding-top: 0;
	border-right: 1pt solid #c9c9c9;
	height: 100%;
}
.block5-2{
	padding-left: 1mm;
	padding-right: 0.5mm;
	width: 68mm;
	font-size: 9pt;
	font-weight: 500;
	line-height: 13pt;
}
.block5-3{
	width: 22mm;
	border-left: 1pt solid #c9c9c9;
	height: 100%;
}
.subline5-3-1{
	font-size: 16px;
	width: 100%;
	text-align: center;
	font-weight: 600;
	padding-top: 0.5mm;

}
.subline5-3-2{
	font-size: 12px;
	width: 100%;
	text-align: center;
	padding-top: 0.2mm;
}
/* Line 6 */

.line-6{
	height: 21mm;
	padding: 0 2mm;
	width: 100%;
}

.line-6-inner{
	border-bottom: 1pt solid #c9c9c9;
	height: 100%;
	width: 100%;
}

.block6-1{
	width: 73mm;
}
.subline6-1-1{
	padding-top: 1mm;
	height: 12mm;
	border-bottom: 1pt solid #c9c9c9;
	border-right: 1pt solid #c9c9c9;
	position: relative;
	width: 100%;
}
.subline6-1-1 ul{
	width: 100%;
}

.subline6-1-1 li{
	width: 100%;
	font-size: 6pt;
}
.subline6-1-2{
	border-right: 1pt solid #c9c9c9;
	height: 9mm;
	width: 100%;
}
.sub-block6-1-2-1{
	width: 5mm;
	font-size: 8pt;
	line-height: 8pt;
	padding-top: 0;
	border-right: 1pt solid #c9c9c9;
	height: 100%;
}
.sub-block6-1-2-2{
	width: 50mm;
	height: 100%;
	border-right: 1pt solid #c9c9c9;
}
.sub-block6-1-2-2{
	font-size: 7pt;
	padding: 1pt;
}
.sub-block6-1-2-3{
	font-size: 6pt;
	width: 17mm;
	padding: 1pt;
}
.subline6-1-1-right{
	position: absolute;
	bottom: 1mm;
	font-size: 7pt;
	right: 1mm;
}
.block6-2{
	position: relative;
	height: 100%;
	width: 22mm;
}
.subline6-2-1{
	font-size: 7pt;
}
.subline6-2-2{
	font-size: 7pt;
	position: absolute;
	bottom: 2pt;
	right: 2pt;
}

/* Line 7 */

.line-7{
	height: 16mm;
	padding: 0 2mm;
	width: 100%;
}

.line-7-inner{
	border-bottom: 1pt solid #c9c9c9;
	height: 100%;
	width: 100%;
}
.block7-1{
	width: 25mm;
	font-size: 8pt;
	line-height: 8pt;
	padding-top: 0;
	border-right: 1pt solid #c9c9c9;
	height: 100%;
}

.subline7-1-1{
	width:100%;
	height: 5.6mm;
	margin-top: 2mm;
	text-align: center;
}

.subline7-1-2{
	width: 100%;
    height: 5.6mm;
	margin-top: 2mm;
	text-align: center;
}

.line-7 .img-1{
	width:15.3mm;
	height: 5.6mm;
	border: none;
	float: none;
	margin: 0 auto;
}
.line-7 .img-2{
	width: 15.3mm;
    height: 5.6mm;
    border: none;
    float: none;
    margin: 0 auto;
}

.block7-2{
	padding-left: 1mm;
	padding-right: 0.5mm;
	width: 70mm;
	font-size: 9pt;
	font-weight: 500;
	line-height: 13pt;
	height: 100%;
	text-align: center;
}
.block7-2 .img{
	width: 55mm;
	height: 8.5mm;
	margin: 0 auto;
	padding-left: 4px;
	margin-top: 2mm;
	display: inline-block;
	float: none;
}
.block7-2 .barcode-label{
	font-size: 10.5pt;
	margin: 0 auto;
	float: none;
	font-size: 10pt;
	position: relative;
	bottom: 1mm;
}
/* Line 8 */

.line-8{
	height: 10mm;
	padding: 0 2mm;
	width: 100%;
}

.line-8-inner{
	border-bottom: 1pt solid #c9c9c9;
	height: 100%;
	width: 100%;
}
.block8-1{
	width: 5mm;
	font-size: 8pt;
	line-height: 8pt;
	padding-top: 1mm;
	border-right: 1pt solid #c9c9c9;
	height: 100%;
}
.block8-2{
	padding: 0.5mm;
	width: 90mm;
	font-size: 9pt;
	font-weight: 500;
	line-height: 13pt;
}

/* Line 9 */

.line-9{
	height: 10mm;
	padding: 0 2mm;
	width: 100%;
}

.line-9-inner{
	border-bottom: 1pt solid #c9c9c9;
	height: 100%;
	width: 100%;
}
.block9-1{
	width: 5mm;
	font-size: 8pt;
	line-height: 8pt;
	padding-top: 1mm;
	border-right: 1pt solid #c9c9c9;
	height: 100%;
}
.block9-2{
	padding: 0.5mm;
	width: 90mm;
	font-size: 9pt;
	font-weight: 500;
	line-height: 13pt;
}

/* Line 10 */

.line-10{
	height: 9mm;
	padding: 0 2mm;
	width: 100%;
}
.line-10-inner{
	width: 100%;
}
.block10-1{
	text-align: center;
	width: 100%;
	line-height: 8pt;
	padding-top: 1mm;
	font-size: 8pt;
}
.block10-2{
	text-align: center;
	width: 100%;
	font-size: 8pt;
	text-align: center;
	line-height: 8pt;
	padding-top: 5mm;
}
	</style>
	<body>
			<div class="label-wrapper">

				<div class="line-1">
					<div class="line-1-inner">
						<div class="block1-1">
						</div>
						<div class="block1-2">
							<div class="e-text"></div>
							<span></span>
						</div>
						<div class="block1-3">
						</div>
					</div>
				</div>

				<div class="line-2">
					<div class="line-2-inner">
						<div class="block2-1">
							<div class="img">'.$htmlBarcode1.'</div>
							<div class="barcode-label">
								'.$textBarcode.'
							</div>
						</div>
						<div class="block2-2">
							<div class="subline2-2-1">
								標準快遞
							</div>
							<div class="subline2-2-2">
								'.$textPayment.'
							</div>
							<div class="subline2-2-3">
								<span class="title">
									卡号
								</span>
								<span class="value">
									'.$textCard.'
								</span>
							</div>
							<div class="subline2-2-4">
								'.$textSubtotal.'
							</div>
						</div>
					</div>
				</div>

				<div class="line-3">
					<div class="line-3-inner">
						<div class="block3-1">
							目的地
						</div>
						<div class="block3-2">
							'.$destcode.'
						</div>
					</div>
				</div>

				<div class="line-4">
					<div class="line-4-inner">
						<div class="block4-1">
							收件人
						</div>
						<div class="block4-2">
							'.$dataLine3.'
						</div>
					</div>
				</div>

				<div class="line-5">
					<div class="line-5-inner">
						<div class="block5-1">
							寄件人
						</div>
						<div class="block5-2">
							'.$dataLine4.'
						</div>
						<div class="block5-3">
							<div class="subline5-3-1">
								
							</div>
							<div class="subline5-3-2">
								
							</div>
						</div>
					</div>
				</div>

				<div class="line-6">
					<div class="line-6-inner">
						<div class="block6-1">
							<div class="subline6-1-1">
								<ul>
									<li>
										付款方式: '.'寄付月結'.'
									</li>
									<li>
										月结账号:'.$creditAccount.'
									</li>
									<li>
										
									</li>
									<li>
										
									</li>
								</ul>
								<div class="subline6-1-1-right">
									
								</div>
							</div>
							<div class="subline6-1-2">
								<div class="sub-block6-1-2-1">
									寄託物
								</div>
								<div class="sub-block6-1-2-2">
									<span>'.$dataLine5.'</span>
								</div>
								<div class="sub-block6-1-2-3">
									<p>收件员 :'.$pickipCourier.'</p>
									<p>寄件日期 :'.$pickupDate.'</p>
									<p>派件員:</p>
								</div>
							</div>	
						</div>
						<div class="block6-2">
							<div class="subline6-2-1">
      							 簽名
      						</div>
    			  			<div class="subline6-2-2">
      							 月  日
      						</div>
						</div>
					</div>
				</div>	

				<div class="line-7">
					<div class="line-7-inner">
						<div class="block7-1">
							<div class="subline7-1-1">
								<img class="img-1" src="'.$logoLink.'">
							</div>
							<div class="subline7-1-2">
								<img class="img-2" src="'.$phoneweb.'">
							</div>	
						</div>
					
						<div class="block7-2">
							<div class="img">'.$htmlBarcode2.'</div>
							<div class="barcode-label">
								'.$textBarcode.'
							</div>
						</div>
					</div>
				</div>

				<div class="line-8">
					<div class="line-8-inner">
						<div class="block8-1">
							寄件人
						</div>
						<div class="block8-2">
							'.$dataLine4.'
						</div>
					</div>
				</div>

				<div class="line-9">
					<div class="line-9-inner">
						<div class="block9-1">
							收件人
						</div>
						<div class="block9-2">
							'.$dataLine3.'
							</div>
					</div>
				</div>

				<div class="line-10">
					<div class="line-10-inner">
						<div class="block10-1">
							
						</div>
						<div class="block10-2">
						'.$dataLine5.'
						</div>
					</div>
				</div>

			</div>


	</body>

</html>';
        return $html;

    }
    public function getServiceType(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/service_type', $storeScope);
    }
    public function getPickupCourier(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/pickup_courier', $storeScope);
    }
    public function getCreditNo(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/credit_no', $storeScope);
    }
    public function getCreditAccount(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/credit_account', $storeScope);
    }
    public function orderservice($orderid,$j_company,$j_contact,$j_telphone,$j_address,$d_company,$d_contact,$d_telphone,$d_address,$pay_method,$d_province,$d_city,$j_province,$j_city,$custid,$mailno,$urlapi,$checkheader,$checkbody,$stringXmlCargo,$stringXmlAddServices){
//        $_CHECKHEADER = 'QJYXGS';//客户卡号,校验码
//        $_URL = 'https://bspoisp.sit.sf-express.com:11443/bsp-oisp/sfexpressService';//快递类服务接口url
//        $_CHECKBODY = '7lPXjI7ZHBdU';//checkbody
        $j_shippercode  = $this->citycode($j_province,$j_city);
        //$d_deliverycode = $this->citycode($d_province,$d_city);
        $d_deliverycode = 886;
        //var_dump($d_deliverycode);die;
        $body = '<?xml version="1.0" encoding="UTF-8" ?><Request service="OrderService" lang="zh-CN"><Head>'.$checkheader.'</Head><Body><Order orderid="'.$orderid.'" express_type="1" j_company="'.$j_company.'" j_contact="'.$j_contact.'" j_tel="'.$j_telphone.'" j_address="'.$j_address.'" d_company="'.$d_company.'" d_contact="'.$d_contact.'" d_tel="'.$d_telphone.'" d_address="'.$d_address.'" parcel_quantity="1" pay_method="'.$pay_method.'" custid="'.$custid.'" j_shippercode="'.$j_shippercode.'" d_deliverycode="'.$d_deliverycode.'" cargo_total_weight="" sendstarttime="" mailno="'.$mailno.'" remark=""  is_gen_bill_no="1" >'.$stringXmlCargo.$stringXmlAddServices.'</Order></Body></Request>';
        $newbody = $body.$checkbody;
               // var_dump($newbody);die;

        $md5 =  md5($newbody,true);
        $verifyCode = base64_encode($md5);
        $url = $urlapi;
        $fields = array('xml'=>$body,'verifyCode'=>$verifyCode);
        $parambody =  http_build_query($fields, '', '&');
        $res = $this->post($url,$parambody);
        return $res;

    }
    public function ordersearch($orderid,$urlapi,$checkheader,$checkbody){
    	  $body = '<?xml version="1.0" encoding="UTF-8" ?><Request service="OrderSearchService" lang="zh-CN"><Head>'.$checkheader.'</Head><Body><OrderSearch orderid="'.$orderid.'" /></Body></Request>';
			$newbody = $body.$checkbody;
        $md5 =  md5($newbody,true);
        $verifyCode = base64_encode($md5);
        $url = $urlapi;
        $fields = array('xml'=>$body,'verifyCode'=>$verifyCode);
        $parambody =  http_build_query($fields, '', '&');
        $res = $this->post($url,$parambody);
        return $res;    	  

    }
    public function confirmorder($orderid,$mailno,$dealtype,$weight,$volume,$urlapi,$checkheader,$checkbody){
        $body = '<?xml version="1.0" encoding="UTF-8" ?><Request service="OrderConfirmService" lang="zh-CN"><Head>'.$checkheader.'</Head><Body><OrderConfirm orderid="'.$orderid.'" mailno="'.$mailno.'" dealtype="'.$dealtype.'" /><OrderConfirmOption weight="'.$weight.'" volume="'.$volume.'" /></OrderConfirm></Body></Request>';

        $newbody = $body.$checkbody;
        $md5 =  md5($newbody,true);
        $verifyCode = base64_encode($md5);
        $url = $urlapi;
        $fields = array('xml'=>$body,'verifyCode'=>$verifyCode);
        $parambody =  http_build_query($fields, '', '&');
        $res = $this->post($url,$parambody);
        return $res;
    }
    public function RouteService($tracking_type,$method_type,$tracking_number,$urlapi,$checkheader,$checkbody)
    {
        $body = '<?xml version="1.0" encoding="UTF-8" ?><Request service="RouteService" lang="zh-CN"><Head>'.$checkheader.'</Head><Body><RouteRequest tracking_type="'.$tracking_type.'" method_type="'.$method_type.'" tracking_number="'.$tracking_number.'" />
     </RouteRequest></Body></Request>';

        $newbody = $body.$checkbody;
        $md5 =  md5($newbody,true);
        $verifyCode = base64_encode($md5);
        $url = $urlapi;
        $fields = array('xml'=>$body,'verifyCode'=>$verifyCode);
        $parambody =  http_build_query($fields, '', '&');
        $res = $this->post($url,$parambody);
        return $res;

    }
    public function post($url,$body)
    {
        $curlObj = curl_init();
        curl_setopt($curlObj, CURLOPT_URL, $url); // 设置访问的url
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1); //curl_exec将结果返回,而不是执行
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded;charset=UTF-8"));
        curl_setopt($curlObj, CURLOPT_URL, $url);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curlObj, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);

        curl_setopt($curlObj, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($curlObj, CURLOPT_POST, true);
        curl_setopt($curlObj, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curlObj, CURLOPT_ENCODING, 'gzip');

        $res = @curl_exec($curlObj);

        curl_close($curlObj);

        if ($res === false) {
            $errno = curl_errno($curlObj);
            if ($errno == CURLE_OPERATION_TIMEOUTED) {
                $msg = "Request Timeout:   seconds exceeded";
            } else {
                $msg = curl_error($curlObj);
            }
            //echo $msg;
            $e = new XN_TimeoutException($msg);
            return $e;
        }
        return $res;
    }
    public function getCheckHeader(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/check_header', $storeScope);
    }
    public function getUrlApi(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/url_api', $storeScope);
    }

    public function getCheckBody(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/check_body', $storeScope);
    }
    public function getCompany(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/j_company', $storeScope);
    }
    public function getContact(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/j_contact', $storeScope);
    }
    public function getTel(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/j_tel', $storeScope);
    }
    public function getMobile(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/j_mobile', $storeScope);
    }
    public function getShippercode(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/j_shippercode', $storeScope);
    }
    public function getAdress(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/j_address', $storeScope);
    }
    public function getProvince(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/j_province', $storeScope);
    }
    public function getCity(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/j_city', $storeScope);
    }
    public function getPostcode(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('carriers/collect_store/j_post_code', $storeScope);
    }
    public function citycode($province,$city){
        $expressname = array(
            '北京'=>'010','平谷'=>'010','延庆'=>'010',
            '广州'=>'020','增城'=>'020','从化'=>'020',
            '上海'=>'021',
            '天津'=>'022','静海'=>'022','蓟县'=>'022','宁河'=>'022',
            '重庆'=>'023','渝中'=>'023','高新'=>'023','南岸'=>'023','万州'=>'023','壁山'=>'023','开县'=>'023','荣昌'=>'023','铜梁'=>'023','綦江'=>'023',
            '沈阳'=>'024','抚顺'=>'024','锦州'=>'024',
            '南京'=>'025','溧水县'=>'025','高淳县'=>'025',
            '武汉'=>'027',
            '成都'=>'028','双流'=>'028','都江堰'=>'028', '崇州'=>'028', '新津'=>'028','金堂'=>'028','大邑'=>'028','邛崃'=>'028','郫县'=>'028','彭州'=>'028','蒲江'=>'028','邛崃'=>'028',
            '西安'=>'029',
            '遵义'=>'052',
            '安顺'=>'053',
            '丽江'=>'088',
            '邯郸'=>'310','成安县'=>'310','永年县'=>'310',
            '石家庄'=>'311','鹿泉'=>'311','辛集'=>'311','晋州'=>'311','新乐'=>'311','正定'=>'311','无极'=>'311','藁城'=>'311','灵寿'=>'311','元氏'=>'311','赵县'=>'311',
            '保定'=>'312','蠡县'=>'312','高阳'=>'312','雄县'=>'312','高碑县'=>'312','容城'=>'312','安新'=>'312','徐水'=>'312','定州'=>'312','满城'=>'312','定兴'=>'312','涿州'=>'312','安国'=>'312','清苑'=>'312',
            '张家口'=>'313',
            '承德'=>'314',
            '唐山'=>'315','玉田县'=>'315','唐海'=>'315','迁西'=>'315',
            '廊坊'=>'316','霸州'=>'316','香河县'=>'316','文安'=>'316','大城'=>'316','固安'=>'316','三河'=>'316','大厂'=>'316',
            '沧州'=>'317','黄骅'=>'317','青县'=>'317','沧县'=>'317','南皮'=>'317','泊头'=>'317','任丘'=>'317',
            '衡水'=>'318','安平县'=>'318','枣强'=>'318',
            '邢台'=>'319','宁晋'=>'319','清河'=>'319','南宫'=>'319','新河'=>'319',
            '秦皇岛'=>'335','昌黎'=>'335','抚宁'=>'335',
            '太原'=>'351',
            '晋中'=>'354',
            '长治'=>'355',
            '商丘'=>'370',
            '郑州'=>'371','新郑'=>'371','荥阳'=>'371','新密'=>'371',
            '安阳'=>'372','安阳县'=>'372',
            '新乡'=>'373','卫辉'=>'373','辉县'=>'373',
            '许昌'=>'374','长葛'=>'374','禹州'=>'374',
            '平顶山'=>'375',
            '信阳'=>'376',
            '南阳'=>'377',
            '开封'=>'378','尉氏'=>'378','中牟'=>'378',
            '洛阳'=>'379','新安'=>'379','偃师'=>'379','宜阳'=>'379',
            '焦作'=>'391',
            '济源'=>'391E',
            '鹤壁'=>'392',
            '濮阳'=>'393',
            '周口'=>'394',
            '漯河'=>'395',
            '驻马店'=>'396',
            '三门峡'=>'398',
            '铁岭'=>'410',
            '大连'=>'411','普兰店'=>'411','瓦房店'=>'411','庄河'=>'411',
            '鞍山'=>'412','海城'=>'412',
            '本溪'=>'414',
            '丹东'=>'415','东巷'=>'415',
            '营口'=>'417','大石桥'=>'417',
            '阜新'=>'418',
            '辽阳'=>'419','灯塔'=>'419',
            '朝阳'=>'421',
            '盘锦'=>'427','大洼'=>'427','盘山'=>'427',
            '葫芦岛'=>'429',
            '兴城'=>'429',
            '长春'=>'431',
            '吉林'=>'432',
            '延吉'=>'433',
            '吉林省四平市'=>'434',
            '哈尔滨'=>'451','双城'=>'451',
            '齐齐哈尔'=>'452',
            '大庆'=>'459',
            '呼和浩特'=>'471','和林格尔县'=>'471',
            '包头'=>'472',
            '无锡'=>'510','江阴'=>'510','宜兴'=>'510',
            '镇江'=>'511','杨中'=>'511', '句容'=>'511','丹阳'=>'511',
            '苏州'=>'512','常昆'=>'512','吴江'=>'512','常熟'=>'512','太仓'=>'512','昆山'=>'512','张家港'=>'512',
            '南通'=>'513','海门'=>'513','如东'=>'513','如皋'=>'513','海安'=>'513','启东'=>'513',
            '杨州'=>'514','江都'=>'514','高邮'=>'514','仪征'=>'514','宝应'=>'514',
            '盐城'=>'515','太丰'=>'515','东台'=>'515','建湖'=>'515','射阳'=>'515','阜宁'=>'515',
            '徐州'=>'516','邳州'=>'516','新沂'=>'516',
            '淮安'=>'517','涟水'=>'517','盯眙'=>'517','洪泽'=>'517','金湖'=>'517',
            '连云港'=>'518','东海县'=>'518','赣榆县'=>'518',
            '常州'=>'519','天宁'=>'519','武进'=>'519','龙城'=>'519','金坛'=>'519','溧阳'=>'519',
            '泰州'=>'523','靖江'=>'523','泰兴'=>'523','姜堰'=>'523','兴化'=>'523',
            '宿迁'=>'527','泗阳'=>'527','沭阳'=>'527',
            '荷泽'=>'530',
            '济南'=>'531','章丘'=>'531','济阳'=>'531',
            '青岛'=>'532','即墨'=>'532','平度'=>'532','胶州'=>'532','胶南'=>'532','莱西'=>'532',
            '淄博'=>'533','恒台'=>'533','高青'=>'533','邹平'=>'533','沂源'=>'533',
            '德州'=>'534','宁津'=>'534','禹城'=>'534','齐河'=>'534','陵县'=>'534','夏津'=>'534','乐陵'=>'534','临邑'=>'534','平原'=>'534','武城'=>'534','高唐'=>'534',
            '烟台'=>'535', '莱阳'=>'535', '海阳'=>'535', '莱州'=>'535', '蓬莱'=>'535', '龙口'=>'535', '栖霞'=>'535', '招远'=>'535',
            '潍坊'=>'536','高密'=>'536','诸城'=>'536','昌邑'=>'536','寿光'=>'536','昌乐'=>'536','青州'=>'536','安丘'=>'536','临朐'=>'536',
            '济宁'=>'537', '邹城'=>'537', '嘉祥'=>'537', '兖州'=>'537', '曲阜'=>'537',
            '泰安'=>'538', '肥城'=>'538',
            '临沂'=>'539','沂水'=>'539','临沭'=>'539','蒙阴'=>'539', '沂南'=>'539',
            '滨州'=>'543', '博兴'=>'543', '沾化'=>'543', '无棣'=>'543', '阳信'=>'543',
            '东营'=>'546', '广饶'=>'546','垦利'=>'546',
            '滁州'=>'550', '来安'=>'550','全椒'=>'550',
            '合肥'=>'551','包河'=>'551','蜀山'=>'551','庐阳'=>'551','肥西'=>'551','长丰'=>'551','肥东'=>'551',
            '蚌埠'=>'552', '凤阳'=>'552','怀远'=>'552','固镇'=>'552',
            '芜湖'=>'553',
            '淮南'=>'554',
            '马鞍山'=>'555',
            '安庆'=>'556', '桐城'=>'556',
            '宿州'=>'557',
            '阜阳'=>'558',
            '毫州'=>'558B',
            '黄山'=>'559','歙县'=>'559', '休宁'=>'559',
            '淮北市'=>'561',
            '铜陵'=>'562',
            '宣城'=>'563','广德'=>'563','宁国'=>'563',
            '六安'=>'564',
            '巢湖'=>'565', '半汤'=>'565', '无为县'=>'565',
            '池州'=>'566',
            '衢州'=>'570','江山'=>'570',
            '杭州'=>'571','富阳'=>'571','临安'=>'571','桐庐'=>'571', '建德'=>'571', '淳安'=>'571',
            '湖州'=>'572', '德清'=>'572','长兴'=>'572','安吉'=>'572',
            '嘉兴'=>'573','嘉善'=>'573','桐乡'=>'573','海宁'=>'573','海盐'=>'573','平湖'=>'573',
            '宁波'=>'574', '鄞州'=>'574','海曙'=>'574','江北'=>'574','北仓'=>'574','江东'=>'574','下应'=>'574','奉化'=>'574','余姚'=>'574','慈溪'=>'574','宁海'=>'574','象山'=>'574',
            '沼兴'=>'575', '越城'=>'575','袍江'=>'575','诸暨'=>'575','嵊州'=>'575','柯桥'=>'575','新昌县'=>'575','上虞'=>'575',
            '台州'=>'576', '温岭'=>'576', '临海'=>'576', '玉环'=>'576', '仙居'=>'576', '天台'=>'576', '三门'=>'576',
            '温州'=>'577','乐清'=>'577','瑞安'=>'577','永嘉'=>'577','苍南'=>'577','平阳'=>'577','文成'=>'577','洞头'=>'577',
            '丽水'=>'578', '云和'=>'578', '青田'=>'578', '松阳'=>'578', '缙云'=>'578', '遂昌'=>'578', '龙泉'=>'578',
            '金华'=>'579', '义乌'=>'579','东阳市'=>'579','武义县'=>'579','兰溪'=>'579','浦江'=>'579','永康'=>'579','磐安'=>'579',
            '舟山'=>'580',
            '福州福清长乐'=>'591', '闽侯'=>'591', '连江'=>'591', '闽清'=>'591', '罗源'=>'591', '永泰'=>'591', '平潭'=>'591',
            '夏门'=>'592','集美'=>'592',
            '宁德'=>'593','福安'=>'593','福鼎'=>'593','霞浦'=>'593','古田'=>'593','柘荣'=>'593','周宁'=>'593','屏南'=>'593','寿宁'=>'593',
            '莆田'=>'594','仙游'=>'594',
            '泉州'=>'595','晋江'=>'595','南安'=>'595','惠安'=>'595','石狮'=>'595','安溪'=>'595','永春'=>'595','德化'=>'595',
            '漳州'=>'596','漳浦'=>'596','龙海'=>'596','长泰'=>'596','南靖'=>'596','石霄'=>'596','东山'=>'596','平和'=>'596','华安'=>'596','诏安'=>'596',
            '龙岩'=>'597','漳平'=>'597','长汀'=>'597','永定'=>'597','上杭'=>'597','连城'=>'597',
            '三明'=>'598','永安'=>'598','沙县'=>'598','龙溪'=>'598','明溪'=>'598','将乐'=>'598','大田'=>'598','宁化'=>'598','泰宁'=>'598',
            '南平'=>'599','建瓯'=>'599','邵武'=>'599','建阳'=>'599','顺昌'=>'599','光泽'=>'599','武夷山'=>'599','浦城'=>'599','政和'=>'599','松溪'=>'599',
            '威海'=>'631','荣城'=>'631','文登'=>'631','乳山'=>'631',
            '枣庄'=>'632',
            '日照'=>'633','五莲'=>'633',
            '莱芜'=>'634','新泰'=>'634',
            '聊城'=>'635','东阿'=>'635','临清'=>'635','茌平'=>'635','阳谷'=>'635',
            '汕尾'=>'660','海丰'=>'660','陆丰'=>'660',
            '阳江'=>'662','阳东'=>'662','阳西'=>'662','阳春'=>'662',
            '揭阳'=>'663','揭东'=>'663','梅州丰顺'=>'663','普宁'=>'663','揭西'=>'663','惠来'=>'663',
            '茂名'=>'668','高州'=>'668','吴川'=>'668','化州'=>'668','信宜'=>'668','电白'=>'668',
            '西双版纳州'=>'691','景洪市'=>'691','勐海县'=>'691',
            '鹰潭'=>'701','贵溪'=>'701','余江'=>'701',
            '襄樊'=>'710','枣阳'=>'710',
            '鄂州'=>'711','泽林镇'=>'711','葛店镇'=>'711',
            '孝感'=>'712','毛陈镇'=>'712',
            '汉川'=>'712B','新河镇'=>'712B','黄冈'=>'713','团风'=>'713',
            '黄石'=>'714','大冶'=>'714',
            '咸宁'=>'715A','715B'=>'赤壁',
            '荆州'=>'716',
            '宜昌'=>'717','枝江'=>'717','宜都'=>'717','当阳'=>'717',
            '恩施'=>'718','十堰'=>'719',
            '随州'=>'722','淅河镇'=>'722','厉山镇'=>'722',
            '荆门'=>'724','钟祥'=>'724',
            '潜江'=>'728','仙桃'=>'728','天门'=>'728',
            '岳阳'=>'730','临湘'=>'730',
            '长沙'=>'7311','浏阳'=>'7311','宁乡'=>'7311','望城'=>'7311','长沙县'=>'7311',
            '湘潭'=>'7312','湘乡'=>'7312',
            '株州'=>'7313','醴陵'=>'7313',
            '衡阳'=>'734','耒阳'=>'734',
            '郴州'=>'735','资兴'=>'735',
            '常德'=>'736','桃源'=>'736',
            '益阳'=>'737',
            '娄底'=>'738A',
            '邵阳'=>'739','邵东'=>'739','新邵'=>'739',
            '吉首'=>'743',
            '张家界'=>'744',
            '怀化'=>'745',
            '永州'=>'746',
            '江门'=>'750','开平'=>'750','台山'=>'750','恩平'=>'750','鹤山'=>'750',
            '韶关'=>'751','曲江'=>'751','乐昌'=>'751','乳源'=>'751','仁化'=>'751','南雄'=>'751','始兴'=>'751','翁源'=>'751',
            '惠州'=>'752', '博罗'=>'752', '惠东'=>'752', '海丰县鹅埠镇圆墩镇'=>'752', '龙门'=>'752',
            '梅州'=>'753', '梅县'=>'753', '兴宁'=>'753', '五华'=>'753', '蕉岭'=>'753', '平远'=>'753', '大浦'=>'753',
            '汕头'=>'754','澄海'=>'754','潮阳'=>'754',
            '深圳西丽分部'=>'755AP',
            '珠海'=>'756',
            '佛山'=>'757','南海'=>'757','高明'=>'757','三水'=>'757','顺德'=>'757',
            '肇庆'=>'758','高要'=>'758','四会'=>'758','广宁'=>'758','德庆'=>'758',
            '湛江'=>'759','遂溪'=>'759','廉江'=>'759','雷州'=>'759','徐闻'=>'759',
            '中山'=>'760','小榄'=>'760','石岐'=>'760','三乡'=>'760','三角'=>'760','沙溪'=>'760',
            '河源'=>'762','东源'=>'762','紫金'=>'762','龙川'=>'762','连平'=>'762','和平'=>'762',
            '清远'=>'763','清新'=>'763','英德'=>'763','佛冈'=>'763','连州'=>'763','阳山'=>'763','连南'=>'763',
            '云浮'=>'766','新兴'=>'766','罗定'=>'766','云安'=>'766',
            '潮州'=>'768','潮安'=>'768','饶平'=>'768',
            '东莞'=>'769',
            '防城港市'=>'770A','东兴'=>'770A',
            '南宁'=>'771','武鸣'=>'771','横县'=>'771','崇左市'=>'771','凭祥'=>'771',
            '柳州'=>'772','柳江'=>'772','鹿寨'=>'772',
            '桂林'=>'773','灵川'=>'773','临桂'=>'773','荔浦'=>'773','阳朔'=>'773',
            '梧州'=>'774','苍梧'=>'774','岑溪'=>'774',
            '玉林'=>'775','北流'=>'775',
            '贵港市'=>'775B',
            '钦州'=>'777A',
            '北海'=>'779',
            '新余'=>'790A','分宜'=>'790A',
            '南昌'=>'791','新建'=>'791','进贤'=>'791',
            '九江'=>'792','德安'=>'792','瑞昌'=>'792','湖口'=>'792','都昌'=>'792',
            '上饶'=>'793','广丰'=>'793','玉山'=>'793','戈阳'=>'793','横峰'=>'793','铅山'=>'793',
            '抚州'=>'794','上顿渡镇'=>'794','东乡县'=>'794',
            '宜春'=>'795','万载'=>'795','高安'=>'795','樟树'=>'795','丰城'=>'795',
            '吉安'=>'796',
            '赣州'=>'797','赣县'=>'797','南康市'=>'797','信丰'=>'797','龙南'=>'797',
            '景德镇'=>'798','浮梁'=>'798','乐平'=>'798',
            '萍乡'=>'799','泸溪'=>'799',
            '攀枝花'=>'812',
            '自贡'=>'813',
            '绵阳'=>'816','江油'=>'816','三台'=>'816','安县'=>'816',
            '南充'=>'817','阆中市'=>'817','南部县'=>'817',
            '达州'=>'818','达县'=>'818',
            '遂宁'=>'825',
            '广安'=>'826A',
            '巴中'=>'827A',
            '泸州'=>'830','泸县'=>'830',
            '宜宾'=>'831',
            '内江'=>'832','隆昌'=>'832','资阳'=>'832',
            '乐山'=>'833',
            '眉山'=>'833B',
            '雅安'=>'835',
            '西昌'=>'834A',
            '德阳'=>'838','广汉'=>'838','绵竹'=>'838','什邡'=>'838',
            '广元'=>'839',
            '贵阳'=>'851','龙洞堡'=>'851',
            '香港'=>'852',
            '澳门'=>'853',
            '都匀'=>'854',
            '凯里市'=>'855A',
            '毕节市'=>'857A',
            '六盘水'=>'858',
            '昆明'=>'871','官渡古镇'=>'871','小板桥镇'=>'871','呈贡县'=>'871',
            '大理'=>'872',
            '红河州蒙自市'=>'873A',
            '个旧市'=>'873B',
            '曲靖'=>'874',
            '云南保山'=>'875',
            '云南文山州'=>'876',
            '玉溪'=>'877',
            '楚雄'=>'878A',
            '普洱市'=>'879',
            '台湾'=>'886',
            '拉萨市'=>'891',
            '海口'=>'898',
            '三亚'=>'8981',
            '咸阳'=>'910',
            '渭南'=>'913',
            '汉中'=>'916',
            '宝鸡'=>'917','岐山'=>'917',
            '兰州'=>'931',
            '银川'=>'951',
            '西宁'=>'971',
            '乌鲁木齐'=>'991'
        );
		$code = '';
        foreach($expressname as $keys => $values){
            if(strstr($province,$keys)){
                //echo $keys."=>".$values;
                $code = $values;
                break;
            }
            elseif(strstr($city,$keys)){
                //echo $keys."=>".$values;
                $code = $values;
                break;
            }
        };
        
        return $code;
    }
    public function WritelogSF($date,$incrementId,$action,$request,$response,$status){
        $foderlogSF = $this->_directory->getPath('var').'/log/sf/';
        $filename = $foderlogSF.$incrementId.'.log';
        if(!is_dir($foderlogSF)) $this->_ioFile->mkdir($foderlogSF, 0777);
            $filelogsf = fopen($filename, "a+");
            $textlog = $date." ,".$incrementId." ,".$action." ,".$request." ,".$response." ,".$status."\n";
            fwrite($filelogsf, $textlog);
            fclose($filelogsf);
    }
}
