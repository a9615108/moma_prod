<?php


namespace Astralweb\Lookbook\Setup;


use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use \Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Eav\Setup\EavSetupFactory;


class UpgradeData implements UpgradeDataInterface
{
    /** @var PageFactory */
    protected $_pageFactory;

    /** @var WriterInterface  */
    protected $_configWriter;

    /**
     * UpgradeData constructor.
     * @param PageFactory $pageFactory
     * @param WriterInterface $configWriter
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Cms\Model\PageFactory $pageFactory,
        WriterInterface $configWriter
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->_pageFactory = $pageFactory;
        $this->_configWriter = $configWriter;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.1.4', '<')) {

          $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
          $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'lookbook1' );
              $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'lookbook1',
                [
                  'group' => 'General',
                  'type' => 'varchar',
                  'backend' => '',
                  'frontend' => '',
                  'label' => 'Lookbook Image 1',
                  'input' => 'media_image',
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
                  'apply_to' => ''
                ]
              );
              $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'lookbook2' );
              $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'lookbook2',
                [
                  'group' => 'General',
                  'type' => 'varchar',
                  'backend' => '',
                  'frontend' => '',
                  'label' => 'Lookbook Image 2',
                  'input' => 'media_image',
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
                  'apply_to' => ''
                ]
              );
              $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'lookbook3' );
              $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'lookbook3',
                [
                  'group' => 'General',
                  'type' => 'varchar',
                  'backend' => '',
                  'frontend' => '',
                  'label' => 'Lookbook Image 3',
                  'input' => 'media_image',
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
                  'apply_to' => ''
                ]
              );
              $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'lookbook4' );
              $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'lookbook4',
                [
                  'group' => 'General',
                  'type' => 'varchar',
                  'backend' => '',
                  'frontend' => '',
                  'label' => 'Lookbook Image 4',
                  'input' => 'media_image',
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
                  'apply_to' => ''
                ]
              );
        }

        $setup->endSetup();
    }

}
