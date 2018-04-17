<?php
namespace Customer\Account\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    private $eavSetupFactory;
    private $eavConfig;

    public function __construct(
        EavSetupFactory         $eavSetupFactory, 
        Config                  $eavConfig
    ) {
		$this->eavSetupFactory  = $eavSetupFactory;
		$this->eavConfig        = $eavConfig;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

// VIP 卡號
        $att_name = 'vip_num';
		$eavSetup->addAttribute(
			Customer::ENTITY,
			$att_name,
            [
                'user_defined' => true,
                'type' => 'varchar',
                'label' => 'VIP Number',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'position' =>150,
                'system' => 0,
                'length' => 12,
                'sort_order' => '150',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true,
                "note"       => "",
                'group' => 'Account Information',
            ]
		);
		$sampleAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, $att_name);
		$sampleAttribute
            ->setData('used_in_forms', [
                                        'adminhtml_customer',
                                        'checkout_register',
                                        'customer_account_create',
                                        'customer_account_edit',
                                        'adminhtml_checkout',
            ])
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 150);

		$sampleAttribute->save();

// 開卡位置註記
        $att_name = 'vip_site';
		$eavSetup->addAttribute(
			Customer::ENTITY,
			$att_name,
            [
                'user_defined' => true,
                'type' => 'varchar',
                'label' => 'VIP Site',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'position' =>160,
                'system' => 0,
                'length' => 1,
                'sort_order' => '160',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true,
                "note"       => "",
                'group' => 'Account Information',
            ]
		);
		$sampleAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, $att_name);
		$sampleAttribute
            ->setData('used_in_forms', [
                                        'adminhtml_customer',
                                        'checkout_register',
                                        'customer_account_create',
                                        'customer_account_edit',
                                        'adminhtml_checkout',
            ])
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 160);

		$sampleAttribute->save();

// Vip有效日期
        $att_name = 'vip_date';
		$eavSetup->addAttribute(
			Customer::ENTITY,
			$att_name,
            [
                'user_defined' => true,
                'type' => 'datetime',
                'label' => 'VIP Date',
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'position' =>170,
                'system' => 0,
                'sort_order' => '170',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true,
                "note"       => "",
                'group' => 'Account Information',
                'input_renderer' => 'Velanapps\Test\Block\Adminhtml\Form\Element\Datetime',
                'class' => 'validate-date',
                'backend' => 'Magento\Catalog\Model\Attribute\Backend\Startdate',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,

                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'filterable_in_search' => true,
                'visible_in_advanced_search' => true,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
		);
		$sampleAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, $att_name);
		$sampleAttribute
            ->setData('used_in_forms', [
                                        'adminhtml_customer',
                                        'checkout_register',
                                        'customer_account_create',
                                        'customer_account_edit',
                                        'adminhtml_checkout',
            ])
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 170);

		$sampleAttribute->save();

// Vip 驗證用電話
        $att_name = 'vip_phone';
		$eavSetup->addAttribute(
			Customer::ENTITY,
			$att_name,
            [
                'user_defined' => true,
                'type' => 'varchar',
                'label' => 'VIP Phone',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'position' =>180,
                'system' => 0,
                'length' => 15,
                'sort_order' => '180',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true,
                "note"       => "",
                'group' => 'Account Information',
            ]
		);
		$sampleAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, $att_name);
		$sampleAttribute
            ->setData('used_in_forms', [
                                        'adminhtml_customer',
                                        'checkout_register',
                                        'customer_account_create',
                                        'customer_account_edit',
                                        'adminhtml_checkout',
            ])
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 180);

		$sampleAttribute->save();








    }
}