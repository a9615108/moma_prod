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


namespace Mirasvit\Rma\Service\Report;

use Mirasvit\Rma\Helper\Locale;
use Mirasvit\Report\Api\Repository\MapRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

abstract class AbstractReasons
{
    public function __construct(
        Locale $localeData,
        MapRepositoryInterface $mapRepository,
        ResourceConnection $resource
    ) {
        $this->localeData    = $localeData;
        $this->mapRepository = $mapRepository;
        $this->resource      = $resource;
    }

    /**
     * @return string
     */
    abstract public function getItemTable();

    /**
     * @return string
     */
    abstract public function getReasonsTable();

    /**
     * @return string
     */
    abstract public function getItemReasonsField();

    /**
     * @return string
     */
    abstract public function getReasonsField();

    /**
     * @param string $prefix
     * @return void
     */
    public function add($prefix)
    {
        $results = $this->resource->getConnection()->query($this->getReasonsSql());
        foreach ($results->fetchAll() as $rule) {
            $object = new \Magento\Framework\DataObject($rule);
            $name = $this->localeData->getLocaleValue($object, 'name');
            $this->mapRepository->createColumn([
                'name' => $name . '_' . $rule['id'],
                'data' => [
                    'expr'  => 'SUM(IF(' . $this->getItemReasonsField() . ' = ' . $rule['id'] . ', 1, 0))',
                    'label' => $prefix . ': ' . $name,
                    'type'  => 'number',
                    'table' => $this->mapRepository->getTable($this->getItemTable()),
                ],
            ]);
        }
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    protected function getReasonsSql()
    {
        return $this->resource->getConnection()
            ->select()
            ->from(
                [$this->resource->getTableName($this->getReasonsTable())],
                ['name', 'id' => $this->getReasonsField()]
            );
    }
}