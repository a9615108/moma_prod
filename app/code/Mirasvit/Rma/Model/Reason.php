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

use Magento\Framework\DataObject\IdentityInterface;

/**
 * @method \Mirasvit\Rma\Model\ResourceModel\Reason\Collection|\Mirasvit\Rma\Model\Reason[] getCollection()
 * @method \Mirasvit\Rma\Model\Reason load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rma\Model\Reason setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rma\Model\Reason setIsMassStatus(bool $flag)
 * @method \Mirasvit\Rma\Model\ResourceModel\Reason getResource()
 */
class Reason extends \Magento\Framework\Model\AbstractModel implements IdentityInterface,
     \Mirasvit\Rma\Api\Data\ReasonInterface
{
    const CACHE_TAG = 'rma_reason';

    /**
     * {@inheritdoc}
     */
    protected $_cacheTag = 'rma_reason';

    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = 'rma_reason';

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\ResourceModel\Reason');
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Locale $localeData,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->localeData = $localeData;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->localeData->getLocaleValue($this, self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->localeData->setLocaleValue($this, self::KEY_NAME, $name);
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
}
