<?php
namespace Astralweb\Shippingcvs\Setup;
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

        if ($installer->tableExists('astralweb_shippingcvs')) {
            $installer->getConnection()->dropTable('astralweb_shippingcvs');
        }
        //START table setup
        $table = $installer->getConnection()->newTable(
            $installer->getTable('astralweb_shippingcvs')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            '10',
            [ 'nullable' => false, 'unsigned' => true, 'primary' => true,'identity' =>true ],
            'Id'
        )->addColumn(
            'increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false],
            'Increment Id'
        )->addColumn(
            'cvsspot',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            ['nullable' => false],
            'CVS SPOT'
        )->addColumn(
                'cvsnum',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [],
                'CVS Num'
        )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [],
                'Status'
        )->addColumn(
            'bc1',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            200,
            [],
            'Barcode 1'
        )->addColumn(
            'bc2',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            200,
            [],
            'Barcode 2'
        );
        $installer->getConnection()->createTable($table);

        //add field to address customer 
        $customer_address = $installer->getTable('customer_address_entity');
        
        $columns = [
            'cvsspot' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '20',
                'nullable' => true,
                'comment' => 'CVS SPOT',
            ],
            'cvsnum' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '20',
                'nullable' => true,
                'comment' => 'CVS NUM',
            ],
        ];
        foreach ($columns as $name => $definition) {
            $installer->getConnection()->addColumn($customer_address, $name, $definition);
        }

        $installer->endSetup();
    }
}
