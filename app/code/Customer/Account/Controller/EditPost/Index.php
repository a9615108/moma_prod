<?php
namespace Customer\Account\Controller\EditPost;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Index extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Form code for data extractor
     */
    const FORM_DATA_EXTRACTOR_CODE = 'customer_account_edit';

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var Session
     */
    protected $session;

    /** @var EmailNotificationInterface */
    private $emailNotification;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var Mapper
     */
    private $customerMapper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param Validator $formKeyValidator
     * @param CustomerExtractor $customerExtractor
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        Validator $formKeyValidator,
        \Magento\Customer\Model\Customer $Customer,
        CustomerExtractor $customerExtractor
    ) {
        parent::__construct($context);
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerExtractor = $customerExtractor;
        $this->Customer = $Customer;
    }

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {

        if (!($this->authentication instanceof AuthenticationInterface)) {
            return ObjectManager::getInstance()->get(
                \Magento\Customer\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Change customer email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());

        if ($validFormKey && $this->getRequest()->isPost()) {
            $currentCustomerDataObject = $this->getCustomerDataObject($this->session->getCustomerId());
            $customerCandidateDataObject = $this->populateNewCustomerDataObject(
                $this->_request,
                $currentCustomerDataObject
            );

            try {
                // whether a customer enabled change email option
                $this->processChangeEmailRequest($currentCustomerDataObject);

                // whether a customer enabled change password option
                $isPasswordChanged = $this->changeCustomerPassword($currentCustomerDataObject->getEmail());

                $customer = $this->Customer->load($this->session->getCustomerId());
                $array_keys = array_keys($customerCandidateDataObject->getCustomAttributes());
                foreach( $array_keys as $key){
                    $customerCandidateDataObject->setCustomAttribute($key,$customer->getData($key));
                }
                $this->customerRepository->save($customerCandidateDataObject);

                // moma-24 start
                /*
                    限網購開卡者
                    修改完後儲存時，歐斯瑞傳送會員資料( 手機 、 姓名 、 生日 、 性別 、 歐斯瑞會員ID 、 Email 、 開卡位置註記 )至凱新指定的URL
                */
                $customer = $this->Customer->load( $this->session->getCustomerId() );

                if( $customer->getVipSite() == 2 ){ // 1:實體店面開卡 2:網購開卡
                    $customer_data = $this->customerRepository->getById($this->session->getCustomerId());
                    $addresses = $customer_data->getAddresses();
                    $telephone = isset($addresses[0])?$addresses[0]->getTelephone():'';

                    $data = array(
                        'C_name'        =>   $customer->getFirstname()
                                           . $customer->getMiddlename()
                                           . $customer->getLastname(),  // 姓名
                        'Sex'           => $customer->getGender(),      // 性別
                        'Birthday'      => $customer->getDob(),         // 生日
                        'Mobile'        => $telephone,                  // 手機
                        'E_mail'        => $customer->getEmail(),       // Email
                        'ID'            => $customer->getId(),          // 歐斯瑞會員ID
                        'Reg_mark'      => $customer->getVipSite(),     // 開卡位置註記
                        //'Reg_date'      => $customer->getCreatedAt(),   // 線上會員註冊日
                    );

                    $model = $this->_objectManager->get('Magento\Variable\Model\Variable')->loadByCode('POS_MEMBER_EDIT');
                    $url = $model->getName();

                    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/POS_MEMBER_EDIT.log');
                    $logger = new \Zend\Log\Logger();
                    $logger->addWriter($writer);

                    /* $data 要記 log */
                    $logger->info('==============================');
                    $logger->info($data);

                    $s = curl_init();
                    curl_setopt($s,CURLOPT_URL,$url);
                    curl_setopt($s, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
                    curl_setopt($s, CURLOPT_POST, 1);
                    curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
                    $contents = curl_exec($s);

                    /* $contents 要記 log */
                    //$logger->info($contents);
                }
                // moma-24 end

                $this->getEmailNotification()->credentialsChanged(
                    $customerCandidateDataObject,
                    $currentCustomerDataObject->getEmail(),
                    $isPasswordChanged
                );
                $this->dispatchSuccessEvent($customerCandidateDataObject);
                $this->messageManager->addSuccess(__('You saved the account information.'));
                return $resultRedirect->setPath('customer/account');
            } catch (InvalidEmailOrPasswordException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (UserLockedException $e) {
                $message = __(
                    'The account is locked. Please wait and try again or contact %1.',
                    $this->getScopeConfig()->getValue('contact/email/recipient_email')
                );
                $this->session->logout();
                $this->session->start();
                $this->messageManager->addError($message);
                return $resultRedirect->setPath('customer/account/login');
            } catch (InputException $e) {
                $this->messageManager->addError($e->getMessage());
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addError($error->getMessage());
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t save the customer.'));
            }

            $this->session->setCustomerFormData($this->getRequest()->getPostValue());
        }

        return $resultRedirect->setPath('*/*/edit');
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * Account editing action completed successfully event
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customerCandidateDataObject
     * @return void
     */
    private function dispatchSuccessEvent(\Magento\Customer\Api\Data\CustomerInterface $customerCandidateDataObject)
    {
        $this->_eventManager->dispatch(
            'customer_account_edited',
            ['email' => $customerCandidateDataObject->getEmail()]
        );
    }

    /**
     * Get customer data object
     *
     * @param int $customerId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function getCustomerDataObject($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * Create Data Transfer Object of customer candidate
     *
     * @param \Magento\Framework\App\RequestInterface $inputData
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function populateNewCustomerDataObject(
        \Magento\Framework\App\RequestInterface $inputData,
        \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
        $customerDto = $this->customerExtractor->extract(
            self::FORM_DATA_EXTRACTOR_CODE,
            $inputData,
            $attributeValues
        );
        $customerDto->setId($currentCustomerData->getId());
        if (!$customerDto->getAddresses()) {
            $customerDto->setAddresses($currentCustomerData->getAddresses());
        }
        if (!$inputData->getParam('change_email')) {
            $customerDto->setEmail($currentCustomerData->getEmail());
        }

        return $customerDto;
    }

    /**
     * Change customer password
     *
     * @param string $email
     * @return boolean
     * @throws InvalidEmailOrPasswordException|InputException
     */
    protected function changeCustomerPassword($email)
    {
        $isPasswordChanged = false;
        if ($this->getRequest()->getParam('change_password')) {
            $currPass = $this->getRequest()->getPost('current_password');
            $newPass = $this->getRequest()->getPost('password');
            $confPass = $this->getRequest()->getPost('password_confirmation');
            if ($newPass != $confPass) {
                throw new InputException(__('Password confirmation doesn\'t match entered password.'));
            }

            $isPasswordChanged = $this->customerAccountManagement->changePassword($email, $currPass, $newPass);
        }

        return $isPasswordChanged;
    }

    /**
     * Process change email request
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerDataObject
     * @return void
     * @throws InvalidEmailOrPasswordException
     * @throws UserLockedException
     */
    private function processChangeEmailRequest(\Magento\Customer\Api\Data\CustomerInterface $currentCustomerDataObject)
    {
        if ($this->getRequest()->getParam('change_email')) {
            // authenticate user for changing email
            try {
                $this->getAuthentication()->authenticate(
                    $currentCustomerDataObject->getId(),
                    $this->getRequest()->getPost('current_password')
                );
            } catch (InvalidEmailOrPasswordException $e) {
                throw new InvalidEmailOrPasswordException(__('The password doesn\'t match this account.'));
            }
        }
    }

    /**
     * Get Customer Mapper instance
     *
     * @return Mapper
     *
     * @deprecated
     */
    private function getCustomerMapper()
    {
        if ($this->customerMapper === null) {
            $this->customerMapper = ObjectManager::getInstance()->get('Magento\Customer\Model\Customer\Mapper');
        }
        return $this->customerMapper;
    }
}
