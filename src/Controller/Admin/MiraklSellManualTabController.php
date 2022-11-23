<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Module\MiraklConnector\Controller\Admin;

use PrestaShop\PrestaShop\Core\Search\Filters\OrderFilters;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Form\Admin\Sell\Order\ChangeOrdersStatusType;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MiraklSellManualTabController extends FrameworkBundleAdminController
{
    const TAB_CLASS_NAME = 'MiraklSellManualTab';

    /**
     * @AdminSecurity("is_granted('read', 'MiraklSellManualTab')")
     *
     * @param Request $request
     * @param OrderFilters $filters
     *
     * @return Response
     */
    public function indexAction(Request $request, OrderFilters $filters): Response
    {
        $orderKpiFactory = $this->get('prestashop.core.kpi_row.factory.orders');
        $orderGrid = $this->get('prestashop.core.grid.factory.order')->getGrid($filters);

        $changeOrderStatusesForm = $this->createForm(ChangeOrdersStatusType::class);

        return $this->render('@Modules/miraklconnector/views/templates/admin/mirakl_sell_manual_tab.html.twig',
            [
                'orderGrid' => $this->presentGrid($orderGrid),
                'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
                'enableSidebar' => true,
                'changeOrderStatusesForm' => $changeOrderStatusesForm->createView(),
                'orderKpi' => $orderKpiFactory->build(),
                'layoutHeaderToolbarBtn' => $this->getOrderToolbarButtons(),
            ]
        );
    }

    /**
     * @return array
     */
    private function getOrderToolbarButtons(): array
    {
        $toolbarButtons = [];

        $isSingleShopContext = $this->get('prestashop.adapter.shop.context')->isSingleShopContext();

        /*$toolbarButtons['add'] = [
            'href' => $this->generateUrl('admin_orders_create'),
            'desc' => $this->trans('Add new order', 'Admin.Orderscustomers.Feature'),
            'icon' => 'add_circle_outline',
            'disabled' => !$isSingleShopContext,
        ];*/

        if (!$isSingleShopContext) {
            $toolbarButtons['add']['help'] = $this->trans(
                'You can use this feature in a single shop context only. Switch context to enable it.',
                'Admin.Orderscustomers.Feature'
            );
            $toolbarButtons['add']['href'] = '#';
        }

        return $toolbarButtons;
    }
}
