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
use Cache;
use Tools;
use ObjectModel;
use PM_AdvancedSearch4;
use AdvancedSearch\Core;
use AdvancedSearch\SearchEngineDb;
use AdvancedSearch\SearchEngineUtils;
use AdvancedSearch\SearchEngineIndexation;
use AdvancedSearch\Traits\SupportsImageCriterionGroup;
class CriterionGroup extends ObjectModel
{
    use SupportsImageCriterionGroup;
    public $id;
    public $id_search;
    public $name;
    public $url_identifier;
    public $url_identifier_original;
    public $icon;
    public $criterion_group_type;
    public $display_type = 1;
    public $context_type;
    public $is_multicriteria;
    public $id_criterion_group_linked;
    public $max_display;
    public $overflow_height;
    public $css_classes = 'col-xs-12 col-sm-3';
    public $visible;
    public $position;
    public $show_all_depth;
    public $only_children;
    public $hidden;
    public $filter_option;
    public $is_combined;
    public $range;
    public $range_sign;
    public $range_interval;
    public $sort_by = 'position';
    public $sort_way = 'ASC';
    public $range_nb = 15;
    public $all_label;
    protected $tables = array(
        'pm_advancedsearch_criterion_group',
        'pm_advancedsearch_criterion_group_lang',
    );
    protected $originalTables = array(
        'pm_advancedsearch_criterion_group',
        'pm_advancedsearch_criterion_group_lang',
    );
    protected $originalTable = 'pm_advancedsearch_criterion_group';
    protected $table = 'pm_advancedsearch_criterion_group';
    public $identifier = 'id_criterion_group';
    public static $definition = array(
        'table' => 'pm_advancedsearch_criterion_group',
        'primary' => 'id_criterion_group',
        'multishop' => false,
        'multilang' => true,
        'fields' => array(
            'id_search' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'criterion_group_type' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 24),
            'sort_by' => array('type' => self::TYPE_STRING, 'size' => 10),
            'sort_way' => array('type' => self::TYPE_STRING, 'size' => 4),
            'id_criterion_group_linked' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'display_type' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'context_type' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'is_multicriteria' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'filter_option' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'is_combined' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'range' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'range_nb' => array('type' => self::TYPE_STRING),
            'show_all_depth' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'only_children' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'hidden' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'max_display' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'css_classes' => array('type' => self::TYPE_STRING, 'size' => 255),
            'overflow_height' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'visible' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => true, 'size' => 255),
            'url_identifier' => array('type' => self::TYPE_STRING, 'lang' => true, 'size' => 255),
            'url_identifier_original' => array('type' => self::TYPE_STRING, 'lang' => true, 'size' => 255),
            'icon' => array('type' => self::TYPE_STRING, 'lang' => true, 'size' => 32),
            'range_sign' => array('type' => self::TYPE_STRING, 'lang' => true, 'size' => 32),
            'range_interval' => array('type' => self::TYPE_STRING, 'lang' => true, 'size' => 255),
            'all_label' => array('type' => self::TYPE_STRING, 'lang' => true, 'size' => 255),
        )
    );
    protected function overrideTableDefinition(int $idSearch)
    {
        $this->id_search = ((int)$idSearch ? (int)$idSearch : (Tools::getIsset('id_search') && Tools::getValue('id_search') ? (int)Tools::getValue('id_search') : false));
        if (empty($this->id_search)) {
            die('Missing id_search');
        }
        $className = get_class($this);
        self::$definition['table'] = $this->originalTable . '_' . (int)$this->id_search;
        self::$definition['classname'] = $className . '_' . (int)$this->id_search;
        $this->def['table'] = $this->originalTable . '_' . (int)$this->id_search;
        $this->def['classname'] = $className . '_' . (int)$this->id_search;
        if (isset(ObjectModel::$loaded_classes) && isset(ObjectModel::$loaded_classes[$className])) {
            unset(ObjectModel::$loaded_classes[$className]);
        }
        $this->table = $this->originalTable . '_' . (int)$this->id_search;
        foreach ($this->originalTables as $key => $table) {
            $this->tables[$key] = $table . '_' . (int)$this->id_search;
        }
    }
    protected function setDefinitionRetrocompatibility()
    {
        parent::setDefinitionRetrocompatibility();
        $this->overrideTableDefinition((int)$this->id_search);
    }
    public function __construct($idCriterionGroup = null, $idSearch = null, $idLang = null, $idShop = null)
    {
        $this->overrideTableDefinition((int)$idSearch);
        parent::__construct($idCriterionGroup, $idLang, $idShop);
    }
    public function __destruct()
    {
        if (is_object($this)) {
            $class = get_class($this);
            if (method_exists('Cache', 'clean')) {
                Cache::clean('objectmodel_def_'.$class);
            }
            if (method_exists($this, 'clearCache')) {
                $this->clearCache(true);
            }
        }
    }
    public function delete()
    {
        if (isset($this->icon) && Core::isFilledArray($this->icon)) {
            foreach ($this->icon as $icon) {
                if ($icon && Tools::file_exists_cache(_PS_ROOT_DIR_ . '/modules/' . _PM_AS_MODULE_NAME_ . '/search_files/criterions_group/'.$icon)) {
                    @unlink(_PS_ROOT_DIR_ . '/modules/' . _PM_AS_MODULE_NAME_ . '/search_files/criterions_group/'.$icon);
                }
            }
        }
        if ($this->criterion_group_type == 'price') {
            SearchEngineDb::execute('TRUNCATE TABLE `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$this->id_search.'`');
        }
        return parent::delete();
    }
    public function save($nullValues = false, $autoDate = true)
    {
        if (!$this->id && $this->criterion_group_type == 'price') {
            $this->display_type = 5;
        }
        if ($this->criterion_group_type == 'category' && $this->display_type == 9) {
            $this->show_all_depth = 1;
            $this->sort_by = 'o_position';
            $this->sort_way = 'ASC';
        } elseif ($this->criterion_group_type == 'category' && $this->display_type != 9) {
            $this->context_type = 0;
        }
        if (!self::supportsImageCriterionGroup() && $this->display_type == 2) {
            $this->display_type = 1;
        }
        if (in_array($this->display_type, [5, 8])) {
            foreach (array_keys($this->range_interval) as $idLang) {
                $this->range_interval[$idLang] = '';
            }
        } elseif (empty($this->range)) {
            foreach (array_keys($this->range_interval) as $idLang) {
                $this->range_interval[$idLang] = '';
                $this->range_sign[$idLang] = '';
            }
        }
        $this->range_nb = $this->convertToPointDecimal($this->range_nb);
        $ret = parent::save($nullValues, $autoDate);
        if (is_array($this->name)) {
            foreach (array_keys($this->name) as $idLang) {
                $this->url_identifier[$idLang] = str_replace('-', '_', Tools::str2url($this->name[$idLang]));
                $this->url_identifier_original[$idLang] = str_replace('-', '_', Tools::str2url($this->name[$idLang]));
                if (isset($this->range_interval[$idLang])) {
                    $this->range_interval[$idLang] = trim($this->range_interval[$idLang]);
                } else {
                    $this->range_interval[$idLang] = '';
                }
            }
        } else {
            $this->url_identifier = str_replace('-', '_', Tools::str2url($this->name));
            $this->url_identifier_original = str_replace('-', '_', Tools::str2url($this->name));
            $this->range_interval = trim($this->range_interval);
        }
        $result = parent::save($nullValues, $autoDate);
        self::forceUniqueUrlIdentifier($this->id_search);
        PM_AdvancedSearch4::clearSmartyCache($this->id_search, $this->id);
        return $result;
    }
    protected function convertToPointDecimal(string $value)
    {
        return (float)str_replace(",", ".", $value);
    }
    private static $getIdCriterionGroupByTypeAndIdLinkedCache = array();
    public static function getIdCriterionGroupByTypeAndIdLinked(int $idSearch, string $criterionsGroupType, int $idCriterionGroupLinked)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionGroupByTypeAndIdLinkedCache[$cacheKey])) {
            return self::$getIdCriterionGroupByTypeAndIdLinkedCache[$cacheKey];
        }
        $row = SearchEngineDb::row('
        SELECT acg.`id_criterion_group`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$idSearch.'` acg
        WHERE acg.`criterion_group_type` = "'.pSQL($criterionsGroupType).'" AND acg.`id_criterion_group_linked` = '.(int)$idCriterionGroupLinked);
        if (isset($row['id_criterion_group']) and $row['id_criterion_group']) {
            self::$getIdCriterionGroupByTypeAndIdLinkedCache[$cacheKey] = (int)$row['id_criterion_group'];
            return self::$getIdCriterionGroupByTypeAndIdLinkedCache[$cacheKey];
        }
        return 0;
    }
    private static $getCriterionsGroupsFromIdSearchCache = array();
    public static function getCriterionsGroupsFromIdSearch(int $idSearch, $idLang = false, bool $visible)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsGroupsFromIdSearchCache[$cacheKey])) {
            return self::$getCriterionsGroupsFromIdSearchCache[$cacheKey];
        }
        $allowPriceGroup = SearchEngineUtils::allowShowPrices();
        if ($idLang) {
            self::$getCriterionsGroupsFromIdSearchCache[$cacheKey] = SearchEngineDb::query('
            SELECT acg.*, acgl.*
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$idSearch.'` acg
            LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$idSearch.'_lang` acgl ON (acg.`id_criterion_group` = acgl.`id_criterion_group` AND acgl.`id_lang` = '.(int)$idLang.' )
            WHERE acg.`id_search` = '.(int)$idSearch.'
            '.($visible ? ' AND `visible` = 1' : '').'
            '.(!$allowPriceGroup ? ' AND acg.`criterion_group_type` != "price"' : '').'
            ORDER BY `position`');
        } else {
            self::$getCriterionsGroupsFromIdSearchCache[$cacheKey] = SearchEngineDb::query('
            SELECT acg.*
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$idSearch.'` acg
            WHERE acg.`id_search` = '.(int)$idSearch.'
            '.($visible ? ' AND `visible` = 1' : '').'
            '.(!$allowPriceGroup ? ' AND acg.`criterion_group_type` != "price"' : '').'
            ORDER BY `position`');
        }
        return self::$getCriterionsGroupsFromIdSearchCache[$cacheKey];
    }
    private static $getCriterionsGroupCache = array();
    public static function getCriterionsGroup(int $idSearch, array $idCriterionGroup, int $idLang)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsGroupCache[$cacheKey])) {
            return self::$getCriterionsGroupCache[$cacheKey];
        }
        $allowPriceGroup = SearchEngineUtils::allowShowPrices();
        self::$getCriterionsGroupCache[$cacheKey] = SearchEngineDb::query('
        SELECT acg.*, acgl.*
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$idSearch.'` acg
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$idSearch.'_lang` acgl ON (acg.`id_criterion_group` = acgl.`id_criterion_group` AND acgl.`id_lang` = '.(int)$idLang.' )
        WHERE acg.`id_criterion_group` IN (' . implode(',', array_map('intval', $idCriterionGroup)).')
        '.(!$allowPriceGroup ? ' AND acg.`criterion_group_type` != "price"' : '').'
        ORDER BY `position`');
        return self::$getCriterionsGroupCache[$cacheKey];
    }
    private static $getCriterionsGroupByTypeCache = array();
    public static function getCriterionsGroupByType(int $idSearch, $groupType)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsGroupByTypeCache[$cacheKey])) {
            return self::$getCriterionsGroupByTypeCache[$cacheKey];
        }
        if (!is_array($groupType)) {
            $groupType = array($groupType);
        }
        $groupType = array_unique($groupType);
        self::$getCriterionsGroupByTypeCache[$cacheKey] = SearchEngineDb::query('
            SELECT *
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'. (int)$idSearch .'`
            WHERE criterion_group_type IN ('. implode(',', array_map(function ($group) {
                return '"' . pSQL($group) . '"';
            }, $groupType)) .')
        ');
        return self::$getCriterionsGroupByTypeCache[$cacheKey];
    }
    private static $getIdCriterionsGroupByURLIdentifierCache = array();
    public static function getIdCriterionsGroupByURLIdentifier(int $idSearch, int $idLang, $urlIdentifier)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionsGroupByURLIdentifierCache[$cacheKey])) {
            return self::$getIdCriterionsGroupByURLIdentifierCache[$cacheKey];
        }
        self::$getIdCriterionsGroupByURLIdentifierCache[$cacheKey] = SearchEngineDb::value('
        SELECT acg.`id_criterion_group`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_' . (int)$idSearch . '` acg
        JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_' . (int)$idSearch . '_lang` acgl ON (acg.`id_criterion_group` = acgl.`id_criterion_group` AND acgl.`id_lang` = ' . (int)$idLang . ')
        WHERE acg.visible = 1
        AND acgl.`url_identifier`="'. pSQL($urlIdentifier) .'"');
        return self::$getIdCriterionsGroupByURLIdentifierCache[$cacheKey];
    }
    private static $getNextIdCriterionGroupCache = array();
    public static function getNextIdCriterionGroup(int $idSearch, int $idCriterionGroup)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getNextIdCriterionGroupCache[$cacheKey])) {
            return self::$getNextIdCriterionGroupCache[$cacheKey];
        }
        $result = SearchEngineDb::query('
        SELECT acg.`id_criterion_group`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$idSearch.'` acg
        WHERE `visible` = 1
        ORDER BY acg.`position`');
        $return = false;
        foreach ($result as $row) {
            if ($return) {
                self::$getNextIdCriterionGroupCache[$cacheKey] = (int)$row['id_criterion_group'];
                return self::$getNextIdCriterionGroupCache[$cacheKey];
            }
            if ($row['id_criterion_group'] == $idCriterionGroup) {
                $return = true;
            }
        }
        self::$getNextIdCriterionGroupCache[$cacheKey] = false;
        return self::$getNextIdCriterionGroupCache[$cacheKey];
    }
    private static $getCriterionGroupTypeAndRangeSignCache = array();
    public static function getCriterionGroupTypeAndRangeSign(int $idSearch, int $idCriterionGroup, int $idLang)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionGroupTypeAndRangeSignCache[$cacheKey])) {
            return self::$getCriterionGroupTypeAndRangeSignCache[$cacheKey];
        }
        $row = SearchEngineDb::row('
                SELECT acgl.`range_sign`,  acg.`criterion_group_type`
                FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$idSearch.'` acg
                LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$idSearch.'_lang` acgl ON (acg.`id_criterion_group` = acgl.`id_criterion_group` AND acgl.`id_lang` = '.(int)$idLang.' )
                WHERE acg.`id_criterion_group` = '.(int)$idCriterionGroup);
        self::$getCriterionGroupTypeAndRangeSignCache[$cacheKey] = (isset($row['range_sign'])) ? $row : '';
        return self::$getCriterionGroupTypeAndRangeSignCache[$cacheKey];
    }
    public static function disableAllCriterions(int $idSearch, int $idCriterionGroup)
    {
        return SearchEngineDb::execute('
        UPDATE `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'`
        SET visible = 0
        WHERE id_criterion_group = '.(int)$idCriterionGroup);
    }
    public static function enableAllCriterions(int $idSearch, int $idCriterionGroup)
    {
        return SearchEngineDb::execute('
        UPDATE `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'`
        SET visible = 1
        WHERE id_criterion_group = '.(int)$idCriterionGroup);
    }
    public static function forceUniqueUrlIdentifier(int $idSearch)
    {
        SearchEngineDb::setGroupConcatMaxLength();
        $duplicateIdentifier = SearchEngineDb::query('
            SELECT acgl.`id_lang`, GROUP_CONCAT(acgl.`id_criterion_group`) as `id_criterion_group_list`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_' . (int)$idSearch . '_lang` acgl
            GROUP BY acgl.`id_lang`, acgl.`url_identifier_original`
            HAVING COUNT(*) > 1');
        foreach ($duplicateIdentifier as $duplicateIdentifierRow) {
            $duplicateIdentifierRow['id_criterion_group_list'] = rtrim($duplicateIdentifierRow['id_criterion_group_list'], ',');
            SearchEngineDb::execute('SET @i=0');
            SearchEngineDb::execute('
            UPDATE `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_' . (int)$idSearch . '_lang` acgl
            SET acgl.url_identifier = IF ((@i:=@i+1) > 1,  CONCAT(acgl.`url_identifier_original`, "_", @i), acgl.`url_identifier_original` )
            WHERE acgl.`id_criterion_group` IN (' . pSQL($duplicateIdentifierRow['id_criterion_group_list']) . ')
            AND acgl.`id_lang` = ' . (int)$duplicateIdentifierRow['id_lang']);
        }
    }
    public function clearCache($all = false)
    {
        if (!SearchEngineIndexation::$processingIndexation) {
            return;
        }
        parent::clearCache($all);
    }
    public function as4ForceClearCache(bool $all)
    {
        self::$getIdCriterionGroupByTypeAndIdLinkedCache = array();
        self::$getCriterionsGroupsFromIdSearchCache = array();
        self::$getCriterionsGroupCache = array();
        self::$getNextIdCriterionGroupCache = array();
        self::$getCriterionGroupTypeAndRangeSignCache = array();
        parent::clearCache($all);
    }
}
