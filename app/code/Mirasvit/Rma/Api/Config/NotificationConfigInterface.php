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



namespace Mirasvit\Rma\Api\Config;


interface NotificationConfigInterface
{
    /**
     * @param null|int $store
     * @return string
     */
    public function getSenderEmail($store = null);

    /**
     * @param null|int $store
     * @return string
     */
    public function getCustomerEmailTemplate($store = null);

    /**
     * @param null|int $store
     * @return string
     */
    public function getAdminEmailTemplate($store = null);

    /**
     * @param null|int $store
     * @return string
     */
    public function getRuleTemplate($store = null);

    /**
     * Returns setting "Send blind carbon copy (BCC) of all emails to" value.
     *
     * @param \Magento\Store\Model\Store $store
     * @return string
     */
    public function getSendEmailBcc($store = null);
}