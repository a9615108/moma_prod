<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   1.1.22
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Helper;

use Mirasvit\Rma\Model\Config as Config;

class Ruleevent extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rule\RuleManagementInterface $ruleManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Helper\Mail $rmaMail,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->ruleManagement = $ruleManagement;
        $this->rmaManagement  = $rmaManagement;
        $this->rmaMail        = $rmaMail;
        $this->context        = $context;

        parent::__construct($context);
    }

    /**
     * @var array
     */
    protected $sentEmails = [];

    /**
     * @var array
     */
    protected $processedEvents = [];

    /**
     * @param string                  $eventType
     * @param \Mirasvit\Rma\Model\Rma $rma
     * @return void
     */
    public function newEvent($eventType, $rma)
    {
        $key = $eventType.$rma->getId();
        if (isset($this->processedEvents[$key])) {
            return;
        } else {
            $this->processedEvents[$key] = true;
        }

        $this->sentEmails = [];
        $rules = $this->ruleManagement->getEventRules($eventType);
        /** @var \Mirasvit\Rma\Model\Rule $rule */
        foreach ($rules as $rule) {
            $rule->afterLoad();
            if (!$rule->validate($rma)) {
                continue;
            }
            $this->processRule($rule, $rma);
            if ($rule->getIsStopProcessing()) {
                break;
            }
        }
    }

    /**
     * @param \Mirasvit\Rma\Model\Rule $rule
     * @param \Mirasvit\Rma\Model\Rma  $rma
     * @return void
     */
    protected function processRule($rule, $rma)
    {
        /* set attributes **/
        if ($rule->getStatusId()) {
            $rma->setStatusId($rule->getStatusId());
        }
        if ($rule->getUserId()) {
            $rma->setUserId($rule->getUserId());
        }

        $rma->save();

        /* send notifications **/
        if ($rule->getIsSendOwner()) {
            /** @var \Magento\User\Model\User $user */
            if ($user = $this->rmaManagement->getUser($rma)) {
                $this->_sendEventNotification($user->getEmail(), $user->getName(), $rule, $rma);
            }
        }
        if ($rule->getIsSendUser()) {
            /** @var \Magento\Customer\Model\Customer $customer */
            if ($customer = $this->rmaManagement->getCustomer($rma)) {
                $this->_sendEventNotification($customer->getEmail(), $customer->getName(), $rule, $rma);
            }
        }
        if ($otherEmail = $rule->getOtherEmail()) {
            $this->_sendEventNotification($otherEmail, '', $rule, $rma);
        }
    }

    /**
     * @param string                   $email
     * @param string                   $name
     * @param \Mirasvit\Rma\Model\Rule $rule
     * @param \Mirasvit\Rma\Model\Rma  $rma
     * @return void
     */
    protected function _sendEventNotification($email, $name, $rule, $rma)
    {
        if (!is_array($this->sentEmails) || !in_array($email, $this->sentEmails)) {
            $this->rmaMail->sendNotificationRule($email, $name, $rule, $rma);
            $this->sentEmails[] = $email;
        }
    }
}
