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

namespace Module\MiraklConnector\Grid\Definition\Factory;

use Module\MiraklConnector\Controller\Admin\MyPdfGeneratorController;
use PrestaShop\PrestaShop\Core\Grid\Action\GridActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\SubmitRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Type\SubmitGridAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProductGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    const GRID_ID = 'product';

    /**
     * {@inheritdoc}
     */
    protected function getId()
    {
        return self::GRID_ID;
    }

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return $this->trans('Products', [], 'Modules.MiraklConnector.Admin');
    }

    /**
     * {@inheritdoc}
     */
    protected function getGridActions()
    {
        return (new GridActionCollection())
            ->add(
                (new SubmitGridAction('delete_all_email_logs'))
                    ->setName('Erase all')
                    ->setIcon('delete')
                    ->setOptions([
                        'submit_route' => 'admin_logs_delete_all',
                        'confirm_message' => 'Are you sure?',
                    ])
            )
            ;
    }

    /**
     * {@inheritdoc}
     */
    protected function getColumns()
    {
        $myPdfController = new MyPdfGeneratorController();

        return (new ColumnCollection())
            ->add(
                (new DataColumn('date'))
                    ->setName($this->trans('Date', [], 'Modules.MiraklConnector.Admin'))
                    ->setOptions([
                        'field' => 'date',
                    ])
            )
            ->add(
                (new DataColumn('billingAddress'))
                    ->setName($this->trans('Billing Address', [], 'Modules.MiraklConnector.Admin'))
                    ->setOptions([
                        'field' => 'billingAddress',
                    ])
            )
            ->add(
                (new DataColumn('title'))
                    ->setName($this->trans('Title', [], 'Modules.MiraklConnector.Admin'))
                    ->setOptions([
                        'field' => 'title',
                    ])
            )
            ->add(
                (new DataColumn('sku'))
                    ->setName($this->trans('SKU', [], 'Modules.MiraklConnector.Admin'))
                    ->setOptions([
                        'field' => 'sku',
                    ])
            )
            ->add(
                (new DataColumn('quantity'))
                    ->setName($this->trans('Quantity', [], 'Modules.MiraklConnector.Admin'))
                    ->setOptions([
                        'field' => 'quantity',
                    ])
            )
            ->add(
                (new DataColumn('totalPrice'))
                    ->setName($this->trans('Total Price (€)', [], 'Modules.MiraklConnector.Admin'))
                    ->setOptions([
                        'field' => 'totalPrice',
                    ])
            )
            ->add(
                (new ActionColumn('actions'))
                    ->setName($this->trans('Actions', [], 'Admin.Global'))
                    ->setOptions([
                        'actions' => (new RowActionCollection())
                            ->add(
                                (new LinkRowAction('invoice'))
                                    ->setName('Invoice')
                                    ->setIcon('receipt')
                                    ->setOptions([
                                        'route' => 'ps_controller_my_pdf_generator_index',
                                        'route_param_name' => 'params',
                                        'route_param_field' => 'sku',
                                        'extra_route_params' => ['date','billingAddress','title','sku','quantity'],
                                        // A click on the row will have the same effect as this action
                                        'clickable_row' => false,
                                        'use_inline_display' => true,
                                    ])
                            )
                    ])
            )
            ;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilters()
    {
        return (new FilterCollection())
            ->add(
                (new Filter('date', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->trans('Date', [], 'Modules.MiraklConnector.Admin'),
                        ],
                    ])
                    ->setAssociatedColumn('date')
            )
            ->add(
                (new Filter('billingAddress', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->trans('Billing Address', [], 'Modules.MiraklConnector.Admin'),
                        ],
                    ])
                    ->setAssociatedColumn('billingAddress')
            )
            ->add(
                (new Filter('title', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->trans('Title', [], 'Modules.MiraklConnector.Admin'),
                        ],
                    ])
                    ->setAssociatedColumn('title')
            )
            ->add(
                (new Filter('sku', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->trans('SKU', [], 'Modules.MiraklConnector.Admin'),
                        ],
                    ])
                    ->setAssociatedColumn('sku')
            )
            ->add(
                (new Filter('quantity', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->trans('Quantity', [], 'Modules.MiraklConnector.Admin'),
                        ],
                    ])
                    ->setAssociatedColumn('quantity')
            )
            ->add(
                (new Filter('totalPrice', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->trans('Total Price', [], 'Modules.MiraklConnector.Admin'),
                        ],
                    ])
                    ->setAssociatedColumn('totalPrice')
            )
            ->add(
                (new Filter('actions', SearchAndResetType::class))
                    ->setTypeOptions([
                        'reset_route' => 'admin_common_reset_search_by_filter_id',
                        'reset_route_params' => [
                            'filterId' => self::GRID_ID,
                        ],
                        'redirect_route' => 'ps_controller_mirakl_sell_manual_tab_index',
                    ])
                    ->setAssociatedColumn('actions')
            )
        ;
    }
}
