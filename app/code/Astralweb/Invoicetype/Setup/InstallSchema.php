<?php
namespace Astralweb\Invoicetype\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        //START: install stuff
        //END:   install stuff

        if ($installer->tableExists('astralweb_invoicetype')) {
            $installer->getConnection()->dropTable('astralweb_invoicetype');
        }
        //START table setup
        $table = $installer->getConnection()->newTable(
        $installer->getTable('astralweb_invoicetype')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '10',
            [ 'nullable' => false, 'unsigned' => true, 'primary' => true,'identity' =>true ],
            'Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false],
            'Order Id'
        )
            ->addColumn(
            'invoice_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false],
            'Invoice Type'
        )->addColumn(
            'tax_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Tax Id'
        )->addColumn(
            'purchaser_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Purchaser Name'
        );
        $installer->getConnection()->createTable($table);
        //END   table setup
        //add another field in sales_order table

//        $installer->getConnection()->addColumn(
//            $installer->getTable('sales_order'),
//            'invoicetype_id',
//            'int(11) default null'
//        );
//
//        //add another field in quotes table
//
//        $installer->getConnection()->addColumn(
//            $installer->getTable('quote'),
//            'invoicetype_id',
//            'int(11) default null'
//        );

        $installer->endSetup();
    }
}
