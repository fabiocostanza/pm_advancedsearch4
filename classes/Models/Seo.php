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
use Tools;
use Context;
use ObjectModel;
use Configuration;
use AdvancedSearch\Core;
use AdvancedSearch\SearchEngineDb;
class Seo extends ObjectModel
{
    public $id;
    public $id_search;
    public $id_currency;
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    public $title;
    public $seo_url;
    public $description;
    public $footer_description;
    public $criteria;
    public $deleted;
    public $seo_key;
    public $cross_links;
    protected $tables = array(
        'pm_advancedsearch_seo',
        'pm_advancedsearch_seo_lang',
    );
    public static $definition = array(
        'table' => 'pm_advancedsearch_seo',
        'primary' => 'id_seo',
        'multishop' => false,
        'multilang' => true,
        'fields' => array(
            'id_search' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'id_currency' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'criteria' => array('type' => self::TYPE_STRING, 'required' => true),
            'seo_key' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 32),
            'deleted' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'meta_title' => array('type' => self::TYPE_STRING, 'required' => true, 'lang' => true, 'size' => 128),
            'meta_description' => array('type' => self::TYPE_STRING, 'required' => true, 'lang' => true, 'size' => 255),
            'title' => array('type' => self::TYPE_STRING, 'required' => true, 'lang' => true, 'size' => 128),
            'seo_url' => array('type' => self::TYPE_STRING, 'required' => true, 'lang' => true, 'size' => 128),
            'meta_keywords' => array('type' => self::TYPE_STRING, 'lang' => true, 'size' => 255),
            'description' => array('type' => self::TYPE_HTML, 'lang' => true),
            'footer_description' => array('type' => self::TYPE_HTML, 'lang' => true),
        )
    );
    protected $table = 'pm_advancedsearch_seo';
    public $identifier = 'id_seo';
    public function __construct($id_seo = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id_seo, $id_lang, $id_shop);
    }
    public function setUrlIdentifier()
    {
        if (is_array($this->seo_url)) {
            foreach (array_keys($this->seo_url) as $idLang) {
                $this->seo_url[$idLang] = Tools::str2url($this->seo_url[$idLang]);
            }
        } else {
            $this->seo_url = Tools::str2url($this->seo_url);
        }
    }
    public function save($nullValues = false, $autodate = true)
    {
        $newCriteria = array();
        if (is_string($this->criteria)) {
            $this->criteria = Core::decodeCriteria($this->criteria);
        }
        $this->criteria = Core::encodeCriteria($this->criteria);
        if ($this->id) {
            $this->cleanCrossLinks();
        }
        if (!$this->id_currency) {
            $this->id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        }
        $this->setUrlIdentifier();
        $ret = parent::save($nullValues, $autodate);
        if (is_array($this->cross_links) && sizeof($this->cross_links)) {
            $this->saveCrossLinks();
        }
        return $ret;
    }
    public function delete()
    {
        $this->cleanCrossLinks(true);
        return parent::delete();
    }
    public static function deleteByIdSearch($id_search)
    {
        SearchEngineDb::execute('DELETE adss.*, adssl.*, ascl.*  FROM `'._DB_PREFIX_.'pm_advancedsearch_seo` adss
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_seo_lang` adssl ON (adss.`id_seo` = adssl.`id_seo` )
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_seo_crosslinks` ascl ON (ascl.`id_seo_linked` = adssl.`id_seo` )
        WHERE `id_search` = '.(int)$id_search);
    }
    public function cleanCrossLinks($delete_seo_linked = false)
    {
        SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_seo_crosslinks` WHERE `id_seo` = '.(int)$this->id . ($delete_seo_linked ? ' OR `id_seo_linked` = '.(int)$this->id : ''));
    }
    public function saveCrossLinks()
    {
        foreach ($this->cross_links as $id_seo_linked) {
            $row = array('id_seo' => (int)$this->id, 'id_seo_linked' => (int)$id_seo_linked);
            Db::getInstance()->insert('pm_advancedsearch_seo_crosslinks', $row);
        }
    }
    private static $getCrossLinksOptionsSelectedCache = array();
    public function getCrossLinksOptionsSelected($id_lang)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCrossLinksOptionsSelectedCache[$cacheKey])) {
            return self::$getCrossLinksOptionsSelectedCache[$cacheKey];
        }
        $result = SearchEngineDb::query('
        SELECT ascl.`id_seo_linked`, adssl.`title`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_seo_crosslinks` ascl
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_seo_lang` adssl ON (ascl.`id_seo_linked` = adssl.`id_seo` AND adssl.`id_lang` = '.((int) $id_lang).' )
        WHERE ascl.`id_seo` = '.(int)($this->id));
        $return = array();
        foreach ($result as $row) {
            $return[$row['id_seo_linked']] = $row['title'];
        }
        self::$getCrossLinksOptionsSelectedCache[$cacheKey] = $return;
        return self::$getCrossLinksOptionsSelectedCache[$cacheKey];
    }
    private static $getCrossLinksAvailableCache = array();
    public static function getCrossLinksAvailable($id_lang, $id_excludes = false, $query_search = false, $count = false, $limit = false, $start = 0)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCrossLinksAvailableCache[$cacheKey])) {
            return self::$getCrossLinksAvailableCache[$cacheKey];
        }
        if ($count) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
            SELECT COUNT(adss.`id_seo`) AS nb
            FROM `'._DB_PREFIX_.'pm_advancedsearch_seo` adss
            LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_seo_lang` adssl ON (adss.`id_seo` = adssl.`id_seo` AND adssl.`id_lang` = '.((int) $id_lang).' )
            WHERE '.($id_excludes ? ' adss.`id_seo` NOT IN ('.implode(',', array_map('intval', $id_excludes)).') AND ':'').'adss.`deleted` = 0
            '.($query_search ? ' AND adssl.`title` LIKE "%'.pSQL($query_search).'%"' : '').'
            ORDER BY adss.`id_seo`');
            self::$getCrossLinksAvailableCache[$cacheKey] = (int)($result['nb']);
            return self::$getCrossLinksAvailableCache[$cacheKey];
        }
        $result = SearchEngineDb::query('
        SELECT adss.`id_seo`, adssl.`title`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_seo` adss
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_seo_lang` adssl ON (adss.`id_seo` = adssl.`id_seo` AND adssl.`id_lang` = '.((int) $id_lang).' )
        WHERE '.($id_excludes ? ' adss.`id_seo` NOT IN ('.implode(',', array_map('intval', $id_excludes)).') AND ':'').'adss.`deleted` = 0
        '.($query_search ? ' AND adssl.`title` LIKE "%'.pSQL($query_search).'%"' : '').'
        ORDER BY adss.`id_seo`
        '.($limit? 'LIMIT '.$start.', '.(int)$limit : ''));
        $return = array();
        foreach ($result as $row) {
            $return[$row['id_seo']] = $row['title'];
        }
        self::$getCrossLinksAvailableCache[$cacheKey] = $return;
        return self::$getCrossLinksAvailableCache[$cacheKey];
    }
    private static $getSeoSearchsCache = array();
    public static function getSeoSearchs($id_lang = false, $withDeleted = 0, $id_search = false)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getSeoSearchsCache[$cacheKey])) {
            return self::$getSeoSearchsCache[$cacheKey];
        }
        self::$getSeoSearchsCache[$cacheKey] = SearchEngineDb::query('
        SELECT *
        FROM `'._DB_PREFIX_.'pm_advancedsearch_seo` adss
        '.($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_seo_lang` adssl ON (adss.`id_seo` = adssl.`id_seo` AND adssl.`id_lang` = '.((int) $id_lang).' )' : '').'
        WHERE 1
        '.(!$withDeleted ? ' AND adss.`deleted` = 0':'').'
        '.($id_search ? ' AND adss.`id_search` = '.(int)$id_search:'').'
        GROUP BY adss.`id_seo`
        ORDER BY adss.`id_seo`');
        return self::$getSeoSearchsCache[$cacheKey];
    }
    private static $getCrossLinksSeoCache = array();
    public static function getCrossLinksSeo($id_lang, $id_seo)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCrossLinksSeoCache[$cacheKey])) {
            return self::$getCrossLinksSeoCache[$cacheKey];
        }
        $link = Context::getContext()->link;
        self::$getCrossLinksSeoCache[$cacheKey] = SearchEngineDb::query('
        SELECT *
        FROM `'._DB_PREFIX_.'pm_advancedsearch_seo` adss
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_seo_crosslinks` ascl ON (adss.`id_seo` = ascl.`id_seo_linked` )
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_seo_lang` adssl ON (adss.`id_seo` = adssl.`id_seo` AND adssl.`id_lang` = '.((int) $id_lang).' )
        WHERE ascl.`id_seo` = '.(int)$id_seo.' AND adss.`id_seo` != '.(int)$id_seo.' AND adss.`deleted` = 0
        GROUP BY adss.`id_seo`
        ORDER BY adss.`id_seo`');
        foreach (self::$getCrossLinksSeoCache[$cacheKey] as &$row) {
            $params = array(
                'id_seo' => $row['id_seo'],
                'seo_url' => $row['seo_url'],
            );
            $row['public_url'] = $link->getModuleLink(_PM_AS_MODULE_NAME_, 'seo', $params);
        }
        return self::$getCrossLinksSeoCache[$cacheKey];
    }
    private static $getSeoSearchBySeoUrlCache = array();
    public static function getSeoSearchBySeoUrl($seo_url, $id_lang)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getSeoSearchBySeoUrlCache[$cacheKey])) {
            return self::$getSeoSearchBySeoUrlCache[$cacheKey];
        }
        self::$getSeoSearchBySeoUrlCache[$cacheKey] = SearchEngineDb::query('
        SELECT *
        FROM `'._DB_PREFIX_.'pm_advancedsearch_seo` adss
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_seo_lang` adssl ON (adss.`id_seo` = adssl.`id_seo`'.($id_lang?' AND adssl.`id_lang` = '.((int) $id_lang):'').' )
        WHERE `seo_url` = "'.pSQL($seo_url).'"
        LIMIT 1');
        return self::$getSeoSearchBySeoUrlCache[$cacheKey];
    }
    private static $getSeoSearchByIdSeoCache = array();
    public static function getSeoSearchByIdSeo($id_seo, $id_lang)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getSeoSearchByIdSeoCache[$cacheKey])) {
            return self::$getSeoSearchByIdSeoCache[$cacheKey];
        }
        self::$getSeoSearchByIdSeoCache[$cacheKey] = SearchEngineDb::query('
        SELECT *
        FROM `'._DB_PREFIX_.'pm_advancedsearch_seo` adss
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_seo_lang` adssl ON (adss.`id_seo` = adssl.`id_seo` AND adssl.`id_lang` = '.((int) $id_lang).')
        WHERE adss.`id_seo` = "'.((int) $id_seo).'"
        GROUP BY adss.`id_seo`
        LIMIT 1');
        return self::$getSeoSearchByIdSeoCache[$cacheKey];
    }
    private static $seoExistsCache = array();
    public static function seoExists($seo_key)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$seoExistsCache[$cacheKey])) {
            return self::$seoExistsCache[$cacheKey];
        }
        $row = SearchEngineDb::row('
            SELECT `id_seo`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_seo`
            WHERE `seo_key` = "'.pSQL($seo_key).'"
            AND `deleted`=0');
        self::$seoExistsCache[$cacheKey] = (isset($row['id_seo']) ? $row['id_seo'] : false);
        return self::$seoExistsCache[$cacheKey];
    }
    private static $seoDeletedExistsCache = array();
    public static function seoDeletedExists($seo_key)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$seoDeletedExistsCache[$cacheKey])) {
            return self::$seoDeletedExistsCache[$cacheKey];
        }
        $row = SearchEngineDb::row('
            SELECT `id_seo`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_seo`
            WHERE `seo_key` = "'.pSQL($seo_key).'" AND `deleted` = 1');
        self::$seoDeletedExistsCache[$cacheKey] = (isset($row['id_seo']) ? $row['id_seo'] : false);
        return self::$seoDeletedExistsCache[$cacheKey];
    }
    public static function undeleteSeoBySeoKey($seo_key)
    {
        $row = array('deleted' => 0);
        Db::getInstance()->update('pm_advancedsearch_seo', $row, '`seo_key` = "'.pSQL($seo_key).'" AND deleted = 1');
    }
    public static function getSeoPageUrlByKeys($seoKeys, $idLang)
    {
        $seoPageListTmp = SearchEngineDb::query('
            SELECT s.`seo_key`, s.`id_seo`, sl.`seo_url`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_seo` s
            JOIN `'._DB_PREFIX_.'pm_advancedsearch_seo_lang` sl ON (s.`id_seo`=sl.`id_seo` AND sl.`id_lang`=' . (int)$idLang . ')
            WHERE s.`seo_key` IN ("'. implode('","', $seoKeys) . '")
            AND s.`deleted`=0');
        $seoPageList = array();
        if (is_array($seoPageListTmp)) {
            $context = Context::getContext();
            foreach ($seoPageListTmp as $seoPage) {
                $seoPageList[$seoPage['seo_key']] = array(
                    'id_seo' => (int)$seoPage['id_seo'],
                    'seo_page_url' => $context->link->getModuleLink(_PM_AS_MODULE_NAME_, 'seo', array('id_seo' => (int)$seoPage['id_seo'], 'seo_url' => $seoPage['seo_url'])),
                );
            }
        }
        return $seoPageList;
    }
}
