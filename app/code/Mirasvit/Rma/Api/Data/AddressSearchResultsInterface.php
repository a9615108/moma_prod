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


namespace Mirasvit\Rma\Api\Data;

/**
 * Interface for return address search results.
 */
interface AddressSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get return address list.
     *
     * @return \Mirasvit\Rma\Api\Data\AddressInterface[]
     */
    public function getItems();

    /**
     * Set return address list.
     *
     * @param array $items Array of \Mirasvit\Rma\Api\Data\AddressInterface[]
     * @return $this
     */
    public function setItems(array $items);
}
