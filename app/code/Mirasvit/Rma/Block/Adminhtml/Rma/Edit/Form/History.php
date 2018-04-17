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


namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form;


class History extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface $messageManagement,
        \Mirasvit\Rma\Helper\Message\Html $rmaMessageHtml,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->attachmentManagement = $attachmentManagement;
        $this->rmaSearchManagement  = $rmaSearchManagement;
        $this->messageManagement    = $messageManagement;
        $this->rmaMessageHtml       = $rmaMessageHtml;
        $this->context              = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->getData('rma');
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\MessageInterface[]
     */
    public function getMessageList()
    {
        return $this->rmaSearchManagement->getMessages($this->getRma());
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return int
     */
    public function getMessageTriggeredBy($message)
    {
        return $this->messageManagement->getTriggeredBy($message);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getCustomerEmail($message)
    {
        return $this->messageManagement->getCustomerEmail($message);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getUserName($message)
    {
        return $this->messageManagement->getUserName($message);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getMessageType($message)
    {
        return $this->messageManagement->getType($message);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return string
     */
    public function getMessageTextHtml($message)
    {
        return $this->rmaMessageHtml->getTextHtml($message);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\AttachmentInterface $attachment
     * @return string
     */
    public function getAttachmentUrl($attachment)
    {
        return $this->attachmentManagement->getUrl($attachment);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface[]
     */
    public function getMessageAttachments($message)
    {
        return $this->attachmentManagement->getAttachments(
            \Mirasvit\Rma\Api\Config\AttachmentConfigInterface::ATTACHMENT_ITEM_MESSAGE, $message->getId()
        );
    }

    /**
     * @param bool $isRead
     * @return string
     */
    public function getMarkUrl($isRead)
    {
        return $this->getUrl('*/*/markRead', ['rma_id' => $this->getRma()->getId(), 'is_read' => (int) $isRead]);
    }
}