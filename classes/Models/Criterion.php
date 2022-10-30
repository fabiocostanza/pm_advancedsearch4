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
use Configuration;
use AdvancedSearch\Core;
use AdvancedSearch\SearchEngineDb;
use AdvancedSearch\SearchEngineIndexation;
class Criterion extends ObjectModel
{
    public $id;
    public $id_search;
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
    protected $tables = array(
        'pm_advancedsearch_criterion',
        'pm_advancedsearch_criterion_lang',
    );
    protected $originalTables = array(
        'pm_advancedsearch_criterion',
        'pm_advancedsearch_criterion_lang',
    );
    protected $originalTable = 'pm_advancedsearch_criterion';
    protected $table = 'pm_advancedsearch_criterion';
    public $identifier = 'id_criterion';
    public static $definition = array(
        'table' => 'pm_advancedsearch_criterion',
        'primary' => 'id_criterion',
        'multishop' => false,
        'multilang' => true,
        'fields' => array(
            'id_criterion_group' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'level_depth' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'color' => array('type' => self::TYPE_STRING, 'size' => 255),
            'visible' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_parent' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'is_custom' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'value' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 255),
            'decimal_value' => array('type' => self::TYPE_STRING, 'lang' => true),
            'url_identifier' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 255),
            'url_identifier_original' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 255),
            'icon' => array('type' => self::TYPE_STRING, 'lang' => true, 'size' => 32),
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
    public function __construct($idCriterion = null, $idSearch = null, $idLang = null, $idShop = null)
    {
        $this->overrideTableDefinition((int)$idSearch);
        parent::__construct($idCriterion, $idLang, $idShop);
        if ($this->id && !isset($this->id_criterion_linked)) {
            $id_criterion_link = self::getIdCriterionLinkByIdCriterion($this->id_search, $this->id);
            if (!empty($id_criterion_link)) {
                $this->id_criterion_linked = $id_criterion_link;
            }
            unset($id_criterion_link);
        }
    }
    public function save($nullValues = false, $autoDate = true)
    {
        $this->setUrlIdentifier();
        $saveResult = parent::save($nullValues, $autoDate);
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
        if (isset($this->icon) && Core::isFilledArray($this->icon)) {
            foreach ($this->icon as $icon) {
                if ($icon && Tools::file_exists_cache(_PS_ROOT_DIR_ . '/modules/' . _PM_AS_MODULE_NAME_ . '/search_files/criterions/'.$icon)) {
                    @unlink(_PS_ROOT_DIR_ . '/modules/' . _PM_AS_MODULE_NAME_ . '/search_files/criterions/'.$icon);
                }
            }
        }
        SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id_search.'_link` WHERE `'.bqSQL($this->identifier).'` = '.(int)$this->id);
        SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$this->id_search.'_list` WHERE `id_criterion_parent` = ' . (int)$this->id . ' OR `id_criterion` = ' . (int)$this->id);
        SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int)$this->id_search.'` WHERE `id_criterion` = '.(int)$this->id);
        return parent::delete();
    }
    private static $getCriterionsListByIdCriterionGroupCache = array();
    public static function getCriterionsListByIdCriterionGroup(int $idSearch, int $idCriterionGroup)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsListByIdCriterionGroupCache[$cacheKey])) {
            return self::$getCriterionsListByIdCriterionGroupCache[$cacheKey];
        }
        $results = SearchEngineDb::query('
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
    public static function getCriterionsValueListByIdCriterionGroup(int $idSearch, int $idCriterionGroup)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsValueListByIdCriterionGroupCache[$cacheKey])) {
            return self::$getCriterionsValueListByIdCriterionGroupCache[$cacheKey];
        }
        $defaultIdLang = (int)Configuration::get('PS_LANG_DEFAULT');
        $results = SearchEngineDb::query('
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
    public static function getCriterionsStatic(int $idSearch, int $idCriterionGroup, int $idLang = null)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsStaticIdCache[$cacheKey])) {
            return self::$getCriterionsStaticIdCache[$cacheKey];
        }
        self::$getCriterionsStaticIdCache[$cacheKey] = SearchEngineDb::query('SELECT ac.* '.((int)$idLang ? ', acl.*':'').'
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'` ac
        '.($idLang ? 'LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int)$idLang.')' : '').'
        WHERE ac.`id_criterion_group` = '.(int)$idCriterionGroup);
        return self::$getCriterionsStaticIdCache[$cacheKey];
    }
    private static $getCustomCriterionsCache = array();
    public static function getCustomCriterions(int $idSearch, int $idCriterionGroup, int $idLang = null)
    {
        $cacheKey = $idSearch.'-'.(int)$idCriterionGroup.'-'.(int)$idLang;
        if (isset(self::$getCustomCriterionsCache[$cacheKey])) {
            return self::$getCustomCriterionsCache[$cacheKey];
        } else {
            $result = SearchEngineDb::query('SELECT ac.* '.((int) $idLang ? ', acl.*':'').'
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
    private static $getCriterionValueByIdCache = array();
    public static function getCriterionValueById(int $idSearch, int $idLang, int $idCriterion)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionValueByIdCache[$cacheKey])) {
            return self::$getCriterionValueByIdCache[$cacheKey];
        }
        self::$getCriterionValueByIdCache[$cacheKey] = SearchEngineDb::row('
                SELECT ac.`id_criterion`, acl.`value`, ac.`visible`
                FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'` ac
                LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int)$idLang.')
                WHERE ac.`id_criterion` = '.(int)$idCriterion);
        return self::$getCriterionValueByIdCache[$cacheKey];
    }
    private static $getIdCriterionByTypeAndIdLinkedCache = array();
    public static function getIdCriterionByTypeAndIdLinked(int $idSearch, int $idCriterionGroup, int $idCriterionLinked)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionByTypeAndIdLinkedCache[$cacheKey])) {
            return self::$getIdCriterionByTypeAndIdLinkedCache[$cacheKey];
        }
        $row = SearchEngineDb::row('
        SELECT ac.`id_criterion`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'` ac
        JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link` aclink ON (ac.`is_custom` = 0 AND ac.`id_criterion` = aclink.`id_criterion`)
        WHERE ac.`id_criterion_group` = '.(int)$idCriterionGroup.' AND aclink.`id_criterion_linked` = '.(int)$idCriterionLinked);
        if (isset($row['id_criterion']) and $row['id_criterion']) {
            self::$getIdCriterionByTypeAndIdLinkedCache[$cacheKey] = (int)$row['id_criterion'];
            return self::$getIdCriterionByTypeAndIdLinkedCache[$cacheKey];
        }
        return 0;
    }
    private static $getIdCriterionByTypeAndValueCache = array();
    public static function getIdCriterionByTypeAndValue(int $idSearch, int $idCriterionGroup, int $idLang, string $criterionValue)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionByTypeAndValueCache[$cacheKey])) {
            return self::$getIdCriterionByTypeAndValueCache[$cacheKey];
        }
        $row = SearchEngineDb::row('
        SELECT ac.`id_criterion`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'` ac
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int)$idLang.')
        WHERE ac.`id_criterion_group` = '.(int)$idCriterionGroup.'
        AND TRIM(acl.`value`) LIKE "'.pSQL(trim($criterionValue)).'"');
        if (isset($row['id_criterion']) and $row['id_criterion']) {
            self::$getIdCriterionByTypeAndValueCache[$cacheKey] = (int)$row['id_criterion'];
            return self::$getIdCriterionByTypeAndValueCache[$cacheKey];
        }
        return 0;
    }
    private static $getIdCriteriongByURLIdentifierCache = array();
    public static function getIdCriteriongByURLIdentifier(int $idSearch, int $idCriterionGroup, int $idLang, string $urlIdentifier)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriteriongByURLIdentifierCache[$cacheKey])) {
            return self::$getIdCriteriongByURLIdentifierCache[$cacheKey];
        }
        $idCriterion = SearchEngineDb::value('
            SELECT ac.`id_criterion`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_' . (int)$idSearch . '` ac
            JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_' . (int)$idSearch . '_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = ' . (int)$idLang . ')
            WHERE ac.`id_criterion_group` = ' . (int)$idCriterionGroup.'
            AND acl.`url_identifier`="'. pSQL($urlIdentifier) .'"');
        if ($idCriterion) {
            self::$getIdCriteriongByURLIdentifierCache[$cacheKey] = (int)$idCriterion;
            return self::$getIdCriteriongByURLIdentifierCache[$cacheKey];
        }
        return 0;
    }
    private static $getIdCriterionGroupByIdCriterionCache = array();
    public static function getIdCriterionGroupByIdCriterion(int $idSearch, int $idCriterion)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionGroupByIdCriterionCache[$cacheKey])) {
            return self::$getIdCriterionGroupByIdCriterionCache[$cacheKey];
        }
        $row = SearchEngineDb::row('
        SELECT ac.`id_criterion_group`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'` ac
        WHERE ac.`id_criterion` = "'.(int)$idCriterion.'"');
        if (isset($row['id_criterion_group']) and $row['id_criterion_group']) {
            self::$getIdCriterionGroupByIdCriterionCache[$cacheKey] = (int)$row['id_criterion_group'];
            return self::$getIdCriterionGroupByIdCriterionCache[$cacheKey];
        }
        return 0;
    }
    public static function getCustomCriterionsLinkIds(int $idSearch, array $criterions, bool $uniqueValues)
    {
        static $getCustomCriterionsLinkIdsCache = array();
        if (!isset($getCustomCriterionsLinkIdsCache[$idSearch])) {
            $result = SearchEngineDb::query('
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
    public static function getCustomCriterionsLinkIdsByGroup(int $idSearch, int $idCriterionGroup)
    {
        $cacheKey = $idSearch.'-'.$idCriterionGroup;
        if (isset(self::$getCustomCriterionsLinkIdsByGroupCache[$cacheKey])) {
            return self::$getCustomCriterionsLinkIdsByGroupCache[$cacheKey];
        }
        $result = SearchEngineDb::query('
        SELECT aclist.`id_criterion_parent`, aclist.`id_criterion`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'` ac
        JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'_list` aclist ON (ac.`id_criterion` = aclist.`id_criterion_parent`)
        WHERE ac.`is_custom`=1 AND ac.`id_criterion_group`=' . (int)$idCriterionGroup);
        self::$getCustomCriterionsLinkIdsByGroupCache[$cacheKey] = array();
        if (is_array($result) && sizeof($result)) {
            foreach ($result as $row) {
                self::$getCustomCriterionsLinkIdsByGroupCache[$cacheKey][(int)$row['id_criterion_parent']][] = (int)$row['id_criterion'];
            }
        }
        return self::$getCustomCriterionsLinkIdsByGroupCache[$cacheKey];
    }
    private static $getCustomMasterIdCriterionCache = array();
    public static function getCustomMasterIdCriterion(int $idSearch, int $idCriterion)
    {
        $cacheKey = $idSearch.'-'.(int)$idCriterion;
        if (isset(self::$getCustomMasterIdCriterionCache[$cacheKey])) {
            return self::$getCustomMasterIdCriterionCache[$cacheKey];
        }
        $result = SearchEngineDb::value('SELECT aclist.`id_criterion_parent`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'` ac
        JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $idSearch.'_list` aclist ON (ac.`id_criterion` = aclist.`id_criterion_parent`)
        WHERE ac.`is_custom`=1 AND aclist.`id_criterion`='.(int)$idCriterion);
        if ($result > 0) {
            self::$getCustomMasterIdCriterionCache[$cacheKey] = (int)$result;
        } else {
            self::$getCustomMasterIdCriterionCache[$cacheKey] = false;
        }
        return self::$getCustomMasterIdCriterionCache[$cacheKey];
    }
    private static $getIdCriterionLinkByIdCriterionCache = array();
    public static function getIdCriterionLinkByIdCriterion(int $idSearch, $criterionsList)
    {
        if (!is_array($criterionsList)) {
            $criterionsList = array((int)$criterionsList);
        }
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getIdCriterionLinkByIdCriterionCache[$cacheKey])) {
            return self::$getIdCriterionLinkByIdCriterionCache[$cacheKey];
        }
        $row = SearchEngineDb::row('
        SELECT GROUP_CONCAT(`id_criterion_linked`) as `id_criterion_linked`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_' . (int)$idSearch . '_link`
        WHERE `id_criterion` IN (' . implode(', ', array_map('intval', $criterionsList)) . ')');
        self::$getIdCriterionLinkByIdCriterionCache[$cacheKey] = isset($row['id_criterion_linked']) ? array_map('intval', explode(',', $row['id_criterion_linked'])) : array();
        return self::$getIdCriterionLinkByIdCriterionCache[$cacheKey];
    }
    public static function addCriterionToList(int $idSearch, int $idCriterionParent, int $idCriterion)
    {
        return SearchEngineDb::execute('
        INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_list`
        (`id_criterion_parent`, `id_criterion`)
        VALUES ('. (int)$idCriterionParent. ', '. (int)$idCriterion .')');
    }
    public static function removeCriterionFromList($idSearch, $idCriterionParent, $idCriterion)
    {
        return SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_list` WHERE `id_criterion_parent`='.(int)$idCriterionParent . ' AND `id_criterion`='.(int)$idCriterion);
    }
    public static function populateCriterionsLink(int $idSearch, int $idCriterion, $idCriterionLinked = false, array $criterionsGroupList = array())
    {
        $result = SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link` WHERE `id_criterion` = '.(int)$idCriterion);
        if (!$idCriterionLinked && is_array($criterionsGroupList) && sizeof($criterionsGroupList)) {
            $result &= SearchEngineDb::execute('INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link` (`id_criterion`, `id_criterion_linked`) (
                SELECT "'. (int)$idCriterion .'" AS `id_criterion`, `id_criterion_linked`
                FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link`
                WHERE `id_criterion` IN (' . implode(',', array_map('intval', $criterionsGroupList)) . ')
            )');
        } elseif ($idCriterionLinked || is_array($idCriterionLinked) && sizeof($idCriterionLinked)) {
            if (!is_array($idCriterionLinked)) {
                $idCriterionLinked = array($idCriterionLinked);
            }
            foreach ($idCriterionLinked as $idCriterionLinkedValue) {
                $result &= SearchEngineDb::execute('INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link` (`id_criterion`, `id_criterion_linked`) VALUES ('. (int)$idCriterion. ', '. (int)$idCriterionLinkedValue .')');
            }
        } elseif (!$idCriterionLinked) {
            $result &= SearchEngineDb::execute('INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link` (`id_criterion`, `id_criterion_linked`) VALUES ('. (int)$idCriterion. ', 0)');
        }
        return $result;
    }
    public static function forceUniqueUrlIdentifier(int $idSearch, int $idCriterionGroup)
    {
        SearchEngineDb::setGroupConcatMaxLength();
        $duplicateIdentifier = SearchEngineDb::query('
            SELECT acl.`id_lang`, acl.`url_identifier_original`, GROUP_CONCAT(ac.`id_criterion`) as `id_criterion_list`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_' . (int)$idSearch . '` ac
            JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_' . (int)$idSearch . '_lang` acl ON (ac.`id_criterion` = acl.`id_criterion`)
            WHERE ac.`id_criterion_group` = ' . (int)$idCriterionGroup.'
            GROUP BY acl.`id_lang`, acl.`url_identifier_original`
            HAVING COUNT(*) > 1');
        foreach ($duplicateIdentifier as $duplicateIdentifierRow) {
            $duplicateIdentifierRow['id_criterion_list'] = rtrim($duplicateIdentifierRow['id_criterion_list'], ',');
            SearchEngineDb::execute('SET @i=0');
            SearchEngineDb::execute('
            UPDATE `'._DB_PREFIX_.'pm_advancedsearch_criterion_' . (int)$idSearch . '_lang` acl
            SET acl.url_identifier = IF ((@i:=@i+1) > 1,  CONCAT(acl.`url_identifier_original`, "_", @i), acl.`url_identifier_original` )
            WHERE acl.`id_criterion` IN (' . pSQL($duplicateIdentifierRow['id_criterion_list']) . ')
            AND acl.`id_lang` = ' . (int)$duplicateIdentifierRow['id_lang']);
        }
    }
    public function clearCache($all = false)
    {
        if (SearchEngineIndexation::$processingIndexation) {
            return;
        }
        parent::clearCache($all);
    }
    public function as4ForceClearCache(bool $all)
    {
        self::$getCriterionsListByIdCriterionGroupCache = array();
        self::$getCriterionsValueListByIdCriterionGroupCache = array();
        self::$getCriterionsStaticIdCache = array();
        self::$getCustomCriterionsCache = array();
        self::$getCriterionValueByIdCache = array();
        self::$getIdCriterionByTypeAndIdLinkedCache = array();
        self::$getIdCriterionByTypeAndValueCache = array();
        self::$getIdCriteriongByURLIdentifierCache = array();
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
