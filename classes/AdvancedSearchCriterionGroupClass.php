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
class AdvancedSearchCriterionGroupClass extends ObjectModel
{
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
    protected $tables = array('pm_advancedsearch_criterion_group','pm_advancedsearch_criterion_group_lang');
    protected $originalTables = array('pm_advancedsearch_criterion_group','pm_advancedsearch_criterion_group_lang');
    protected $fieldsRequired = array('id_search','criterion_group_type','display_type');
    protected $fieldsSize = array();
    protected $fieldsValidate = array();
    protected $fieldsRequiredLang = array('name');
    protected $fieldsSizeLang = array();
    protected $fieldsValidateLang = array('name'=>'isGenericName','icon'=>'isString','range_sign'=>'isGenericName','range_interval'=>'isGenericName', 'all_label'=>'isGenericName');
    protected $originalTable = 'pm_advancedsearch_criterion_group';
    protected $table = 'pm_advancedsearch_criterion_group';
    public $identifier = 'id_criterion_group';
    public static $definition = array(
        'table' => 'pm_advancedsearch_criterion_group',
        'primary' => 'id_criterion_group',
        'multishop' => false,
        'fields' => array(
            'name'                      => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => true),
            'url_identifier'            => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'url_identifier_original'   => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'icon'                      => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'range_sign'                => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'range_interval'            => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'all_label'                 => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
        )
    );
    private function overrideTableDefinition($id_search)
    {
        $this->id_search = ((int)$id_search ? (int)$id_search : (Tools::getIsset('id_search') && Tools::getValue('id_search') ? (int)Tools::getValue('id_search') : false));
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
    public function __construct($id_criterion_group = null, $id_search = null, $id_lang = null, $id_shop = null)
    {
        $this->overrideTableDefinition((int)$id_search);
        parent::__construct($id_criterion_group, $id_lang, $id_shop);
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
    public function getFields()
    {
        parent::validateFields();
        $fields = array();
        if (isset($this->id)) {
            $fields['id_criterion_group'] = (int)$this->id;
        }
        $fields['id_search'] = (int)$this->id_search;
        $fields['criterion_group_type'] = pSQL($this->criterion_group_type);
        $fields['display_type']        =    (int)$this->display_type;
        $fields['context_type']        =    (int)$this->context_type;
        $fields['is_multicriteria']    =    (int)$this->is_multicriteria;
        $fields['id_criterion_group_linked'] = (int)$this->id_criterion_group_linked;
        $fields['max_display']        =    (int)$this->max_display;
        $fields['css_classes']            =    pSQL($this->css_classes);
        $fields['visible']            =    (int)$this->visible;
        $fields['position']            =    (int)$this->position;
        $fields['overflow_height']    =    (int)$this->overflow_height;
        $fields['show_all_depth']    =    (int)$this->show_all_depth;
        $fields['only_children']    =    (int)$this->only_children;
        $fields['hidden']            =    (int)$this->hidden;
        $fields['filter_option']    = (int)$this->filter_option;
        $fields['is_combined']        = (int)$this->is_combined;
        $fields['range']            = (int)$this->range;
        $fields['range_nb']        = (float)$this->range_nb;
        $fields['sort_by']            = pSQL($this->sort_by);
        $fields['sort_way']        = pSQL($this->sort_way);
        return $fields;
    }
    public function delete()
    {
        if (isset($this->icon) && AdvancedSearchCoreClass::_isFilledArray($this->icon)) {
            foreach ($this->icon as $icon) {
                if ($icon && Tools::file_exists_cache(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions_group/'.$icon)) {
                    @unlink(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions_group/'.$icon);
                }
            }
        }
        if ($this->criterion_group_type == 'price') {
            As4SearchEngineDb::execute('TRUNCATE TABLE `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$this->id_search.'`');
        }
        return parent::delete();
    }
    public function getTranslationsFieldsChild()
    {
        parent::validateFieldsLang();
        return parent::getTranslationsFields(array('name', 'url_identifier', 'url_identifier_original', 'icon', 'range_sign', 'range_interval', 'all_label'));
    }
    public function save($nullValues = false, $autodate = true)
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
        $this->range_nb = $this->convertToPointDecimal($this->range_nb);
        $ret = parent::save($nullValues, $autodate);
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
        $ret = parent::save($nullValues, $autodate);
        self::forceUniqueUrlIdentifier($this->id_search);
        PM_AdvancedSearch4::clearSmartyCache($this->id_search, $this->id);
        return $ret;
    }
    protected function convertToPointDecimal($value)
    {
        return (float)str_replace(",", ".", $value);
    }
    private static $getIdCriterionGroupByTypeAndIdLinkedCache = array();
    public static function getIdCriterionGroupByTypeAndIdLinked($id_search, $criterions_group_type, $id_criterion_group_linked)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionGroupByTypeAndIdLinkedCache[$cacheKey])) {
            return self::$getIdCriterionGroupByTypeAndIdLinkedCache[$cacheKey];
        }
        $row = As4SearchEngineDb::row('
        SELECT acg.`id_criterion_group`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'` acg
        WHERE acg.`criterion_group_type` = "'.pSQL($criterions_group_type).'" AND acg.`id_criterion_group_linked` = '.(int)($id_criterion_group_linked));
        if (isset($row['id_criterion_group']) and $row['id_criterion_group']) {
            self::$getIdCriterionGroupByTypeAndIdLinkedCache[$cacheKey] = (int)$row['id_criterion_group'];
            return self::$getIdCriterionGroupByTypeAndIdLinkedCache[$cacheKey];
        }
        return 0;
    }
    private static $getCriterionsGroupsFromIdSearchCache = array();
    public static function getCriterionsGroupsFromIdSearch($id_search, $id_lang = false, $visible = false)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsGroupsFromIdSearchCache[$cacheKey])) {
            return self::$getCriterionsGroupsFromIdSearchCache[$cacheKey];
        }
        $allowPriceGroup = As4SearchEngine::allowShowPrices();
        if ($id_lang) {
            self::$getCriterionsGroupsFromIdSearchCache[$cacheKey] = As4SearchEngineDb::query('
            SELECT acg.*, acgl.*
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'` acg
            LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'_lang` acgl ON (acg.`id_criterion_group` = acgl.`id_criterion_group` AND acgl.`id_lang` = '.((int) $id_lang).' )
            WHERE acg.`id_search` = '.((int)$id_search).'
            '.($visible ? ' AND `visible` = 1' : '').'
            '.(!$allowPriceGroup ? ' AND acg.`criterion_group_type` != "price"' : '').'
            ORDER BY `position`');
        } else {
            self::$getCriterionsGroupsFromIdSearchCache[$cacheKey] = As4SearchEngineDb::query('
            SELECT acg.*
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'` acg
            WHERE acg.`id_search` = '.((int)$id_search).'
            '.($visible ? ' AND `visible` = 1' : '').'
            '.(!$allowPriceGroup ? ' AND acg.`criterion_group_type` != "price"' : '').'
            ORDER BY `position`');
        }
        return self::$getCriterionsGroupsFromIdSearchCache[$cacheKey];
    }
    private static $getCriterionsGroupCache = array();
    public static function getCriterionsGroup($id_search, $id_criterion_group, $id_lang)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsGroupCache[$cacheKey])) {
            return self::$getCriterionsGroupCache[$cacheKey];
        }
        $allowPriceGroup = As4SearchEngine::allowShowPrices();
        self::$getCriterionsGroupCache[$cacheKey] = As4SearchEngineDb::query('
        SELECT acg.*, acgl.*
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'` acg
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'_lang` acgl ON (acg.`id_criterion_group` = acgl.`id_criterion_group` AND acgl.`id_lang` = '.((int) $id_lang).' )
        WHERE acg.`id_criterion_group`  '.((is_array($id_criterion_group) ? 'IN ('.implode(',', array_map('intval', $id_criterion_group)).')':'='.(int)$id_criterion_group)).'
        '.(!$allowPriceGroup ? ' AND acg.`criterion_group_type` != "price"' : '').'
        ORDER BY `position`');
        return self::$getCriterionsGroupCache[$cacheKey];
    }
    private static $getCriterionsGroupByTypeCache = array();
    public static function getCriterionsGroupByType($idSearch, $groupType)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsGroupByTypeCache[$cacheKey])) {
            return self::$getCriterionsGroupByTypeCache[$cacheKey];
        }
        self::$getCriterionsGroupByTypeCache[$cacheKey] = As4SearchEngineDb::query('
            SELECT *
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'. (int)$idSearch .'`
            WHERE criterion_group_type="'. pSQL($groupType) .'"
        ');
        return self::$getCriterionsGroupByTypeCache[$cacheKey];
    }
    private static $getIdCriterionsGroupByURLIdentifierCache = array();
    public static function getIdCriterionsGroupByURLIdentifier($idSearch, $idLang, $name)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionsGroupByURLIdentifierCache[$cacheKey])) {
            return self::$getIdCriterionsGroupByURLIdentifierCache[$cacheKey];
        }
        self::$getIdCriterionsGroupByURLIdentifierCache[$cacheKey] = As4SearchEngineDb::value('
        SELECT acg.`id_criterion_group`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_' . (int)$idSearch . '` acg
        JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_' . (int)$idSearch . '_lang` acgl ON (acg.`id_criterion_group` = acgl.`id_criterion_group` AND acgl.`id_lang` = ' . (int)$idLang . ')
        WHERE acg.visible = 1
        AND acgl.`url_identifier`="'. pSQL($name) .'"');
        return self::$getIdCriterionsGroupByURLIdentifierCache[$cacheKey];
    }
    private static $getNextIdCriterionGroupCache = array();
    public static function getNextIdCriterionGroup($id_search, $id_criterion_group)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getNextIdCriterionGroupCache[$cacheKey])) {
            return self::$getNextIdCriterionGroupCache[$cacheKey];
        }
        $result = As4SearchEngineDb::query('
        SELECT acg.`id_criterion_group`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'` acg
        WHERE `visible` = 1
        ORDER BY acg.`position`');
        $return = false;
        foreach ($result as $row) {
            if ($return) {
                self::$getNextIdCriterionGroupCache[$cacheKey] = $row['id_criterion_group'];
                return self::$getNextIdCriterionGroupCache[$cacheKey];
            }
            if ($row['id_criterion_group'] == $id_criterion_group) {
                $return = true;
            }
        }
        self::$getNextIdCriterionGroupCache[$cacheKey] = false;
        return self::$getNextIdCriterionGroupCache[$cacheKey];
    }
    private static $getCriterionGroupTypeAndRangeSignCache = array();
    public static function getCriterionGroupTypeAndRangeSign($id_search, $id_criterion_group, $id_lang)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionGroupTypeAndRangeSignCache[$cacheKey])) {
            return self::$getCriterionGroupTypeAndRangeSignCache[$cacheKey];
        }
        $row = As4SearchEngineDb::row('
                SELECT acgl.`range_sign`,  acg.`criterion_group_type`
                FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'` acg
                LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'_lang` acgl ON (acg.`id_criterion_group` = acgl.`id_criterion_group` AND acgl.`id_lang` = '.((int) $id_lang).' )
                WHERE acg.`id_criterion_group` = '.(int)$id_criterion_group);
        self::$getCriterionGroupTypeAndRangeSignCache[$cacheKey] = (isset($row['range_sign'])) ? $row : '';
        return self::$getCriterionGroupTypeAndRangeSignCache[$cacheKey];
    }
    private static $getCriterionGroupTypeCache = array();
    public static function getCriterionGroupType($id_search, $id_criterion_group)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionGroupTypeCache[$cacheKey])) {
            return self::$getCriterionGroupTypeCache[$cacheKey];
        }
        $row = As4SearchEngineDb::row('
                SELECT acg.`criterion_group_type`
                FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'` acg
                WHERE acg.`id_criterion_group` = '.(int)$id_criterion_group);
        self::$getCriterionGroupTypeCache[$cacheKey] = (isset($row['criterion_group_type'])) ? $row['criterion_group_type'] : false;
        return self::$getCriterionGroupTypeCache[$cacheKey];
    }
    public static function disableAllCriterions($id_search, $id_criterion_group)
    {
        $result = As4SearchEngineDb::execute('
        UPDATE `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$id_search.'`
        SET visible = 0
        WHERE id_criterion_group = '.(int)$id_criterion_group);
        return $result;
    }
    public static function enableAllCriterions($id_search, $id_criterion_group)
    {
        $result = As4SearchEngineDb::execute('
        UPDATE `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$id_search.'`
        SET visible = 1
        WHERE id_criterion_group = '.(int)$id_criterion_group);
        return $result;
    }
    public static function forceUniqueUrlIdentifier($idSearch)
    {
        As4SearchEngineDb::setGroupConcatMaxLength();
        $duplicateIdentifier = As4SearchEngineDb::query('
            SELECT acgl.`id_lang`, GROUP_CONCAT(acgl.`id_criterion_group`) as `id_criterion_group_list`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_' . (int)$idSearch . '_lang` acgl
            GROUP BY acgl.`id_lang`, acgl.`url_identifier_original`
            HAVING COUNT(*) > 1');
        foreach ($duplicateIdentifier as $duplicateIdentifierRow) {
            $duplicateIdentifierRow['id_criterion_group_list'] = rtrim($duplicateIdentifierRow['id_criterion_group_list'], ',');
            As4SearchEngineDb::execute('SET @i=0');
            As4SearchEngineDb::execute('
            UPDATE `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_' . (int)$idSearch . '_lang` acgl
            SET acgl.url_identifier = IF ((@i:=@i+1) > 1,  CONCAT(acgl.`url_identifier_original`, "_", @i), acgl.`url_identifier_original` )
            WHERE acgl.`id_criterion_group` IN (' . pSQL($duplicateIdentifierRow['id_criterion_group_list']) . ')
            AND acgl.`id_lang` = ' . (int)$duplicateIdentifierRow['id_lang']);
        }
    }
    public function clearCache($all = false)
    {
        if (!As4SearchEngineIndexation::$processingIndexation) {
            parent::clearCache($all);
        }
    }
    public function as4ForceClearCache($all = false)
    {
        self::$getIdCriterionGroupByTypeAndIdLinkedCache = array();
        self::$getCriterionsGroupsFromIdSearchCache = array();
        self::$getCriterionsGroupCache = array();
        self::$getNextIdCriterionGroupCache = array();
        self::$getCriterionGroupTypeAndRangeSignCache = array();
        self::$getCriterionGroupTypeCache = array();
        parent::clearCache($all);
    }
}
