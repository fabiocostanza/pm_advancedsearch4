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
class AdvancedSearchCriterionClass extends ObjectModel
{
    public $id;
    public $id_criterion_group;
    public $id_criterion_linked;
    public $value;
    public $decimal_value;
    public $url_identifier;
    public $url_identifier_original;
    public $icon;
    public $color;
    public $visible = 1;
    public $level_depth;
    public $id_parent;
    public $position;
    public $is_custom;
    protected $tables = array('pm_advancedsearch_criterion','pm_advancedsearch_criterion_lang');
    protected $originalTables = array('pm_advancedsearch_criterion','pm_advancedsearch_criterion_lang');
    public $id_search;
    protected $fieldsRequired = array('id_criterion_group');
    protected $fieldsSize = array();
    protected $fieldsValidate = array();
    protected $fieldsRequiredLang = array();
    protected $fieldsSizeLang = array();
    protected $fieldsValidateLang = array('value'=>'isString');
    protected $originalTable = 'pm_advancedsearch_criterion';
    protected $table = 'pm_advancedsearch_criterion';
    public $identifier = 'id_criterion';
    public static $definition = array(
        'table' => 'pm_advancedsearch_criterion',
        'primary' => 'id_criterion',
        'multishop' => false,
        'fields' => array(
            'value'                     => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false, 'validate' => 'isString'),
            'decimal_value'             => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'url_identifier'            => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false, 'validate' => 'isString'),
            'url_identifier_original'   => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false, 'validate' => 'isString'),
            'icon'                      => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false)
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
    public function __construct($id_criterion = null, $id_search = null, $id_lang = null, $id_shop = null)
    {
        $this->overrideTableDefinition((int)$id_search);
        parent::__construct($id_criterion, $id_lang, $id_shop);
        if ($this->id && !isset($this->id_criterion_linked)) {
            $id_criterion_link = self::getIdCriterionLinkByIdCriterion($this->id_search, $this->id);
            if ($id_criterion_link !== false) {
                $this->id_criterion_linked = $id_criterion_link;
            }
            unset($id_criterion_link);
        }
    }
    public function save($null_values = false, $autodate = true)
    {
        $this->setUrlIdentifier();
        $saveResult = parent::save($null_values, $autodate);
        if ($saveResult) {
            self::populateCriterionsLink((int)$this->id_search, $this->id, $this->id_criterion_linked);
            self::addCriterionToList((int)$this->id_search, $this->id, $this->id);
        }
        return $saveResult;
    }
    public function setUrlIdentifier()
    {
        if (is_array($this->value)) {
            foreach (array_keys($this->value) as $idLang) {
                $this->url_identifier[$idLang] = str_replace('-', '_', Tools::str2url($this->value[$idLang]));
                $this->url_identifier_original[$idLang] = str_replace('-', '_', Tools::str2url($this->value[$idLang]));
            }
        } else {
            $this->url_identifier = str_replace('-', '_', Tools::str2url($this->value));
            $this->url_identifier_original = str_replace('-', '_', Tools::str2url($this->value));
        }
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
        if (isset($this->icon) && AdvancedSearchCoreClass::_isFilledArray($this->icon)) {
            foreach ($this->icon as $icon) {
                if ($icon && Tools::file_exists_cache(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions/'.$icon)) {
                    @unlink(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions/'.$icon);
                }
            }
        }
        As4SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id_search.'_link` WHERE `'.bqSQL($this->identifier).'` = '.(int)$this->id);
        As4SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id_search.'_list` WHERE `id_criterion_parent` = ' . (int)$this->id . ' OR `id_criterion` = ' . (int)$this->id);
        As4SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int)$this->id_search.'` WHERE `id_criterion` = '.(int)$this->id);
        return parent::delete();
    }
    public function getFields()
    {
        parent::validateFields();
        $fields = array();
        if (isset($this->id)) {
            $fields['id_criterion'] = (int)$this->id;
        }
        $fields['id_criterion_group'] = (int)$this->id_criterion_group;
        $fields['level_depth'] = (int)$this->level_depth;
        $fields['color'] = pSQL($this->color);
        $fields['visible'] = (int)$this->visible;
        $fields['id_parent'] = (int)$this->id_parent;
        $fields['position'] = (int)$this->position;
        $fields['is_custom'] = (int)$this->is_custom;
        return $fields;
    }
    public function getTranslationsFieldsChild()
    {
        parent::validateFieldsLang();
        $res = parent::getTranslationsFields(array('value', 'decimal_value', 'url_identifier', 'url_identifier_original', 'icon'));
        if (is_array($this->value)) {
            foreach ($this->value as $idLangCriterionTmp => $criterionValueTmp) {
                if ($criterionValueTmp === '0') {
                    $res[$idLangCriterionTmp]['value'] = '0';
                }
            }
        }
        if (is_array($res)) {
            foreach (array_keys($res) as $idLang) {
                $res[$idLang]['url_identifier'] = str_replace('-', '_', Tools::str2url($res[$idLang]['value']));
                $res[$idLang]['url_identifier_original'] = str_replace('-', '_', Tools::str2url($res[$idLang]['value']));
            }
        }
        return $res;
    }
    private static $getCriterionsListByIdCriterionGroupCache = array();
    public static function getCriterionsListByIdCriterionGroup($idSearch, $idCriterionGroup)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsListByIdCriterionGroupCache[$cacheKey])) {
            return self::$getCriterionsListByIdCriterionGroupCache[$cacheKey];
        }
        $results = As4SearchEngineDb::query('
        SELECT aclink.`id_criterion`, aclink.`id_criterion_linked`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $idSearch.'` acg
        JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'` ac ON (ac.`id_criterion_group` = '.(int)$idCriterionGroup.' AND ac.`id_criterion_group` = acg.`id_criterion_group`)
        JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'_link` aclink ON (ac.`is_custom` = 0 AND ac.`id_criterion` = aclink.`id_criterion`)');
        self::$getCriterionsListByIdCriterionGroupCache[$cacheKey] = array();
        if (is_array($results)) {
            foreach ($results as $row) {
                self::$getCriterionsListByIdCriterionGroupCache[$cacheKey][(int)$row['id_criterion']] = (int)$row['id_criterion_linked'];
            }
        }
        return self::$getCriterionsListByIdCriterionGroupCache[$cacheKey];
    }
    private static $getCriterionsValueListByIdCriterionGroupCache = array();
    public static function getCriterionsValueListByIdCriterionGroup($idSearch, $idCriterionGroup)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsValueListByIdCriterionGroupCache[$cacheKey])) {
            return self::$getCriterionsValueListByIdCriterionGroupCache[$cacheKey];
        }
        $defaultIdLang = (int)Configuration::get('PS_LANG_DEFAULT');
        $results = As4SearchEngineDb::query('
        SELECT ac.`id_criterion`, acl.`value`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'` ac
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int)$defaultIdLang.')
        WHERE ac.`id_criterion_group`='.(int)$idCriterionGroup.' AND ac.`is_custom` = 0');
        self::$getCriterionsValueListByIdCriterionGroupCache[$cacheKey] = array();
        if (is_array($results)) {
            foreach ($results as $row) {
                self::$getCriterionsValueListByIdCriterionGroupCache[$cacheKey][(int)$row['id_criterion']] = Tools::strtolower(trim($row['value']));
            }
        }
        return self::$getCriterionsValueListByIdCriterionGroupCache[$cacheKey];
    }
    private static $getCriterionsStaticIdCache = array();
    public static function getCriterionsStatic($id_search, $idCriterionGroup, $id_lang = false)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsStaticIdCache[$cacheKey])) {
            return self::$getCriterionsStaticIdCache[$cacheKey];
        }
        self::$getCriterionsStaticIdCache[$cacheKey] = As4SearchEngineDb::query('SELECT ac.* '.((int) $id_lang ? ', acl.*':'').'
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'` ac
        '.($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int) $id_lang.')' : '').'
        WHERE ac.`id_criterion_group` = '.(int)$idCriterionGroup);
        return self::$getCriterionsStaticIdCache[$cacheKey];
    }
    private static $getCustomCriterionsCache = array();
    public static function getCustomCriterions($idSearch, $idCriterionGroup, $idLang = false)
    {
        $cacheKey = $idSearch.'-'.(int)$idCriterionGroup.'-'.(int)$idLang;
        if (isset(self::$getCustomCriterionsCache[$cacheKey])) {
            return self::$getCustomCriterionsCache[$cacheKey];
        } else {
            $result = As4SearchEngineDb::query('SELECT ac.* '.((int) $idLang ? ', acl.*':'').'
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'` ac
        '.($idLang ? 'LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int) $idLang.')' : '').'
        WHERE ac.`is_custom`=1
        AND ac.`id_criterion_group` = '.(int)$idCriterionGroup);
        }
        self::$getCustomCriterionsCache[$cacheKey] = array();
        if (is_array($result) && sizeof($result)) {
            foreach ($result as $row) {
                self::$getCustomCriterionsCache[$cacheKey][$row['id_criterion']] = $row['value'];
            }
        }
        return self::$getCustomCriterionsCache[$cacheKey];
    }
    private static $getIdCriterionsGroupByIdCriterionCache = array();
    public static function getIdCriterionsGroupByIdCriterion($id_search, $selected_criterion, $visible = false)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionsGroupByIdCriterionCache[$cacheKey])) {
            return self::$getIdCriterionsGroupByIdCriterionCache[$cacheKey];
        }
        $results = As4SearchEngineDb::query('
        SELECT DISTINCT ac.`id_criterion_group`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'` ac
        WHERE ac.`id_criterion` IN ('.implode(',', array_map('intval', $selected_criterion)).')
        '.($visible ? ' AND `visible` = 1' : ''));
        $return = array();
        foreach ($results as $row) {
            $return[] = $row['id_criterion_group'];
        }
        self::$getIdCriterionsGroupByIdCriterionCache[$cacheKey] = $return;
        return self::$getIdCriterionsGroupByIdCriterionCache[$cacheKey];
    }
    private static $getCriterionsByIdCache = array();
    public static function getCriterionsById($id_search, $id_lang, $selected_criterion, $visible = false)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsByIdCache[$cacheKey])) {
            return self::$getCriterionsByIdCache[$cacheKey];
        }
        self::$getCriterionsByIdCache[$cacheKey] = As4SearchEngineDb::query('
        SELECT ac.* '.((int) $id_lang ? ', acl.*':'').'
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'` ac
        '.($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int) $id_lang.')' : '').'
        WHERE ac.`id_criterion` IN ('.implode(',', array_map('intval', $selected_criterion)).')
        '.($visible ? ' AND `visible` = 1' : ''));
        return self::$getCriterionsByIdCache[$cacheKey];
    }
    private static $getCriterionValueByIdCache = array();
    public static function getCriterionValueById($id_search, $id_lang, $id_criterion)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionValueByIdCache[$cacheKey])) {
            return self::$getCriterionValueByIdCache[$cacheKey];
        }
        self::$getCriterionValueByIdCache[$cacheKey] = As4SearchEngineDb::row('
                SELECT ac.`id_criterion`, acl.`value`, ac.`visible`
                FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'` ac
                LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int) $id_lang.')
                WHERE ac.`id_criterion` = '.(int)$id_criterion);
        return self::$getCriterionValueByIdCache[$cacheKey];
    }
    private static $getIdCriterionByTypeAndIdLinkedCache = array();
    public static function getIdCriterionByTypeAndIdLinked($id_search, $idCriterionGroup, $id_criterion_linked)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionByTypeAndIdLinkedCache[$cacheKey])) {
            return self::$getIdCriterionByTypeAndIdLinkedCache[$cacheKey];
        }
        $row = As4SearchEngineDb::row('
        SELECT ac.`id_criterion`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'` ac
        JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'_link` aclink ON (ac.`is_custom` = 0 AND ac.`id_criterion` = aclink.`id_criterion`)
        WHERE ac.`id_criterion_group` = '.(int)$idCriterionGroup.' AND aclink.`id_criterion_linked` = '.(int)$id_criterion_linked);
        if (isset($row['id_criterion']) and $row['id_criterion']) {
            self::$getIdCriterionByTypeAndIdLinkedCache[$cacheKey] = (int)$row['id_criterion'];
            return self::$getIdCriterionByTypeAndIdLinkedCache[$cacheKey];
        }
        return 0;
    }
    private static $getIdCriterionByTypeAndValueCache = array();
    public static function getIdCriterionByTypeAndValue($id_search, $idCriterionGroup, $id_lang, $criterion_value)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionByTypeAndValueCache[$cacheKey])) {
            return self::$getIdCriterionByTypeAndValueCache[$cacheKey];
        }
        $row = As4SearchEngineDb::row('
        SELECT ac.`id_criterion`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'` ac
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int) $id_lang.')
        WHERE ac.`id_criterion_group` = '.(int)$idCriterionGroup.'
        AND TRIM(acl.`value`) LIKE "'.pSQL(trim($criterion_value)).'"');
        if (isset($row['id_criterion']) and $row['id_criterion']) {
            self::$getIdCriterionByTypeAndValueCache[$cacheKey] = (int)$row['id_criterion'];
            return self::$getIdCriterionByTypeAndValueCache[$cacheKey];
        }
        return 0;
    }
    private static $getIdCriteriongByURLIdentifierCache = array();
    public static function getIdCriteriongByURLIdentifier($idSearch, $idCriterionGroup, $idLang, $name)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriteriongByURLIdentifierCache[$cacheKey])) {
            return self::$getIdCriteriongByURLIdentifierCache[$cacheKey];
        }
        $idCriterion = As4SearchEngineDb::value('
            SELECT ac.`id_criterion`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_' . (int)$idSearch . '` ac
            JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_' . (int)$idSearch . '_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = ' . (int)$idLang . ')
            WHERE ac.`id_criterion_group` = ' . (int)$idCriterionGroup.'
            AND acl.`url_identifier`="'. pSQL($name) .'"');
        if ($idCriterion) {
            self::$getIdCriteriongByURLIdentifierCache[$cacheKey] = (int)$idCriterion;
            return self::$getIdCriteriongByURLIdentifierCache[$cacheKey];
        }
        return 0;
    }
    private static $getIdCriterionGroupByIdCriterionCache = array();
    public static function getIdCriterionGroupByIdCriterion($id_search, $id_criterion)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionGroupByIdCriterionCache[$cacheKey])) {
            return self::$getIdCriterionGroupByIdCriterionCache[$cacheKey];
        }
        $row = As4SearchEngineDb::row('
        SELECT ac.`id_criterion_group`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'` ac
        WHERE ac.`id_criterion` = "'.(int)($id_criterion).'"');
        if (isset($row['id_criterion_group']) and $row['id_criterion_group']) {
            self::$getIdCriterionGroupByIdCriterionCache[$cacheKey] = (int)$row['id_criterion_group'];
            return self::$getIdCriterionGroupByIdCriterionCache[$cacheKey];
        }
        return 0;
    }
    public static function getCustomCriterionsLinkIds($idSearch, $criterions, $uniqueValues = true)
    {
        static $getCustomCriterionsLinkIdsCache = array();
        if (!isset($getCustomCriterionsLinkIdsCache[$idSearch])) {
            $result = As4SearchEngineDb::query('
            SELECT aclist.`id_criterion_parent`, aclist.`id_criterion`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'` ac
            JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'_list` aclist ON (ac.`id_criterion` = aclist.`id_criterion_parent`)
            WHERE ac.`is_custom`=1');
            if (is_array($result) && sizeof($result)) {
                foreach ($result as $row) {
                    $getCustomCriterionsLinkIdsCache[$idSearch][(int)$row['id_criterion_parent']][] = (int)$row['id_criterion'];
                }
            } else {
                $getCustomCriterionsLinkIdsCache[$idSearch] = array();
            }
        }
        $listToReturn = array();
        $uniqueListToReturn = array();
        foreach ($criterions as $idCriterion) {
            if (!isset($listToReturn[$idCriterion])) {
                $listToReturn[$idCriterion] = array();
            }
            if (isset($getCustomCriterionsLinkIdsCache[$idSearch][$idCriterion])) {
                $listToReturn[$idCriterion] += $getCustomCriterionsLinkIdsCache[$idSearch][$idCriterion];
                $uniqueListToReturn = array_merge($uniqueListToReturn, $getCustomCriterionsLinkIdsCache[$idSearch][$idCriterion]);
            } else {
                $listToReturn[$idCriterion] += array($idCriterion);
                $uniqueListToReturn = array_merge($uniqueListToReturn, array($idCriterion));
            }
        }
        if ($uniqueValues) {
            return array_unique($uniqueListToReturn);
        } else {
            return $listToReturn;
        }
    }
    private static $getCustomCriterionsLinkIdsByGroupCache = array();
    public static function getCustomCriterionsLinkIdsByGroup($idSearch, $idCriterionGroup)
    {
        $cacheKey = $idSearch.'-'.$idCriterionGroup;
        if (isset(self::$getCustomCriterionsLinkIdsByGroupCache[$cacheKey])) {
            return self::$getCustomCriterionsLinkIdsByGroupCache[$cacheKey];
        } else {
            $result = As4SearchEngineDb::query('
        SELECT aclist.`id_criterion_parent`, aclist.`id_criterion`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'` ac
        JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'_list` aclist ON (ac.`id_criterion` = aclist.`id_criterion_parent`)
        WHERE ac.`is_custom`=1 AND ac.`id_criterion_group`=' . (int)$idCriterionGroup);
        }
        self::$getCustomCriterionsLinkIdsByGroupCache[$cacheKey] = array();
        if (is_array($result) && sizeof($result)) {
            foreach ($result as $row) {
                self::$getCustomCriterionsLinkIdsByGroupCache[$cacheKey][(int)$row['id_criterion_parent']][] = (int)$row['id_criterion'];
            }
        }
        return self::$getCustomCriterionsLinkIdsByGroupCache[$cacheKey];
    }
    private static $getCustomMasterIdCriterionCache = array();
    public static function getCustomMasterIdCriterion($idSearch, $idCriterion)
    {
        $cacheKey = $idSearch.'-'.(int)$idCriterion;
        if (isset(self::$getCustomMasterIdCriterionCache[$cacheKey])) {
            return self::$getCustomMasterIdCriterionCache[$cacheKey];
        } else {
            $result = As4SearchEngineDb::value('SELECT aclist.`id_criterion_parent`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'` ac
        JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'_list` aclist ON (ac.`id_criterion` = aclist.`id_criterion_parent`)
        WHERE ac.`is_custom`=1 AND aclist.`id_criterion`='.(int)$idCriterion);
        }
        if ($result > 0) {
            self::$getCustomMasterIdCriterionCache[$cacheKey] = (int)$result;
        } else {
            self::$getCustomMasterIdCriterionCache[$cacheKey] = false;
        }
        return self::$getCustomMasterIdCriterionCache[$cacheKey];
    }
    private static $getIdCriterionLinkByIdCriterionCache = array();
    public static function getIdCriterionLinkByIdCriterion($id_search, $criterionsList)
    {
        if (!is_array($criterionsList)) {
            $criterionsList = array((int)$criterionsList);
        }
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionLinkByIdCriterionCache[$cacheKey])) {
            return self::$getIdCriterionLinkByIdCriterionCache[$cacheKey];
        }
        $row = As4SearchEngineDb::row('
        SELECT GROUP_CONCAT(`id_criterion_linked`) as `id_criterion_linked`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_' . (int)$id_search . '_link`
        WHERE `id_criterion` IN (' . implode(', ', array_map('intval', $criterionsList)) . ')');
        self::$getIdCriterionLinkByIdCriterionCache[$cacheKey] = isset($row['id_criterion_linked']) ? array_map('intval', explode(',', $row['id_criterion_linked'])) : false;
        return self::$getIdCriterionLinkByIdCriterionCache[$cacheKey];
    }
    private static $getCriterionsByCategoryParentIdCache = array();
    public static function getCriterionsByCategoryParentId($id_search, $id_criterion_group, $id_lang, $id_parent, $level_depth = false)
    {
        $cacheKey = (int)$id_search.'-'.(int)$id_criterion_group.'-'.(int)$id_lang.'-'.(int)$id_parent;
        if (isset(self::$getCriterionsByCategoryParentIdCache[$cacheKey])) {
            return self::$getCriterionsByCategoryParentIdCache[$cacheKey];
        } else {
            $result = As4SearchEngineDb::query('
            SELECT ac.`id_criterion`, acl.`value`, ac.`id_parent`, ac.`level_depth`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'` ac
            LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int) $id_lang.')
            WHERE ac.`id_criterion_group` = '.(int)$id_criterion_group.'
            AND ac.`id_parent` = '.(int)$id_parent.'
            ORDER BY '.($level_depth ? 'ac.`level_depth`, ' : '').' ac.`position`');
        }
        self::$getCriterionsByCategoryParentIdCache[$cacheKey] = array();
        if (is_array($result) && sizeof($result)) {
            foreach ($result as $row) {
                $row['nb_product'] = As4SearchEngineDb::value('
                    SELECT COUNT(`id_cache_product`) as `nb_product`
                    FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int) $id_search.'` acpc
                    WHERE acpc.`id_criterion` = '.(int)$row['id_criterion'].'
                ');
                self::$getCriterionsByCategoryParentIdCache[$cacheKey][] = $row;
            }
        }
        return self::$getCriterionsByCategoryParentIdCache[$cacheKey];
    }
    public static function addCriterionToList($idSearch, $idCriterionParent, $idCriterion)
    {
        return As4SearchEngineDb::execute('
        INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_list`
        (`id_criterion_parent`, `id_criterion`)
        VALUES ('. (int)$idCriterionParent. ', '. (int)$idCriterion .')');
    }
    public static function removeCriterionFromList($idSearch, $idCriterionParent, $idCriterion)
    {
        return As4SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_list` WHERE `id_criterion_parent`='.(int)$idCriterionParent . ' AND `id_criterion`='.(int)$idCriterion);
    }
    public static function populateCriterionsLink($idSearch, $idCriterion, $idCriterionLinked = false, $criterionsGroupList = array())
    {
        As4SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link` WHERE `id_criterion` = '.(int)$idCriterion);
        if (!$idCriterionLinked && is_array($criterionsGroupList) && sizeof($criterionsGroupList)) {
            As4SearchEngineDb::execute('INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link` (`id_criterion`, `id_criterion_linked`)
            (SELECT "'. (int)$idCriterion .'" AS `id_criterion`, `id_criterion_linked` FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link` WHERE `id_criterion` IN (' . implode(',', array_map('intval', $criterionsGroupList)) . '))');
        } elseif ($idCriterionLinked || is_array($idCriterionLinked) && sizeof($idCriterionLinked)) {
            if (!is_array($idCriterionLinked)) {
                $idCriterionLinked = array($idCriterionLinked);
            }
            foreach ($idCriterionLinked as $idCriterionLinkedValue) {
                As4SearchEngineDb::execute('INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link` (`id_criterion`, `id_criterion_linked`) VALUES ('. (int)$idCriterion. ', '. (int)$idCriterionLinkedValue .')');
            }
        } elseif (!$idCriterionLinked) {
            As4SearchEngineDb::execute('INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link` (`id_criterion`, `id_criterion_linked`) VALUES ('. (int)$idCriterion. ', 0)');
        }
    }
    public static function forceUniqueUrlIdentifier($idSearch, $idCriterionGroup)
    {
        As4SearchEngineDb::setGroupConcatMaxLength();
        $duplicateIdentifier = As4SearchEngineDb::query('
            SELECT acl.`id_lang`, acl.`url_identifier_original`, GROUP_CONCAT(ac.`id_criterion`) as `id_criterion_list`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_' . (int)$idSearch . '` ac
            JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_' . (int)$idSearch . '_lang` acl ON (ac.`id_criterion` = acl.`id_criterion`)
            WHERE ac.`id_criterion_group` = ' . (int)$idCriterionGroup.'
            GROUP BY acl.`id_lang`, acl.`url_identifier_original`
            HAVING COUNT(*) > 1');
        foreach ($duplicateIdentifier as $duplicateIdentifierRow) {
            $duplicateIdentifierRow['id_criterion_list'] = rtrim($duplicateIdentifierRow['id_criterion_list'], ',');
            As4SearchEngineDb::execute('SET @i=0');
            As4SearchEngineDb::execute('
            UPDATE `'._DB_PREFIX_.'pm_advancedsearch_criterion_' . (int)$idSearch . '_lang` acl
            SET acl.url_identifier = IF ((@i:=@i+1) > 1,  CONCAT(acl.`url_identifier_original`, "_", @i), acl.`url_identifier_original` )
            WHERE acl.`id_criterion` IN (' . pSQL($duplicateIdentifierRow['id_criterion_list']) . ')
            AND acl.`id_lang` = ' . (int)$duplicateIdentifierRow['id_lang']);
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
        self::$getCriterionsListByIdCriterionGroupCache = array();
        self::$getCriterionsStaticIdCache = array();
        self::$getCustomCriterionsCache = array();
        self::$getIdCriterionsGroupByIdCriterionCache = array();
        self::$getCriterionsByIdCache = array();
        self::$getCriterionValueByIdCache = array();
        self::$getIdCriterionByTypeAndIdLinkedCache = array();
        self::$getIdCriterionByTypeAndValueCache = array();
        self::$getIdCriterionGroupByIdCriterionCache = array();
        self::$getCustomCriterionsLinkIdsByGroupCache = array();
        self::$getCustomMasterIdCriterionCache = array();
        self::$getIdCriterionLinkByIdCriterionCache = array();
        parent::clearCache($all);
    }
    public function getHashIdentifier()
    {
        $idCriterionLinked = (is_array($this->id_criterion_linked) ? array_map('intval', $this->id_criterion_linked) : array((int)$this->id_criterion_linked));
        sort($idCriterionLinked);
        if (is_array($this->value)) {
            ksort($this->value);
        }
        return sha1(serialize(array(
            'id' => (int)$this->id,
            'id_criterion_group' => (int)$this->id_criterion_group,
            'id_criterion_linked' => $idCriterionLinked,
            'value' => $this->value,
            'decimal_value' => array_map('floatval', $this->decimal_value),
            'url_identifier' => $this->url_identifier,
            'url_identifier_original' => $this->url_identifier_original,
            'icon' => $this->icon,
            'color' => trim($this->color),
            'visible' => (int)$this->visible,
            'level_depth' => (int)$this->level_depth,
            'id_parent' => (int)$this->id_parent,
            'position' => (int)$this->position,
            'is_custom' => (int)$this->is_custom,
        )));
    }
}
