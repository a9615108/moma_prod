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



namespace Mirasvit\Rma\Helper\User;

/**
 * Helper which creates different html code
 */
class Html extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->userCollectionFactory = $userCollectionFactory;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * @param string $emptyOption
     * @return array
     */
    public function toAdminUserOptionArray($emptyOption = false)
    {
        $arr = $this->userCollectionFactory->create()->toArray();
        $result = [];
        foreach ($arr['items'] as $value) {
            $result[] = ['value' => $value['user_id'], 'label' => $value['firstname'] . ' ' . $value['lastname']];
        }
        if ($emptyOption) {
            array_unshift($result, ['value' => 0, 'label' => __('-- Please Select --')]);
        }

        return $result;
    }

    /**
     * @param string $emptyOption
     * @return array
     */
    public function getAdminUserOptionArray($emptyOption = false)
    {
        $arr = $this->userCollectionFactory->create()->toArray();
        $result = [];
        foreach ($arr['items'] as $value) {
            $result[$value['user_id']] = $value['firstname'] . ' ' . $value['lastname'];
        }
        if ($emptyOption) {
            $result[0] = __('-- Please Select --');
        }

        return $result;
    }


}