<?php
namespace Astralweb\ShippingStorePickUp\Setup;
use Magento\Framework\DB\Ddl\Table;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'shop'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('astralweb_shippingstorepickup_shop')
        )->addColumn(
            'astralweb_shippingstorepickup_shop_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Agreement Id'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Name'
        )->addColumn(
            'postcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            5,
            [],
            'County'
        )->addColumn(
            'county',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            [],
            'County'
        )->addColumn(
            'region',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            [],
            'Region'
        )->addColumn(
            'street',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'Street'
        )->addColumn(
            'code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            [],
            'Code'
        )->addColumn(
            'telephone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            15,
            [],
            'telephone'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            1,
            ['nullable' => false, 'default' => '1'],
            'Is Active'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable' => false,
                'default' => Table::TIMESTAMP_INIT
            ],
            'Category Creation Time'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable' => false,
                'default' => Table::TIMESTAMP_INIT_UPDATE
            ],
            'Category updated Time'

        )->setComment(
            'shop'
        );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}