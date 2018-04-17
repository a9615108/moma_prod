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
use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Checksms extends \Magento\Customer\Controller\AbstractAccount
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
        $this->context          = $context;

        parent::__construct($context);
    }

    public function execute()
    {
        if( isset($this->context->messageManager) ){
            $this->messageManager    = $this->context->messageManager;
        }else{
            $this->messageManager  = $this->_objectManager->create('Magento\Framework\Message\ManagerInterface');
        }

        $reverification_seconds = 360;   // 驗證碼有效時間

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if( ! $this->getRequest()->isPost() ){
            // 沒有資料傳入

            // $resultRedirect->setUrl('/customer_account/Loginpost/Loginverification');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl()); // 導去首頁
            return $resultRedirect;
        }

        if( 1 ){
            // 時間驗證
            $sms_time = $this->catalogSession->getData('sms_time');
            if( $sms_time ){
                if( time() - $sms_time > $reverification_seconds ){
                    // 超過驗證碼有效時間

                    $resultRedirect->setUrl('/customer_account/Loginpost/Loginverification?error=timeout');  // 該驗證碼已失效
                    return $resultRedirect;
                }
            }
        }

        $tel        = $this->catalogSession->getData('vip_phone');
        $verify     = $this->getRequest()->getPost('verify');
        $firstname  = $this->getRequest()->getPost('firstname');
        $lastname   = $this->getRequest()->getPost('lastname');

        $sms_verifycation = $this->catalogSession->getData('sms_verifycation');

        if( $verify == $sms_verifycation ){
            // 登入完成

            // 將電話寫入
            $customer_eav = $this->Customer->load($this->session->getCustomerId());
            // $customer_eav->setData('gender',2);
            $customer_eav->setData('vip_phone',$tel);

            $customer_eav->setData('firstname',$firstname);
            $customer_eav->setData('lastname',$lastname);

            $customer_eav->save();

            // 會員登入時 ，歐斯瑞傳送會員資料( 手機 、 姓名 、 生日 、 性別 、 歐斯瑞會員ID 、 Email 、 開卡位置註記 )至凱新指定的URL

            $gender   = $customer_eav->getGender();     // 性別
            $dob      = $customer_eav->getDob();        // 生日
            $Reg_date = $customer_eav->getCreatedAt();  // 線上會員註冊日

            if( $dob == '' ){
                $dob = date('Ymd',strtotime("-1 days"));
            }

            $gender_arr   = array('女','男','女','',''=>'女');
            $pos_gender = $gender_arr[$gender];

            $pos_dob    = explode(' ',$dob)[0];
            $pos_dob    = str_replace('-', '', $pos_dob);

            $pos_Reg_date = explode(' ',$Reg_date)[0];
            $pos_Reg_date = str_replace('-', '', $pos_Reg_date);

            $vipSite = $customer_eav->getVipSite();
            if( $vipSite != 1 ){
                $vipSite = 2;
            }

            $data = array(
                'C_name'        =>   $customer_eav->getFirstname()
                    . $customer_eav->getMiddlename()
                    . $customer_eav->getLastname(),  // 姓名
                'Sex'           => $pos_gender,                     // 性別
                'Birthday'      => $pos_dob,                        // 生日
                'Mobile'        => $tel,                            // 手機
                'E_mail'        => $customer_eav->getEmail(),       // Email
                'ID'            => $customer_eav->getId(),          // 歐斯瑞會員ID
                'Reg_mark'      => $vipSite,                        // 開卡位置註記
                'Reg_date'      => $pos_Reg_date,                   // 線上會員註冊日
            );

            $data_json = json_encode( $data );

            // api 取得 VIP number
            $model = $this->_objectManager->get('Magento\Variable\Model\Variable')->loadByCode('POS_MEMBER_LOGIN');
            $POS_MEMBER_LOGIN_url = $model->getName();

            $model = $this->_objectManager->get('Magento\Variable\Model\Variable')->loadByCode('POS_MEMBER_REGISTER');
            $POS_MEMBER_REGISTER_url = $model->getName();

            $POS_MEMBER = $this->_objectManager->create('Customer\Account\Helper\Data');
            $POS_MEMBER->setData($data);
            $POS_MEMBER->setCustomer($customer_eav);
            $POS_MEMBER->setLoginUrl($POS_MEMBER_LOGIN_url);
            $POS_MEMBER->setRegisterUrl($POS_MEMBER_REGISTER_url);
            $POS_MEMBER->login();

            $status = $POS_MEMBER->getStatus();
            if( $status['err'] ){
                // $this->session->setCustomerDataAsLoggedIn($customer); // 紀錄登入狀態
                $this->session->logout();
                $this->messageManager->addError('MOMA會員您好，『此手機號碼已有綁定VIP卡號』，為不影響您的消費累積權益，故無法申請新帳號或登入，若您需更改VIP資訊，請來電客服 0800-086-986，由客服人員為您服務，謝謝！');
                $resultRedirect->setUrl('/customer/account/login');
            }else{
                // 導去首頁
                $resultRedirect->setUrl('/');
            }
            return $resultRedirect;
        }

        $resultRedirect->setUrl('/customer_account/Loginpost/Loginverification?error=error');  // 驗證碼錯誤
        return $resultRedirect;

    }
}