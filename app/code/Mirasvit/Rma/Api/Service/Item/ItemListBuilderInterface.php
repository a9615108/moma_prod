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



namespace Mirasvit\Rma\Api\Service\Item;


interface ItemListBuilderInterface
{
    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getRmaItems($rma);

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getList(\Magento\Sales\Api\Data\OrderInterface $order);
}