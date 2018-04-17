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

interface ResolutionInterface extends ReturnInterface
{
    const REFUND = 'refund';
    const EXCHANGE = 'exchange';
    const CREDIT = 'credit';

    const KEY_ID = 'reason_id';
    const KEY_NAME = 'name';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_IS_ACTIVE = 'is_active';

    const RESERVED_IDS = [1, 2, 3];
}