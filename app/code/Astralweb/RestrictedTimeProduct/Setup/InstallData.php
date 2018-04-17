<?php


namespace Astralweb\RestrictedTimeProduct\Setup;


use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);


        /**
         * remove all old attributes to the eav/attribute
         */
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "enable_product_restrict");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "enable_product_from");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "enable_product_to");


        /**
         * Add attributes to the eav/attribute
         */

        // $eavSetup->addAttribute(
        //     \Magento\Catalog\Model\Product::ENTITY,
        //     'enable_product_restrict',
        //     [
        //         'type' => 'int',
        //         'backend' => '',
        //         'frontend' => '',
        //         'label' => 'Enable Restricted Feature',
        //         'input' => 'boolean',
        //         'class' => '',
        //         'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
        //         'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
        //         'visible' => true,
        //         'required' => false,
        //         'user_defined' => false,
        //         'default' => 0,
        //         'searchable' => false,
        //         'filterable' => false,
        //         'comparable' => false,
        //         'visible_on_front' => false,
        //         'used_in_product_listing' => true,
        //         'unique' => false,
        //         'apply_to' => '',
        //         'group' => 'Time Restricted Product',
        //         'attribute_set_name' => array('Default')
        //     ]
        // );


        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'enable_product_from',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                'backend' => '',
                'frontend' => '',
                'label' => 'Enable Product From',
                'class' => '',
              'source' => '',
              'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
              'visible' => true,
              'required' => false,
              'user_defined' => true,
              'default' => '',
              'searchable' => false,
              'filterable' => false,
              'comparable' => false,
              'visible_on_front' => false,
              'used_in_product_listing' => true,
              'unique' => false,
              'group' => 'General',
              'input_renderer' => 'Astralweb\RestrictedTimeProduct\Block\Adminhtml\Product\DateTime\DateTimePicker',
              'apply_to' => ''
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'enable_product_to',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
                'frontend' => '',
                'label' => 'Enable Product To',
                'class' => '',
              'source' => '',
              'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
              'visible' => true,
              'required' => false,
              'user_defined' => true,
              'default' => '',
              'searchable' => false,
              'filterable' => false,
              'comparable' => false,
              'visible_on_front' => false,
              'used_in_product_listing' => true,
              'unique' => false,
              'group' => 'General',
              'input_renderer' => 'Astralweb\RestrictedTimeProduct\Block\Adminhtml\Product\DateTime\DateTimePicker',
              'apply_to' => ''
            ]
        );
    }
}