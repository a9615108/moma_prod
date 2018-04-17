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


namespace Mirasvit\Rma\Reports;

use Mirasvit\Report\Model\AbstractReport;

use Mirasvit\Report\Api\Data\Query\ColumnInterface;

class Reasons extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('RMA: Report by Reasons');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'rma_reasons';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setBaseTable('mst_rma_item');
        $this->addFastFilters([]);
        $this->setDefaultColumns([
            'mst_rma_item|total_condition_cnt',
            'mst_rma_item|total_reasons_cnt',
            'mst_rma_item|total_resolution_cnt',
        ]);

        $this->addAvailableColumns(
            $this->context->getMapRepository()
                ->getTable('mst_rma_item')->getColumns(ColumnInterface::TYPE_SIMPLE)
        );

        $this->setDefaultDimension('mst_rma_item|day');

        $this->addAvailableDimensions([
            'mst_rma_item|day',
            'mst_rma_item|week',
            'mst_rma_item|month',
            'mst_rma_item|year',
        ]);

        $this->setGridConfig([
            'paging' => true,
        ]);
        $this->setChartConfig([
            'chartType' => 'column',
            'vAxis'     => 'mst_rma_item|total_reasons_cnt',
        ]);
    }
}