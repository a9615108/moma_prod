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


namespace Mirasvit\Report\Service;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Mirasvit\Report\Api\Service\SelectServiceInterface;
use Mirasvit\Report\Api\Data\Query\TableInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class SelectService implements SelectServiceInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        ResourceConnection $resource,
        TimezoneInterface $timezone
    ) {
        $this->resource = $resource;
        $this->timezone = $timezone;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function replicateTable(TableInterface $table, TableInterface $baseTable)
    {
        if ($table->getConnectionName() == $baseTable->getConnectionName()) {
            return true;
        }

        $baseConnection = $this->resource->getConnection($baseTable->getConnectionName());

        $tblConnection = $this->resource->getConnection($table->getConnectionName());
        $tableName = $this->resource->getTableName($table->getName());

        if (!$baseConnection->isTableExists($tableName)) {
            $schema = $tblConnection->describeTable($tableName);

            $temporaryTable = $baseConnection->newTable($tableName);

            foreach ($schema as $column) {
                $type = $column['DATA_TYPE'];
                if ($column['DATA_TYPE'] == 'int') {
                    $type = 'integer';
                } elseif ($column['DATA_TYPE'] == 'varchar') {
                    $type = 'text';
                }

                $temporaryTable->setColumn([
                    'COLUMN_NAME'      => $column['COLUMN_NAME'],
                    'TYPE'             => $type,
                    'LENGTH'           => $column['LENGTH'],
                    'COLUMN_POSITION'  => $column['COLUMN_POSITION'],
                    'PRIMARY'          => $column['PRIMARY'],
                    'PRIMARY_POSITION' => $column['PRIMARY_POSITION'],
                    'NULLABLE'         => $column['PRIMARY'] ? false : $column['NULLABLE'],
                    'COMMENT'          => $column['COLUMN_NAME'],
                ]);
            }

            try {
                $baseConnection->createTemporaryTable($temporaryTable);

                $offset = 1;
                while (true) {
                    $select = $tblConnection->select()->from($tableName)
                        ->limitPage($offset, 10000);
                    $rows = $tblConnection->fetchAll($select);

                    if (count($rows)) {
                        $baseConnection->insertMultiple($tableName, $rows);
                    } else {
                        break;
                    }

                    $offset++;

                    if ($offset > 30) {
                        break;
                    }
                }
            } catch (\Exception $e) {
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function applyTimeZone(AdapterInterface $connection)
    {
        $utc = $connection->fetchOne('SELECT CURRENT_TIMESTAMP');
        $offset = (new \DateTimeZone($this->timezone->getConfigTimezone()))->getOffset(new \DateTime($utc));
        $h = floor($offset / 3600);
        $m = floor(($offset - $h * 3600) / 60);
        $offset = sprintf("%02d:%02d", $h, $m);

        if (substr($offset, 0, 1) != "-") {
            $offset = "+" . $offset;
        }

        $connection->query("SET time_zone = '$offset'");

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function restoreTimeZone(AdapterInterface $connection)
    {
        $connection->query("SET time_zone = '+00:00'");

        return $this;
    }
}