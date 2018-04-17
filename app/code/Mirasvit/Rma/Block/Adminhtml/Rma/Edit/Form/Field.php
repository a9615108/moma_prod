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


namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form;


class Field extends \Magento\Backend\Block\Template
{
    /**
     * @return bool|\Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFieldForm()
    {
        $form = $this->formFactory->create();
        $rma = $this->registry->registry('current_rma');
        $fieldset = $form->addFieldset('field_fieldset', ['legend' => __('Additional Information')]);
        $collection = $this->rmaField->getStaffCollection();
        if (!$collection->count()) {
            return false;
        }
        foreach ($collection as $field) {
            $fieldset->addField(
                $field->getCode(),
                $field->getType(),
                $this->rmaField->getInputParams($field, true, $rma)
            );
        }

        return $form;
    }
}