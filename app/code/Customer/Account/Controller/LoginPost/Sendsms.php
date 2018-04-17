<?php
namespace Customer\Account\Controller\LoginPost;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Sendsms extends \Magento\Customer\Controller\AbstractAccount
{
    protected $resultPageFactory;
    protected $session;
    protected $formKeyValidator;
    protected $catalogSession;
    protected $Customer;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $Session,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Customer\Model\Customer $Customer
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->session           = $Session;
        $this->formKeyValidator  = $formKeyValidator;
        $this->catalogSession    = $catalogSession;
        $this->Customer          = $Customer;

        // 登入驗證
        if ( ! $this->session->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */

            $return = array();
            $return['status']   = 0;
            $return['msg']      = '請重新登入';
            $return['redirect'] = true;
            echo json_encode( $return );
            exit;
        }

        parent::__construct($context);
    }

    public function execute(){
        $reverification_seconds = 360;   // 驗證碼有效時間
        $resend_seconds = 60;            // 簡訊重送時間

        $return = array(
            'status'   => '0',
            'msg'      => '',
            // 'sms_time' => '',
        );

        $time = time();

if( 1 ){
        // 時間驗證
        $sms_time           = $this->catalogSession->getData('sms_time');
        $verification_time  = $this->catalogSession->getData('verification_time');
        if( $sms_time ){
            if( $time - $sms_time < $resend_seconds ){
                // 未過驗證碼重送時間

                $return['msg']      = '未過驗證碼重送時間';
                $return['sms_time']          = $resend_seconds         - ($time - $sms_time);          // 簡訊重送時間
                $return['verification_time'] = $reverification_seconds - ($time - $verification_time); // 驗證碼有效時間
                echo json_encode( $return );
                return;
            }
        }
}
        if( ! $this->getRequest()->isPost() ){
            $return['msg']      = '沒有資料傳入';
            echo json_encode( $return );
            return;
        }

        $verification_time = $this->catalogSession->getData('verification_time');
        $code = '';
        $new_verification = false;
        if( $verification_time ){
            if( $time - $verification_time > $reverification_seconds ){

                $new_verification = true;
            }
        }else{
            $new_verification = true;
        }

        if( $new_verification ){
            // 產生驗證碼
            $code = substr(explode(' ',microtime())[0],-8,-2);
            $this->catalogSession->setData('sms_verifycation',$code);

            // 紀錄時間
            $this->catalogSession->setData('verification_time',$time);
        }

        if( $code == '' ){
            $code = $this->catalogSession->getData('sms_verifycation');
        }

        // 紀錄時間
        $this->catalogSession->setData('sms_time',$time);

        $customer = $this->Customer->load($this->session->getCustomerId());
        // $vip_phone = $customer->getData('vip_phone');
        $vip_phone    = $this->getRequest()->getPost('tel');
        $this->catalogSession->setData('vip_phone',$vip_phone);

        // 送出簡訊
        $Sms_Helper = $this->_objectManager->create('Astralweb\Sms\Helper\Data');
        $Sms_Helper->setTo('+886'.substr($vip_phone,1));
        $Sms_Helper->setText('您的驗證碼是'.$code.'。');
        $Sms_Helper->send();

        $return['status'] = 1;
        $return['msg']    = '簡訊已送出';

// test
// $return['msg'] .= $code;

        echo json_encode( $return );
    }
}