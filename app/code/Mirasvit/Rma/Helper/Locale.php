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

class Locale extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->context = $context;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param string $object
     * @param string $field
     * @param string $value
     * @return void
     */
    public function setLocaleValue($object, $field, $value)
    {
        $storeId = (int) $object->getStoreId();
        $serializedValue = $object->getData($field);
        $arr = $this->unserialize($serializedValue);

        if ($storeId === 0) {
            $arr[0] = $value;
        } else {
            $arr[$storeId] = $value;
            if (!isset($arr[0])) {
                $arr[0] = $value;
            }
        }
        $object->setData($field, serialize($arr));
                       // pr($object->getData());die;
    }

    /**
     * @param string $object
     * @param string $field
     * @return null
     */
    public function getLocaleValue($object, $field)
    {
        $storeId = ($object->getStoreId()) ? (int) $object->getStoreId() : $this->storeManager->getStore()->getId();
        $serializedValue = $object->getData($field);
        $arr = $this->unserialize($serializedValue);
        // pr($arr);die;
        $defaultValue = null;
        if (isset($arr[0])) {
            $defaultValue = $arr[0];
        }

        if (isset($arr[$storeId])) {
            $localizedValue = $arr[$storeId];
        } else {
            $localizedValue = $defaultValue;
        }

        return $localizedValue;
    }

    /**
     * @param string $string
     * @return array
     */
    public function unserialize($string)
    {
        if (strpos($string, 'a:') !== 0) {
            return [0 => $string];
        }
        if (!$string) {
            return [];
        }
        try {
            return @unserialize($string);
        } catch (\Exception $e) {
            return [0 => $string];
        }
    }
}
