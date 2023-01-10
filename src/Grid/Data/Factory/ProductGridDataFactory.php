<?php

namespace Module\MiraklConnector\Grid\Data\Factory;

use Doctrine\DBAL\Query\QueryBuilder;
use Module\MiraklConnector\Grid\Filters\ProductFilters;
use Module\MiraklConnector\Mirakl\FetchOrders;
use PDO;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineQueryBuilderInterface;
use PrestaShop\PrestaShop\Core\Grid\Query\QueryParserInterface;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteria;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;
use Symfony\Component\DependencyInjection\Container;

class ProductGridDataFactory implements GridDataFactoryInterface
{
    /**
     * @var DoctrineQueryBuilderInterface
     */
    private $gridQueryBuilder;

    /**
     * @var HookDispatcherInterface
     */
    private $hookDispatcher;

    /**
     * @var QueryParserInterface
     */
    private $queryParser;

    /**
     * @var string
     */
    private $gridId;

    /**
     * @param DoctrineQueryBuilderInterface $gridQueryBuilder
     * @param HookDispatcherInterface $hookDispatcher
     * @param QueryParserInterface $queryParser
     * @param string $gridId
     */
    public function __construct(
        DoctrineQueryBuilderInterface $gridQueryBuilder,
        HookDispatcherInterface $hookDispatcher,
        QueryParserInterface $queryParser,
        $gridId
    ) {
        $this->gridQueryBuilder = $gridQueryBuilder;
        $this->hookDispatcher = $hookDispatcher;
        $this->queryParser = $queryParser;
        $this->gridId = $gridId;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(SearchCriteriaInterface $searchCriteria)
    {
        //$searchCriteria = new SearchCriteria(ProductFilters::getDefaults());

        $searchQueryBuilder = $this->gridQueryBuilder->getSearchQueryBuilder($searchCriteria);
        $countQueryBuilder = $this->gridQueryBuilder->getCountQueryBuilder($searchCriteria);

        $this->hookDispatcher->dispatchWithParameters('action' . Container::camelize($this->gridId) . 'GridQueryBuilderModifier', [
            'search_query_builder' => $searchQueryBuilder,
            'count_query_builder' => $countQueryBuilder,
            'search_criteria' => $searchCriteria,
        ]);

        $records = $searchQueryBuilder->execute()->fetchAll();
        $recordsTotal = (int) $countQueryBuilder->execute()->fetch(PDO::FETCH_COLUMN);

        /*
        #region Injected code
        $records = FetchOrders::fetchOrders();
        $recordsTotal = count($records);
        #endregion
        */

        $records = new RecordCollection($records);

        return new GridData(
            $records,
            $recordsTotal,
            $this->getRawQuery($searchQueryBuilder)
        );
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return string
     */
    private function getRawQuery(QueryBuilder $queryBuilder)
    {
        $query = $queryBuilder->getSQL();
        $parameters = $queryBuilder->getParameters();

        return $this->queryParser->parse($query, $parameters);
    }
}

