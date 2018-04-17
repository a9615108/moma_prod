<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   1.1.22
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Model\Rule\Condition;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Mirasvit\Rma\Model\Rule\Condition\ProductFactory $ruleConditionProductFactory,
        \Mirasvit\Rma\Model\Rule\Condition\RmaFactory  $ruleConditionRmaFactory,
        array $data = []
    ) {
        $this->ruleConditionProductFactory = $ruleConditionProductFactory;
        $this->ruleConditionRmaFactory = $ruleConditionRmaFactory;

        parent::__construct($context, $data);

        $this->setType('Mirasvit\Rma\Model\Rule\Condition\Combine');
    }

    /**
     * {@inheritdoc}
     */
    public function getNewChildSelectOptions()
    {
        $ticketCondition = $this->ruleConditionRmaFactory->create();
        $ticketAttributes = $ticketCondition->loadAttributeOptions()->getAttributeOption();

        $attributes = [];
        foreach ($ticketAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Mirasvit\Rma\Model\Rule\Condition\Rma|'.$code,
                'label' => $label,
            ];
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'Mirasvit\Rma\Model\Rule\Condition\Combine',
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('RMA Attribute'), 'value' => $attributes],
            ]
        );

        return $conditions;
    }

    /**
     * @param array $productCollection
     *
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /* @var Product|Combine $condition */
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }
}
