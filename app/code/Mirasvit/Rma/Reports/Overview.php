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

class Overview extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('RMA: Report by Status');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'rma_overview';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setBaseTable('mst_rma_rma');
        $this->addFastFilters([]);
        $this->setDefaultColumns([
            'mst_rma_rma|pending_rma_cnt',
            'mst_rma_rma|approved_rma_cnt',
            'mst_rma_rma|rejected_rma_cnt',
            'mst_rma_rma|sent_rma_cnt',
            'mst_rma_rma|closed_rma_cnt',
            'mst_rma_rma|total_rma_cnt',
        ]);

        $this->addAvailableColumns(
            $this->context->getMapRepository()
                ->getTable('mst_rma_rma')->getColumns(ColumnInterface::TYPE_SIMPLE)
        );

        $this->setDefaultDimension('mst_rma_rma|day');

        $this->addAvailableDimensions([
            'mst_rma_rma|day',
            'mst_rma_rma|week',
            'mst_rma_rma|month',
            'mst_rma_rma|year',
        ]);

        $this->setGridConfig([
            'paging' => true,
        ]);
        $this->setChartConfig([
            'chartType' => 'column',
            'vAxis'     => 'mst_rma_rma|pending_rma_cnt',
        ]);
    }
}