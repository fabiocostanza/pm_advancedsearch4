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
class AdvancedSearchClass extends ObjectModel
{
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
    protected $tables = array('pm_advancedsearch','pm_advancedsearch_lang');
    protected $fieldsRequired = array('id_hook');
    protected $fieldsSize = array();
    protected $fieldsValidate = array();
    protected $fieldsRequiredLang = array();
    protected $fieldsSizeLang = array();
    protected $fieldsValidateLang = array('title'=>'isGenericName','description'=>'isCleanHTML');
    protected $table = 'pm_advancedsearch';
    public $identifier = 'id_search';
    public static $definition = array(
        'table' => 'pm_advancedsearch',
        'primary' => 'id_search',
        'multishop' => true,
        'multilang_shop' => false,
        'fields' => array(
            'title'        => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'description'  => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'internal_name' => array('type' => self::TYPE_STRING, 'lang' => false, 'required' => false, 'size' => 255),
            'css_classes' => array('type' => self::TYPE_STRING, 'lang' => false, 'required' => false, 'size' => 255),
            'search_results_selector' => array('type' => self::TYPE_STRING, 'lang' => false, 'required' => false, 'size' => 64),
            'smarty_var_name' => array('type' => self::TYPE_STRING, 'lang' => false, 'required' => false, 'size' => 64),
        )
    );
    public function __construct($id_search = null, $id_lang = null, $id_shop = null)
    {
        Shop::addTableAssociation(self::$definition['table'], array('type' => 'shop'));
        parent::__construct($id_search, $id_lang, $id_shop);
    }
    public function getFields()
    {
        parent::validateFields();
        $fields = array();
        if (isset($this->id)) {
            $fields['id_search'] = (int)$this->id;
        }
        $fields['id_hook']    = (int)$this->id_hook;
        $fields['active'] = (int)$this->active;
        $fields['internal_name'] = pSQL($this->internal_name);
        $fields['css_classes'] = pSQL($this->css_classes);
        $fields['search_results_selector_css'] = pSQL($this->search_results_selector_css);
        $fields['display_nb_result_on_blc'] = (int)$this->display_nb_result_on_blc;
        $fields['display_nb_result_criterion'] = (int)$this->display_nb_result_criterion;
        $fields['remind_selection'] = (int)$this->remind_selection;
        $fields['show_hide_crit_method'] = (int)$this->show_hide_crit_method;
        $fields['filter_by_emplacement'] = (int)$this->filter_by_emplacement;
        $fields['search_on_stock'] = (int)$this->search_on_stock;
        $fields['hide_empty_crit_group'] = (int)$this->hide_empty_crit_group;
        $fields['search_method'] = (int)$this->search_method;
        $fields['priority_on_combination_image'] = (int)$this->priority_on_combination_image;
        $fields['products_per_page'] = (int)$this->products_per_page;
        $fields['products_order_by'] = (int)$this->products_order_by;
        $fields['products_order_way'] = (int)$this->products_order_way;
        $fields['step_search'] = (int)$this->step_search;
        $fields['step_search_next_in_disabled'] = (int)$this->step_search_next_in_disabled;
        $fields['keep_category_information'] = (int)$this->keep_category_information;
        $fields['display_empty_criteria'] = (int)$this->display_empty_criteria;
        $fields['recursing_indexing'] = (int)$this->recursing_indexing;
        $fields['search_results_selector'] = pSQL($this->search_results_selector);
        $fields['smarty_var_name'] = pSQL($this->smarty_var_name);
        $fields['insert_in_center_column'] = (int)($this->insert_in_center_column);
        $fields['reset_group']    =    (int)($this->reset_group);
        $fields['unique_search']    =    (int)($this->unique_search);
        $fields['scrolltop_active'] = (int)$this->scrolltop_active;
        $fields['id_category_root'] = (int)$this->id_category_root;
        $fields['redirect_one_product'] = (int)$this->redirect_one_product;
        $fields['add_anchor_to_url'] = (int)$this->add_anchor_to_url;
        $fields['position'] = (int)$this->position;
        $fields['hide_criterions_group_with_no_effect'] = (int)$this->hide_criterions_group_with_no_effect;
        return $fields;
    }
    public function getTranslationsFieldsChild()
    {
        parent::validateFieldsLang();
        $fieldsArray = array('title');
        $fields = array();
        $languages = Language::getLanguages(false);
        $defaultLanguage = Configuration::get('PS_LANG_DEFAULT');
        foreach ($languages as $language) {
            $fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
            $fields[$language['id_lang']][$this->identifier] = (int)$this->id;
            $fields[$language['id_lang']]['description'] = (isset($this->description[$language['id_lang']]) and !empty($this->description[$language['id_lang']])) ? pSQL($this->description[$language['id_lang']], true) : pSQL($this->description[$defaultLanguage], true);
            foreach ($fieldsArray as $field) {
                if (!Validate::isTableOrIdentifier($field)) {
                    die(Tools::displayError());
                }
                if (isset($this->{$field}[$language['id_lang']]) and !empty($this->{$field}[$language['id_lang']])) {
                    $fields[$language['id_lang']][$field] = pSQL($this->{$field}[$language['id_lang']]);
                } else {
                    $fields[$language['id_lang']][$field] = pSQL($this->{$field}[$defaultLanguage]);
                }
            }
        }
        return $fields;
    }
    public function save($nullValues = false, $autodate = false)
    {
        As4SearchEngine::setLocalStorageCacheKey();
        if ($this->id_hook != -1) {
            if ($this->id_hook == Hook::getIdByName('displayHome')) {
                $this->insert_in_center_column = 1;
            } else {
                $this->insert_in_center_column = 0;
            }
        }
        if (!empty($this->id) && !$this->filter_by_emplacement) {
            $this->id_category_root = 0;
            As4SearchEngineDb::execute('
                UPDATE `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_' . (int)$this->id . '`
                SET `context_type`="2"
                WHERE `criterion_group_type`="category"
            ');
        }
        $ret = parent::save($nullValues, $autodate);
        $add_associations = true;
        if ((int)$this->id_hook == (int)Hook::getIdByName('displayAdvancedSearch4')) {
            $add_associations = false;
        }
        if (Tools::getIsset('categories_association') && $add_associations) {
            $this->addAssociations($this->categories_association, 'pm_advancedsearch_category', 'id_category');
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_category');
        }
        if (Tools::getIsset('cms_association') && $add_associations) {
            $this->addAssociations($this->cms_association, 'pm_advancedsearch_cms', 'id_cms');
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_cms');
        }
        if (Tools::getIsset('products_association') && $add_associations) {
            $this->addAssociations($this->products_association, 'pm_advancedsearch_products', 'id_product');
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_products');
        }
        if (Tools::getIsset('product_categories_association') && $add_associations) {
            $this->addAssociations($this->product_categories_association, 'pm_advancedsearch_products_cat', 'id_category');
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_products_cat');
        }
        if (Tools::getIsset('manufacturers_association') && $add_associations) {
            $this->addAssociations($this->manufacturers_association, 'pm_advancedsearch_manufacturers', 'id_manufacturer');
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_manufacturers');
        }
        if (Tools::getIsset('suppliers_association') && $add_associations) {
            $this->addAssociations($this->suppliers_association, 'pm_advancedsearch_suppliers', 'id_supplier');
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_suppliers');
        }
        if (Tools::getIsset('special_pages_association') && $add_associations) {
            $this->addAssociations($this->special_pages_association, 'pm_advancedsearch_special_pages', 'page');
        } elseif (Tools::isSubmit('submitSearchVisibility')) {
            $this->cleanAssociation('pm_advancedsearch_special_pages');
        }
        return $ret;
    }
    public function duplicate($id_shop = null, $importData = array())
    {
        As4SearchEngine::setLocalStorageCacheKey();
        $obj = parent::duplicateObject();
        if (!Validate::isLoadedObject($obj)) {
            return false;
        }
        if ((int)$id_shop) {
            $obj->internal_name = $this->internal_name;
            $obj->active = $this->active;
        } else {
            $translated_string = Module::getInstanceByName('pm_advancedsearch4')->translateMultiple('duplicated_from');
            $obj->internal_name = sprintf($translated_string[Context::getContext()->language->id], $this->internal_name);
            $obj->active = false;
        }
        $obj->title = $this->title;
        $obj->description = $this->description;
        $obj->update();
        $ret = Module::getInstanceByName('pm_advancedsearch4')->installDBCache((int)$obj->id);
        $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$obj->id.'` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'`');
        $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$obj->id.'_lang` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_lang`');
        $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$obj->id.'_link` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_link`');
        $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$obj->id.'_list` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_list`');
        $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$obj->id.'` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$this->id.'`');
        $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$obj->id.'_lang` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$this->id.'_lang`');
        $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int)$obj->id.'` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int)$this->id.'`');
        $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int)$obj->id.'` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int)$this->id.'`');
        $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$obj->id.'` SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$this->id.'`');
        As4SearchEngineDb::execute('UPDATE `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$obj->id.'` SET `id_search` = '.(int)$obj->id);
        $criterionsGroupsImages = As4SearchEngineDb::query('SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$obj->id.'_lang` WHERE `icon`!=""');
        if ($criterionsGroupsImages && AdvancedSearchCoreClass::_isFilledArray($criterionsGroupsImages)) {
            foreach ($criterionsGroupsImages as $criterionGroupImage) {
                if ($criterionGroupImage['icon'] && Tools::file_exists_cache(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions_group/'.$criterionGroupImage['icon'])) {
                    $newImageName = uniqid(AdvancedSearchCoreClass::$_module_prefix . mt_rand()).'.'.AdvancedSearchCoreClass::_getFileExtension($criterionGroupImage['icon']);
                    if (copy(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions_group/' . $criterionGroupImage['icon'], _PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions_group/' . $newImageName)) {
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
        $criterionsImages = As4SearchEngineDb::query('SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$obj->id.'_lang` WHERE `icon`!=""');
        if ($criterionsImages && AdvancedSearchCoreClass::_isFilledArray($criterionsImages)) {
            foreach ($criterionsImages as $criterionsImage) {
                if ($criterionsImage['icon'] && Tools::file_exists_cache(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions/'.$criterionsImage['icon'])) {
                    $newImageName = uniqid(AdvancedSearchCoreClass::$_module_prefix . mt_rand()).'.'.AdvancedSearchCoreClass::_getFileExtension($criterionsImage['icon']);
                    if (copy(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions/' . $criterionsImage['icon'], _PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions/' . $newImageName)) {
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
        if ((int)$id_shop) {
            $categoryListCondition = '';
            if (isset($importData['categoryList']) && is_array($importData['categoryList']) && sizeof($importData['categoryList'])) {
                $categoryListCondition = ' AND `id_category` IN (' . implode(',', array_map('intval', $importData['categoryList'])) . ')';
            }
            $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_category` (SELECT "'.(int)$obj->id.'" AS `id_search`, `id_category` FROM `'._DB_PREFIX_.'pm_advancedsearch_category` WHERE `id_search` = '.(int)$this->id . $categoryListCondition . ')');
            $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_products_cat` (SELECT "'.(int)$obj->id.'" AS `id_search`, `id_category` FROM `'._DB_PREFIX_.'pm_advancedsearch_products_cat` WHERE `id_search` = '.(int)$this->id . $categoryListCondition . ')');
            $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_products` (SELECT "'.(int)$obj->id.'" AS `id_search`, `id_product` FROM `'._DB_PREFIX_.'pm_advancedsearch_products` WHERE `id_search` = '.(int)$this->id.')');
            $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_special_pages` (SELECT "'.(int)$obj->id.'" AS `id_search`, `page` FROM `'._DB_PREFIX_.'pm_advancedsearch_special_pages` WHERE `id_search` = '.(int)$this->id.')');
            if (isset($importData['cms'])) {
                $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_cms` (SELECT "'.(int)$obj->id.'" AS `id_search`, `id_cms` FROM `'._DB_PREFIX_.'pm_advancedsearch_cms` WHERE `id_search` = '.(int)$this->id.')');
            }
            if (isset($importData['manufacturer'])) {
                $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_manufacturers` (SELECT "'.(int)$obj->id.'" AS `id_search`, `id_manufacturer` FROM `'._DB_PREFIX_.'pm_advancedsearch_manufacturers` WHERE `id_search` = '.(int)$this->id.')');
            }
            if (isset($importData['supplier'])) {
                $ret &= As4SearchEngineDb::execute('INSERT INTO `'._DB_PREFIX_.'pm_advancedsearch_suppliers` (SELECT "'.(int)$obj->id.'" AS `id_search`, `id_supplier` FROM `'._DB_PREFIX_.'pm_advancedsearch_suppliers` WHERE `id_search` = '.(int)$this->id.')');
            }
        }
        if ((int)$id_shop) {
            Db::getInstance()->update(
                'pm_advancedsearch_shop',
                array(
                    'id_shop' => (int)$id_shop,
                ),
                'id_search = '.(int)$obj->id
            );
            Db::getInstance()->update(
                'pm_advancedsearch_product_price_'.(int)$obj->id,
                array(
                    'id_shop' => (int)$id_shop,
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
        As4SearchEngine::setLocalStorageCacheKey();
        $ret = parent::delete();
        $this->cleanAssociation('pm_advancedsearch_cms');
        As4SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'`');
        As4SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_shop`');
        As4SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_lang`');
        As4SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_link`');
        As4SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id.'_list`');
        As4SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$this->id.'`');
        As4SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$this->id.'_lang`');
        As4SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int)$this->id.'`');
        As4SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int)$this->id.'`');
        As4SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$this->id.'`');
        AdvancedSearchSeoClass::deleteByIdSearch($this->id);
        return $ret;
    }
    public function addAssociations($associations, $asso_table, $asso_identifier, $cleanBefore = true)
    {
        if ($cleanBefore) {
            $this->cleanAssociation($asso_table);
        }
        foreach ($associations as $value) {
            $value = trim($value);
            if (!$value) {
                continue;
            }
            $row = array($this->identifier => (int)$this->id, $asso_identifier => $value);
            Db::getInstance()->insert($asso_table, $row);
        }
    }
    public function cleanAssociation($asso_table)
    {
        As4SearchEngineDb::execute('DELETE FROM `' . bqSQL(_DB_PREFIX_ . $asso_table) . '` WHERE `'.bqSQL($this->identifier).'` = '.(int)$this->id);
    }
}
