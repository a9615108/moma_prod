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


//@codingStandardsIgnoreFile
namespace Mirasvit\Rma\Block\Adminhtml\Rule\Edit\Tab;

class Condition extends \Magento\Backend\Block\Widget\Form
{
    public function __construct(
        \Mirasvit\Rma\Model\Config\Source\Rule\Event $configSourceRuleEvent,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $widgetFormRendererFieldset,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Url $backendUrlManager,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->configSourceRuleEvent = $configSourceRuleEvent;
        $this->widgetFormRendererFieldset = $widgetFormRendererFieldset;
        $this->conditions = $conditions;
        $this->formFactory = $formFactory;
        $this->backendUrlManager = $backendUrlManager;
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create();
        $this->setForm($form);
        /** @var \Mirasvit\Rma\Model\Rule $rule */
        $rule = $this->registry->registry('current_rule');

        $fieldset = $form->addFieldset('event_fieldset', ['legend' => __('Event')]);
        if ($rule->getId()) {
            $fieldset->addField('rule_id', 'hidden', [
                'name'  => 'rule_id',
                'value' => $rule->getId(),
            ]);
        }
        $fieldset->addField('event', 'select', [
            'label'    => __('Event'),
            'required' => true,
            'name'     => 'event',
            'value'    => $rule->getEvent(),
            'values'   => $this->configSourceRuleEvent->toOptionArray(),
        ]);
        $fieldset = $form->addFieldset('condition_fieldset', ['legend' => __('Conditions')]);
        $renderer = $this->widgetFormRendererFieldset
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($this->backendUrlManager->getUrl(
                '*/rule/newConditionHtml/form/rule_conditions_fieldset',
                ['rule_type' => $rule->getType()]
            ));
        $fieldset->setRenderer($renderer);

        $fieldset->addField('condition', 'text', [
            'name'     => 'condition',
            'label'    => __('Filters'),
            'title'    => __('Filters'),
            'required' => true,
        ])->setRule($rule)
            ->setRenderer($this->conditions);

        return parent::_prepareForm();
    }

    /************************/
}
