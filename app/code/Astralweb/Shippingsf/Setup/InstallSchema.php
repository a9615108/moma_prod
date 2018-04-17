<?php
namespace Astralweb\Shippingsf\Setup;
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

        if ($installer->tableExists('astralweb_shippingsf')) {
            $installer->getConnection()->dropTable('astralweb_shippingsf');
        }
        //START table setup
        $table = $installer->getConnection()->newTable(
            $installer->getTable('astralweb_shippingsf')
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
        )->addColumn(
            'destcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            ['nullable' => false],
            'Dest Code'
        )->addColumn(
                'mailno',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                40,
                ['nullable' => false],
                'Mail No'
            )->addColumn(
                'return_tracking',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Return Tracking'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Status'
            )->addColumn(
                'route_tracking',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                500,
                [],
                'Status'
            );
        $installer->getConnection()->createTable($table);


        $installer->endSetup();
    }
}
