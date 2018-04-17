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


namespace Mirasvit\Rma\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if ($context->getVersion()
            && version_compare($context->getVersion(), '1.0.2') < 0
        ) {
            $select = $setup->getConnection()->select()
                ->from(
                    $setup->getTable('sales_order'),
                    [
                        'entity_id',
                        'status',
                        'created_at'
                    ]
                );
            $select = $setup->getConnection()->insertFromSelect(
                $select,
                $setup->getTable('mst_rma_order_status_history'),
                [
                    'order_id',
                    'status',
                    'created_at'
                ]
            );
            $setup->getConnection()->query($select);
        }
        $setup->endSetup();
    }
}
