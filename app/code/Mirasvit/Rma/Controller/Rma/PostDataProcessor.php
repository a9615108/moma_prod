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


namespace Mirasvit\Rma\Controller\Rma;

class PostDataProcessor
{
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory
    ) {
        $this->productRepository = $productRepository;
        $this->orderFactory      = $orderFactory;
        $this->dateFilter        = $dateFilter;
        $this->messageManager    = $messageManager;
        $this->validatorFactory  = $validatorFactory;
    }

    /**
     * Filtering posted data. Return only RMA data.
     *
     * @param array $data
     * @return array
     */
    public function filterRmaData($data)
    {
        $newData = $data;
        unset($newData['items']);

        return $newData;
    }

    /**
     * Filtering posted data. Return only RMA items.
     *
     * @param array $data
     * @return array
     */
    public function filterRmaItems($data)
    {
        $order = $this->orderFactory->create()->load((int) $data['order_id']);
        $itemCollection = $order->getItemsCollection();

        $items = $data['items'];
        foreach ($items as $k => $item) {
            $item = $this->filterConditions($item);
            $item['order_id'] = $data['order_id'];
            $item['order_item_id'] = $k;

            $orderItem = $itemCollection->getItemById($k);
            if ($orderItem) {
                $productId = $orderItem->getProductId();
                if (!$productId) { //items migrated from M1 do not have ID, only SKU
                    $product   = $this->productRepository->get($orderItem->getSku());
                    $productId = $product->getId();
                }
                $item['product_id'] = $productId;
            }
            $items[$k] = $item;
        }

        return $items;
    }

    /**
     * @param array $item
     * @return array
     */
    private function filterConditions($item)
    {
        if (isset($item['reason_id']) && !(int)$item['reason_id']) {
            unset($item['reason_id']);
        }
        if (isset($item['resolution_id']) && !(int)$item['resolution_id']) {
            unset($item['resolution_id']);
        }
        if (isset($item['condition_id']) && !(int)$item['condition_id']) {
            unset($item['condition_id']);
        }

        return $item;
    }

    /**
     * Validate post data
     *
     * @param array $data
     * @return bool Return FALSE if someone item is invalid
     */
    public function validate($data)
    {
        return $this->validateRequireEntry($data) && $this->validateItemsQty($data);
    }

    /**
     * Check if required fields is not empty
     *
     * @param array $data
     * @return bool
     */
    public function validateRequireEntry(array $data)
    {
        $requiredFields = [
            'items' => __('Items'),
        ];
        $errorNo = true;
        foreach ($data as $field => $value) {
            if (in_array($field, array_keys($requiredFields)) && $value == '') {
                $errorNo = false;
                $this->messageManager->addError(
                    __('To apply changes you should fill in required "%1" field', $requiredFields[$field])
                );
            }
        }
        return $errorNo;
    }

    /**
     * Check if any item has qty > 0
     *
     * @param array $data
     * @return bool
     */
    public function validateItemsQty(array $data)
    {
        $isEmpty = true;
        foreach ($data['items'] as $item) {
            if ((int)$item['qty_requested'] > 0) {
                $isEmpty = false;
                break;
            }
        }
        if ($isEmpty) {
            $this->messageManager->addError(
                __("Please, add order items to the RMA (set 'Qty to Return')")
            );
            return false;
        }
        return true;
    }
}
