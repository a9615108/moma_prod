<?php
namespace Customer\Account\Block;
class Loginverification extends \Magento\Framework\View\Element\Template
{
    protected $catalogSession;
    protected $customer;
    protected $session;
    protected $customerRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    )
    {

        $this->catalogSession       = $catalogSession;
        $this->customer             = $customer;
        $this->session              = $session;
        $this->customerRepository   = $customerRepository;
        parent::__construct($context, $data);
    }

    function _prepareLayout(){
        $customer_eav = $this->customer->load($this->session->getCustomerId());
        $vip_phone = $customer_eav->getData('vip_phone');

        if( $vip_phone == '' ){
            $customer = $this->customerRepository->getById($this->session->getCustomerId());
            $addresses = $customer->getAddresses();
            $vip_phone = isset($addresses[0])?$addresses[0]->getTelephone():'';
        }

        $this->setData('vip_phone',$vip_phone);

        $this->setData('firstname',$customer_eav->getData('firstname'));
        $this->setData('lastname',$customer_eav->getData('lastname'));

    }
}