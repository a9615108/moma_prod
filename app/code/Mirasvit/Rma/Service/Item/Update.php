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


namespace Mirasvit\Rma\Service\Item;

/**
 * Update RMA items
 */
class Update
{
    /**
     * @param \Mirasvit\Rma\Model\ItemFactory $itemFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\ItemFactory $itemFactory
    ) {
        $this->itemFactory = $itemFactory;
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma $rma
     * @param array $items
     * @return void
     */
    public function updateItems($rma, $items)
    {
        foreach ($items as $item) {
            $rmaItem = $this->itemFactory->create();
            if (isset($item['item_id']) && $item['item_id']) {
                $rmaItem->load((int) $item['item_id']);
            }
            unset($item['item_id']);
            $rmaItem->addData($item)
                ->setRmaId($rma->getId());
            $rmaItem->save();
        }
    }
}