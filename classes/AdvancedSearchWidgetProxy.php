<?php
/**
 *
 * @author Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *
 ****/

if (!defined('_PS_VERSION_')) {
    exit;
}
if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
    class AdvancedSearchWidgetProxy extends AdvancedSearchCoreClass implements PrestaShop\PrestaShop\Core\Module\WidgetInterface
    {
        public function renderWidget($hookName, array $configuration)
        {
            return parent::renderWidget($hookName, $configuration);
        }
        public function getWidgetVariables($hookName, array $configuration)
        {
            return parent::getWidgetVariables($hookName, $configuration);
        }
    }
} else {
    class AdvancedSearchWidgetProxy extends AdvancedSearchCoreClass
    {
    }
}
