<?php
namespace Moma\Pos\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $setup->getConnection()->addColumn(
            $setup->getTable('astralweb_invoicetype'),
            'order_invoice',                                // 要新增的欄位
            array(
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'length' => '12',
                'comment' => 'invoice number'
            )
        );

        $setup->endSetup();
    }
}