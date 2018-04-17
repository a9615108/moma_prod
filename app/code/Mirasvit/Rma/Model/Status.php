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



namespace Mirasvit\Rma\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * @method ResourceModel\Status\Collection getCollection()
 * @method $this load(int $id)
 * @method bool getIsMassDelete()
 * @method $this setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method $this setIsMassStatus(bool $flag)
 * @method ResourceModel\Status getResource()
 */
class Status extends AbstractModel implements \Mirasvit\Rma\Api\Data\StatusInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\ResourceModel\Status');
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::KEY_SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsShowShipping()
    {
        return $this->getData(self::KEY_IS_SHOW_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsShowShipping($isShowShipping)
    {
        return $this->setData(self::KEY_IS_SHOW_SHIPPING, $isShowShipping);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerMessage()
    {
        return $this->decodeMessage($this->getData(self::KEY_CUSTOMER_MESSAGE));
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerMessage($customerMessage)
    {
        return $this->setData(self::KEY_CUSTOMER_MESSAGE, json_encode($customerMessage));
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminMessage()
    {
        return $this->decodeMessage($this->getData(self::KEY_ADMIN_MESSAGE));
    }

    /**
     * {@inheritdoc}
     */
    public function setAdminMessage($adminMessage)
    {
        return $this->setData(self::KEY_ADMIN_MESSAGE, json_encode($adminMessage));
    }

    /**
     * {@inheritdoc}
     */
    public function getHistoryMessage()
    {
        return $this->decodeMessage($this->getData(self::KEY_HISTORY_MESSAGE));
    }

    /**
     * {@inheritdoc}
     */
    public function setHistoryMessage($historyMessage)
    {
        return $this->setData(self::KEY_HISTORY_MESSAGE, json_encode($historyMessage));
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::KEY_IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::KEY_IS_ACTIVE, $isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Compatibility to old versions
     *
     * @param string $message
     * @return string
     */
    public function decodeMessage($message)
    {
        if ($decoded = json_decode($message, true)) {
            $message = $decoded;
        }
        if (is_array($message)) {
            return $message;
        } else {
            return [$message];
        }
    }
}
