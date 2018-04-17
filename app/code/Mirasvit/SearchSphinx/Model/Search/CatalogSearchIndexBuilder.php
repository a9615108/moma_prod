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


namespace Mirasvit\SearchSphinx\Model\Search;

use Magento\Framework\Search\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class CatalogSearchIndexBuilder extends IndexBuilder
{
    /**
     * @var ConditionManager
     */
    protected $conditionManager;

    /**
     * @var ScopeResolverInterface
     */
    protected $dimensionScopeResolver;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param ConditionManager                                $conditionManager
     * @param ScopeResolverInterface                          $dimensionScopeResolver
     * @param ScopeConfigInterface                            $config
     * @param ResourceConnection                              $resource
     * @param \Mirasvit\SearchSphinx\Model\Adapter\MapperQL   $mapperQL
     * @param \Magento\CatalogSearch\Model\Search\TableMapper $tableMapper
     */
    public function __construct(
        ConditionManager $conditionManager,
        ScopeResolverInterface $dimensionScopeResolver,
        ScopeConfigInterface $config,
        ResourceConnection $resource,
        \Mirasvit\SearchSphinx\Model\Adapter\MapperQL $mapperQL,
        \Magento\CatalogSearch\Model\Search\TableMapper $tableMapper
    ) {
        $this->conditionManager = $conditionManager;
        $this->dimensionScopeResolver = $dimensionScopeResolver;
        $this->config = $config;

        parent::__construct($resource, $mapperQL, $tableMapper);
    }

    /**
     * {@inheritdoc}
     */
    public function build(RequestInterface $request)
    {
        $table = $this->mapperQL->buildQuery($request);

        $select = $this->resource->getConnection()->select()
            ->from(
                ['search_index' => $table->getName()],
                ['entity_id' => 'entity_id', 'score' => 'score']
            );

        $select = $this->tableMapper->addTables($select, $request);

        $select = $this->processDimensions($request, $select);

        $isShowOutOfStock = $this->config->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );
        if ($isShowOutOfStock === false) {
            $select->joinLeft(
                ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
                'search_index.entity_id = stock_index.product_id'
                . $this->resource->getConnection()->quoteInto(
                    ' AND stock_index.website_id = ?',
                    $this->getStockConfiguration()->getDefaultScopeId()
                ),
                []
            );
            $select->where('stock_index.stock_status = ?', Stock::DEFAULT_STOCK_ID);
        }

        return $select;
    }

    /**
     * @return StockConfigurationInterface
     *
     * @deprecated
     */
    private function getStockConfiguration()
    {
        if ($this->stockConfiguration === null) {
            $this->stockConfiguration = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Api\StockConfigurationInterface');
        }

        return $this->stockConfiguration;
    }

    /**
     * Add filtering by dimensions
     *
     * @param RequestInterface $request
     * @param Select           $select
     * @return \Magento\Framework\DB\Select
     */
    private function processDimensions(RequestInterface $request, Select $select)
    {
        $dimensions = $this->prepareDimensions($request->getDimensions());

        $query = $this->conditionManager->combineQueries($dimensions, Select::SQL_OR);
        if (!empty($query)) {
            $select->where($this->conditionManager->wrapBrackets($query));
        }

        return $select;
    }

    /**
     * @param array $dimensions
     * @return string[]
     */
    private function prepareDimensions(array $dimensions)
    {
        $preparedDimensions = [];
        foreach ($dimensions as $dimension) {
            if ('scope' === $dimension->getName()) {
                continue;
            }
            $preparedDimensions[] = $this->conditionManager->generateCondition(
                $dimension->getName(),
                '=',
                $this->dimensionScopeResolver->getScope($dimension->getValue())->getId()
            );
        }

        return $preparedDimensions;
    }
}
