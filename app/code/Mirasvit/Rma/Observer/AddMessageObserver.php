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



namespace Mirasvit\Rma\Observer;
use Mirasvit\Rma\Model\Config;

use Magento\Framework\Event\ObserverInterface;

class AddMessageObserver implements ObserverInterface
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement,
        \Mirasvit\Rma\Helper\Mail $rmaMail,
        \Mirasvit\Rma\Helper\Ruleevent $rmaRuleEvent
    ) {
        $this->attachmentManagement = $attachmentManagement;
        $this->rmaMail              = $rmaMail;
        $this->rmaRuleEvent         = $rmaRuleEvent;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Mirasvit\Rma\Model\Rma $rma */
        $rma = $observer->getData('rma');
        /** @var \Mirasvit\Rma\Api\Service\Performer\PerformerInterface $performer */
        $performer = $observer->getData('performer');
        /** @var \Mirasvit\Rma\Model\Message $message */
        $message = $observer->getData('message');

        $params = $observer->getData('params');

        if (!empty($params['helpdeskEmail'])) {
            $message->setEmailId($params['helpdeskEmail']->getId());
            $params['helpdeskEmail']->setIsProcessed(true)
                ->save();
            $this->attachmentManagement->copyEmailAttachments($params['email'], $message);
        } else {
            $this->attachmentManagement->saveAttachments(
                \Mirasvit\Rma\Api\Config\AttachmentConfigInterface::ATTACHMENT_ITEM_MESSAGE,
                $message->getId(),
                'attachment'
            );
        }

        if ($performer instanceof \Mirasvit\Rma\Service\Performer\UserStrategy) {
            if ($message->getIsCustomerNotified()) {
                $this->rmaMail->sendNotificationCustomerEmail($rma, $message);
            }
            //send notification about internal message
            if (
                $rma->getUserId() != $performer->getId() && !$message->getIsVisibleInFrontend()
            ) {
                $this->rmaMail->sendNotificationAdminEmail($rma, $message);
            }
            $this->rmaRuleEvent->newEvent(
                \Mirasvit\Rma\Api\Config\RuleConfigInterface::RULE_EVENT_NEW_STAFF_REPLY, $rma
            );
        } else {
            if (isset($params['isNotifyAdmin']) && $params['isNotifyAdmin']) {
                $this->rmaMail->sendNotificationAdminEmail($rma, $message);
            }
            if ($message->getIsCustomerNotified()) {
                $this->rmaMail->sendNotificationCustomerEmail($rma, $message);
            }
            $this->rmaRuleEvent->newEvent(
                \Mirasvit\Rma\Api\Config\RuleConfigInterface::RULE_EVENT_NEW_STAFF_REPLY, $rma
            );
        }
    }
}