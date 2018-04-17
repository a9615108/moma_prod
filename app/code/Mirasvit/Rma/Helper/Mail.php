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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mail extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Rma\Service\Rma\RmaAdapter $rmaAdapter,
        \Mirasvit\Core\Model\Mail\Template\TransportBuilder $transportBuilder,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface $messageManagement,
        \Mirasvit\Rma\Helper\Rma\Url $rmaUrlHelper,
        \Mirasvit\Rma\Api\Config\NotificationConfigInterface $notificationConfig,
        \Mirasvit\Rma\Api\Config\HelpdeskConfigInterface $helpdeskConfig,
        \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    ) {
        $this->rmaAdapter           = $rmaAdapter;
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->transportBuilder     = $transportBuilder;
        $this->rmaManagement        = $rmaManagement;
        $this->rmaSearchManagement  = $rmaSearchManagement;
        $this->messageManagement    = $messageManagement;
        $this->rmaUrlHelper         = $rmaUrlHelper;
        $this->notificationConfig   = $notificationConfig;
        $this->helpdeskConfig       = $helpdeskConfig;
        $this->storeManager         = $storeManager;
        $this->context              = $context;
        $this->inlineTranslation    = $inlineTranslation;

        parent::__construct($context);
    }

    /**
     * @var array
     */
    public $emails = [];

    /**
     * @return \Mirasvit\Rma\Api\Config\NotificationConfigInterface
     */
    protected function getNotificationConfig()
    {
        return $this->notificationConfig;
    }

    /**
     * @return string
     */
    protected function getSender()
    {
        return $this->getNotificationConfig()->getSenderEmail();
    }

    /**
     * @param string $templateName
     * @param string $senderName
     * @param string $senderEmail
     * @param string $recipientEmail
     * @param string $recipientName
     * @param array  $variables
     * @param int    $storeId
     * @param string $code
     * @param array  $attachments
     *
     * @return bool
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function send(
        $templateName,
        $senderName,
        $senderEmail,
        $recipientEmail,
        $recipientName,
        $variables,
        $storeId,
        $code,
        $attachments
    ) {
        if (!$senderEmail || !$recipientEmail || $templateName == 'none') {
            return false;
        }

        /** @var \Mirasvit\Rma\Api\Data\AttachmentInterface $attachment */
        foreach ($attachments as $attachment) {
            $this->transportBuilder->addAttachment(
                $attachment->getBody(),
                $attachment->getType(),
                \Zend_Mime::DISPOSITION_ATTACHMENT,
                \Zend_Mime::ENCODING_BASE64,
                $attachment->getName()
            );
        }

        // Add blind carbon copy of all emails if such exists
        $bcc = $this->getNotificationConfig()->getSendEmailBcc();
        if ($bcc) {
            if (strpos($bcc, ',')) {
                $bcc = explode(', ', $bcc);
            }
        }
        if ($bcc) {
            $this->transportBuilder->addBcc($bcc);
        }

        $hiddenCode = $hiddenSeparator = '';
        $isActiveHelpdesk = $this->helpdeskConfig->isHelpdeskActive();
        if ($isActiveHelpdesk) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Mirasvit\Helpdesk\Helper\Email $mailHelper */
            $mailHelper = $objectManager->get('\Mirasvit\Helpdesk\Helper\Email');

            $hiddenCode      = $mailHelper->getHiddenCode($code);
            $hiddenSeparator = $mailHelper->getHiddenSeparator();
        }

        $variables = array_merge($variables, [
            'hidden_separator' => $hiddenSeparator,
            'hidden_code'      => $hiddenCode,
        ]);

        $this->inlineTranslation->suspend();
        $this->transportBuilder
            ->setTemplateIdentifier($templateName)
            ->setTemplateOptions(
                [
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId ? $storeId : $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($variables);

        $this->transportBuilder
            ->setFrom(
                [
                    'name'  => $senderName,
                    'email' => $senderEmail,
                ]
            )
            ->addTo($recipientEmail, $recipientName)
            ->setReplyTo($senderEmail);

        $transport = $this->transportBuilder->getTransport();

        /* @var \Magento\Framework\Mail\Transport $transport */
        $transport->sendMessage();

        $this->inlineTranslation->resume();

        return true;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface            $rma
     * @param \Mirasvit\Rma\Api\Data\MessageInterface|string $message
     * @param boolean                                        $isAllowParseVariables
     * @return void
     */
    public function sendNotificationCustomerEmail($rma, $message, $isAllowParseVariables = false)
    {
        $this->rmaAdapter->setData($rma->getData());
        $attachments = [];
        if (is_object($message)) {
            $attachments = $this->messageManagement->getAttachments($message);
            $message     = $this->messageManagement->getTextHtml($message);
        }
        if ($isAllowParseVariables) {
            $message = $this->parseVariables($message, $rma);
        }
        $storeId = $rma->getStoreId();
        $templateName = $this->getNotificationConfig()->getCustomerEmailTemplate();

        $customer = $this->rmaManagement->getCustomer($rma);
        $recipientEmail = $rma->getEmail() ? $rma->getEmail() : $customer->getEmail();
        $recipientName  = $this->rmaManagement->getFullName($rma);
        $variables = [
            'customer' => $customer,
            'rma'      => $this->rmaAdapter,
            'rmaUrl'   => $this->rmaUrlHelper->getGuestUrl($rma),
            'store'    => $this->storeManager->getStore($storeId),
        ];
        $message = $this->processVariable($message, $variables, $storeId);
        $variables['message'] = $message;

        $senderName = $this->context->getScopeConfig()->getValue("trans_email/ident_{$this->getSender()}/name");
        $senderEmail = $this->context->getScopeConfig()->getValue("trans_email/ident_{$this->getSender()}/email");

        $this->send(
            $templateName,
            $senderName,
            $senderEmail,
            $recipientEmail,
            $recipientName,
            $variables,
            $storeId,
            $rma->getCode(),
            $attachments
        );
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface            $rma
     * @param \Mirasvit\Rma\Api\Data\MessageInterface|string $message
     * @param boolean                                        $isAllowParseVariables
     * @return void
     */
    public function sendNotificationAdminEmail($rma, $message, $isAllowParseVariables = false)
    {
        $this->rmaAdapter->setData($rma->getData());
        if ($isAllowParseVariables) {
            $message = $this->parseVariables($message, $rma);
        }

        $attachments = [];
        if (is_object($message)) {
            $attachments = $this->messageManagement->getAttachments($message);
            $message     = $this->messageManagement->getTextHtml($message);
        }
        $storeId = $rma->getStoreId();
        $templateName = $this->getNotificationConfig()->getAdminEmailTemplate();
        if ($user = $this->rmaManagement->getUser($rma)) {
            $recipientEmail = $user->getEmail();
        } else {
            return;
        }

        $recipientName = '';

        $variables = [
            'customer'              => $this->rmaManagement->getCustomer($rma),
            'rma'                   => $this->rmaAdapter,
            'rma_user_name'         => $this->rmaManagement->getUser($rma)->getName(),
            'rma_status'            => $this->rmaManagement->getStatus($rma)->getName(),
            'rma_createdAtFormated' => $this->rmaManagement->getCreatedAtFormated($rma),
            'rma_updatedAtFormated' => $this->rmaManagement->getUpdatedAtFormated($rma),
            'store'                 => $this->storeManager->getStore($storeId),
        ];
        $message = $this->processVariable($message, $variables, $storeId);
        $variables['message'] = $message;

        $senderName = $this->context->getScopeConfig()->getValue("trans_email/ident_{$this->getSender()}/name");
        $senderEmail = $this->context->getScopeConfig()->getValue("trans_email/ident_{$this->getSender()}/email");
        $this->send(
            $templateName,
            $senderName,
            $senderEmail,
            $recipientEmail,
            $recipientName,
            $variables,
            $storeId,
            $this->rmaManagement->getCode($rma),
            $attachments
        );
    }

    /**
     * @param string                              $recipientEmail
     * @param string                              $recipientName
     * @param \Mirasvit\Rma\Model\Rule            $rule
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return void
     */
    public function sendNotificationRule($recipientEmail, $recipientName, $rule, $rma)
    {
        $this->rmaAdapter->setData($rma->getData());
        $attachments = [];

        $text = '';
        if ($message = $this->rmaSearchManagement->getLastMessage($rma)) {
            if ($rule->getIsSendAttachment()) {
                $attachments = $this->messageManagement->getAttachments($message);
            }

            $text = $this->messageManagement->getTextHtml($message);
        }

        $storeId = $rma->getStoreId();
        $templateName = $this->getNotificationConfig()->getRuleTemplate($rma->getStoreId());

        $variables = [
            'customer'      => $this->rmaManagement->getCustomer($rma),
            'rma'           => $this->rmaAdapter,
            'store'         => $this->storeManager->getStore($storeId),
            'email_subject' => $rule->getEmailSubject(),
            'email_body'    => $rule->getEmailBody(),
        ];
        $text = $this->processVariable($text, $variables, $storeId);
        $variables['message'] = $text;
        $senderName = $this->context->getScopeConfig()->getValue("trans_email/ident_{$this->getSender()}/name");
        $senderEmail = $this->context->getScopeConfig()->getValue("trans_email/ident_{$this->getSender()}/email");
        $this->send(
            $templateName,
            $senderName,
            $senderEmail,
            $recipientEmail,
            $recipientName,
            $variables,
            $storeId,
            $this->rmaManagement->getCode($rma),
            $attachments
        );
    }

    /**
     * Can parse template and return ready text.
     *
     * @param string $text  Text with variables like {{var customer.name}}.
     * @param array  $variables Array of variables.
     * @param int    $storeId
     *
     * @return string - ready text
     */
    protected function processVariable($text, $variables, $storeId)
    {
        $template = $this->emailTemplateFactory->create();
        $template->setDesignConfig([
            'area'  => 'frontend',
            'store' => $storeId,
        ]);
        $template->setTemplateText($text);
        $html = $template->getProcessedTemplate($variables);

        return $html;
    }

    /**
     * @param string                              $text
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return string
     */
    public function parseVariables($text, $rma)
    {
        //$this->storeManager->setCurrentStore($rma->getStoreId()); @todo check this for emails

        $this->rmaAdapter->setData($rma->getData());
        $variables = [
            'rma'      => $this->rmaAdapter,
            'store'    => $this->storeManager->getStore($rma->getStoreId()),
            'order'    => $this->rmaManagement->getOrder($rma),
            'status'   => $this->rmaManagement->getStatus($rma),
            'customer' => $this->rmaManagement->getCustomer($rma),
        ];
        $text = $this->processVariable($text, $variables, $rma->getStoreId());

        return $text;
    }
}
