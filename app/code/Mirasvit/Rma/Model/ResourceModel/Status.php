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



namespace Mirasvit\Rma\Model\ResourceModel;

class Status extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('mst_rma_status', 'status_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rma\Model\Status $object */
        if (!$object->getIsMassDelete()) {
        }

        return parent::_afterLoad($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rma\Model\Status $object */
        if (!$object->getId()) {
            $object->setCreatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

        }
        $object->setUpdatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

        $adminMessage    = [];
        $historyMessage  = [];
        $customerMessage = [];
        $saveToStore     = (int)$object->getStore();
        if ($object->getId()) {
            $adminMessage    = $object->decodeMessage($object->getOrigData('admin_message'));
            $historyMessage  = $object->decodeMessage($object->getOrigData('history_message'));
            $customerMessage = $object->decodeMessage($object->getOrigData('customer_message'));
        } else {
            if ($saveToStore) { //set default messages
                $adminMessage[0]    = $object->getData('admin_message');
                $historyMessage[0]  = $object->getData('history_message');
                $customerMessage[0] = $object->getData('customer_message');
            }
        }
        $adminMessage[$saveToStore]    = $object->getData('admin_message');
        $historyMessage[$saveToStore]  = $object->getData('history_message');
        $customerMessage[$saveToStore] = $object->getData('customer_message');

        $object->setAdminMessage($adminMessage);
        $object->setHistoryMessage($historyMessage);
        $object->setCustomerMessage($customerMessage);

        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rma\Model\Status $object */
        if (!$object->getIsMassStatus()) {
        }

        return parent::_afterSave($object);
    }

    /************************/
}
