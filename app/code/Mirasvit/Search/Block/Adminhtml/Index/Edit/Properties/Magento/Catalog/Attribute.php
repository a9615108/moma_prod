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
 * @package   mirasvit/module-search-sphinx
 * @version   1.0.61
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Block\Adminhtml\Index\Edit\Properties\Magento\Catalog;

use Magento\Framework\Data\Form\Element\Fieldset;
use Mirasvit\Search\Model\Index;

/**
 * @method Index getIndex()
 */
class Attribute extends Fieldset
{
    /**
     * {@inheritdoc}
     */
    public function getHtml()
    {
        /** @var \Mirasvit\Search\Model\Index\Magento\Catalog\Attribute\Index $instance */
        $instance = $this->getIndex()->getIndexInstance();

        $values = [];
        foreach ($instance->getCatalogAttributes() as $attribute) {
            $values[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getDefaultFrontendLabel().' ['.$attribute->getAttributeCode().']',
            ];
        }


        $this->addField('properties', 'select', [
            'name'     => 'properties[attribute]',
            'label'    => __('Attribute'),
            'values'   => $values,
            'value'    => $this->getIndex()->getProperty('attribute'),
            'required' => true,
            'note'     => __('Attribute should be Visible in Advanced Search and Filterable')
        ]);


        return parent::getHtml();
    }
}
