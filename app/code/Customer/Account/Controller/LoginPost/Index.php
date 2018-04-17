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
class Index extends \Magento\Customer\Controller\AbstractAccount
{
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var Validator */
    protected $formKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    private $Customer;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        \Magento\Customer\Model\Customer $Customer
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        $this->Customer = $Customer;
        parent::__construct($context);
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {

        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        if ($this->getRequest()->isPost()) {

            $login = $this->getRequest()->getPost('login');

            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);

                    // moma-24
                    $customer_eav = $this->Customer->load($customer->getId());
//
                    $telephone = $customer_eav->getData('vip_phone');

                    if( $customer_eav->getVipNum() == '' || $telephone == '' ){
                        // 沒有 VIP 卡號
                        //      驗證電話號碼

                        $this->session->setCustomerDataAsLoggedIn($customer); // 紀錄登入狀態

                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath('customer_account/Loginpost/Loginverification');

                        return $resultRedirect;
                    }

                    $gender   = $customer->getGender();     // 性別
                    $dob      = $customer->getDob();        // 生日
                    $Reg_date = $customer->getCreatedAt();  // 線上會員註冊日

                    $gender_arr   = array('女','男','女','',''=>'女');
                    $pos_gender = $gender_arr[$gender];

                    $pos_dob    = explode(' ',$dob)[0];
                    $pos_dob    = str_replace('-', '', $pos_dob);
                    if( $pos_dob == '' ){
                        $pos_dob = date('Ymd',strtotime("-1 days"));
                    }

                    $pos_Reg_date = explode(' ',$Reg_date)[0];
                    $pos_Reg_date = str_replace('-', '', $pos_Reg_date);

                    $vipSite = $customer_eav->getVipSite();
                    if( $vipSite != 1 ){
                        $vipSite = 2;
                    }

                    // 會員登入時 ，歐斯瑞傳送會員資料( 手機 、 姓名 、 生日 、 性別 、 歐斯瑞會員ID 、 Email 、 開卡位置註記 )至凱新指定的URL
                    $data = array(
                        'C_name'        =>   $customer->getFirstname()
                                           . $customer->getMiddlename()
                                           . $customer->getLastname(),  // 姓名
                        'Sex'           => $pos_gender,                 // 性別
                        'Birthday'      => $pos_dob,                    // 生日
                        'Mobile'        => $telephone,                  // 手機
                        'E_mail'        => $customer->getEmail(),       // Email
                        'ID'            => $customer->getId(),          // 歐斯瑞會員ID
                        'Reg_mark'      => $vipSite,                    // 開卡位置註記
                        'Reg_date'      => $pos_Reg_date,               // 線上會員註冊日
                    );

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
                        $this->session->logout();
                        $this->messageManager->addError('MOMA會員您好，『此手機號碼已有綁定VIP卡號』，為不影響您的消費累積權益，故無法申請新帳號或登入，若您需更改VIP資訊，請來電客服 0800-086-986，由客服人員為您服務，謝謝！');
                    }else{
                        $this->session->setCustomerDataAsLoggedIn($customer); // 紀錄登入狀態
                    }

                    // moma-24 end

                    // 紀錄登入狀態
                    //$this->session->setCustomerDataAsLoggedIn($customer);
                    //$this->session->regenerateId();
                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }
                    $redirectUrl = $this->accountRedirect->getRedirectCookie();
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                        $this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->resultRedirectFactory->create();
                        // URL is checked to be internal in $this->_redirect->success()
                        $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                        return $resultRedirect;
                    }
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (UserLockedException $e) {
                    $message = __(
                        'The account is locked. Please wait and try again or contact %1.',
                        $this->getScopeConfig()->getValue('contact/email/recipient_email')
                    );
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (AuthenticationException $e) {
                    $message = __('Invalid login or password.');
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (\Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $this->messageManager->addError(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
                }
            } else {
                $this->messageManager->addError(__('A login and a password are required.'));
            }
        }

        return $this->accountRedirect->getRedirect();
    }
}