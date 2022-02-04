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
class pm_advancedsearch4cronModuleFrontController extends ModuleFrontController
{
    private $idSearch;
    private $searchInstance;
    public $ajax = true;
    public $display_header = false;
    public $display_footer = false;
    public $display_column_left = false;
    public $display_column_right = false;
    public function init()
    {
        if (ob_get_length() > 0) {
            ob_clean();
        }
        header('X-Robots-Tag: noindex, nofollow', true);
        header('Content-type: application/json');
        $secureKey = Configuration::getGlobalValue('PM_AS4_SECURE_KEY');
        if (empty($secureKey) || $secureKey !== Tools::getValue('secure_key')) {
            Tools::redirect('404');
            die;
        }
        $this->idSearch = (int)Tools::getValue('id_search');
        if (!empty($this->idSearch)) {
            $this->searchInstance = new AdvancedSearchClass((int)$this->idSearch, (int)$this->context->language->id);
            if (!Validate::isLoadedObject($this->searchInstance)) {
                Tools::redirect('404');
            }
        }
        if (!empty($this->searchInstance->id)) {
            $indexationStats = $this->module->cronTask($this->searchInstance->id);
        } else {
            $indexationStats = $this->module->cronTask();
        }
        die(Tools::jsonEncode($indexationStats));
    }
}
