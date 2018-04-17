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
 * @package   mirasvit/module-report
 * @version   1.2.3
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Report\Ui\Component;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Mirasvit\Report\Ui\Context;
use Magento\Framework\Api\Filter;

class Toolbar extends AbstractComponent
{
    /**
     * @var Context
     */
    protected $uiContext;

    /**
     * @param ContextInterface $context
     * @param Context          $uiContext
     * @param array            $components
     * @param array            $data
     */
    public function __construct(
        ContextInterface $context,
        Context $uiContext,
        array $components = [],
        array $data = []
    ) {
        $this->uiContext = $uiContext;

        parent::__construct($context, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        return 'toolbar';
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepare()
    {
        $this->prepareOptions();

        $filters = $this->context->getRequestParam('filters');

        $filters = is_array($filters) ? $filters : [];

        foreach ($filters as $column => $value) {
            if (isset($value['from']) && isset($value['to'])) {

                $from = date('Y-m-d H:i:s', strtotime($value['from']));
                $to = date('Y-m-d H:i:s', strtotime($value['to']) + 23 * 60 * 60 + 59 * 60 + 59);

                $filter = new Filter();
                $filter->setField($column)
                    ->setConditionType('gteq')
                    ->setValue($from);
                $this->getDataProvider()->addRegularFilter($filter);

                if (isset($value['compareFrom']) && !empty($value['compareFrom'])) {
                    $from = date('Y-m-d H:i:s', strtotime($value['compareFrom']));
                    $cFilter = new Filter();
                    $cFilter->setField($column)
                        ->setConditionType('gteq')
                        ->setValue($from);
                    $this->getDataProvider()->addComparisonFilter($cFilter);
                }

                $filter = new Filter();
                $filter->setField($column)
                    ->setConditionType('lteq')
                    ->setValue($to);
                $this->getDataProvider()->addRegularFilter($filter);

                if (isset($value['compareTo']) && !empty($value['compareTo'])) {
                    $to = date('Y-m-d H:i:s', strtotime($value['compareTo']) + 23 * 60 * 60 + 59 * 60 + 59);
                    $cFilter = new Filter();
                    $cFilter->setField($column)
                        ->setConditionType('lteq')
                        ->setValue($to);
                    $this->getDataProvider()->addComparisonFilter($cFilter);
                }
            }
            
            if (strpos($column, 'store_id') !== false && $value) {
                $filter = new Filter();
                $filter->setField($column)
                    ->setConditionType('eq')
                    ->setValue($value);

                $this->getDataProvider()->addFilter($filter);
            }
        }

        $dimension = $this->uiContext->getActiveDimension();

        $this->getDataProvider()
            ->setDimension($dimension);

        parent::prepare();
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOptions()
    {
        $config = $this->getData('config');

        $config['fastFilters'] = [];
        foreach ($this->uiContext->getReport()->getFastFilters() as $column) {
            $jsConfig = $column->getJsConfig();
            if ($value = $this->getFilterValue($column->getName())) {
                $jsConfig['value'] = $value;
            }
            $config['fastFilters'][] = $jsConfig;
        }

        $config['dimensions'] = [];
        foreach ($this->uiContext->getReport()->getAvailableDimensions() as $column) {
            $jsConfig = $column->getJsConfig();
            if ($value = $this->getFilterValue($column->getName())) {
                $jsConfig['value'] = $value;
            }
            $config['dimensions'][] = $jsConfig;
        }

        $config['dimension'] = $this->uiContext->getActiveDimension();

        $this->setData('config', $config);
    }

    /**
     * @param string $columnName
     * @return string
     */
    protected function getFilterValue($columnName)
    {
        $value = $this->getContext()->getFilterParam($columnName);
        if ($value) {
            $this->uiContext->getSession()->setData($columnName, $value);
        } else {
            $value = $this->uiContext->getSession()->getData($columnName);
        }

        return $value;
    }

    /**
     * @return \Mirasvit\Report\Ui\DataProvider
     */
    protected function getDataProvider()
    {
        return $this->getContext()->getDataProvider();
    }
}
