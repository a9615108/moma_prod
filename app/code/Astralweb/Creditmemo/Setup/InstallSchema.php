<?php
namespace Astralweb\Creditmemo\Setup;
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

        //add field to address customer
        $creditmemoGrid = $installer->getTable('sales_creditmemo');

        $columns = [
            'product_custom' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'product custom',
            ],
            'telephone_billing' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'telephone billing',
            ],
            'telephone_shipping' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'telephone shipping',
            ]

        ];
        foreach ($columns as $name => $definition) {
            $installer->getConnection()->addColumn($creditmemoGrid, $name, $definition);
        }

        $installer->endSetup();
    }
}
