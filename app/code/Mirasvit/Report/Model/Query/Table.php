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


namespace Mirasvit\Report\Model\Query;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\Report\Api\Data\Query\ColumnInterface;
use Mirasvit\Report\Api\Data\Query\FieldInterface;
use Mirasvit\Report\Api\Data\Query\TableInterface;
use Mirasvit\Report\Api\Repository\MapRepositoryInterface;

class Table implements TableInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var FieldInterface[]
     */
    protected $fieldsPool = [];

    /**
     * @var ColumnInterface[]
     */
    protected $columnsPool = [];

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var string
     */
    protected $connectionName;

    /**
     * @var MapRepositoryInterface
     */
    protected $mapRepository;

    /**
     * @param ResourceConnection     $resource
     * @param MapRepositoryInterface $mapRepository
     * @param string                 $name
     * @param string                 $connection
     */
    public function __construct(
        ResourceConnection $resource,
        MapRepositoryInterface $mapRepository,
        $name,
        $connection = 'default'
    ) {
        $this->name = $name;
        $this->connectionName = $connection;

        $this->resource = $resource;
        $this->connection = $resource->getConnection($this->connectionName);
        $this->mapRepository = $mapRepository;

        $this->initFields();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn($name)
    {
        if (isset($this->columnsPool[$name])) {
            return $this->columnsPool[$name];
        } else {
            throw new \Exception(__('Undefined column "%1"', $name));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        $types = func_get_args();

        if (count($types)) {

            $columns = [];

            foreach ($this->columnsPool as $column) {
                if (in_array($column->getType(), $types)) {
                    $columns[] = $column;
                }
            }

            return $columns;
        }

        return $this->columnsPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * {@inheritdoc}
     */
    public function addColumn(ColumnInterface $column)
    {
        $this->columnsPool[$column->getName()] = $column;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getField($name)
    {
        if (key_exists($name, $this->fieldsPool)) {
            return $this->fieldsPool[$name];
        }

        throw new \Exception(__("Field %1 not exists in table %2", $name, $this->getName()));
    }

    /**
     * @return void
     */
    private function initFields()
    {
        $tableName = $this->resource->getTableName($this->name);
        $fields = $this->connection->describeTable($tableName);

        foreach (array_keys($fields) as $fieldName) {
            $field = $this->mapRepository->createField([
                'table' => $this,
                'name'  => $fieldName,
            ]);

            $this->fieldsPool[$field->getName()] = $field;
        }
    }
}
