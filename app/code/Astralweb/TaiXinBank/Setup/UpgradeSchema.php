<?php


namespace Astralweb\TaiXinBank\Setup;


use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Setup\SalesSetupFactory;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
    }
    /**
     * Upgrades DB for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Sales\Setup\SalesSetup $salesInstaller */
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '0.0.4') < 0) {
            //Add attributes to quote
            $entityAttributesCodes = [
                'last_4_digit_of_pan' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'first_6_digit_of_pan' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            ];
            foreach ($entityAttributesCodes as $code => $type) {
                $salesInstaller->addAttribute('order', $code, ['type' => $type, 'length'=> 255, 'visible' => false, 'nullable' => true,]);
            }
        }

        $setup->endSetup();
    }
}