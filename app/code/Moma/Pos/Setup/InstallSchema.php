<?php
namespace Moma\Pos\Setup;
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

        if ($installer->tableExists('moma_pos')) {
            $installer->getConnection()->dropTable('moma_pos');
        }
        //START table setup
        $table = $installer->getConnection()->newTable(
        $installer->getTable('moma_pos')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '10',
            [ 'nullable' => false, 'unsigned' => true, 'primary' => true,'identity' =>true ],
            'Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Order Id'
        )
            ->addColumn(
            'sync',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['nullable' => false, 'default'=> 0],
            'Sync'
        )->addColumn(
            'refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['nullable' => false, 'default'=> 0],
            'Refunded'
        );
        $installer->getConnection()->createTable($table);
        //END   table setup
        //add another field in sales_order table

        $installer->endSetup();
    }
}
