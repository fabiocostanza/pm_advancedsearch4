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
// Load Search Providers for PrestaShop 1.7
if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
    include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/controllers/front/seo-17.php');
} else {
    include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/controllers/front/seo-16.php');
}
