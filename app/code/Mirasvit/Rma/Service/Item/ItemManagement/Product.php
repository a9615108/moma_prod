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


namespace Mirasvit\Rma\Service\Item\ItemManagement;

/**
 *  We put here only methods directly connected with Item properties
 */
class Product implements \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface
{
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->productFactory    = $productFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $product = null;
        if ($item->getProductId()) { //items migrated from M1 do not have ID, only SKU
            $product = $this->productRepository->getById($item->getProductId());
        } elseif ($item->getSku()) {
            $product = $this->productRepository->get($item->getSku());
        }

        return $product;
    }

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $exchangeProduct;

    /**
     * {@inheritdoc}
     */
    public function getExchangeProduct(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        if (!$this->exchangeProduct) {
            $this->exchangeProduct = $this->productFactory->create()->load($item->getExchangeProductId());
        }

        return $this->exchangeProduct;
    }
}