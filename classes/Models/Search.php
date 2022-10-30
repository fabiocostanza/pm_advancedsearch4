<?php
/**
 *
 * @author Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module
 * @license see file: LICENSE.txt
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *
 ****/

namespace AdvancedSearch\Models;
if (!defined('_PS_VERSION_')) {
    exit;
}
use Db;
use Hook;
use Shop;
use Tools;
use Module;
use Context;
use Validate;
use ObjectModel;
use AdvancedSearch\Core;
use AdvancedSearch\Models\Seo;
use AdvancedSearch\SearchEngineDb;
use AdvancedSearch\SearchEngineUtils;
use AdvancedSearch\Traits\SupportsSeoPages;
class Search extends ObjectModel
{
    use SupportsSeoPages;
    public $id;
    public $id_hook;
    public $active = true;
    public $internal_name;
    public $description;
    public $title;
    public $css_classes;
    public $search_results_selector_css;
    public $display_nb_result_on_blc = false;
    public $display_nb_result_criterion = true;
    public $remind_selection;
    public $show_hide_crit_method;
    public $filter_by_emplacement = true;
    public $search_on_stock = false;
    public $hide_empty_crit_group;
    public $search_method;
    public $step_search = false;
    public $step_search_next_in_disabled;
    public $position;
    public $products_per_page;
    public $products_order_by;
    public $products_order_way;
    public $keep_category_information;
    public $display_empty_criteria = 0;
    public $recursing_indexing = true;
    public $search_results_selector;
    public $smarty_var_name;
    public $insert_in_center_column;
    public $unique_search;
    public $reset_group;
    public $scrolltop_active = 1;
    public $id_category_root = 0;
    public $redirect_one_product = 1;
    public $priority_on_combination_image = true;
    public $add_anchor_to_url = true;
    public $hide_criterions_group_with_no_effect;
    protected $tables = array(
        'pm_advancedsearch',
        'pm_advancedsearch_lang',
    );
    protected $table = 'pm_advancedsearch';
    public $identifier = 'id_search';
    public static $definition = array(
        'table' => 'pm_advancedsearch',
        'primary' => 'id_search',
        'multishop' => true,
        'multilang' => true,
        'fields' => array(
            'id_hook' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'active' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'internal_name' => array('type' => self::TYPE_STRING, 'size' => 255),
            'css_classes' => array('type' => self::TYPE_STRING, 'size' => 255),
            'search_results_selector' => array('type' => self::TYPE_STRING, 'size' => 255),
            'display_nb_result_on_blc' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'display_nb_result_criterion' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'remind_selection' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'show_hide_crit_method' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'filter_by_emplacement' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'search_on_stock' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'hide_empty_crit_group' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'search_method' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'priority_on_combination_image' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'products_per_page' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'products_order_by' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'products_order_way' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'step_search' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'step_search_next_in_disabled' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'keep_category_information' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'display_empty_criteria' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'recursing_indexing' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'search_results_selector' => array('type' => self::TYPE_STRING, 'size' => 64),
            'smarty_var_name' => array('type' => self::TYPE_STRING, 'size' => 64),
            'insert_in_center_column' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'reset_group' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'unique_search' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'scrolltop_active' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_category_root' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'redirect_one_product' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'add_anchor_to_url' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'hide_criterions_group_with_no_effect' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'title' => array('type' => self::TYPE_STRING, 'lang' => true),
            'description' => array('type' => self::TYPE_HTML, 'lang' => true),
        )
    );
    public function __construct($idSearch = null, $idLang = null, $idShop = null)
    {
        Shop::addTableAssociation(self::$definition['table'], array('type' => 'shop'));
        parent::__construct($idSearch, $idLang, $idShop);
    }
    public function save($nullValues = false, $autoDate = false)
    {
        SearchEngineUtils::setLocalStorageCacheKey();
        if ($this->id_hook != -1) {
            if ($this->id_hook == Hook::getIdByName('displayHome')) {
                $this->insert_in_center_column = 1;
            } else {
                $this->insert_in_center_column = 0;
            }
        }
        if (!empty($this->id) && !$this->filter_by_emplacement) {
            $this->id_category_root = 0;
            SearchEngineDb::execute('
                UPDATE `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_' . (int)$this->id . '`
                SET `context_type`="2"
                WHERE `criterion_group_type`="category"
            ');
        }
        $result = parent::save($nullValues, $autoDate);
        $add_associations = true;
        if ((int)$this->id_hook == (int)Hook::getIdByName('displayAdvancedSearch4')) {
            $add_associations = false;
        }
        if (Tools::getIsset('categories_association') && $add_associations) {
            $this->addAssociations($this->categories_association, 'pm_advancedsearch_category', 'id_category', true);
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_category');
        }
        if (Tools::getIsset('cms_association') && $add_associations) {
            $this->addAssociations($this->cms_association, 'pm_advancedsearch_cms', 'id_cms', true);
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_cms');
        }
        if (Tools::getIsset('products_association') && $add_associations) {
            $this->addAssociations($this->products_association, 'pm_advancedsearch_products', 'id_product', true);
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_products');
        }
        if (Tools::getIsset('product_categories_association') && $add_associations) {
            $this->addAssociations($this->product_categories_association, 'pm_advancedsearch_products_cat', 'id_category', true);
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_products_cat');
        }
        if (Tools::getIsset('manufacturers_association') && $add_associations) {
            $this->addAssociations($this->manufacturers_association, 'pm_advancedsearch_manufacturers', 'id_manufacturer', true);
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_manufacturers');
        }
        if (Tools::getIsset('suppliers_association') && $add_associations) {
            $this->addAssociations($this->suppliers_association, 'pm_advancedsearch_suppliers', 'id_supplier', true);
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_suppliers');
        }
        if (Tools::getIsset('special_pages_association') && $add_associations) {
            $this->addAssociations($this->special_pages_association, 'pm_advancedsearch_special_pages', 'page', true);
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_special_pages');
        }
        return $result;
    }
    public function duplicate(int $idShop = null, array $importData = array())
    {
        SearchEngineUtils::setLocalStorageCacheKey();
        $obj = parent::duplicateObject();
        if (!Validate::isLoadedObject($obj)) {
            return false;
        }
        if ((int)$idShop) {
            $obj->internal_name = $this->internal_name;
            $obj->active = $this->active;
        } else {
            $translated_string = Module::getInstanceByName(_PM_AS_MODULE_NAME_)->translateMultiple('duplicated_from');
            $obj->internal_name = sprintf($translated_string[Context::getContext()->language->id], $this->internal_name);
            $obj->active = false;
        }
        $obj->title = $this->title;
        $obj->description = $this->description;
        $obj->update();
        $ret = Module::getInstanceByName(_PM_AS_MODULE_NAME_)->installDBCache((int)$obj->id);
        $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$obj->id.'` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'`');
        $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$obj->id.'_lang` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_lang`');
        $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$obj->id.'_link` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_link`');
        $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$obj->id.'_list` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_list`');
        $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$obj->id.'` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$this->id.'`');
        $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$obj->id.'_lang` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$this->id.'_lang`');
        $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int)$obj->id.'` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int)$this->id.'`');
        $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int)$obj->id.'` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int)$this->id.'`');
        $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$obj->id.'` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$this->id.'`');
        SearchEngineDb::execute('UPDATE `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$obj->id.'` SET `id_search` = '.(int)$obj->id);
        $criterionsGroupsImages = SearchEngineDb::query('SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$obj->id.'_lang` WHERE `icon`!=""');
        if ($criterionsGroupsImages && Core::isFilledArray($criterionsGroupsImages)) {
            foreach ($criterionsGroupsImages as $criterionGroupImage) {
                if ($criterionGroupImage['icon'] && Tools::file_exists_cache(_PS_ROOT_DIR_ . '/modules/' . _PM_AS_MODULE_NAME_ . '/search_files/criterions_group/'.$criterionGroupImage['icon'])) {
                    $newImageName = uniqid(Core::$modulePrefix . mt_rand()).'.'.Core::getFileExtension($criterionGroupImage['icon']);
                    if (copy(_PS_ROOT_DIR_ . '/modules/' . _PM_AS_MODULE_NAME_ . '/search_files/criterions_group/' . $criterionGroupImage['icon'], _PS_ROOT_DIR_ . '/modules/' . _PM_AS_MODULE_NAME_ . '/search_files/criterions_group/' . $newImageName)) {
                        Db::getInstance()->update(
                            'pm_advancedsearch_criterion_group_'.(int)$obj->id.'_lang',
                            array(
                                'icon' => $newImageName,
                            ),
                            'id_criterion_group = '.(int)$criterionGroupImage['id_criterion_group'].' AND id_lang = '.(int)$criterionGroupImage['id_lang']
                        );
                    }
                }
            }
        }
        $criterionsImages = SearchEngineDb::query('SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$obj->id.'_lang` WHERE `icon`!=""');
        if ($criterionsImages && Core::isFilledArray($criterionsImages)) {
            foreach ($criterionsImages as $criterionsImage) {
                if ($criterionsImage['icon'] && Tools::file_exists_cache(_PS_ROOT_DIR_ . '/modules/' . _PM_AS_MODULE_NAME_ . '/search_files/criterions/'.$criterionsImage['icon'])) {
                    $newImageName = uniqid(Core::$modulePrefix . mt_rand()).'.'.Core::getFileExtension($criterionsImage['icon']);
                    if (copy(_PS_ROOT_DIR_ . '/modules/' . _PM_AS_MODULE_NAME_ . '/search_files/criterions/' . $criterionsImage['icon'], _PS_ROOT_DIR_ . '/modules/' . _PM_AS_MODULE_NAME_ . '/search_files/criterions/' . $newImageName)) {
                        Db::getInstance()->update(
                            'pm_advancedsearch_criterion_'.(int)$obj->id.'_lang',
                            array(
                                'icon' => $newImageName,
                            ),
                            'id_criterion = '.(int)$criterionsImage['id_criterion'].' AND id_lang = '.(int)$criterionsImage['id_lang']
                        );
                    }
                }
            }
        }
        if ((int)$idShop) {
            $categoryListCondition = '';
            if (isset($importData['categoryList']) && is_array($importData['categoryList']) && sizeof($importData['categoryList'])) {
                $categoryListCondition = ' AND `id_category` IN (' . implode(',', array_map('intval', $importData['categoryList'])) . ')';
            }
            $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_category` (SELECT "'.(int)$obj->id.'" AS `id_search`, `id_category` FROM `'._DB_PREFIX_.'pm_advancedsearch_category` WHERE `id_search` = '.(int)$this->id . $categoryListCondition . ')');
            $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_products_cat` (SELECT "'.(int)$obj->id.'" AS `id_search`, `id_category` FROM `'._DB_PREFIX_.'pm_advancedsearch_products_cat` WHERE `id_search` = '.(int)$this->id . $categoryListCondition . ')');
            $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_products` (SELECT "'.(int)$obj->id.'" AS `id_search`, `id_product` FROM `'._DB_PREFIX_.'pm_advancedsearch_products` WHERE `id_search` = '.(int)$this->id.')');
            $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_special_pages` (SELECT "'.(int)$obj->id.'" AS `id_search`, `page` FROM `'._DB_PREFIX_.'pm_advancedsearch_special_pages` WHERE `id_search` = '.(int)$this->id.')');
            if (isset($importData['cms'])) {
                $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_cms` (SELECT "'.(int)$obj->id.'" AS `id_search`, `id_cms` FROM `'._DB_PREFIX_.'pm_advancedsearch_cms` WHERE `id_search` = '.(int)$this->id.')');
            }
            if (isset($importData['manufacturer'])) {
                $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_manufacturers` (SELECT "'.(int)$obj->id.'" AS `id_search`, `id_manufacturer` FROM `'._DB_PREFIX_.'pm_advancedsearch_manufacturers` WHERE `id_search` = '.(int)$this->id.')');
            }
            if (isset($importData['supplier'])) {
                $ret &= SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_suppliers` (SELECT "'.(int)$obj->id.'" AS `id_search`, `id_supplier` FROM `'._DB_PREFIX_.'pm_advancedsearch_suppliers` WHERE `id_search` = '.(int)$this->id.')');
            }
            Db::getInstance()->delete(
                'pm_advancedsearch_shop',
                'id_shop != ' . (int)$idShop . ' AND id_search = ' . (int)$obj->id
            );
            Db::getInstance()->update(
                'pm_advancedsearch_product_price_'.(int)$obj->id,
                array(
                    'id_shop' => (int)$idShop,
                )
            );
        }
        if ($ret) {
            $ret = $obj;
        }
        return $ret;
    }
    public function delete()
    {
        SearchEngineUtils::setLocalStorageCacheKey();
        $result = parent::delete();
        $this->cleanAllAssociations();
        SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'`');
        SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_shop`');
        SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_lang`');
        SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_link`');
        SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_list`');
        SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$this->id.'`');
        SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$this->id.'_lang`');
        SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int)$this->id.'`');
        SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int)$this->id.'`');
        SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$this->id.'`');
        if (self::supportsSeoPages()) {
            Seo::deleteByIdSearch($this->id);
        }
        return $result;
    }
    public function addAssociations(array $associations, string $associationTable, string $associationIdentifier, bool $cleanBefore)
    {
        $result = true;
        if ($cleanBefore) {
            $result &= $this->cleanAssociation($associationTable);
        }
        $formattedAssociations = [];
        foreach ($associations as $value) {
            $value = trim($value);
            if (!$value) {
                continue;
            }
            $formattedAssociations[] = [
                $this->identifier => (int)$this->id,
                $associationIdentifier => $value,
            ];
        }
        foreach (array_chunk($formattedAssociations, 5000) as $rows) {
            $result &= Db::getInstance()->insert($associationTable, $rows);
        }
        return $result;
    }
    public function cleanAssociation(string $associationTable)
    {
        return SearchEngineDb::execute('DELETE FROM `' . bqSQL(_DB_PREFIX_ . $associationTable) . '` WHERE `'.bqSQL($this->identifier).'` = '.(int)$this->id);
    }
    public function cleanAllAssociations()
    {
        $result = $this->cleanAssociation('pm_advancedsearch_category');
        $result &= $this->cleanAssociation('pm_advancedsearch_cms');
        $result &= $this->cleanAssociation('pm_advancedsearch_products');
        $result &= $this->cleanAssociation('pm_advancedsearch_products_cat');
        $result &= $this->cleanAssociation('pm_advancedsearch_manufacturers');
        $result &= $this->cleanAssociation('pm_advancedsearch_suppliers');
        $result &= $this->cleanAssociation('pm_advancedsearch_special_pages');
        return $result;
    }
}
