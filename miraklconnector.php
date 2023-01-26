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

// Needed for install process
require_once __DIR__ . '/vendor/autoload.php';

use Module\MiraklConnector\Controller\Admin\MiraklSellManualTabController;

/**
 * Main module class. Don't rename it.
 */
class miraklconnector extends Module{
    public function __construct($name = null, Context $context = null)
    {
        $this->name = "miraklconnector";
        $this->tab = "administration";

        $this->version = "0.2.0";
        $this->author = "Johannes Thyroff";
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            "min" => "8.0.0",
            "max" => "8.0.0"
        ];
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l("Mirakl Connector");
        $this->description = $this->l("Prestashop module to integrate Mirakl orders in Prestashop using the Mirakl sdk.");

        $this->confirmUninstall = $this->l("Are you sure you want to uninstall ".$this->displayName);
    }

    /**
     * Displays the configuration page
     *
     * @return false|string
     */
    public function getContent(){
        //this is the configuration page
        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    public function install()
    {
        return parent::install() && $this->manuallyInstallMiraklSellTab();
    }

    public function uninstall()
    {
        return parent::uninstall() &&$this->manuallyUnInstallMiraklSellTab();
    }

    public function enable($force_all = false)
    {
        return parent::enable($force_all) && $this->manuallyInstallMiraklSellTab();
    }

    public function disable($force_all = false)
    {
        return parent::disable($force_all) && $this->manuallyUnInstallMiraklSellTab();
    }

    /**
     * Install Mirakl Tab
     *
     * @return bool
     */
    private function manuallyInstallMiraklSellTab(): bool
    {
        // Add Tab for ManualTabController
        // See https://devdocs.prestashop.com/1.7/modules/concepts/controllers/admin-controllers/tabs/

        $controllerClassName = MiraklSellManualTabController::TAB_CLASS_NAME;
        $tabId = (int) Tab::getIdFromClassName($controllerClassName);
        if (!$tabId) {
            $tabId = null;
        }

        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = $controllerClassName;
        $tab->route_name = 'ps_controller_mirakl_sell_manual_tab_index';
        $tab->name = [];
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('PC-Componentes', [], 'Modules.MiraklConnector.Admin', $lang['locale']);
        }
        $tab->icon = 'build';
        $tab->id_parent = (int) Tab::getIdFromClassName('SELL');
        $tab->module = $this->name;

        return (bool) $tab->save();
    }

    /**
     * Uninstall Mirakl Tab
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function manuallyUnInstallMiraklSellTab(): bool
    {
        $tabId = (int) Tab::getIdFromClassName(MiraklSellManualTabController::TAB_CLASS_NAME);
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }
}