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



namespace Mirasvit\Rma\Api\Service\Attachment;


interface AttachmentManagementInterface
{
    /**
     * @param \Mirasvit\Rma\Api\Data\MessageInterface $message
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface[]
     */
    public function getAttachmentsByMessage(\Mirasvit\Rma\Api\Data\MessageInterface $message);

    /**
     * @param int        $itemType
     * @param int        $itemId
     * @param string     $field
     * @return bool
     */
    public function saveAttachments(
        $itemType,
        $itemId,
        $field = 'attachment'
    );

    /**
     * @param string $field
     * @return bool
     */
    public function hasAttachments($field = 'attachment');

    /**
     * @param string $itemType
     * @param int    $itemId
     * @return \Mirasvit\Rma\Model\Attachment
     */
    public function getAttachment($itemType, $itemId);

    /**
     * @param string $itemType
     * @param int    $itemId
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface[] $items
     */
    public function getAttachments($itemType, $itemId);

    /**
     * @param string     $itemType
     * @param string     $itemId
     * @param bool|false $field
     * @return bool
     */
    public function saveAttachment($itemType, $itemId, $field = false);

    /**
     * @param \Mirasvit\Rma\Api\Data\AttachmentInterface $attachment
     * @return string
     */
    public function getUrl($attachment);
}