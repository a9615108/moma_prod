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



namespace Mirasvit\Rma\Block\Adminhtml\Rule\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rule_tabs');
        $this->setDestElementId('edit_form');
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $this->addTab('general_section', [
            'label'   => __('General Information'),
            'title'   => __('General Information'),
            'content' => $this->getLayout()->createBlock(
                '\Mirasvit\Rma\Block\Adminhtml\Rule\Edit\Tab\General'
            )->toHtml(),
        ]);

        $this->addTab('condition_section', [
            'label'   => __('Conditions'),
            'title'   => __('Conditions'),
            'content' => $this->getLayout()->createBlock(
                '\Mirasvit\Rma\Block\Adminhtml\Rule\Edit\Tab\Condition'
            )->toHtml(),
        ]);

        $this->addTab('action_section', [
            'label'   => __('Actions'),
            'title'   => __('Actions'),
            'content' => $this->getLayout()->createBlock(
                '\Mirasvit\Rma\Block\Adminhtml\Rule\Edit\Tab\Action'
            )->toHtml(),
        ]);

        $this->addTab('notification_section', [
            'label'   => __('Notifications'),
            'title'   => __('Notifications'),
            'content' => $this->getLayout()->createBlock(
                '\Mirasvit\Rma\Block\Adminhtml\Rule\Edit\Tab\Notification'
            )->toHtml(),
        ]);

        return parent::_beforeToHtml();
    }
}
