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


namespace Mirasvit\Rma\Service\Attachment;

/**
 *  We put here only methods directly connected with Attachment properties
 */
class AttachmentManagement implements \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface
{
    public function __construct(
        \Mirasvit\Rma\Model\AttachmentFactory $attachmentFactory,
        \Magento\Framework\Url $urlManager,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Mirasvit\Rma\Api\Repository\AttachmentRepositoryInterface $attachmentRepository,
        \Mirasvit\Rma\Api\Config\AttachmentConfigInterface $config
    ) {
        $this->attachmentFactory     = $attachmentFactory;
        $this->urlManager            = $urlManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attachmentRepository  = $attachmentRepository;
        $this->config                = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachmentsByMessage(\Mirasvit\Rma\Api\Data\MessageInterface $message)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('item_id', $message->getId())
            ->addFilter('item_type', 'message')
            ->create();

        return $this->attachmentRepository->getList($searchCriteria)->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function saveAttachments(
        $itemType,
        $itemId,
        $field = 'attachment'
    ) {
        $allowedFileExtensions = $this->config->getFileAllowedExtensions();
        $fileSizeLimit = $this->config->getFileSizeLimit() * 1024 * 1024;

        if (!$this->hasAttachments($field)) {
            return false;
        }
        $i = 0;
        foreach ($_FILES[$field]['name'] as $name) {
            if ($name == '' || !empty($_FILES[$field][$name]['is_saved'])) {
                continue;
            }

            $type = $_FILES[$field]['type'][$i];
            $size = $_FILES[$field]['size'][$i];
            $ext = pathinfo($name, PATHINFO_EXTENSION);

            if (count($allowedFileExtensions) && !in_array($ext, $allowedFileExtensions)) {
                continue;
            }

            if ($fileSizeLimit && $size > $fileSizeLimit) {
                continue;
            }

            $this->_saveFile($itemType, $itemId, $name, $_FILES[$field]['tmp_name'][$i], $type, $size);
            ++$i;
            $_FILES[$field][$name]['is_saved'] = 1;
        }

        return true;
    }

    /**
     * @param string $itemType
     * @param int    $itemId
     * @param string $name
     * @param string $tmpName
     * @param string $fileType
     * @param string $size
     * @param bool   $isReplace
     * @return void
     */
    protected function _saveFile($itemType, $itemId, $name, $tmpName, $fileType, $size, $isReplace = false)
    {
        /** @var \Mirasvit\Rma\Model\Attachment $attachment */
        $attachment = false;
        if ($isReplace) {
            $attachment = $this->getAttachment($itemType, $itemId);
        }

        if (!$attachment) {
            $attachment = $this->attachmentRepository->create();
        }

        //@tofix - need to check for max upload size and alert error
        $body = @file_get_contents(addslashes($tmpName));

        $attachment
            ->setItemType($itemType)
            ->setItemId($itemId)
            ->setName($name)
            ->setSize($size)
            ->setBody($body)
            ->setType($fileType)
            ->save();

    }


    /**
     * {@inheritdoc}
     */
    public function saveAttachment($itemType, $itemId, $field = false)
    {
        if (!$this->hasAttachments($field)) {
            if (isset($_POST[$field]['delete']) && $_POST[$field]['delete']) {
                $attachment = $this->getAttachment($itemType, $itemId);
                $attachment->delete();

                return true;
            }

            return false;
        }
        $this->_saveFile(
            $itemType,
            $itemId,
            $_FILES[$field]['name'],
            $_FILES[$field]['tmp_name'],
            $_FILES[$field]['type'],
            $_FILES[$field]['size'],
            true
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachments($itemType, $itemId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('item_id', $itemId)
            ->addFilter('item_type', $itemType)
            ->create();

        return $this->attachmentRepository->getList($searchCriteria)->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttachments($field = 'attachment')
    {
        return isset($_FILES[$field]['name'][0]) && $_FILES[$field]['name'][0] != '';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachment($itemType, $itemId)
    {
        $items = $this->getAttachments($itemType, $itemId);

        return array_shift($items);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($attachment)
    {
        return $this->urlManager->getUrl('rma/attachment/download', ['uid' => $attachment->getUid()]);
    }

    /**
     * @param \Mirasvit\Helpdesk\Api\Service\EmailInterface $email
     * @param \Mirasvit\Rma\Api\Data\MessageInterface       $message
     * @return void
     */
    public function copyEmailAttachments($email, $message)
    {
        foreach ($email->getAttachments() as $emailAttachment) {
            $this->attachmentFactory->create()
                ->setEntityId($message->getId())
                ->setEntityType('COMMENT')
                ->setName($emailAttachment->getName())
                ->setSize($emailAttachment->getSize())
                ->setBody($emailAttachment->getBody())
                ->setType($emailAttachment->getType())
                ->save();
        }
    }
}