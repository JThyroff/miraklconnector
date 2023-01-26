<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

namespace Module\MiraklConnector\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Query\Filter\DoctrineFilterApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Query\Filter\SqlFilters;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

/**
 * Defines all required sql statements to render products list.
 *
 * https://devdocs.prestashop-project.org/8/development/components/grid/
 */
class ProductQueryBuilder extends AbstractDoctrineQueryBuilder
{
    /**
     * @var DoctrineSearchCriteriaApplicatorInterface
     */
    private $searchCriteriaApplicator;

    /**
     * @var int
     */
    private $contextLanguageId;

    /**
     * @var int
     */
    private $contextShopId;

    /**
     * @var bool
     */
    private $isStockSharingBetweenShopGroupEnabled;

    /**
     * @var int
     */
    private $contextShopGroupId;

    /**
     * @var DoctrineFilterApplicatorInterface
     */
    private $filterApplicator;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(
        Connection $connection,
        string $dbPrefix,
        DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator,
        int $contextLanguageId,
        int $contextShopId,
        int $contextShopGroupId,
        bool $isStockSharingBetweenShopGroupEnabled,
        DoctrineFilterApplicatorInterface $filterApplicator,
        Configuration $configuration
    ) {
        parent::__construct($connection, $dbPrefix);
        $this->searchCriteriaApplicator = $searchCriteriaApplicator;
        $this->contextLanguageId = $contextLanguageId;
        $this->contextShopId = $contextShopId;
        $this->isStockSharingBetweenShopGroupEnabled = $isStockSharingBetweenShopGroupEnabled;
        $this->contextShopGroupId = $contextShopGroupId;
        $this->filterApplicator = $filterApplicator;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getQueryBuilder($searchCriteria->getFilters());
        $qb
            ->select('o.`date`, o.title')
            ->addSelect('o.`billingAddress`, o.`sku`')
            ->addSelect('o.`quantity`, o.`basePricePerUnit`')
            ->addSelect('o.`basePrice`')
            ->addSelect('o.`totalBasePrice`')
            ->addSelect('o.`taxes`')
            ->addSelect('o.`totalPrice`');

        $this->searchCriteriaApplicator
            ->applyPagination($searchCriteria, $qb)
            ->applySorting($searchCriteria, $qb)
        ;

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getQueryBuilder($searchCriteria->getFilters());
        $qb->select('COUNT(o.`date`)');

        return $qb;
    }

    /**
     * Gets query builder.
     *
     * @param array $filterValues
     *
     * @return QueryBuilder
     */
    private function getQueryBuilder(array $filterValues): QueryBuilder
    {
        $qb = $this->connection
            ->createQueryBuilder()
            ->from($this->dbPrefix . 'Orders', 'o')
            ->innerJoin(
                'o',
                $this->dbPrefix . 'BillingAddress',
                'ba',
                'o.billingAddress = ba.ID'
            )
        ;

        $qb->setParameter('id_shop', $this->contextShopId);
        $qb->setParameter('id_lang', $this->contextLanguageId);

        foreach ($filterValues as $filterName => $filter) {
            if ('lastname' === $filterName) {
                $qb->andWhere('ba.`lastname` LIKE :name');
                $qb->setParameter('lastname', '%' . $filter . '%');

                continue;
            }

            if ('date' === $filterName) {
                $qb->andWhere('o.`date` LIKE :date');
                $qb->setParameter('date', '%'.$filter.'%');

                continue;
            }

            if ('billingAddress' === $filterName) {
                $qb->andWhere('o.`billingAddress` = :billingAddress');
                $qb->setParameter('billingAddress', $filter);

                continue;
            }

            if ('title' === $filterName) {
                $qb->andWhere('o.`title` LIKE :title');
                $qb->setParameter('title', '%' . $filter . '%');

                continue;
            }

            if ('sku' === $filterName) {
                $qb->andWhere('o.`sku` LIKE :sku');
                $qb->setParameter('sku', '%'.$filter.'%');

                continue;
            }

            if ('quantity' === $filterName) {
                $qb->andWhere('o.`quantity` = :quantity');
                $qb->setParameter('quantity', $filter);

                continue;
            }

            if ('totalPrice' === $filterName) {
                $qb->andWhere('ABS (o.totalPrice - :totalPrice) < 2');
                $qb->setParameter('totalPrice', $filter);

                continue;
            }
        }

        return $qb;
    }
}
