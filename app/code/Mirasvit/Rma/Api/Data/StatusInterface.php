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

use Mirasvit\Rma\Api;

interface StatusInterface extends DataInterface
{
    const APPROVED     = 'approved';
    const PACKAGE_SENT = 'package_sent';
    const REJECTED     = 'rejected';
    const CLOSED       = 'closed';

    const KEY_NAME             = 'name';
    const KEY_SORT_ORDER       = 'sort_order';
    const KEY_IS_SHOW_SHIPPING = 'is_show_shipping';
    const KEY_CUSTOMER_MESSAGE = 'customer_message';
    const KEY_ADMIN_MESSAGE    = 'admin_message';
    const KEY_HISTORY_MESSAGE  = 'history_message';
    const KEY_IS_ACTIVE        = 'is_active';
    const KEY_CODE             = 'code';

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * @return bool|null
     */
    public function getIsShowShipping();

    /**
     * @param bool $isShowShipping
     * @return $this
     */
    public function setIsShowShipping($isShowShipping);

    /**
     * @return string
     */
    public function getCustomerMessage();

    /**
     * @param string $customerMessage
     * @return $this
     */
    public function setCustomerMessage($customerMessage);

    /**
     * @return string
     */
    public function getAdminMessage();

    /**
     * @param string $adminMessage
     * @return $this
     */
    public function setAdminMessage($adminMessage);

    /**
     * @return string
     */
    public function getHistoryMessage();

    /**
     * @param string $historyMessage
     * @return $this
     */
    public function setHistoryMessage($historyMessage);

    /**
     * @return bool|null
     */
    public function getIsActive();

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code);
}