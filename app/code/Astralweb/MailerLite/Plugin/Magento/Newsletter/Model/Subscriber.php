<?php
namespace Astralweb\MailerLite\Plugin\Magento\Newsletter\Model;

use Astralweb\MailerLite\Helper\MailerLite;

class Subscriber
{
    /**
     * @var \Astralweb\MailerLite\Helper\MailerLite
     */
    protected $mailerLiteHelper;
    protected $customerRepository;

    /**
     * Groups constructor.
     * @param \Astralweb\MailerLite\Helper\MailerLite $mailerLiteHelper
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        MailerLite $mailerLiteHelper
    ) {
        $this->mailerLiteHelper = $mailerLiteHelper;
        $this->customerRepository = $customerRepositoryInterface;
    }


    public function beforeSubscribe(\Magento\Newsletter\Model\Subscriber $subscriber, $email)
    {
        try{
            $customer = $this->customerRepository->get($email);    
        }
        catch (\Magento\Framework\Exception\NoSuchEntityException $e){
            $customer = $subscriber->loadByEmail($email);
        }

        $info = new \stdClass();
        $info->email = $email;
        $info->type =  MailerLite::TYPE_ACTIVE;
        if($customer->getFirstname() || $customer->getLastname()){
            $info->name = $customer->getFirstname() . ' ' . $customer->getLastname();
        }
        $res = $this->mailerLiteHelper->addSubscribersToGroup($info);
    }


    public function beforeUnsubscribeCustomerById(\Magento\Newsletter\Model\Subscriber $subscriber, $customerId)
    {
        try{
            $customer = $this->customerRepository->getById($customerId);
        }
        catch (\Magento\Framework\Exception\NoSuchEntityException $e){
            $customer = $subscriber->loadByCustomerId($customerId);
        }

        $info = new \stdClass();
        $info->email = $customer->getEmail();
        $info->type =  MailerLite::TYPE_UNSUBSCRIBED;
        if($customer->getFirstname() || $customer->getLastname()){
            $info->name = $customer->getFirstname() . ' ' . $customer->getLastname();
        }
        $res = $this->mailerLiteHelper->addSubscribersToGroup($info);
    }

    public function beforeSubscribeCustomerById(\Magento\Newsletter\Model\Subscriber $subscriber, $customerId)
    {
        try{
            $customer = $this->customerRepository->getById($customerId);
        }
        catch (\Magento\Framework\Exception\NoSuchEntityException $e){
            $customer = $subscriber->loadByCustomerId($customerId);
        }


        $info = new \stdClass();
        $info->email = $customer->getEmail();
        $info->type =  MailerLite::TYPE_ACTIVE;
        if($customer->getFirstname() || $customer->getLastname()){
            $info->name = $customer->getFirstname() . ' ' . $customer->getLastname();
        }
        $res = $this->mailerLiteHelper->addSubscribersToGroup($info);
    }



}
