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
 * @method bool getIsShowShipping()
 * @method $this setIsShowShipping(bool $param)
 */
class OrderStatusHistory extends AbstractModel
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory');
    }
}
