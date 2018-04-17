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


namespace Mirasvit\Rma\Plugin\Attachment;
use Mirasvit\Rma\Api\Data\AttachmentInterface;

class SetDefaultsBeforeCreatePlugin
{
    public function __construct(
        \Mirasvit\Core\Api\TextHelperInterface $mstCoreString
    ) {
        $this->mstCoreString = $mstCoreString;
    }

    /**
     * @param AttachmentInterface $attachment
     * @return void
     */
    public function beforeSave(AttachmentInterface $attachment)
    {
        if ($attachment->getId()) {
            return;
        }
        $uid = md5(
            (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT).
            $this->mstCoreString->generateRandHeavy(100)
        );
        $attachment->setUid($uid);
    }
}