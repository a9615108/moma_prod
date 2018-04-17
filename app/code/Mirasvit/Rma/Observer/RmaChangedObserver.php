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

use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Rma\Api\Config\AttachmentConfigInterface as Config;
use Mirasvit\Rma\Api\Repository\StatusRepositoryInterface;
use Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface;
use Mirasvit\Rma\Api\Service\Message\MessageManagement\AddInterface;
use Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface;

/**
 * Notify about RMA changes
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RmaChangedObserver implements ObserverInterface
{
    public function __construct(
        \Mirasvit\Rma\Api\Config\HelpdeskConfigInterface $helpdeskConfig,
        \Mirasvit\Rma\Helper\Ruleevent $rmaRuleEvent,
        \Mirasvit\Rma\Helper\Helpdesk $helpdeskHelper,
        StatusRepositoryInterface $statusRepository,
        RmaManagementInterface $rmaManagement,
        AddInterface $messageAddManagement,
        AttachmentManagementInterface $attachmentManagement,
        \Mirasvit\Rma\Helper\Message\Html $messageHtmlHelper,
        \Mirasvit\Rma\Helper\Mail $rmaMail
    ) {
        $this->helpdeskConfig       = $helpdeskConfig;
        $this->rmaRuleEvent         = $rmaRuleEvent;
        $this->helpdeskHelper       = $helpdeskHelper;
        $this->statusRepository     = $statusRepository;
        $this->rmaManagement        = $rmaManagement;
        $this->messageAddManagement = $messageAddManagement;
        $this->attachmentManagement = $attachmentManagement;
        $this->messageHtmlHelper    = $messageHtmlHelper;
        $this->rmaMail              = $rmaMail;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rma = $observer->getData('rma');

        $this->attachmentManagement->saveAttachment(
            Config::ATTACHMENT_ITEM_RETURN_LABEL,
            $rma->getId(),
            Config::ATTACHMENT_ITEM_RETURN_LABEL
        );

        $this->notifyRmaChange($rma, $observer->getData('performer'));
    }


    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface                    $rma
     * @param \Mirasvit\Rma\Api\Service\Performer\PerformerInterface $performer
     *
     * @return void
     */
    public function notifyRmaChange($rma, $performer)
    {
        $status = $this->rmaManagement->getStatus($rma);
        if ($rma->getStatusId() != $rma->getOrigData('status_id')) {
            $this->onRmaStatusChange($rma, $performer);
        }
        if ($rma->getOrigData('rma_id')) {
            if (
                $rma->getUserId() != $rma->getOrigData('user_id') &&
                $this->statusRepository->getAdminMessageForStore($status, $rma->getStoreId())
            ) {
                $this->onRmaUserChange($rma);
            }
            $this->rmaRuleEvent->newEvent(
                \Mirasvit\Rma\Api\Config\RuleConfigInterface::RULE_EVENT_RMA_UPDATED, $rma
            );
        } else {
            $this->rmaRuleEvent->newEvent(
                \Mirasvit\Rma\Api\Config\RuleConfigInterface::RULE_EVENT_RMA_CREATED, $rma
            );
            if ($rma->getTicketId() && $this->helpdeskConfig->isHelpdeskActive()) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $objectManager->create('\Mirasvit\Helpdesk\Helper\Ruleevent')->newEvent(
                    \Mirasvit\Helpdesk\Model\Config::RULE_EVENT_TICKET_CONVERTED_TO_RMA,
                    $this->helpdeskHelper->getTicket($rma->getTicketId())
                );
            }
        }
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface                    $rma
     * @param \Mirasvit\Rma\Api\Service\Performer\PerformerInterface $performer
     *
     * @return void
     */
    public function onRmaStatusChange($rma, $performer)
    {
        $status = $this->rmaManagement->getStatus($rma);
        $customerMessage = $this->statusRepository->getCustomerMessageForStore($status, $rma->getStoreId());
        $adminMessage    = $this->statusRepository->getAdminMessageForStore($status, $rma->getStoreId());
        $historyMessage  = $this->statusRepository->getHistoryMessageForStore($status, $rma->getStoreId());
        if ($customerMessage) {
            $this->rmaMail->sendNotificationCustomerEmail($rma, $customerMessage, true);
        }
        if ($adminMessage) {
            $this->rmaMail->sendNotificationAdminEmail($rma, $adminMessage, true);
        }

        if ($historyMessage) {
            $message = $this->rmaMail->parseVariables($historyMessage, $rma);

            $params = [
                'isNotified' => $status->getCustomerMessage() != '',
                'isVisible'  => 1
            ];
            $this->messageAddManagement->addMessage($performer, $rma, $message, $params);
        }
        if ($customerMessage || $historyMessage) {
            if ($rma->getUserId()) {
                $rma->setLastReplyName($this->rmaManagement->getFullName($rma))
                    ->save();
            }
        }
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return void
     */
    protected function onRmaUserChange($rma)
    {
        $status  = $this->rmaManagement->getStatus($rma);
        $message = $this->statusRepository->getAdminMessageForStore($status, $rma->getStoreId());
        $message = $this->rmaMail->parseVariables($message, $rma);
        $this->rmaMail->sendNotificationAdminEmail($rma, $message);
    }
}