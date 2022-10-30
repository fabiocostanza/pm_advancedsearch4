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

namespace AdvancedSearch;
if (!defined('_PS_VERSION_')) {
    exit;
}
use Db;
use CMS;
use Tax;
use Hook;
use Meta;
use Shop;
use Group;
use Image;
use Tools;
use Module;
use Address;
use Context;
use Product;
use Category;
use Currency;
use Supplier;
use Validate;
use Combination;
use Manufacturer;
use Configuration;
use SpecificPrice;
use GroupReduction;
use StockAvailable;
use FrontController;
use IndexController;
use ProductController;
use CategoryController;
use PM_AdvancedSearch4;
use Search as PsSearch;
use SupplierController;
use AdvancedSearch\Core;
use ManufacturerController;
use AdvancedSearch\Models\Seo;
use AdvancedSearch\Models\Search;
use AdvancedSearch\SearchEngineDb;
use AdvancedSearch\Models\Criterion;
use AdvancedSearch\Models\CriterionGroup;
use AdvancedSearch\SearchEngineIndexation;
use AdvancedSearch\Traits\SupportsSeoPages;
use pm_advancedsearch4seoModuleFrontController;
use pm_advancedsearch4searchresultsModuleFrontController;
abstract class SearchEngineUtils
{
    use SupportsSeoPages;
    public static $valid_hooks = array('displaytop','displaynavfullwidth','displayrightcolumn','displayleftcolumn','displayhome', 'displayAdvancedSearch4');
    public static $valid_hooks_cms = array('displaytop','displaynavfullwidth','displayrightcolumn','displayleftcolumn', -1);
    public static $valid_hooks_supplier = array('displaytop','displaynavfullwidth','displayrightcolumn','displayleftcolumn', -1);
    public static $valid_hooks_manufacturer = array('displaytop','displaynavfullwidth','displayrightcolumn','displayleftcolumn', -1);
    public static $valid_hooks_product = array('displaytop','displaynavfullwidth','displayrightcolumn','displayleftcolumn', -1);
    public static $valid_hooks_special_page = array('displaytop','displaynavfullwidth','displayrightcolumn','displayleftcolumn', -1);
    public static $valid_hooks_category = array('displaytop','displaynavfullwidth','displayrightcolumn','displayleftcolumn', -1);
    public static $validPageName = array('best-sales', 'new-products', 'prices-drop', 'search', 'index', 'jolisearch', 'module-ambjolisearch-jolisearch', 'prestasearch', 'subscription');
    public static $productFilterListSearchSource = [
        'search',
        'jolisearch',
        'module-ambjolisearch-jolisearch',
        'prestasearch',
    ];
    public static $productFilterListData = false;
    public static $productFilterListSource = false;
    public static $productFilterListQuery = false;
    public static $productFilterListAdditionalColumns = '';
    public static $orderByValues = array(0 => 'name', 1 => 'price', 2 => 'date_add', 3 => 'date_upd', 4 => 'position', 5 => 'manufacturer_name', 6 => 'quantity', 7 => 'reference', 8 => 'sales');
    public static $orderWayValues = array(0 => 'asc', 1 => 'desc');
    public static function getCMSAssociation($id_search, $id_lang)
    {
        return SearchEngineDb::query('
            SELECT cl.`meta_title`, cl.`id_cms`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_cms` a
            LEFT JOIN `'._DB_PREFIX_.'cms_lang` cl ON (a.`id_cms` = cl.`id_cms` AND cl.`id_lang` = '.(int)$id_lang.')
            ' . Shop::addSqlAssociation('cms', 'cl') . '
            WHERE a.`id_search` = '.(int)$id_search.'
            GROUP BY cl.`id_cms`
            ORDER BY cl.`meta_title` ASC');
    }
    public static function getManufacturersAssociation($id_search)
    {
        return SearchEngineDb::query('
            SELECT m.`name`, m.`id_manufacturer`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_manufacturers` a
            LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = a.`id_manufacturer`)
            WHERE a.`id_search` = '.(int)$id_search.'
            ORDER BY m.`name` ASC');
    }
    public static function getSuppliersAssociation($id_search)
    {
        return SearchEngineDb::query('
            SELECT s.`name`, s.`id_supplier`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_suppliers` a
            LEFT JOIN `'._DB_PREFIX_.'supplier` s ON (s.`id_supplier` = a.`id_supplier`)
            WHERE a.`id_search` = '.(int)$id_search.'
            ORDER BY s.`name` ASC');
    }
    public static function getProductsAssociation($id_search, $id_lang)
    {
        return SearchEngineDb::query('
            SELECT pl.`name`, pl.`id_product`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_products` a
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (a.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)$id_lang.')
            ' . Shop::addSqlAssociation('product', 'pl') . '
            WHERE a.`id_search` = '.(int)$id_search.'
            GROUP BY pl.`id_product`
            ORDER BY pl.`name` ASC');
    }
    public static function getCategoriesAssociation($id_search, $id_lang)
    {
        return SearchEngineDb::query('
            SELECT cl.`name`, cl.`id_category`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_category` a
            LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (a.`id_category` = cl.`id_category` AND cl.`id_lang` = '.(int)$id_lang.')
            ' . Shop::addSqlAssociation('category', 'cl') . '
            WHERE a.`id_search` = '.(int)$id_search.'
            GROUP BY cl.`id_category`
            ORDER BY cl.`name` ASC');
    }
    public static function getProductsCategoriesAssociation($id_search, $id_lang)
    {
        return SearchEngineDb::query('
            SELECT cl.`name`, cl.`id_category`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_products_cat` a
            LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (a.`id_category` = cl.`id_category` AND cl.`id_lang` = '.(int)$id_lang.')
            ' . Shop::addSqlAssociation('category', 'cl') . '
            WHERE a.`id_search` = '.(int)$id_search.'
            GROUP BY cl.`id_category`
            ORDER BY cl.`name` ASC');
    }
    public static function getSpecialPagesAssociation($id_search)
    {
        $metaNames = Core::getCustomMetasByIdLang();
        $pagesList = SearchEngineDb::query('
            SELECT a.`page`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_special_pages` a
            WHERE a.`id_search` = '.(int)$id_search .
            ' ORDER BY a.`page` ASC');
        foreach ($pagesList as $k => $pageName) {
            foreach ($metaNames as $meta) {
                if ($pageName['page'] == $meta['page']) {
                    if (!empty($meta['title'])) {
                        $pagesList[$k]['title'] = $meta['title'];
                    } else {
                        $pagesList[$k]['title'] = $meta['page'];
                    }
                }
            }
        }
        return $pagesList;
    }
    public static function clearAllTables()
    {
        $advanced_searchs_id = self::getSearchsId(false);
        foreach ($advanced_searchs_id as $idSearch) {
            SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'`');
            SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_lang`');
            SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link`');
            SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_list`');
            SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$idSearch.'`');
            SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$idSearch.'_lang`');
            SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int)$idSearch.'`');
            SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int)$idSearch.'`');
            SearchEngineDb::execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$idSearch.'`');
        }
        SearchEngineDb::execute('TRUNCATE `'._DB_PREFIX_.'pm_advancedsearch`');
        SearchEngineDb::execute('TRUNCATE `'._DB_PREFIX_.'pm_advancedsearch_shop`');
        SearchEngineDb::execute('TRUNCATE `'._DB_PREFIX_.'pm_advancedsearch_category`');
        SearchEngineDb::execute('TRUNCATE `'._DB_PREFIX_.'pm_advancedsearch_cms`');
        SearchEngineDb::execute('TRUNCATE `'._DB_PREFIX_.'pm_advancedsearch_lang`');
    }
    public static function getSearchAssociations($idSearch, $controllerType = null)
    {
        static $cache = array();
        $cacheKey = (int)$idSearch . '-' . (!empty($controllerType) ? $controllerType : '');
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }
        $cache[$cacheKey] = array(
            'category' => SearchEngineDb::valueList('SELECT id_category FROM `'._DB_PREFIX_.'pm_advancedsearch_category` WHERE id_search=' . (int)$idSearch, 'intval'),
            'products_category' => SearchEngineDb::valueList('SELECT id_category FROM `'._DB_PREFIX_.'pm_advancedsearch_products_cat` WHERE id_search=' . (int)$idSearch, 'intval'),
            'cms' => SearchEngineDb::valueList('SELECT id_cms FROM `'._DB_PREFIX_.'pm_advancedsearch_cms` WHERE id_search=' . (int)$idSearch, 'intval'),
            'supplier' => SearchEngineDb::valueList('SELECT id_supplier FROM `'._DB_PREFIX_.'pm_advancedsearch_suppliers` WHERE id_search=' . (int)$idSearch, 'intval'),
            'manufacturer' => SearchEngineDb::valueList('SELECT id_manufacturer FROM `'._DB_PREFIX_.'pm_advancedsearch_manufacturers` WHERE id_search=' . (int)$idSearch, 'intval'),
            'product' => SearchEngineDb::valueList('SELECT id_product FROM `'._DB_PREFIX_.'pm_advancedsearch_products` WHERE id_search=' . (int)$idSearch, 'intval'),
            'page' => SearchEngineDb::valueList('SELECT page FROM `'._DB_PREFIX_.'pm_advancedsearch_special_pages` WHERE id_search=' . (int)$idSearch),
            'seo' => (!isset($controllerType) || $controllerType == 'seo' ? SearchEngineDb::valueList('SELECT id_seo FROM `'._DB_PREFIX_.'pm_advancedsearch_seo` WHERE id_search=' . (int)$idSearch, 'intval') : array()),
        );
        return $cache[$cacheKey];
    }
    public static function getSearchsFromHook($hookName, $idLang = null, $fromWidget = false)
    {
        if ($hookName == -1) {
            $idHook = -1;
        } else {
            $idHook = Hook::getIdByName($hookName);
        }
        $context = Context::getContext();
        if ($idLang == null) {
            $idLang = (int)$context->language->id;
        }
        $searchIds = self::getSearchsIdByHook($idHook, true, $context->shop->id);
        if (!sizeof($searchIds)) {
            return array();
        }
        $hookName = Tools::strtolower($hookName);
        if ($hookName == 'displayadvancedsearch4') {
            return self::getSearchsById($searchIds, $idLang);
        }
        $currentIdSearch = ($context->controller instanceof pm_advancedsearch4searchresultsModuleFrontController ? $context->controller->getSearchEngine()->id : false);
        $currentIdSeoPage = ($context->controller instanceof pm_advancedsearch4seoModuleFrontController ? $context->controller->getIdSeo() : false);
        if ($currentIdSeoPage) {
            $currentIdSearch = (int)$context->controller->getSearchEngine()->id;
        }
        $currentIdCms = (int)Tools::getValue('id_cms', false);
        $currentIdCategory = (int)($context->controller instanceof CategoryController && method_exists($context->controller, 'getCategory') && Validate::isLoadedObject($context->controller->getCategory()) ? $context->controller->getCategory()->id : Tools::getValue('id_category', false));
        $currentIdProduct = (int)($context->controller instanceof ProductController && method_exists($context->controller, 'getProduct') && Validate::isLoadedObject($context->controller->getProduct()) ? $context->controller->getProduct()->id : Tools::getValue('id_product', false));
        $currentIdCategoryProduct = (int)($context->controller instanceof ProductController && method_exists($context->controller, 'getProduct') && Validate::isLoadedObject($context->controller->getProduct()) ? $context->controller->getProduct()->id_category_default : false);
        $currentIdManufacturer = (int)($context->controller instanceof ManufacturerController && method_exists($context->controller, 'getManufacturer') && Validate::isLoadedObject($context->controller->getManufacturer()) ? $context->controller->getManufacturer()->id : Tools::getValue('id_manufacturer', false));
        $currentIdSupplier = (int)($context->controller instanceof SupplierController && method_exists($context->controller, 'getSupplier') && Validate::isLoadedObject($context->controller->getSupplier()) ? $context->controller->getSupplier()->id : Tools::getValue('id_supplier', false));
        $currentSpecialPage = "";
        $page = Core::getSmartyVarValue('page');
        if (is_array($page) && isset($page['page_name'])) {
            $currentSpecialPage = Tools::strtolower($page['page_name']);
        }
        $controllerType = null;
        if (isset($context->controller->php_self) && $context->controller->php_self) {
            $controllerType = $context->controller->php_self;
        } elseif ($currentIdCms) {
            $controllerType = 'cms';
        } elseif ($currentIdSeoPage) {
            $controllerType = 'seo';
        }
        $idCategoryRoot = null;
        if ($controllerType == 'index' && empty($currentIdCategory)) {
            $idCategoryRoot = (int)Context::getContext()->shop->getCategory();
        }
        foreach ($searchIds as $k => $idSearch) {
            $searchAssociations = self::getSearchAssociations($idSearch, $controllerType);
            $hasAtLeastOneAssociation = (array_sum(array_map('sizeof', $searchAssociations)) > 0);
            if ($currentIdSeoPage && $currentIdSearch == $idSearch) {
                $searchIds = array($idSearch);
                break;
            }
            if (!$currentIdSeoPage) {
                if ($controllerType == 'category' && ($fromWidget || in_array($hookName, self::$valid_hooks_category)) && sizeof($searchAssociations['category']) && !in_array($currentIdCategory, $searchAssociations['category'])) {
                    unset($searchIds[$k]);
                    continue;
                }
                if ($controllerType == 'product' && ($fromWidget || in_array($hookName, self::$valid_hooks_product)) && sizeof($searchAssociations['product']) && !in_array($currentIdProduct, $searchAssociations['product'])) {
                    unset($searchIds[$k]);
                    continue;
                }
                if ($controllerType == 'product' && ($fromWidget || in_array($hookName, self::$valid_hooks_product)) && sizeof($searchAssociations['products_category']) && !in_array($currentIdCategoryProduct, $searchAssociations['products_category'])) {
                    unset($searchIds[$k]);
                    continue;
                }
                if ($controllerType == 'manufacturer' && ($fromWidget || in_array($hookName, self::$valid_hooks_manufacturer)) && sizeof($searchAssociations['manufacturer']) && !in_array($currentIdManufacturer, $searchAssociations['manufacturer'])) {
                    unset($searchIds[$k]);
                    continue;
                }
                if ($controllerType == 'supplier' && ($fromWidget || in_array($hookName, self::$valid_hooks_supplier)) && sizeof($searchAssociations['supplier']) && !in_array($currentIdSupplier, $searchAssociations['supplier'])) {
                    unset($searchIds[$k]);
                    continue;
                }
                if ($controllerType == 'cms' && ($fromWidget || in_array($hookName, self::$valid_hooks_cms)) && sizeof($searchAssociations['cms']) && !in_array($currentIdCms, $searchAssociations['cms'])) {
                    unset($searchIds[$k]);
                    continue;
                }
                if ($controllerType == 'category' && ($fromWidget || in_array($hookName, self::$valid_hooks_category)) && (!sizeof($searchAssociations['category']) && !$hasAtLeastOneAssociation || in_array($currentIdCategory, $searchAssociations['category']))) {
                    continue;
                }
                if ($controllerType == 'product' && ($fromWidget || in_array($hookName, self::$valid_hooks_product)) && (!sizeof($searchAssociations['product']) && !$hasAtLeastOneAssociation  || in_array($currentIdProduct, $searchAssociations['product']))) {
                    continue;
                }
                if ($controllerType == 'product' && ($fromWidget || in_array($hookName, self::$valid_hooks_product)) && (!sizeof($searchAssociations['products_category']) && !$hasAtLeastOneAssociation  || in_array($currentIdCategoryProduct, $searchAssociations['products_category']))) {
                    continue;
                }
                if ($controllerType == 'manufacturer' && ($fromWidget || in_array($hookName, self::$valid_hooks_manufacturer)) && (!sizeof($searchAssociations['manufacturer']) && !$hasAtLeastOneAssociation  || in_array($currentIdManufacturer, $searchAssociations['manufacturer']))) {
                    continue;
                }
                if ($controllerType == 'supplier' && ($fromWidget || in_array($hookName, self::$valid_hooks_supplier)) && (!sizeof($searchAssociations['supplier']) && !$hasAtLeastOneAssociation  || in_array($currentIdSupplier, $searchAssociations['supplier']))) {
                    continue;
                }
                if ($controllerType == 'cms' && ($fromWidget || in_array($hookName, self::$valid_hooks_cms)) && (!sizeof($searchAssociations['cms']) && !$hasAtLeastOneAssociation  || in_array($currentIdCms, $searchAssociations['cms']))) {
                    continue;
                }
                if ($controllerType == 'index' && ($fromWidget || in_array($hookName, self::$valid_hooks_special_page)) && sizeof($searchAssociations['category']) && in_array($idCategoryRoot, $searchAssociations['category'])) {
                    continue;
                }
                if (($fromWidget || in_array($hookName, self::$valid_hooks_special_page)) && (!sizeof($searchAssociations['page']) && !$hasAtLeastOneAssociation  || in_array($currentSpecialPage, $searchAssociations['page']))) {
                    continue;
                }
                if (!empty($controllerType) && !in_array($controllerType, array('category', 'product', 'manufacturer', 'supplier', 'cms')) && sizeof($searchAssociations['page']) && !in_array($controllerType, $searchAssociations['page'])) {
                    unset($searchIds[$k]);
                    continue;
                }
                if ($hasAtLeastOneAssociation && empty($currentIdSearch)) {
                    unset($searchIds[$k]);
                    continue;
                }
            }
            if (!empty($currentIdSearch) && $currentIdSearch != $idSearch) {
                unset($searchIds[$k]);
            }
        }
        if (sizeof($searchIds)) {
            $uniqueSearchList = array_intersect(self::getUniqueSearchsId(true, $context->shop->id), $searchIds);
            if (sizeof($uniqueSearchList)) {
                $searchIds = array_intersect($searchIds, $uniqueSearchList);
            }
            return self::getSearchsById($searchIds, $idLang);
        }
        return array();
    }
    public static function getSearchsById($idSearchList, $idLang = null)
    {
        static $cache = array();
        $cacheKey = implode(',', array_map('intval', $idSearchList)) . '-' . (!empty($idLang) ? (int)$idLang : '');
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }
        $cache[$cacheKey] = SearchEngineDb::query('
        SELECT ads.* '.($idLang ? ', adsl.*' : '').', ads.`id_search`
        FROM `'._DB_PREFIX_.'pm_advancedsearch` ads
        '.($idLang ? 'LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_lang` adsl ON (ads.`id_search` = adsl.`id_search` AND adsl.`id_lang` = '.(int)$idLang.' )':'').'
        WHERE ads.`id_search` IN (' . implode(',', array_map('intval', $idSearchList)) . ')
        ORDER BY ads.`position`');
        return $cache[$cacheKey];
    }
    protected static $getAllSearchsCache = null;
    public static function getAllSearchs($id_lang, $active = true, $multishop = true)
    {
        $cacheKey = (int)$id_lang.'-'.(int)$active.'-'.(int)$multishop;
        if (isset(self::$getAllSearchsCache[$cacheKey])) {
            return self::$getAllSearchsCache[$cacheKey];
        }
        $result = SearchEngineDb::query('
        SELECT ads.* '.($id_lang ? ', adsl.*':'').', ads.`id_search`
        FROM `'._DB_PREFIX_.'pm_advancedsearch` ads
        '.($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_lang` adsl ON (ads.`id_search` = adsl.`id_search` AND adsl.`id_lang` = '.((int) $id_lang).' )':'').'
        ' . ($multishop ? 'INNER JOIN `'._DB_PREFIX_.'pm_advancedsearch_shop` adss ON adss.`id_search` = ads.`id_search` AND adss.`id_shop` IN ('.implode(', ', Shop::getContextListShopID()).')' : '') . '
        WHERE 1
        '.($active ? ' AND `active` = 1' : '').'
        GROUP BY ads.`id_search`
        ORDER BY ads.`position`');
        self::$getAllSearchsCache[$cacheKey] = $result;
        return $result;
    }
    private static $getUniqueSearchsIdCache = array();
    public static function getUniqueSearchsId($active = true, $id_shop = false)
    {
        self::getSearchsId($active, $id_shop);
        return self::$getUniqueSearchsIdCache;
    }
    private static $getSearchsIdByHookCache = array();
    public static function getSearchsIdByHook($idHook, $active = true, $id_shop = false)
    {
        self::getSearchsId($active, $id_shop);
        if (isset(self::$getSearchsIdByHookCache[(int)$idHook])) {
            return self::$getSearchsIdByHookCache[(int)$idHook];
        }
        return array();
    }
    protected static $getSearchsIdCache = array();
    public static function getSearchsId($active = true, $id_shop = false, $sort = true)
    {
        if (isset(self::$getSearchsIdCache[(int)$active.'-'.(int)$id_shop])) {
            return self::$getSearchsIdCache[(int)$active.'-'.(int)$id_shop];
        }
        $searchIdList = array();
        self::$getSearchsIdByHookCache = array();
        self::$getUniqueSearchsIdCache = array();
        $result = SearchEngineDb::query('
        SELECT ads.`id_search`, ads.`id_hook`, ads.`unique_search`
        FROM `'._DB_PREFIX_.'pm_advancedsearch` ads
        ' .($id_shop ? ' JOIN `'._DB_PREFIX_.'pm_advancedsearch_shop` adss ON (ads.`id_search`=adss.`id_search` AND adss.`id_shop`='.(int)$id_shop.') ' : '') .
        ' WHERE 1 '
        .($active ? ' AND ads.`active`=1 ' : '')
        .($sort ? ' ORDER BY ads.`position` ' : ''));
        foreach ($result as $row) {
            $searchIdList[] = (int)$row['id_search'];
            self::$getSearchsIdByHookCache[(int)$row['id_hook']][] = (int)$row['id_search'];
            if ($row['unique_search']) {
                self::$getUniqueSearchsIdCache[] = (int)$row['id_search'];
            }
        }
        self::$getSearchsIdCache[(int)$active.'-'.(int)$id_shop] = $searchIdList;
        return $searchIdList;
    }
    public static function getSearchsIdWithAutoReindex($active = true)
    {
        $idSearchList = self::getSearchsId($active, false, true);
        foreach ($idSearchList as $k => $idSearch) {
            $conf = pm_advancedsearch4::getModuleConfigurationStatic((int)SearchEngineUtils::getShopBySearch($idSearch));
            if (empty($conf['autoReindex'])) {
                unset($idSearchList[$k]);
            }
        }
        return $idSearchList;
    }
    private static $getSearchCache = array();
    public static function getSearch($id_search, $id_lang, $active = true)
    {
        $cacheKey = $id_search.'-'.$id_lang.'-'.(int)$active;
        if (isset(self::$getSearchCache[$cacheKey])) {
            return self::$getSearchCache[$cacheKey];
        }
        $result = SearchEngineDb::query('
        SELECT ads.* '.($id_lang ? ', adsl.*':'').', ads.`id_search`
        FROM `'._DB_PREFIX_.'pm_advancedsearch` ads
        '.($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_lang` adsl ON (ads.`id_search` = adsl.`id_search` AND adsl.`id_lang` = '.((int) $id_lang).' )':'').'
        WHERE ads.`id_search` = '.(int) $id_search.'
        '.($active ? 'AND `active` = 1' : '').'
        LIMIT 1');
        self::$getSearchCache[$cacheKey] = $result;
        return self::$getSearchCache[$cacheKey];
    }
    public static function categoryHasChild($id_category)
    {
        $row = SearchEngineDb::row('
        SELECT c.`id_category`
        FROM `'._DB_PREFIX_.'category` c
        WHERE c.`id_parent` = '.(int)$id_category);
        return isset($row['id_category']);
    }
    public static function getAsCriterionCategoryHigherLevelDepth($id_search, $id_criterion)
    {
        $row = SearchEngineDb::row('
        SELECT ac.`level_depth`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'` ac
        WHERE ac.`id_criterion` IN ('.implode(',', array_map('intval', $id_criterion)).')
        ORDER BY ac.`level_depth` DESC');
        return isset($row['level_depth']) ? $row['level_depth'] : false;
    }
    public static function getHighestLevelDepthCategory($idCategoryList)
    {
        $row = SearchEngineDb::row('
        SELECT c.`id_category`
        FROM `'._DB_PREFIX_.'category` c
        WHERE c.`id_category` IN ('.implode(',', array_map('intval', $idCategoryList)).')
        ORDER BY c.`level_depth` DESC');
        return isset($row['id_category']) ? (int)$row['id_category'] : false;
    }
    public static function getCategoryName($id_category, $id_lang)
    {
        $category = new Category((int)$id_category, (int)$id_lang);
        if (Validate::isLoadedObject($category)) {
            return $category->name;
        }
        return false;
    }
    public static function getCriterionsFromCriterionGroup($id_criterion_group, $id_search, $order_by, $order_way, $id_lang, $includeCustom = true)
    {
        if ($order_by == 'position' || $order_by == 'o_position' || !$id_lang) {
            $field_order_by = 'ac.`position`';
        } elseif ($order_by == 'numeric') {
            $field_order_by = 'acl.`decimal_value`';
        } else {
            $field_order_by = 'acl.`value`';
        }
        $result = array();
        $resource = SearchEngineDb::query('
        SELECT ac.*, acl.*
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'` ac
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int)$id_lang .')
        WHERE ac.`id_criterion_group` = '.(int)$id_criterion_group.'
        '.(!$includeCustom ? ' AND ac.`is_custom`=0 ' : '').'
        ORDER BY '.pSQL($field_order_by).' '.pSQL($order_way), 1, false);
        while ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->nextRow($resource)) {
            $idCriterion = (int)$row['id_criterion'];
            if (!isset($result[$idCriterion])) {
                $result[$idCriterion] = $row;
            }
        }
        return $result;
    }
    private static $getShopBySearchCache = array();
    public static function getShopBySearch($id_search)
    {
        if (isset(self::$getShopBySearchCache[$id_search])) {
            return self::$getShopBySearchCache[$id_search];
        }
        self::$getShopBySearchCache[$id_search] = 0;
        $row = SearchEngineDb::row('SELECT `id_shop` FROM `'._DB_PREFIX_.'pm_advancedsearch_shop` WHERE `id_search` = '.(int)($id_search));
        if ($row && isset($row['id_shop'])) {
            self::$getShopBySearchCache[$id_search] = (int)$row['id_shop'];
        }
        return self::$getShopBySearchCache[$id_search];
    }
    private static $getCriterionsWithIdGroupFromIdLinkedCache = array();
    public static function getCriterionsWithIdGroupFromIdLinked($criterion_group_type, $id_criterion_linked, $id_search)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriterionsWithIdGroupFromIdLinkedCache[$cacheKey])) {
            return self::$getCriterionsWithIdGroupFromIdLinkedCache[$cacheKey];
        }
        $results = SearchEngineDb::query('
        SELECT ac.`id_criterion`, ac.`id_criterion_group`, acg.`visible`,aclink.`id_criterion_linked`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'` ac
        JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'_link` aclink ON (ac.`id_criterion` = aclink.`id_criterion`)
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'` acg ON (acg.`id_criterion_group` = ac.`id_criterion_group`)
        WHERE acg.`criterion_group_type` = "'.pSQL($criterion_group_type).'"
        AND aclink.`id_criterion_linked` '.(is_array($id_criterion_linked) ? ' IN ('.implode(', ', array_map('intval', $id_criterion_linked)).')':' = '.(int)$id_criterion_linked));
        $selected_criterion = false;
        if ($results && sizeof($results)) {
            $selected_criterion = array();
            foreach ($results as $row) {
                if (!isset($selected_criterion[$row['id_criterion_group']])) {
                    $selected_criterion[$row['id_criterion_group']] = array();
                }
                $selected_criterion[$row['id_criterion_group']][] = $row['id_criterion'];
            }
        }
        self::$getCriterionsWithIdGroupFromIdLinkedCache[$cacheKey] = $selected_criterion;
        return self::$getCriterionsWithIdGroupFromIdLinkedCache[$cacheKey];
    }
    public static function cleanArrayCriterion($selected_criterion)
    {
        foreach ($selected_criterion as $id_criterion_group => $criteria) {
            foreach ($criteria as $k => $criterion) {
                if (preg_match('#~#', $criterion)) {
                    $interval = explode('~', $criterion);
                } else {
                    $interval = null;
                }
                if ($interval != null && is_array($interval) && count($interval) == 2) {
                    $isValidInterval = true;
                    foreach ($interval as $kInterval => $intervalValue) {
                        if ($kInterval == 1 && $intervalValue == '' && is_numeric($interval[0])) {
                            continue;
                        } elseif (!is_numeric($intervalValue)) {
                            $isValidInterval = false;
                            break;
                        }
                    }
                    if (!$isValidInterval) {
                        unset($selected_criterion[$id_criterion_group][$k]);
                    }
                } elseif (!trim($criterion) || $criterion == 0) {
                    unset($selected_criterion[$id_criterion_group][$k]);
                }
            }
            if (!$selected_criterion[$id_criterion_group]) {
                unset($selected_criterion[$id_criterion_group]);
            } else {
                $selected_criterion[$id_criterion_group] = array_values($selected_criterion[$id_criterion_group]);
            }
        }
        return Core::arrayMapRecursive('intval', $selected_criterion);
    }
    public static function getUseTax()
    {
        $id_customer = false;
        $context = Context::getContext();
        if (isset($context->cart) && is_object($context->cart) && isset($context->cart->id_customer)) {
            $id_customer = (int)$context->cart->id_customer;
        }
        return Product::getTaxCalculationMethod((int)$id_customer) == PS_TAX_INC;
    }
    public static function getPriceRangeConditions($id_group)
    {
        $taxConversion = '';
        if (self::getUseTax()) {
            $taxConversion = ' / IF(t.`rate` > 0, ((t.`rate`/100) + 1), 1)';
            $taxConversionForReduction = ' * IF(app.`reduction_tax`=1, 1, IF(t.`rate` > 0, ((t.`rate`/100) + 1), 1)) ' . $taxConversion;
        } else {
            $taxConversionForReduction = ' * IF(app.`reduction_tax`=1, IF(t.`rate` > 0, (100 / (1 + (t.`rate`/100)))/100, 1), 1)';
        }
        $specificPriceCondition = '
            (
                app.`price_wt` -
                IF(
                    app.`reduction_type`=\'amount\',
                    app.`reduction_amount`' . $taxConversionForReduction . ',
                    app.`reduction_amount`
                )
            )
        ';
        $groupReductionValue = ((int)Group::getReductionByIdGroup($id_group) > 0 ? (1 - (int)Group::getReductionByIdGroup($id_group)/100) : 1);
        $specificPriceGroupCondition = ' * IF(grc.`reduction` > 0, 1 - grc.`reduction`, ' . (float)$groupReductionValue . ')';
        return array($taxConversion, $taxConversionForReduction, $specificPriceCondition, $specificPriceGroupCondition);
    }
    protected static $makeLeftJoinWhereCriterionCache = array();
    public static function makeLeftJoinWhereCriterion($fromMethod, $search, $id_lang, $selected_criterion, $selected_criteria_groups_type = array(), $current_id_criterion_group = false, $is_attribute_group = false, $id_currency = false, $id_country = false, $id_group = false, $include_price_table = false, $include_product_table = false, $group_type = false, $criterion_groups = array())
    {
        $context = Context::getContext();
        if (!$id_currency) {
            $id_currency = $context->currency->id;
        }
        // MOD
        $schemas = false;
        if (isset($context->cookie) AND isset($context->cookie->schemas) AND $context->cookie->schemas) {
            $schemas = $context->cookie->schemas;
        }
        // /MOD
        $join_criterion_tables = array();
        $join_criterion = array();
        $setVariables = array();
        $count_criterion = array();
        $where_criterion = array();
        $where_qty        = array();
        $field_select = array();
        $attribute_selected = false;
        $lastAttributeCombinationTableId = false;
        $previousIdCriterionGroupSelected = null;
        $attribute_check_table = null;
        $stock_management = (int)(Configuration::get('PS_STOCK_MANAGEMENT')) ? true : false;
        if (!$stock_management) {
            $search['search_on_stock'] = false;
        }
        if ($group_type == 'stock' && $stock_management) {
            $strict_stock = true;
        } else {
            $strict_stock = false;
        }
        if ($stock_management && Core::isFilledArray($selected_criterion) && Core::isFilledArray($criterion_groups)) {
            foreach (array_keys($selected_criterion) as $id_criterion_group_tmp) {
                foreach ($criterion_groups as $criterion_group) {
                    if ($criterion_group['id_criterion_group'] == $id_criterion_group_tmp && $criterion_group['criterion_group_type'] == 'stock') {
                        $search['search_on_stock'] = true;
                        $strict_stock = true;
                        break;
                    }
                }
            }
        }
        if (Core::isFilledArray($selected_criterion)) {
            foreach ($selected_criterion as $idCriterionGroupTmp => $idCriterionListTmp) {
                foreach ($idCriterionListTmp as $idCriterionListKeyTmp => $idCriterionTmp) {
                    if ($idCriterionTmp == -1) {
                        unset($selected_criterion[$idCriterionGroupTmp][$idCriterionListKeyTmp]);
                    }
                }
            }
            $selected_criterion = self::cleanArrayCriterion($selected_criterion);
        }
        $table_stock_index = 0;
        $idSelectedCriteria = implode('-', self::arrayValuesRecursive($selected_criterion));
        $cacheKey = sha1($fromMethod.$search['id_search'].$idSelectedCriteria.'-'.implode('-', array_keys($selected_criterion)).'-'.(int)$current_id_criterion_group.(int)$include_price_table.(int)$include_product_table.(int)$id_lang.(int)$is_attribute_group.(int)$group_type.(int)$strict_stock.(int)$schemas);
        if (isset(self::$makeLeftJoinWhereCriterionCache[$cacheKey])) {
            return self::$makeLeftJoinWhereCriterionCache[$cacheKey];
        }
        if ($group_type && !$include_product_table && $search['display_empty_criteria']) {
            $make_union = true;
        } else {
            $make_union = false;
        }
        $price_is_included = false;
        if ($fromMethod == 'getProductsSearched') {
            $join_criterion['product'] = '
                JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = acp.`id_product`)
            ';
        }
        $join_criterion['product_shop'] = '
        JOIN `'._DB_PREFIX_.'product_shop` ps ON (
            '.(!empty(self::$productFilterListQuery)  && !empty($search['filter_by_emplacement']) ?
                'ps.`id_product` IN ('.(is_array(self::$productFilterListQuery) ? array_map('intval', self::$productFilterListQuery) : self::$productFilterListQuery).') AND ' : '').'
            ps.id_shop IN ('.implode(', ', array_map('intval', Shop::getContextListShopID())).')
            AND ps.`id_product` = acp.`id_product`
        )';
        $join_criterion_tables[] = 'ps';
        $join_criterion_tables[] = 'p';
        if (Core::isFilledArray($selected_criterion)) {
            $price_is_included = false;
            $attribute_qty_compare_on_join = array();
            $now = date('Y-m-d H:i:s');
            foreach ($selected_criterion as $id_criterion_group => $id_criterion) {
                if (!isset($selected_criteria_groups_type[$id_criterion_group])) {
                    $where_criterion[] = '1 = 2';
                    continue;
                }
                if ($selected_criteria_groups_type[$id_criterion_group]['criterion_group_type'] == 'stock') {
                    $strict_stock = true;
                    continue;
                }
                $where_join = array();
                $where_translatable_value_range = array();
                if (isset($selected_criteria_groups_type[$id_criterion_group]) && ($selected_criteria_groups_type[$id_criterion_group]['display_type'] == 5 || $selected_criteria_groups_type[$id_criterion_group]['display_type'] == 8 || $selected_criteria_groups_type[$id_criterion_group]['range'])) {
                    $id_currency_default = Configuration::get('PS_CURRENCY_DEFAULT');
                    if ($id_currency != $id_currency_default) {
                        $currency = new Currency($id_currency);
                        $conversion_rate = $currency->conversion_rate;
                    } else {
                        $conversion_rate = 0;
                    }
                    $where_price_criterion = array();
                    foreach ($id_criterion as $range) {
                        $range = explode('~', $range);
                        $maxRangeOperator = '<=';
                        if ($selected_criteria_groups_type[$id_criterion_group]['criterion_group_type'] == 'price') {
                            $original_range = $range;
                            if ($conversion_rate > 0) {
                                $range[0] = $range[0] / $conversion_rate;
                                if (isset($range[1])) {
                                    $range[1] = $range[1] / $conversion_rate;
                                }
                            }
                            $price_is_included = true;
                            list($taxConversion, $taxConversionForReduction, $specificPriceCondition, $specificPriceGroupCondition) = self::getPriceRangeConditions($id_group);
                            $specificPriceCondition .= $specificPriceGroupCondition;
                            $priceMinCondition = '
                            IF(app.`is_specific` = 1 AND app.`id_currency` IN (0, '.(int)$id_currency.'),
                                ROUND(' . sprintf($specificPriceCondition . ', 2) >= ROUND(%f' . pSQL($taxConversion) .', 2)', (float)$original_range[0]) . ',
                                ROUND(' . sprintf($specificPriceCondition . ', 2) >= ROUND(%f' . pSQL($taxConversion) .', 2)', (float)$range[0]) . '
                            )';
                            $priceMaxCondition = '';
                            if (isset($range[1]) && $range[1]) {
                                $priceMaxCondition = '
                                AND
                                IF(app.`is_specific` = 1 AND app.`id_currency` IN (0, '.(int)$id_currency.'),
                                    ROUND(' . sprintf($specificPriceCondition . ', 2) ' . $maxRangeOperator . ' ROUND(%f' . pSQL($taxConversion) .', 2)', (float)$original_range[1]) . ',
                                    ROUND(' . sprintf($specificPriceCondition . ', 2) ' . $maxRangeOperator . ' ROUND(%f' . pSQL($taxConversion) .', 2)', (float)$range[1]) . '
                                )';
                            }
                            $where_price_criterion[] = ' (
                                /*AS4-PR-Start*/
                                ' . $priceMinCondition . $priceMaxCondition . '
                                /*AS4-PR-End*/
                                AND app.`id_country` IN (0, '.(int)$id_country.')
                                AND app.`id_group` IN (0, '.(int)$id_group.')
                                AND ((app.`from` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' >= app.`from`) AND (app.`to` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' <= app.`to`))
                                AND app.`id_shop` IN (0, '.implode(', ', array_map('intval', Shop::getContextListShopID())).')) ';
                        } else {
                            $where_translatable_value_range[] = 'ROUND(acl'.(int)$id_criterion_group.'.`decimal_value`, 6) >= ROUND("'.(float)$range[0].'", 6)'.(isset($range[1]) && $range[1] ? ' AND ROUND(acl'.(int)$id_criterion_group.'.`decimal_value`, 6) ' . $maxRangeOperator . ' ROUND("'.(float)$range[1].'", 6)':'');
                        }
                    }
                    if (isset($where_price_criterion) && Core::isFilledArray($where_price_criterion)) {
                        $where_criterion[] = '( '.implode(' OR ', $where_price_criterion) . ' )';
                    }
                    $subQueryForRange = '';
                    if (Core::isFilledArray($where_translatable_value_range)) {
                        $sqlVarName = 'acpc_' . $fromMethod . '_' . (int)$id_criterion_group;
                        if (!isset($setVariables[$sqlVarName])) {
                            $setVariables[$sqlVarName] = 'SELECT GROUP_CONCAT(ac' . (int)$id_criterion_group . '.`id_criterion`) AS `concatened_id_criterion_list`
                                FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$search['id_search'] . '` ac' . (int)$id_criterion_group . '
                                JOIN `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$search['id_search'] . '_link` aclink' . (int)$id_criterion_group . ' ON (ac' . (int)$id_criterion_group . '.`id_criterion` = aclink' . (int)$id_criterion_group . '.`id_criterion`)
                                JOIN `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$search['id_search'] . '_lang` acl' . (int)$id_criterion_group . ' ON (ac' . (int)$id_criterion_group . '.`id_criterion` = acl' . (int)$id_criterion_group . '.`id_criterion` AND acl' . (int)$id_criterion_group . '.`id_lang` = ' . (int)$id_lang . ' AND (' . implode(' OR ', $where_translatable_value_range) . '))
                                WHERE ac' . (int)$id_criterion_group . '.`id_criterion_group` = ' . (int)$id_criterion_group;
                        }
                        $subQueryForRange = ' AND acpc' . (int)$id_criterion_group . '.`id_criterion` IN (%' . $sqlVarName . '%) ';
                    }
                    if ($selected_criteria_groups_type[$id_criterion_group]['criterion_group_type'] != 'price') {
                        if (!in_array('acpc'.(int)$id_criterion_group, $join_criterion_tables)) {
                            $join_criterion[] = 'JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int) $search['id_search'].'` acpc'.(int)$id_criterion_group.' ON ( acp.`id_cache_product` = acpc'.(int)$id_criterion_group.'.`id_cache_product`'. $subQueryForRange . ')';
                            $join_criterion_tables[] = 'acpc'.(int)$id_criterion_group;
                        }
                    }
                } else {
                    $customCriterions = array();
                    $customCriterionsWithParent = array();
                    $originalCriterions = $id_criterion;
                    if (is_array($id_criterion) && sizeof($id_criterion)) {
                        $customCriterions = Criterion::getCustomCriterionsLinkIds($search['id_search'], $id_criterion, true);
                        $customCriterionsWithParent = Criterion::getCustomCriterionsLinkIds($search['id_search'], $id_criterion, false);
                        if (Core::isFilledArray($customCriterions)) {
                            $id_criterion = $customCriterions;
                        }
                    }
                    $current_where = '`id_criterion` IN ('.implode(', ', array_map('intval', $id_criterion)).')';
                    $where_join[] = 'acpc'.(int)$id_criterion_group.'.'.$current_where;
                    $join_criterion[] = 'JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int) $search['id_search'].'` acpc'.(int)$id_criterion_group.' ON ( acp.`id_cache_product` = acpc'.(int)$id_criterion_group.'.`id_cache_product`'.(Core::isFilledArray($where_join) ?' AND '.implode(' OR ', $where_join) : '').')';
                    $join_criterion_tables[] = 'acpc'.(int)$id_criterion_group;
                    if (is_array($originalCriterions) && sizeof($originalCriterions) > 1 && $selected_criteria_groups_type[$id_criterion_group]['is_combined']) {
                        foreach ($originalCriterions as $idCriterionOriginal) {
                            if (isset($customCriterionsWithParent[$idCriterionOriginal])) {
                                $criterionsListForMerge = $customCriterionsWithParent[$idCriterionOriginal];
                            } else {
                                $criterionsListForMerge = array($idCriterionOriginal);
                            }
                            $join_criterion[] = 'JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int) $search['id_search'].'` acpc'.(int)$id_criterion_group.'_'.(int)$idCriterionOriginal.' ON (
                            acpc'.(int)$id_criterion_group.'.`id_cache_product` = acpc'.(int)$id_criterion_group.'_'.(int)$idCriterionOriginal.'.`id_cache_product`
                            AND acpc'.(int)$id_criterion_group.'_'.(int)$idCriterionOriginal.'.`id_criterion` IN ('. implode(',', array_map('intval', $criterionsListForMerge)) . '))';
                            $join_criterion_tables[] = 'acpc'.(int)$id_criterion_group.'_'.(int)$idCriterionOriginal;
                        }
                    }
                }
                if ($selected_criteria_groups_type[$id_criterion_group]['criterion_group_type'] != 'price') {
                    $count_criterion[$id_criterion_group] = 'acpc'.(int)$id_criterion_group.'.`id_cache_product`';
                } else {
                    $count_criterion[$id_criterion_group] = 'app.`id_cache_product`';
                }
                if (isset($selected_criteria_groups_type[$id_criterion_group]) && $selected_criteria_groups_type[$id_criterion_group]['criterion_group_type'] == 'attribute') {
                    if ($fromMethod != 'getCriterionsRange' || $fromMethod == 'getCriterionsRange' && $id_criterion_group == $current_id_criterion_group) {
                        $attribute_selected = true;
                        $join_criterion['criterion_'.(int)$search['id_search'].'_'.(int)$id_criterion_group] = 'JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'` ac'.(int)$id_criterion_group.' ON (acpc'.(int)$id_criterion_group.'.`id_criterion` = ac'.(int)$id_criterion_group.'.`id_criterion`)';
                        $join_criterion['criterion_link_'.(int)$search['id_search'].'_'.(int)$id_criterion_group] = 'JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'_link` aclink'.(int)$id_criterion_group.' ON (ac'.(int)$id_criterion_group.'.`id_criterion` = aclink'.(int)$id_criterion_group.'.`id_criterion`)';
                        $join_criterion['pa'.(int)$id_criterion_group] = 'JOIN `'._DB_PREFIX_.'product_attribute` pa'.(int)$id_criterion_group.' ON (pa'.(int)$id_criterion_group.'.`id_product` = acp.`id_product`)';
                        $join_criterion[] = 'JOIN `'._DB_PREFIX_.'product_attribute_combination` pac'.(int)$id_criterion_group.' ON (pa'.(int)$id_criterion_group.'.`id_product_attribute` = pac'.(int)$id_criterion_group.'.`id_product_attribute` AND pac'.(int)$id_criterion_group.'.`id_attribute` = aclink'.(int)$id_criterion_group.'.`id_criterion_linked`'.($previousIdCriterionGroupSelected != null ? ' AND pa'.(int)$previousIdCriterionGroupSelected.'.`id_product_attribute` = pa'.(int)$id_criterion_group.'.`id_product_attribute` ' : '').')';
                        $join_criterion_tables[] = 'ac'.(int)$id_criterion_group;
                        $join_criterion_tables[] = 'pa'.(int)$id_criterion_group;
                        $join_criterion_tables[] = 'pac'.(int)$id_criterion_group;
                        $lastAttributeCombinationTableId = 'pac' . (int)$id_criterion_group;
                        $previousIdCriterionGroupSelected = (int)$id_criterion_group;
                        if (!isset($attribute_check_table) && (!$include_product_table && !$search['display_empty_criteria'])) {
                            $attribute_check_table = $id_criterion_group;
                        } elseif (!isset($attribute_check_table)) {
                            $attribute_check_table = $id_criterion_group;
                        }
                        $attribute_qty_compare_on_join[] = 'pa'.(int)$attribute_check_table.'.`id_product_attribute` = pac'.(int)$id_criterion_group.'.`id_product_attribute`';
                    }
                }
                if ($selected_criteria_groups_type[$id_criterion_group]['criterion_group_type'] == 'prices_drop' && $id_group) {
                    if (!empty($join_criterion['price_join'])) {
                        $join_criterion['price_drop_join'] = 'JOIN `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$search['id_search'].'` app ON (
                            acp.`id_cache_product` = app.`id_cache_product`
                            AND ((app.`valid_id_specific_price`=1 AND app.`is_specific`=1) OR app.`has_no_specific`=1)
                            AND app.`id_shop` IN (0, '.implode(', ', array_map('intval', Shop::getContextListShopID())).')
                            AND app.`reduction_amount` > 0
                            AND app.`id_group` IN (0, ' . (int)$id_group . ')
                        )';
                    } else {
                        $join_criterion['prices_drop_join'] = 'JOIN `'._DB_PREFIX_.'specific_price` sp ON (
                            sp.`id_product` = acp.`id_product`
                            AND sp.`id_product_attribute`=0
                            AND sp.`id_group` IN (0, ' . (int)$id_group . ')
                            AND sp.`id_shop` IN (0, '.implode(', ', array_map('intval', Shop::getContextListShopID())).')
                            AND sp.`reduction` > 0
                            AND (
                                (sp.`from` = \'0000-00-00 00:00:00\' OR \''.pSQL(date('Y-m-d H:i:00')).'\' >= sp.`from`)
                                AND
                                (sp.`to` = \'0000-00-00 00:00:00\' OR \''.pSQL(date('Y-m-d H:i:00')).'\' <= sp.`to`)
                            )
                        )';
                    }
                }
            }
            if ($price_is_included || $include_product_table) {
                $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (ps.`id_tax_rules_group` = tr.`id_tax_rules_group` AND tr.`id_country` = ' . (int)Context::getContext()->country->id . ' AND tr.`id_state` = 0)';
                $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)';
                $join_criterion_tables[] = 'tr';
                $join_criterion_tables[] = 't';
                $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'group_reduction` grc ON (grc.`id_group`='.(int)$id_group.' AND ps.`id_category_default` = grc.`id_category`)';
                $join_criterion_tables[] = 'grc';
            }
            if ($price_is_included && $fromMethod != "getPriceRangeForSearchBloc") {
                $field_select[] = self::getScoreQuery(Context::getContext()->shop->id, $id_currency, $id_country, $id_group);
                $join_criterion['price_join'] = 'JOIN `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int) $search['id_search'].'` app ON (
                    acp.`id_cache_product` = app.`id_cache_product`
                    AND ((app.`valid_id_specific_price`=1 AND app.`is_specific`=1) OR app.`has_no_specific`=1)
                    AND app.`id_shop` IN (0, '.implode(', ', array_map('intval', Shop::getContextListShopID())).')
                )';
            }
            if ($group_type == 'prices_drop' && $id_group) {
                if (!empty($join_criterion['price_join'])) {
                    $join_criterion['price_join'] = 'JOIN `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$search['id_search'].'` app ON (
                        acp.`id_cache_product` = app.`id_cache_product`
                        AND ((app.`valid_id_specific_price`=1 AND app.`is_specific`=1) OR app.`has_no_specific`=1)
                        AND app.`id_shop` IN (0, '.implode(', ', array_map('intval', Shop::getContextListShopID())).')
                        AND app.`reduction_amount` > 0
                        AND app.`id_group` IN (0, ' . (int)$id_group . ')
                    )';
                } else {
                    $join_criterion['prices_drop_join'] = 'JOIN `'._DB_PREFIX_.'specific_price` sp ON (
                        sp.`id_product` = acp.`id_product`
                        AND sp.`id_product_attribute`=0
                        AND sp.`id_group` IN (0, ' . (int)$id_group . ')
                        AND sp.`id_shop` IN (0, '.implode(', ', array_map('intval', Shop::getContextListShopID())).')
                        AND sp.`reduction` > 0
                        AND (
                            (sp.`from` = \'0000-00-00 00:00:00\' OR \''.pSQL(date('Y-m-d H:i:00')).'\' >= sp.`from`)
                            AND
                            (sp.`to` = \'0000-00-00 00:00:00\' OR \''.pSQL(date('Y-m-d H:i:00')).'\' <= sp.`to`)
                        )
                    )';
                }
            }
            if ($search['search_on_stock'] || $strict_stock && !$group_type && !isset($attribute_check_table)) {
                $table_stock_index++;
                if ($strict_stock) {
                    $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'stock_available` sa'.(int)$table_stock_index.' ON ( sa'.(int)$table_stock_index.'.`id_product` = acp.`id_product` AND sa'.(int)$table_stock_index.'.`id_product_attribute`=0 '.StockAvailable::addSqlShopRestriction(null, null, 'sa'.(int)$table_stock_index).')';
                    $join_criterion_tables[] = 'sa'.(int)$table_stock_index;
                    $where_qty[] = 'sa'.(int)$table_stock_index.'.`quantity` > 0';
                } else {
                    if (!(($group_type || $include_product_table) && isset($attribute_check_table) && sizeof($attribute_qty_compare_on_join))) {
                        $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'stock_available` sa'.(int)$table_stock_index.' ON ( sa'.(int)$table_stock_index.'.`id_product` = acp.`id_product` AND sa'.(int)$table_stock_index.'.`id_product_attribute`=0 '.StockAvailable::addSqlShopRestriction(null, null, 'sa'.(int)$table_stock_index).')';
                        $join_criterion_tables[] = 'sa'.(int)$table_stock_index;
                        $where_qty[] = 'IF (sa'.(int)$table_stock_index.'.`quantity` > 0, 1, IF (sa'.(int)$table_stock_index.'.`out_of_stock` = 2, '.(int)Configuration::get('PS_ORDER_OUT_OF_STOCK').' = 1, sa'.(int)$table_stock_index.'.`out_of_stock` = 1))';
                    }
                }
            }
            if ($current_id_criterion_group && isset($attribute_check_table) && $group_type) {
                if ($is_attribute_group) {
                    if (!isset($previousIdCriterionGroupSelected)) {
                        $previousIdCriterionGroupSelected = null;
                    }
                    if (!in_array('pa'.(int)$current_id_criterion_group, $join_criterion_tables)) {
                        $join_criterion[] = 'JOIN `'._DB_PREFIX_.'product_attribute` pa'.(int)$current_id_criterion_group.' ON (pa'.(int)$current_id_criterion_group.'.`id_product` = acp.`id_product`)';
                        $join_criterion_tables[] = 'pa'.(int)$current_id_criterion_group;
                    }
                    if (!in_array('pac'.(int)$current_id_criterion_group, $join_criterion_tables)) {
                        $join_criterion[] = 'JOIN `'._DB_PREFIX_.'product_attribute_combination` pac'.(int)$current_id_criterion_group.' ON (pa'.(int)$current_id_criterion_group.'.`id_product_attribute` = pac'.(int)$current_id_criterion_group.'.`id_product_attribute` AND pac'.(int)$current_id_criterion_group.'.`id_attribute` = aclink.`id_criterion_linked`'.($previousIdCriterionGroupSelected != null ? ' AND pa'.(int)$previousIdCriterionGroupSelected.'.`id_product_attribute` = pa'.(int)$current_id_criterion_group.'.`id_product_attribute` ' : '').')';
                        $join_criterion_tables[] = 'pac'.(int)$current_id_criterion_group;
                    }
                    $lastAttributeCombinationTableId = 'pac' . (int)$current_id_criterion_group;
                    $attribute_qty_compare_on_join[] = 'pa'.(int)$attribute_check_table.'.`id_product_attribute` = pac'.(int)$current_id_criterion_group.'.`id_product_attribute`';
                    $previousIdCriterionGroupSelected = (int)$current_id_criterion_group;
                }
            } elseif (($search['search_on_stock'] || $strict_stock) && $group_type && !isset($attribute_check_table)) {
                $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa'.(int)$current_id_criterion_group.' ON (pa'.(int)$current_id_criterion_group.'.`id_product` = acp.`id_product` '.($fromMethod != 'getCriterionsForSearchBloc' ? ' AND pa'.(int)$current_id_criterion_group.'.id_product_attribute = aclink.id_criterion_linked ' : '').')';
                $join_criterion_tables[] = 'pa'.(int)$current_id_criterion_group;
                if ($search['search_on_stock'] || $strict_stock) {
                    $table_stock_index++;
                    $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'stock_available` sa'.(int)$table_stock_index.' ON ( sa'.(int)$table_stock_index.'.`id_product` = acp.`id_product` AND sa'.(int)$table_stock_index.'.`id_product_attribute` = IFNULL(pa'.(int)$current_id_criterion_group.'.`id_product_attribute`, 0) '.StockAvailable::addSqlShopRestriction(null, null, 'sa'.(int)$table_stock_index).')';
                    $join_criterion_tables[] = 'sa'.(int)$table_stock_index;
                    if ($strict_stock) {
                        $where_qty[] = 'sa'.(int)$table_stock_index.'.`quantity` > 0';
                    } else {
                        $where_qty[] = 'IF (sa'.(int)$table_stock_index.'.`quantity` > 0,1, IF (sa'.(int)$table_stock_index.'.`out_of_stock` = 2, '.(int)Configuration::get('PS_ORDER_OUT_OF_STOCK').' = 1, sa'.(int)$table_stock_index.'.`out_of_stock` = 1))';
                    }
                }
                if ($is_attribute_group) {
                    if (!in_array('pa'.(int)$current_id_criterion_group, $join_criterion_tables)) {
                        $join_criterion['pa'.(int)$current_id_criterion_group] = 'JOIN `'._DB_PREFIX_.'product_attribute` pa'.(int)$current_id_criterion_group.' ON (pa'.(int)$current_id_criterion_group.'.`id_product` = acp.`id_product`)';
                        $join_criterion_tables[] = 'pa'.(int)$current_id_criterion_group;
                    }
                    $join_criterion[] = 'JOIN `'._DB_PREFIX_.'product_attribute_combination` pac'.(int)$current_id_criterion_group.' ON ('.($is_attribute_group ? 'pa'.(int)$current_id_criterion_group.'.`id_product_attribute` = pac'.(int)$current_id_criterion_group.'.`id_product_attribute` AND ' : '').'pac'.(int)$current_id_criterion_group.'.`id_attribute` = aclink.`id_criterion_linked`)';
                    $join_criterion_tables[] = 'pac'.(int)$current_id_criterion_group;
                    $lastAttributeCombinationTableId = 'pac' . (int)$current_id_criterion_group;
                }
            }
            if (($group_type || $include_product_table) && isset($attribute_check_table) && sizeof($attribute_qty_compare_on_join)) {
                if (!in_array('pa'.(int)$attribute_check_table, $join_criterion_tables)) {
                    $join_criterion['pa'.(int)$attribute_check_table] = 'JOIN `'._DB_PREFIX_.'product_attribute` pa'.(int)$attribute_check_table.' ON ('.implode(' AND ', $attribute_qty_compare_on_join).' AND pa'.(int)$attribute_check_table.'.`id_product` = acp.`id_product`)';
                    $join_criterion_tables[] = 'pa'.(int)$attribute_check_table;
                }
                if ($search['search_on_stock'] || $strict_stock) {
                    $table_stock_index++;
                    $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'stock_available` sa'.(int)$table_stock_index.' ON ( sa'.(int)$table_stock_index.'.`id_product` = acp.`id_product` AND sa'.(int)$table_stock_index.'.`id_product_attribute` = pa'.(int)$attribute_check_table.'.`id_product_attribute` '.StockAvailable::addSqlShopRestriction(null, null, 'sa'.(int)$table_stock_index).')';
                    $join_criterion_tables[] = 'sa'.(int)$table_stock_index;
                    if ($strict_stock) {
                        $where_qty[] = 'sa'.(int)$table_stock_index.'.`quantity` > 0';
                    } else {
                        $where_qty[] = 'IF (sa'.(int)$table_stock_index.'.`quantity` > 0, 1, IF(sa'.(int)$table_stock_index.'.`out_of_stock` = 2, '.(int)Configuration::get('PS_ORDER_OUT_OF_STOCK').' = 1, sa'.(int)$table_stock_index.'.`out_of_stock` = 1))';
                    }
                }
            }
        } else {
            if (($include_product_table && $include_price_table)) {
                $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (ps.`id_tax_rules_group` = tr.`id_tax_rules_group` AND tr.`id_country` = '.(int)Context::getContext()->country->id.' AND tr.`id_state` = 0)';
                $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)';
                $join_criterion_tables[] = 'tr';
                $join_criterion_tables[] = 't';
                $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'group_reduction` grc ON (grc.`id_group`='.(int)$id_group.' AND ps.`id_category_default` = grc.`id_category`)';
                $join_criterion_tables[] = 'grc';
            }
            if ($is_attribute_group && ($search['search_on_stock'] || $strict_stock)) {
                if (!in_array('pa'.(int)$current_id_criterion_group, $join_criterion_tables)) {
                    $join_criterion['pa'.(int)$current_id_criterion_group] = 'JOIN `'._DB_PREFIX_.'product_attribute` pa'.(int)$current_id_criterion_group.' ON (pa'.(int)$current_id_criterion_group.'.`id_product` = acp.`id_product`)';
                    $join_criterion_tables[] = 'pa'.(int)$current_id_criterion_group;
                }
                $table_stock_index++;
                $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'stock_available` sa'.(int)$table_stock_index.' ON ( sa'.(int)$table_stock_index.'.`id_product` = acp.`id_product` AND sa'.(int)$table_stock_index.'.`id_product_attribute` = pa'.(int)$current_id_criterion_group.'.`id_product_attribute` '.StockAvailable::addSqlShopRestriction(null, null, 'sa'.(int)$table_stock_index).')';
                $join_criterion_tables[] = 'sa'.(int)$table_stock_index;
                if ($strict_stock) {
                    $where_qty[] = 'sa'.(int)$table_stock_index.'.`quantity` > 0';
                } else {
                    $where_qty[] = 'IF (sa'.(int)$table_stock_index.'.`quantity` > 0, 1, IF (sa'.(int)$table_stock_index.'.`out_of_stock` = 2, '.(int)Configuration::get('PS_ORDER_OUT_OF_STOCK').' = 1, sa'.(int)$table_stock_index.'.`out_of_stock` = 1))';
                }
                if ($is_attribute_group) {
                    $join_criterion[] = 'JOIN `'._DB_PREFIX_.'product_attribute_combination` pac'.(int)$current_id_criterion_group.' ON ('.($is_attribute_group ? 'pa'.(int)$current_id_criterion_group.'.`id_product_attribute` = pac'.(int)$current_id_criterion_group.'.`id_product_attribute` AND ' : '').'pac'.(int)$current_id_criterion_group.'.`id_attribute` = aclink.`id_criterion_linked`)';
                    $join_criterion_tables[] = 'pac'.(int)$current_id_criterion_group;
                    $lastAttributeCombinationTableId = 'pac' . (int)$current_id_criterion_group;
                }
            } elseif ($search['search_on_stock'] || $strict_stock) {
                $table_stock_index++;
                $join_criterion[] = 'LEFT JOIN `'._DB_PREFIX_.'stock_available` sa'.(int)$table_stock_index.' ON ( sa'.(int)$table_stock_index.'.`id_product` = acp.`id_product` AND sa'.(int)$table_stock_index.'.`id_product_attribute`=0 '.StockAvailable::addSqlShopRestriction(null, null, 'sa'.(int)$table_stock_index).')';
                $join_criterion_tables[] = 'sa'.(int)$table_stock_index;
                if ($strict_stock) {
                    $where_qty[] = 'sa'.(int)$table_stock_index.'.`quantity` > 0';
                } else {
                    $where_qty[] = 'IF(sa'.(int)$table_stock_index.'.`quantity` > 0, 1, IF(sa'.(int)$table_stock_index.'.`out_of_stock` = 2, '.(int)Configuration::get('PS_ORDER_OUT_OF_STOCK').' = 1, sa'.(int)$table_stock_index.'.`out_of_stock` = 1))';
                }
            }
        }
        if ($include_product_table || $fromMethod == 'getCriterionsForSearchBloc') {
            $where_criterion[] = 'ps.`active` = 1';
            if($schemas){
                $where_criterion[] = 'p.`reference` LIKE "%SPL%"';
            } else {
                $where_criterion[] = 'ps.`visibility` IN ("both", "search")';
            }

            if (in_array(SearchEngineUtils::$productFilterListSource, SearchEngineUtils::$productFilterListSearchSource)) {
                $where_criterion[] = 'ps.`visibility` IN ("both", "search")';
            } else {
                $where_criterion[] = 'ps.`visibility` IN ("both", "catalog")';
            }
            if (Group::isFeatureActive()) {
                $sqlGroups = self::getSQLGroups();
                $where_criterion[] = 'EXISTS(
                    SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp
                    JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.id_category = cg.id_category ' . $sqlGroups . ')
                    WHERE cp.`id_product` = ps.`id_product`
                )';
            }
        }
        if (Core::isFilledArray($where_qty)) {
            if ($is_attribute_group || $attribute_selected) {
                $where_criterion[] = '('.implode(' AND ', $where_qty).')';
            } else {
                $where_criterion[] = '('.implode(' OR ', $where_qty).')';
            }
        }
        $return = array(
            'count' => $count_criterion,
            'setVariables' => $setVariables,
            'join' => $join_criterion,
            'where' => $where_criterion,
            'select' => $field_select,
            'make_union' => $make_union,
            'whereUnion' => array(),
            'joinUnion' => array(),
            'nbSelectedCriterions' => sizeof($selected_criterion),
            'priceIncluded' => $price_is_included,
            'productTableIncluded' => $include_product_table,
            'lastAttributeCombinationTableId' => $lastAttributeCombinationTableId,
            'isAttributeSelected' => (bool)$attribute_selected,
        );
        foreach ($setVariables as $variableName => $setVariableQuery) {
            $variableContent = SearchEngineDb::value($setVariableQuery);
            if (empty($variableContent)) {
                $variableContent = '0';
            }
            foreach (array('join', 'where') as $key) {
                foreach ($return[$key] as $arrayKey => $arrayValue) {
                    $return[$key][$arrayKey] = str_replace("%$variableName%", $variableContent, $return[$key][$arrayKey]);
                }
            }
        }
        self::$makeLeftJoinWhereCriterionCache[$cacheKey] = $return;
        return $return;
    }
    public static function getQueryCountResults($search, $id_lang, $selected_criterion, $selected_criteria_groups_type = array(), $id_currency = false, $id_country = false, $id_group = false)
    {
        $query_count = false;
        $leftJoinWhereCriterion = self::makeLeftJoinWhereCriterion('getQueryCountResults', $search, $id_lang, $selected_criterion, $selected_criteria_groups_type, false, false, $id_currency, $id_country, $id_group, true, true);
        $query_count = '
            SELECT COUNT(DISTINCT acp.`id_product`) as total
            FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $search['id_search'].'` acp
        ';
        $query_count .= ($leftJoinWhereCriterion && isset($leftJoinWhereCriterion['join']) ? implode("\n", $leftJoinWhereCriterion['join']) : '').
        (Core::isFilledArray($leftJoinWhereCriterion['where']) ? 'WHERE '.implode("\n AND ", $leftJoinWhereCriterion['where']) : '');
        return $query_count;
    }
    public static function getCriterionsForSearchBloc($search, $id_criterion_group, $selected_criterion = array(), $selected_criteria_groups_type = array(), $visible = false, $groupInfos = false, $criterion_groups = array())
    {
        $cacheKey = func_get_args();
        $schemas = false;
        if (isset($context->cookie) AND isset($context->cookie->schemas) and $context->cookie->schemas) {
            $schemas = $context->cookie->schemas;
        }
        $customCacheKey = 'pm_advancedsearch|' . (int)$search['id_search'] . '|customCache|getCriterionsForSearchBloc|' . (int)$id_criterion_group . '|' .(int)$schemas . '|' . sha1(serialize($cacheKey));

        $resultFromSmartyCache = pm_advancedsearch4::getModuleInstance()->getFromSmartyCache($customCacheKey);
        if ($resultFromSmartyCache !== null) {
            return $resultFromSmartyCache;
        }
        $context = Context::getContext();
        $id_lang = (int)$context->language->id;
        $id_country = (int)$context->country->id;
        $id_currency = (int)$context->currency->id;
        $id_group = (int)pm_advancedsearch4::getModuleInstance()->getCurrentCustomerGroupId();
        if (($groupInfos['sort_by'] == '' || $groupInfos['sort_by'] == 'position' || $groupInfos['sort_by'] == 'o_position') || !$id_lang) {
            $field_order_by = '`position`';
        } elseif ($groupInfos['sort_by'] == 'numeric') {
            $field_order_by = 'acl.`decimal_value`';
        } elseif ($groupInfos['sort_by'] == 'nb_product') {
            $field_order_by = 'nb_product';
        } else {
            $field_order_by = '`value`';
        }
        $criterionsToIgnore = $criterionsLinkToIgnore = array();
        if ($groupInfos['criterion_group_type'] == 'category') {
            foreach ($criterion_groups as $cg) {
                if ($groupInfos['id_criterion_group'] == $cg['id_criterion_group']) {
                    continue;
                }
                if (isset($selected_criterion[$cg['id_criterion_group']]) && $cg['criterion_group_type'] == 'category' && $cg['id_criterion_group_linked'] > 0 && $cg['id_criterion_group_linked'] == $groupInfos['id_criterion_group_linked']) {
                    if (Core::isFilledArray($selected_criterion[$cg['id_criterion_group']])) {
                        $criterionsToIgnore = array_merge($criterionsToIgnore, $selected_criterion[$cg['id_criterion_group']]);
                    }
                }
            }
        }
        if (sizeof($criterionsToIgnore)) {
            $criterionsToIgnore = array_unique($criterionsToIgnore);
            $criterionsLinkToIgnore = Criterion::getIdCriterionLinkByIdCriterion((int)$search['id_search'], $criterionsToIgnore);
            unset($criterionsToIgnore);
        }
        $selected_criterion = Core::arrayMapRecursive('intval', $selected_criterion);
        $selected_criterion_copy = $selected_criterion;
        if (isset($selected_criterion[$id_criterion_group]) && !$selected_criteria_groups_type[$id_criterion_group]['is_combined']) {
            unset($selected_criterion[$id_criterion_group]);
        }
        $leftJoinWhereCriterion = self::makeLeftJoinWhereCriterion('getCriterionsForSearchBloc', $search, $id_lang, $selected_criterion, $selected_criteria_groups_type, $id_criterion_group, ($groupInfos['criterion_group_type'] == 'attribute'), $id_currency, $id_country, $id_group, false, false, $groupInfos['criterion_group_type'], $criterion_groups);
        $leftJoinWhereCriterion['whereSubQuery'] = array();
        if ($search['filter_by_emplacement'] && Tools::getValue('id_seo') == false && (SearchEngineUtils::getCurrentManufacturer() || SearchEngineUtils::getCurrentSupplier())) {
            $id_manufacturer = SearchEngineUtils::getCurrentManufacturer();
            $id_supplier = SearchEngineUtils::getCurrentSupplier();
            $preSelectedCriterion = array();
            if ($id_manufacturer) {
                $preSelectedCriterion = self::getCriterionsWithIdGroupFromIdLinked('manufacturer', $id_manufacturer, (int)$search['id_search']);
            } elseif ($id_supplier) {
                $preSelectedCriterion = self::getCriterionsWithIdGroupFromIdLinked('supplier', $id_supplier, (int)$search['id_search']);
            }
            if (is_array($preSelectedCriterion) && isset($preSelectedCriterion[$id_criterion_group]) && Core::isFilledArray($preSelectedCriterion[$id_criterion_group])) {
                $leftJoinWhereCriterion['where'][] = $leftJoinWhereCriterion['whereUnion'][] = '(ac.`id_criterion` IN ('.implode(',', array_map('intval', $preSelectedCriterion[$id_criterion_group])).'))';
            }
        }
        if (self::supportsSeoPages() && $search['filter_by_emplacement'] && Tools::getValue('id_seo') !== false && is_numeric(Tools::getValue('id_seo')) && Tools::getValue('id_seo') > 0) {
            $search['selected_criteres_seo'] = array();
            $seo_search = new Seo((int)Tools::getValue('id_seo'));
            if (Validate::isLoadedObject($seo_search) && isset($seo_search->criteria) && !empty($seo_search->criteria)) {
                $criteres_seo = Core::decodeCriteria($seo_search->criteria);
                if (Core::isFilledArray($criteres_seo)) {
                    foreach ($criteres_seo as $critere_seo) {
                        $critere_seo = explode('_', $critere_seo);
                        $id_criterion_group_seo = (int)$critere_seo[0];
                        if (!preg_match('#~#', $critere_seo[1])) {
                            $id_criterion_value = (int)$critere_seo[1];
                        } else {
                            $id_criterion_value = $critere_seo[1];
                        }
                        if (isset($selected_criterion[$id_criterion_group_seo])) {
                            if (!in_array($id_criterion_value, $selected_criterion[$id_criterion_group_seo])) {
                                $search['selected_criteres_seo'][$id_criterion_group_seo][] = $id_criterion_value;
                            }
                        } else {
                            $search['selected_criteres_seo'][$id_criterion_group_seo][] = $id_criterion_value;
                        }
                    }
                }
            }
        }
        if (self::supportsSeoPages() && isset($search['selected_criteres_seo']) && Core::isFilledArray($search['selected_criteres_seo']) && isset($search['selected_criteres_seo'][$id_criterion_group]) && Core::isFilledArray($search['selected_criteres_seo'][$id_criterion_group]) && $search['filter_by_emplacement'] && Tools::getValue('id_seo') !== false && is_numeric(Tools::getValue('id_seo')) && Tools::getValue('id_seo') > 0) {
            $leftJoinWhereCriterion['where'][] = $leftJoinWhereCriterion['whereUnion'][] = '(ac.`id_criterion` IN ('.implode(',', array_map('intval', $search['selected_criteres_seo'][$id_criterion_group])).'))';
            $visible = false;
        }
        if (self::allowChildCategorySearch($search['id_search'], $id_criterion_group)) {
            $current_category_depth = $groupInfos['id_criterion_group_linked'];
            if (is_array($selected_criterion_copy) && sizeof($selected_criterion_copy)) {
                $prev_category_depth = null;
                foreach ($selected_criterion_copy as $id_criterion_group2 => $criteria2) {
                    if ($selected_criteria_groups_type[$id_criterion_group2]['criterion_group_type'] == 'category' && $id_criterion_group2 != $id_criterion_group && $selected_criteria_groups_type[$id_criterion_group2]['id_criterion_group_linked'] < $current_category_depth) {
                        if (!isset($prev_category_depth) || $prev_category_depth < $selected_criteria_groups_type[$id_criterion_group2]['id_criterion_group_linked']) {
                            $prev_category_depth = $selected_criteria_groups_type[$id_criterion_group2]['id_criterion_group_linked'];
                            $criteria_category_parent = $criteria2;
                        }
                    }
                }
                if (isset($criteria_category_parent)) {
                    $childsCategoriesId = SearchEngineIndexation::getChildsCategoriesId(Criterion::getIdCriterionLinkByIdCriterion($search['id_search'], $criteria_category_parent));
                    $leftJoinWhereCriterion['where'][] = $leftJoinWhereCriterion['whereUnion'][] = '(aclink.`id_criterion_linked` IN ('.implode(', ', array_map('intval', $childsCategoriesId)).'))';
                }
            }
        }
        if ($groupInfos['criterion_group_type'] == 'category') {
            $groups = FrontController::getCurrentCustomerGroups();
            $leftJoinWhereCriterion['join'][] = $leftJoinWhereCriterion['joinUnion'][] = 'LEFT JOIN `'._DB_PREFIX_.'category_group` cg ON ( cg.`id_category` = aclink.`id_criterion_linked`)';
            $leftJoinWhereCriterion['where'][] = $leftJoinWhereCriterion['whereUnion'][] = 'cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', array_map('intval', $groups)).')' : '= 1');
        }
        $query = '';
        if ($leftJoinWhereCriterion['make_union']) {
            if ($groupInfos['sort_by'] == 'numeric') {
                $field_order_by = '`decimal_value`';
            }
            $query .= 'SELECT * FROM (';
        }
        $leftJoinWhereCriterion['where'][] = 'ac.`id_criterion_group` = '.(int)$id_criterion_group;
        $leftJoinWhereCriterion['whereSubQuery'][] = 'ac.`id_criterion_group` = '.(int)$id_criterion_group;
        $leftJoinWhereCriterion['whereUnion'][] = 'ac.`id_criterion_group` = '.(int)$id_criterion_group;
        if (!empty($visible)) {
            $leftJoinWhereCriterion['where'][] = 'ac.`visible` = 1';
            $leftJoinWhereCriterion['whereSubQuery'][] = 'ac.`visible` = 1';
            $leftJoinWhereCriterion['whereUnion'][] = 'ac.`visible` = 1';
        }
        $countColumn = (Core::isFilledArray($leftJoinWhereCriterion['count']) ? 'COUNT(DISTINCT '.implode(' + ', $leftJoinWhereCriterion['count']).')':'COUNT(DISTINCT acpc.`id_cache_product`)');
        if (self::isSPAModuleActive() && version_compare(self::getSPAModuleInstance()->version, '2.0.0', '>=')) {
            if ($groupInfos['criterion_group_type'] == 'attribute' && in_array($groupInfos['id_criterion_group_linked'], self::getSPAModuleInstance()->getSplittedGroups())) {
                if (empty($leftJoinWhereCriterion['lastAttributeCombinationTableId'])) {
                    $leftJoinWhereCriterion['join'][] = 'JOIN `'._DB_PREFIX_.'pm_spa_cache` spa_cache ON (acp.`id_product` = spa_cache.`id_product` AND spa_cache.`id_shop` = ' . (int)Context::getContext()->shop->id . ')';
                } else {
                    $leftJoinWhereCriterion['join'][] = 'JOIN `'._DB_PREFIX_.'pm_spa_cache` spa_cache ON (acp.`id_product` = spa_cache.`id_product` AND spa_cache.`id_shop` = ' . (int)Context::getContext()->shop->id . ' AND spa_cache.`id_product_attribute` = ' . $leftJoinWhereCriterion['lastAttributeCombinationTableId'] . '.`id_product_attribute`)';
                }
                $countColumn = 'SUM(IF(FIND_IN_SET(aclink.`id_criterion_linked`, spa_cache.`id_attribute_list`), 1, 0))';
            } else {
                if (empty($leftJoinWhereCriterion['lastAttributeCombinationTableId'])) {
                    $leftJoinWhereCriterion['join'][] = 'LEFT JOIN `'._DB_PREFIX_.'pm_spa_cache` spa_cache ON (acp.`id_product` = spa_cache.`id_product` AND spa_cache.`id_shop` = ' . (int)Context::getContext()->shop->id . ')';
                } else {
                    $leftJoinWhereCriterion['join'][] = 'LEFT JOIN `'._DB_PREFIX_.'pm_spa_cache` spa_cache ON (acp.`id_product` = spa_cache.`id_product` AND spa_cache.`id_shop` = ' . (int)Context::getContext()->shop->id . ' AND spa_cache.`id_product_attribute` = ' . $leftJoinWhereCriterion['lastAttributeCombinationTableId'] . '.`id_product_attribute`)';
                }
                $countColumn = 'COUNT(spa_cache.`id_product_attribute`) - COUNT(DISTINCT spa_cache.`id_product`) + ' . $countColumn;
            }
        }
        if($schemas) {
            $leftJoinWhereCriterion['join'][] = 'LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON (p.`id_product` = acp.`id_product`)';
            $leftJoinWhereCriterion['where'][] = ' p.`reference` LIKE "%SPL%"';
        }
        $leftJoinWhereCriterion['setVariables']['acListCriterionsForSearchBloc'] = 'SELECT GROUP_CONCAT(DISTINCT aclist.`id_criterion_parent`) AS `concatened_id_criterion_list`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'` ac
            JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'_list` aclist ON (ac.`id_criterion` = aclist.`id_criterion_parent`)
            ' . (Core::isFilledArray($leftJoinWhereCriterion['whereSubQuery']) ? ' WHERE ' . implode("\n AND ", $leftJoinWhereCriterion['whereSubQuery']) : '');
        $leftJoinWhereCriterion['setVariables']['acLinkCriterionsForSearchBloc'] = 'SELECT GROUP_CONCAT(DISTINCT aclink.`id_criterion`) AS `concatened_id_criterion_list`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'` ac
            JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'_link` aclink ON (ac.`id_criterion` = aclink.`id_criterion`'.(Core::isFilledArray($criterionsLinkToIgnore) ? ' AND aclink.`id_criterion_linked` NOT IN ('.implode(', ', array_map('intval', $criterionsLinkToIgnore)) .')' : '') . ')
            JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'_list` aclist ON (ac.`id_criterion` = aclist.`id_criterion_parent`)
            ' . (Core::isFilledArray($leftJoinWhereCriterion['whereSubQuery']) ? ' WHERE ' . implode("\n AND ", $leftJoinWhereCriterion['whereSubQuery']) : '');
        if (!Core::isFilledArray($leftJoinWhereCriterion['join'])) {
            $leftJoinWhereCriterion['join'] = array();
        }
        $straightJoinCriterionLink = 'STRAIGHT_JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$search['id_search'].'_link` aclink ON (
            aclink.`id_criterion` = ac.`id_criterion`
            AND aclink.`id_criterion` IN (%acLinkCriterionsForSearchBloc%)
        )';
        $newJoin = array();
        $acLinkAdded = false;
        foreach ($leftJoinWhereCriterion['join'] as $k => $v) {
            if (!$acLinkAdded && preg_match('/aclink/', $v)) {
                $acLinkAdded = true;
                $newJoin[] = $straightJoinCriterionLink;
            }
            $newJoin[] = $v;
        }
        if (empty($acLinkAdded)) {
            $newJoin[] = $straightJoinCriterionLink;
        }
        $leftJoinWhereCriterion['join'] = $newJoin;
        $query .= 'SELECT ac.*, aclink.`id_criterion_linked` '.((int) $id_lang ? ', acl.id_lang, acl.icon':'').
        ', acl.`value`, acl.`decimal_value`, (' . pSQL($countColumn) . ') AS nb_product
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'` ac
        '.
        ($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int) $id_lang.')' : '').
        ($search['display_empty_criteria'] ? 'LEFT ' : '').'JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int) $search['id_search'].'` acpc ON (
            ac.`id_criterion` = acpc.`id_criterion`
            AND ac.`id_criterion` IN (%acListCriterionsForSearchBloc%)
        )
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $search['id_search'].'` acp ON (acp.`id_cache_product` = acpc.`id_cache_product`)
        '.
        (Core::isFilledArray($leftJoinWhereCriterion['join']) ? implode("\n", $leftJoinWhereCriterion['join']) : '').
        (Core::isFilledArray($leftJoinWhereCriterion['where']) ? ' WHERE ' . implode("\n AND ", $leftJoinWhereCriterion['where']) : '') .
        ' GROUP BY ac.`id_criterion`'
        .($leftJoinWhereCriterion['make_union'] ? '' : '
        ORDER BY '.pSQL($field_order_by).' '.pSQL($groupInfos['sort_way']));
        if ($leftJoinWhereCriterion['make_union']) {
            if (!Core::isFilledArray($leftJoinWhereCriterion['joinUnion'])) {
                $leftJoinWhereCriterion['joinUnion'] = array();
            }
            $straightJoinCriterionLink = 'STRAIGHT_JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$search['id_search'].'_link` aclink ON (
                aclink.`id_criterion` = ac.`id_criterion`
                AND aclink.`id_criterion` IN (%acLinkCriterionsForSearchBloc%)
                AND ac.`id_criterion` IN (%acListCriterionsForSearchBloc%)
            )';
            $newJoin = array();
            $acLinkAdded = false;
            foreach ($leftJoinWhereCriterion['joinUnion'] as $k => $v) {
                if (!$acLinkAdded && preg_match('/aclink/', $v)) {
                    $acLinkAdded = true;
                    $newJoin[] = $straightJoinCriterionLink;
                }
                $newJoin[] = $v;
            }
            if (empty($acLinkAdded)) {
                $newJoin[] = $straightJoinCriterionLink;
            }
            $leftJoinWhereCriterion['joinUnion'] = $newJoin;
            $query .= ' UNION
             SELECT ac.*, aclink.`id_criterion_linked`, acl.id_lang, acl.icon, acl.`value`, acl.`decimal_value`,
             0 AS nb_product
             FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'` ac
             LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int) $id_lang.')
             '.
            (Core::isFilledArray($leftJoinWhereCriterion['joinUnion']) ? implode("\n", $leftJoinWhereCriterion['joinUnion']) : '').
            (Core::isFilledArray($leftJoinWhereCriterion['whereUnion']) ? ' WHERE ' . implode("\n AND ", $leftJoinWhereCriterion['whereUnion']) : '').
            '
            GROUP BY ac.`id_criterion`
             ) as tmp GROUP BY `id_criterion`
            ORDER BY '.pSQL($field_order_by).' '.pSQL($groupInfos['sort_way']);
        }
        foreach ($leftJoinWhereCriterion['setVariables'] as $variableName => $setVariableQuery) {
            $variableContent = SearchEngineDb::value($setVariableQuery);
            if (empty($variableContent)) {
                $variableContent = '0';
            }
            $query = str_replace("%$variableName%", $variableContent, $query);
        }
        $result = SearchEngineDb::query($query);
        pm_advancedsearch4::getModuleInstance()->putInSmartyCache($customCacheKey, $result);
        return $result;
    }
    public static function getDefaultGroupId()
    {
        return (int)Configuration::get('PS_UNIDENTIFIED_GROUP');
    }
    private static $getScoreQueryCache = array();
    public static function getScoreQuery($id_shop, $id_currency, $id_country, $id_group, $add_alias = true, $force_priority = false, $use_native_table = false)
    {
        $cacheKey = $id_shop.'-'.$id_currency.'-'.$id_country.'-'.$id_group.'-'.(int)$add_alias.'-'.(int)$force_priority.'-'.(int)$use_native_table;
        if (isset(self::$getScoreQueryCache[$cacheKey])) {
            return self::$getScoreQueryCache[$cacheKey];
        }
        if (!$use_native_table) {
            $select = '(IF(app.`valid_id_specific_price`=1 AND app.`is_specific`=1, 1, 0) + app.`has_no_specific` + ';
            $table_alias = 'app';
        } else {
            $select = '( ';
            $table_alias = 'sp';
        }
        $now = date('Y-m-d H:i:s');
        $select .= ' IF (('.$table_alias.'.`from` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' >= '.$table_alias.'.`from`) AND ('.$table_alias.'.`to` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' <= '.$table_alias.'.`to`), '.pow(2, 0).', 0) + ';
        $priority = preg_split('/;/', Configuration::get('PS_SPECIFIC_PRICE_PRIORITIES'));
        foreach (array_reverse($priority) as $k => $field) {
            if ($force_priority) {
                $select .= ' IF ('.$table_alias.'.`'.bqSQL($field).'` = '.(int)(${$field}).', '.pow(2, $k + 1).', 0) + ';
            } else {
                if ($field == 'id_group' && (int)${$field} > 0 && (int)${$field} != self::getDefaultGroupId()) {
                    $select .= ' IF ('.$table_alias.'.`'.bqSQL($field).'` IN (0, '.(int)(${$field}).'), '.pow(2, $k + 1).', 0) + ';
                } elseif ($field != 'id_group') {
                    $select .= ' IF ('.$table_alias.'.`'.bqSQL($field).'` IN (0, '.(int)(${$field}).'), '.pow(2, $k + 1).', 0) + ';
                }
            }
        }
        self::$getScoreQueryCache[$cacheKey] = rtrim($select, ' +').')'.($add_alias ? ' AS `score`' : '');
        return self::$getScoreQueryCache[$cacheKey];
    }
    public static function getCurrentCategory()
    {
        static $idCategory = null;
        if ($idCategory !== null) {
            return $idCategory;
        }
        if (Tools::getIsset('id_category_search') && Tools::getValue('id_category_search')) {
            $idCategory = (int)Tools::getValue('id_category_search');
        } elseif (Tools::getIsset('id_category') && Tools::getValue('id_category')) {
            $context = Context::getContext();
            if ($context->controller instanceof CategoryController) {
                if (method_exists($context->controller, 'getCategory') && Validate::isLoadedObject($context->controller->getCategory())) {
                    $idCategory = (int)$context->controller->getCategory()->id;
                } else {
                    $idCategory = (int)Tools::getValue('id_category', false);
                }
            }
        }
        if (empty($idCategory)) {
            $context = Context::getContext();
            $isFromHome = (Tools::getValue('productFilterListSource') == 'index');
            if ($context->controller instanceof IndexController || $isFromHome) {
                $idCategory = (int)$context->shop->getCategory();
            }
        }
        if (!empty($idCategory)) {
            $category = new Category($idCategory);
            if (!Validate::isLoadedObject($category)) {
                $idCategory = null;
            }
        }
        return $idCategory;
    }
    public static function getCurrentManufacturer()
    {
        static $idManufacturer = null;
        if ($idManufacturer !== null) {
            return $idManufacturer;
        }
        if (Tools::getIsset('id_manufacturer_search') && Tools::getValue('id_manufacturer_search')) {
            $idManufacturer = (int)Tools::getValue('id_manufacturer_search');
        } elseif (Tools::getIsset('id_manufacturer') && Tools::getValue('id_manufacturer')) {
            $context = Context::getContext();
            if (isset($context->controller->php_self) && $context->controller->php_self == 'manufacturer') {
                if (method_exists($context->controller, 'getManufacturer')) {
                    $idManufacturer = (int)$context->controller->getManufacturer()->id;
                } else {
                    $idManufacturer = (int)Tools::getValue('id_manufacturer', false);
                }
            }
        }
        if (!empty($idManufacturer)) {
            $manufacturer = new Manufacturer($idManufacturer);
            if (!Validate::isLoadedObject($manufacturer)) {
                $idManufacturer = null;
            }
        }
        return $idManufacturer;
    }
    public static function getCurrentSupplier()
    {
        static $idSupplier = null;
        if ($idSupplier !== null) {
            return $idSupplier;
        }
        if (Tools::getIsset('id_supplier_search') && Tools::getValue('id_supplier_search')) {
            $idSupplier = (int)Tools::getValue('id_supplier_search');
        } elseif (Tools::getIsset('id_supplier') && Tools::getValue('id_supplier')) {
            $context = Context::getContext();
            if (isset($context->controller->php_self) && $context->controller->php_self == 'supplier') {
                if (method_exists($context->controller, 'getSupplier')) {
                    $idSupplier = (int)$context->controller->getSupplier()->id;
                } else {
                    $idSupplier = (int)Tools::getValue('id_supplier', false);
                }
            }
        }
        if (!empty($idSupplier)) {
            $supplier = new Supplier($idSupplier);
            if (!Validate::isLoadedObject($supplier)) {
                $idSupplier = null;
            }
        }
        return $idSupplier;
    }
    public static function getCurrentCMS()
    {
        static $idCms = null;
        if ($idCms !== null) {
            return $idCms;
        }
        if (Tools::getIsset('id_cms_search') && Tools::getValue('id_cms_search')) {
            $idCms = (int)Tools::getValue('id_cms_search');
        } elseif (Tools::getIsset('id_cms') && Tools::getValue('id_cms')) {
            $context = Context::getContext();
            if (isset($context->controller->php_self)
                && $context->controller->php_self == 'cms'
                && Validate::isLoadedObject($context->controller->cms)
            ) {
                $idCms = (int)$context->controller->cms->id;
            }
        }
        if (!empty($idCms)) {
            $cms = new CMS($idCms);
            if (!Validate::isLoadedObject($cms)) {
                $idCms = null;
            }
        }
        return $idCms;
    }
    private static $getCriteriaFromEmplacementCache = array();
    public static function getCriteriaFromEmplacement($id_search, $id_category_root = false)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$getCriteriaFromEmplacementCache[$cacheKey])) {
            return self::$getCriteriaFromEmplacementCache[$cacheKey];
        }
        if ($id_category_root !== false && is_numeric($id_category_root) && (int)$id_category_root > 0) {
            $criterion = self::getCriterionsWithIdGroupFromIdLinked('category', (int)$id_category_root, (int)$id_search);
        } elseif ($id_category = self::getCurrentCategory()) {
            $criterion = self::getCriterionsWithIdGroupFromIdLinked('category', $id_category, (int)$id_search);
        } elseif ($id_manufacturer = self::getCurrentManufacturer()) {
            $criterion = self::getCriterionsWithIdGroupFromIdLinked('manufacturer', $id_manufacturer, (int)$id_search);
        } elseif ($id_supplier = self::getCurrentSupplier()) {
            $criterion = self::getCriterionsWithIdGroupFromIdLinked('supplier', $id_supplier, (int)$id_search);
        }
        if (isset($criterion) && Core::isFilledArray($criterion)) {
            $criterion = Core::arrayMapRecursive('intval', $criterion);
        }
        self::$getCriteriaFromEmplacementCache[$cacheKey] = (isset($criterion) && $criterion ? $criterion : array());
        return self::$getCriteriaFromEmplacementCache[$cacheKey];
    }
    public static function getCriterionsRange($search, $id_criterion_group, $id_lang, $selected_criterion = array(), $selected_criteria_groups_type = array(), $id_currency = false, $id_group = false, $groupInfos = false)
    {
        $cacheKey = func_get_args();
        $customCacheKey = 'pm_advancedsearch|' . (int)$search['id_search'] . '|customCache|getCriterionsRange|' . (int)$id_criterion_group . '|' . sha1(serialize($cacheKey));
        $resultFromSmartyCache = pm_advancedsearch4::getModuleInstance()->getFromSmartyCache($customCacheKey);
        if ($resultFromSmartyCache !== null) {
            return $resultFromSmartyCache;
        }
        $id_country = (int)Context::getContext()->country->id;
        $search['display_empty_criteria'] = false;
        $leftJoinWhereCriterion = self::makeLeftJoinWhereCriterion('getCriterionsRange', $search, $id_lang, $selected_criterion, $selected_criteria_groups_type, $id_criterion_group, ($groupInfos['criterion_group_type'] == 'attribute'), $id_currency, $id_country, $id_group);
        $row = SearchEngineDb::row('
                SELECT FLOOR(MIN(acl.`decimal_value`)) AS `min`, CEIL(MAX(acl.`decimal_value`)) AS `max`
                FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'` ac
                JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int) $search['id_search'].'` acpc ON (ac.`id_criterion` = acpc.`id_criterion`)
                LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $search['id_search'].'` acp ON ( acp.`id_cache_product` = acpc.`id_cache_product`)
                '.
                (Core::isFilledArray($leftJoinWhereCriterion['join']) ? implode("\n", $leftJoinWhereCriterion['join']) : '').
                ($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $search['id_search'].'_lang` acl ON (ac.`id_criterion` = acl.`id_criterion` AND acl.`id_lang` = '.(int) $id_lang.')' : '').
                ' WHERE ac.`id_criterion_group` = '.(int)$id_criterion_group.
                (Core::isFilledArray($leftJoinWhereCriterion['where']) ? ' AND ' . implode("\n AND ", $leftJoinWhereCriterion['where']) : '')
                . ' AND ac.`visible`=1');
        $return = array(0 => array(
            'min' => (float)$row['min'],
            'max' => (float)$row['max'],
        ));
        pm_advancedsearch4::getModuleInstance()->putInSmartyCache($customCacheKey, $return);
        return $return;
    }
    public static function setupMinMaxUsingStep($step, &$min, &$max = null)
    {
        $step = (float)$step;
        if ($step > 1) {
            if ((int)$min == 0 && (int)$max == 0) {
                return;
            }
            if (is_numeric($min)) {
                $min = $min - ($min % $step);
            }
            if ($max !== null && is_numeric($max)) {
                $max = $max - ($max % $step) + $step;
            }
        }
    }
    public static function getGroupReducedPrice($id_product, $id_group, $price)
    {
        $reduction_from_category = GroupReduction::getValueForProduct($id_product, $id_group);
        if ($reduction_from_category !== false) {
            $price -= $price * (float)$reduction_from_category;
        } else {
            $price *= ((100 - Group::getReductionByIdGroup($id_group)) / 100);
        }
        return $price;
    }
    public static function getMinIdProductSlider($search, $id_criterion_group, $id_currency, $id_country, $id_group, $selected_criterion = array(), $selected_criteria_groups_type = array())
    {
        $context = Context::getContext();
        if (!$id_country) {
            $id_country = (int)$context->country->id;
        }
        $search['display_empty_criteria'] = false;
        $leftJoinWhereCriterion = self::makeLeftJoinWhereCriterion('getPriceRangeForSearchBloc', $search, (int)$context->language->id, $selected_criterion, $selected_criteria_groups_type, $id_criterion_group, false, $id_currency, $id_country, $id_group, true, true);
        $now = date('Y-m-d H:i:s');
        list($taxConversion, $taxConversionForReduction, $specificPriceCondition, $specificPriceGroupCondition) = self::getPriceRangeConditions($id_group);
        if ($leftJoinWhereCriterion['priceIncluded'] || $leftJoinWhereCriterion['productTableIncluded']) {
            $specificPriceCondition .= $specificPriceGroupCondition;
        }
        $query_min = '
        SELECT acp.id_product, '.self::getScoreQuery(Context::getContext()->shop->id, $id_currency, $id_country, $id_group).'
        FROM `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int) $search['id_search'].'` app
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $search['id_search'].'` acp ON (app.`id_cache_product` = acp.`id_cache_product`)'.
        (Core::isFilledArray($leftJoinWhereCriterion['join']) ? implode("\n", $leftJoinWhereCriterion['join']) : '').
        ' WHERE ((app.`from` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' >= app.`from`) AND (app.`to` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' <= app.`to`)) AND '.
        ' ((app.`valid_id_specific_price`=1 AND app.`is_specific`=1 AND app.`id_currency` IN (0, '.(int)$id_currency.')) OR app.`has_no_specific`=1) AND '.
        (Core::isFilledArray($leftJoinWhereCriterion['where']) ? implode("\n AND ", $leftJoinWhereCriterion['where']) :' 1 ').
        ' AND app.`id_country` IN (0, '.(int)$id_country.') '.
        ' AND app.`id_group` IN (0, '.(int)$id_group.') '.
        ' AND app.`id_shop` IN (0, '.implode(', ', array_map('intval', Shop::getContextListShopID())).') ' .
        ' ORDER BY score DESC, '. $specificPriceCondition .' ASC';
        $row = SearchEngineDb::row($query_min);
        if (isset($row['id_product']) && $row['id_product']) {
            return $row['id_product'];
        }
        return false;
    }
    public static function getMaxIdProductSlider($search, $id_criterion_group, $id_currency, $id_country, $id_group, $selected_criterion = array(), $selected_criteria_groups_type = array())
    {
        $context = Context::getContext();
        if (!$id_country) {
            $id_country = (int)$context->country->id;
        }
        $search['display_empty_criteria'] = false;
        $leftJoinWhereCriterion = self::makeLeftJoinWhereCriterion('getPriceRangeForSearchBloc', $search, (int)$context->language->id, $selected_criterion, $selected_criteria_groups_type, $id_criterion_group, false, $id_currency, $id_country, $id_group, true, true);
        $now = date('Y-m-d H:i:s');
        list($taxConversion, $taxConversionForReduction, $specificPriceCondition, $specificPriceGroupCondition) = self::getPriceRangeConditions($id_group);
        if ($leftJoinWhereCriterion['priceIncluded'] || $leftJoinWhereCriterion['productTableIncluded']) {
            $specificPriceCondition .= $specificPriceGroupCondition;
        }
        $query_max = '
        SELECT acp.id_product, '.self::getScoreQuery(Context::getContext()->shop->id, $id_currency, $id_country, $id_group).'
        FROM `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int) $search['id_search'].'` app
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $search['id_search'].'` acp ON (app.`id_cache_product` = acp.`id_cache_product`)'.
        (Core::isFilledArray($leftJoinWhereCriterion['join']) ? implode("\n", $leftJoinWhereCriterion['join']) : '').
        ' WHERE ((app.`from` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' >= app.`from`) AND (app.`to` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' <= app.`to`)) AND '.
        ' ((app.`valid_id_specific_price`=1 AND app.`is_specific`=1 AND app.`id_currency` IN (0, '.(int)$id_currency.')) OR app.`has_no_specific`=1) AND '.
        (Core::isFilledArray($leftJoinWhereCriterion['where']) ? implode("\n AND ", $leftJoinWhereCriterion['where']) : ' 1 ').
        ' AND app.`id_country` IN (0, '.(int)$id_country.') '.
        ' AND app.`id_group` IN (0, '.(int)$id_group.') '.
        ' AND app.`id_shop` IN (0, '.implode(', ', array_map('intval', Shop::getContextListShopID())).') ' .
        ' ORDER BY score DESC, '. $specificPriceCondition .' DESC';
        $row = SearchEngineDb::row($query_max);
        if (isset($row['id_product']) && $row['id_product']) {
            return $row['id_product'];
        }
        return false;
    }
    public static function getPriceRangeForSearchBloc($search, $id_criterion_group, $id_currency, $id_country, $id_group, $selected_criterion = array(), $selected_criteria_groups_type = array())
    {
        $cacheKey = func_get_args();
        $customCacheKey = 'pm_advancedsearch|' . (int)$search['id_search'] . '|customCache|getPriceRangeForSearchBloc|' . (int)$id_criterion_group . '|' . sha1(serialize($cacheKey));
        $resultFromSmartyCache = pm_advancedsearch4::getModuleInstance()->getFromSmartyCache($customCacheKey);
        if ($resultFromSmartyCache !== null) {
            return $resultFromSmartyCache;
        }
        $context = Context::getContext();
        $search['display_empty_criteria'] = false;
        $leftJoinWhereCriterion = self::makeLeftJoinWhereCriterion('getPriceRangeForSearchBloc', $search, (int)$context->language->id, $selected_criterion, $selected_criteria_groups_type, $id_criterion_group, false, $id_currency, $id_country, $id_group, true, true);
        $now = date('Y-m-d H:i:s');
        $minIdProduct = self::getMinIdProductSlider($search, $id_criterion_group, $id_currency, $id_country, $id_group, $selected_criterion, $selected_criteria_groups_type);
        list($taxConversion, $taxConversionForReduction, $specificPriceCondition, $specificPriceGroupCondition) = self::getPriceRangeConditions($id_group);
        if ($leftJoinWhereCriterion['nbSelectedCriterions'] > 0 && ($leftJoinWhereCriterion['priceIncluded'] || $leftJoinWhereCriterion['productTableIncluded'])) {
            $specificPriceCondition .= $specificPriceGroupCondition;
        }
        $return = [
            0 => [
                'min_price' => 0,
                'max_price' => 0,
                'min_price_id_currency' => $id_currency,
                'max_price_id_currency' => $id_currency,
            ],
        ];
        $query_min = '
        SELECT app.price_wt as min_price, app.`reduction_amount`, app.`reduction_type`, app.`reduction_tax`, app.id_currency, acp.id_product, app.id_country, '.self::getScoreQuery(Context::getContext()->shop->id, $id_currency, $id_country, $id_group, true, true).'
        FROM `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int) $search['id_search'].'` app
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $search['id_search'].'` acp ON (app.`id_cache_product` = acp.`id_cache_product`)'.
        (Core::isFilledArray($leftJoinWhereCriterion['join']) ? implode("\n", $leftJoinWhereCriterion['join']) : '').
        ' WHERE acp.`id_product` = ' . (int)$minIdProduct . ' AND ' .
        ' ((app.`from` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' >= app.`from`) AND (app.`to` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' <= app.`to`)) AND '.
        ' ((app.`valid_id_specific_price`=1 AND app.`is_specific`=1 AND app.`id_currency` IN (0, '.(int)$id_currency.')) OR app.`has_no_specific`=1) AND '.
        (Core::isFilledArray($leftJoinWhereCriterion['where']) ? implode("\n AND ", $leftJoinWhereCriterion['where']) : ' 1 ').
        ' AND app.`id_country` IN (0, '.(int)$id_country.') '.
        ' AND app.`id_group` IN (0, '.(int)$id_group.') '.
        ' AND app.`id_shop` IN (0, '.implode(', ', array_map('intval', Shop::getContextListShopID())).') '.
        ' ORDER BY score DESC, '. $specificPriceCondition .' ASC';
        $row = SearchEngineDb::row($query_min);
        if (empty($row)) {
            return $return;
        }
        $tax_rate = Tax::getProductTaxRate((int)$row['id_product']);
        $reduction_amount = $row['reduction_amount'];
        $reduction_type = $row['reduction_type'];
        $reduction_tax = $row['reduction_tax'];
        if (floor($row['min_price']) == 0) {
            $reduction_amount = 0;
        }
        if (Product::$_taxCalculationMethod != PS_TAX_EXC) {
            if ($reduction_type == 'amount') {
                if (!$reduction_tax) {
                    $reduction_amount = $reduction_amount * (1 + ($tax_rate / 100));
                }
                $price_ttc = (($row['min_price'] * (1 + ($tax_rate / 100))) - $reduction_amount);
            } else {
                $price_ttc = ((($row['min_price'] - $reduction_amount) * (1 + ($tax_rate / 100))));
            }
            $return[0]['min_price'] = floor($price_ttc);
        } else {
            if ($reduction_type == 'amount') {
                if ($reduction_tax) {
                    $reduction_amount = ($reduction_amount / (1 + ($tax_rate / 100)));
                }
            }
            $return[0]['min_price'] = floor($row['min_price']-$reduction_amount);
        }
        $return[0]['min_price_id_currency'] = $row['id_currency'];
        $return[0]['min_price'] = self::getGroupReducedPrice((int)$row['id_product'], $id_group, $return[0]['min_price']);
        $maxIdProduct = self::getMaxIdProductSlider($search, $id_criterion_group, $id_currency, $id_country, $id_group, $selected_criterion, $selected_criteria_groups_type);
        $query_max = '
        SELECT app.price_wt as max_price, app.`reduction_amount`, app.`reduction_type`, app.`reduction_tax`, acp.id_product, app.id_currency, app.id_country, '.self::getScoreQuery(Context::getContext()->shop->id, $id_currency, $id_country, $id_group, true, true).'
        FROM `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int) $search['id_search'].'` app
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $search['id_search'].'` acp ON (app.`id_cache_product` = acp.`id_cache_product`)'.
        (Core::isFilledArray($leftJoinWhereCriterion['join']) ? implode("\n", $leftJoinWhereCriterion['join']) : '').
        ' WHERE acp.`id_product` = ' . (int)$maxIdProduct . ' AND ' .
        ' ((app.`from` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' >= app.`from`) AND (app.`to` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' <= app.`to`)) AND '.
        ' ((app.`valid_id_specific_price`=1 AND app.`is_specific`=1 AND app.`id_currency` IN (0, '.(int)$id_currency.')) OR app.`has_no_specific`=1) AND '.
        (Core::isFilledArray($leftJoinWhereCriterion['where']) ? implode("\n AND ", $leftJoinWhereCriterion['where']) : ' 1 ').
        ' AND app.`id_country` IN (0, '.(int)$id_country.') '.
        ' AND app.`id_group` IN (0, '.(int)$id_group.') '.
        ' AND app.`id_shop` IN (0, '.implode(', ', array_map('intval', Shop::getContextListShopID())).') ' .
        ' ORDER BY score DESC, '. $specificPriceCondition .' DESC';
        $row = SearchEngineDb::row($query_max);
        $tax_rate = Tax::getProductTaxRate((int)$row['id_product']);
        $reduction_amount = $row['reduction_amount'];
        $reduction_type = $row['reduction_type'];
        $reduction_tax = $row['reduction_tax'];
        if (Product::$_taxCalculationMethod != PS_TAX_EXC) {
            if ($reduction_type == 'amount') {
                if (!$reduction_tax) {
                    $reduction_amount = $reduction_amount * (1 + ($tax_rate / 100));
                }
                $price_ttc = (($row['max_price'] * (1 + ($tax_rate / 100))) - $reduction_amount);
            } else {
                $price_ttc = ((($row['max_price'] - $reduction_amount) * (1 + ($tax_rate / 100))));
            }
            $return[0]['max_price'] = ceil($price_ttc);
        } else {
            if ($reduction_type == 'amount') {
                if ($reduction_tax) {
                    $reduction_amount = ($reduction_amount / (1 + ($tax_rate / 100)));
                }
            }
            $return[0]['max_price'] = ceil($row['max_price']-$reduction_amount);
        }
        $return[0]['max_price_id_currency'] = $row['id_currency'];
        $return[0]['max_price'] = self::getGroupReducedPrice((int)$row['id_product'], $id_group, $return[0]['max_price']);
        pm_advancedsearch4::getModuleInstance()->putInSmartyCache($customCacheKey, $return);
        return $return;
    }
    public static function getProductsSearched($id_search, $selected_criterion = array(), $selected_criteria_groups_type = array(), $p = null, $n = null, $getTotal = false, $productSearchQuery = null)
    {
        if ($p < 1) {
            $p = 1;
        }
        $context = Context::getContext();
        $id_country = (int)$context->country->id;
        $id_currency = (int)$context->currency->id;
        $id_group = (int)pm_advancedsearch4::getModuleInstance()->getCurrentCustomerGroupId();
        $id_lang = (int)$context->language->id;
        $search = current(self::getSearch((int)$id_search, $id_lang));
        $orderByPrefix = '';
        $orderBy = SearchEngineUtils::getOrderByValue($search, $productSearchQuery);
        $orderWay = SearchEngineUtils::getOrderWayValue($search, $productSearchQuery);
        $splitDone = false;
        if ($orderBy == 'date_upd' || $orderBy == 'date_add') {
            $orderByPrefix = 'ps';
        } elseif ($orderBy == 'quantity') {
            $orderByPrefix = null;
        } elseif ($orderBy == 'id_product' || $orderBy == 'date_upd' || $orderBy == 'date_add' || $orderBy == 'reference') {
            $orderByPrefix = 'p';
        } elseif ($orderBy == 'name') {
            $orderByPrefix = 'pl';
        } elseif ($orderBy == 'manufacturer') {
            $orderByPrefix = 'm';
            $orderBy = 'name';
        } elseif ($orderBy == 'position' && !empty(self::$productFilterListAdditionalColumns)) {
            $orderByPrefix = '';
        } elseif ($orderBy == 'position') {
            $orderByPrefix = 'cp';
            $id_category = (int)self::getCurrentCategory();
            $idCriterionCategoryList = array();
            if (!empty($id_category)) {
                $idCriterionCategoryList[] = (int)$id_category;
            }
            foreach (array_reverse($selected_criteria_groups_type, true) as $selectedIdCriterionGroup => $selectedCriterionGroup) {
                if ($selectedCriterionGroup['criterion_group_type'] == 'category' && !empty($selectedCriterionGroup['visible'])) {
                    if (isset($selected_criterion[$selectedIdCriterionGroup]) && is_array($selected_criterion[$selectedIdCriterionGroup]) && sizeof($selected_criterion[$selectedIdCriterionGroup])) {
                        $selectedCategoryIdCriterion = end($selected_criterion[$selectedIdCriterionGroup]);
                        $selectedIdCategoryList = Criterion::getIdCriterionLinkByIdCriterion($id_search, $selectedCategoryIdCriterion);
                        if (is_array($selectedIdCategoryList) && sizeof($selectedIdCategoryList)) {
                            $id_category = current($selectedIdCategoryList);
                            if (!empty($id_category)) {
                                $idCriterionCategoryList[] = $id_category;
                            }
                        }
                    }
                }
            }
            $idCriterionCategoryList = array_unique($idCriterionCategoryList);
            if (sizeof($idCriterionCategoryList) > 1) {
                $id_category = (int)self::getHighestLevelDepthCategory($idCriterionCategoryList);
            }
        } elseif ($orderBy == 'sales') {
            $orderByPrefix = 'p_sale';
            $orderBy = 'quantity';
        }
        if ($orderBy == 'price') {
            $orderBy = 'orderprice';
        }
        if (!Validate::isOrderBy($orderBy) or !Validate::isOrderWay($orderWay)) {
            die(Tools::displayError());
        }
        $cacheKey = func_get_args();
        unset($cacheKey[3], $cacheKey[4]);
        if (!$getTotal) {
            $cacheKey += array('p' => (int)$p);
            $cacheKey += array('n' => (int)$n);
            $cacheKey += array('orderBy' => (!empty($orderByPrefix) ? $orderByPrefix . '.' : '') . $orderBy);
            $cacheKey += array('orderWay' => $orderWay);
        }
        $cacheKey += array('spa_active' => self::isSPAModuleActive());
        $customCacheKey = 'pm_advancedsearch|' . (int)$search['id_search'] . '|customCache|getProductsSearched|' . sha1(serialize($cacheKey));
        $resultFromSmartyCache = pm_advancedsearch4::getModuleInstance()->getFromSmartyCache($customCacheKey);
        if ($resultFromSmartyCache !== null) {
            return $resultFromSmartyCache;
        }
        if ($getTotal && !self::isSPAModuleActive()) {
            $result = SearchEngineDb::row(self::getQueryCountResults($search, $id_lang, $selected_criterion, $selected_criteria_groups_type, $id_currency, $id_country, $id_group));
            $productCount = isset($result) ? (int)$result['total'] : 0;
            pm_advancedsearch4::getModuleInstance()->putInSmartyCache($customCacheKey, $productCount);
            return $productCount;
        }
        $leftJoinWhereCriterion = self::makeLeftJoinWhereCriterion('getProductsSearched', $search, $id_lang, $selected_criterion, $selected_criteria_groups_type, false, false, $id_currency, $id_country, $id_group, true, true);
        foreach ($leftJoinWhereCriterion['join'] as $leftJoinWhereCriterionJoinKey => $leftJoinWhereCriterionJoinValue) {
            if ($leftJoinWhereCriterionJoinKey == 'product_shop' || $leftJoinWhereCriterionJoinKey == 'product') {
                continue;
            }
            if (preg_match('#'.preg_quote('`'._DB_PREFIX_.'product`').'#', $leftJoinWhereCriterionJoinValue)
            && !preg_match('#specific_max_score#', $leftJoinWhereCriterionJoinValue)) {
                unset($leftJoinWhereCriterion['join'][$leftJoinWhereCriterionJoinKey]);
            }
        }
        $minAttributesLeftJoin = $leftJoinWhereCriterion['join'];
        foreach ($minAttributesLeftJoin as $minAttributesLeftJoin_k => $minAttributesLeftJoin_value) {
            if (preg_match('#' . preg_quote('`'._DB_PREFIX_.'tax`') . '|' . preg_quote('`'._DB_PREFIX_.'tax_rule`') . '|' . preg_quote('`'._DB_PREFIX_.'group_reduction`') . '#', $minAttributesLeftJoin_value)) {
                unset($minAttributesLeftJoin[$minAttributesLeftJoin_k]);
            }
        }
        $sql_min_attribute_price = '
            SELECT * FROM
            (
                SELECT pas.*
                FROM '._DB_PREFIX_.'product_attribute_shop pas
                JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $search['id_search'].'` acp ON (acp.id_product = pas.id_product)
                '. (Core::isFilledArray($minAttributesLeftJoin) ? implode("\n", $minAttributesLeftJoin):''). '
                WHERE ' .
                ((!empty($search['add_anchor_to_url']) || !empty($search['priority_on_combination_image'])) && !empty($leftJoinWhereCriterion['lastAttributeCombinationTableId']) ? ' pas.`id_product_attribute`=' . $leftJoinWhereCriterion['lastAttributeCombinationTableId'] . '.`id_product_attribute`' : ' pas.`default_on` = 1') . ' AND pas.`id_shop`='.(int)Context::getContext()->shop->id.'
                GROUP BY pas.id_product, pas.id_product_attribute
                ORDER BY pas.id_product, MIN(pas.price)
            ) AS t
            GROUP BY id_product
        ';
        $sql = '
        SELECT p.*, ps.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, product_attribute_shop.id_product_attribute AS id_product_attribute, product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity, pl.`description`, pl.`description_short`, pl.`available_now`,
        pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, image_shop.`id_image` id_image,
        il.`legend`, m.`name` AS manufacturer_name, cl.`name` AS category_default,
        DATEDIFF(ps.`date_add`, DATE_SUB("'.date('Y-m-d').' 00:00:00",
        INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0 AS new, (ps.`price` + IFNULL(product_attribute_shop.price, 0)) AS orderprice' .
        (!empty(self::$productFilterListAdditionalColumns) ? self::$productFilterListAdditionalColumns : '') .
        ($leftJoinWhereCriterion && isset($leftJoinWhereCriterion['select']) && sizeof($leftJoinWhereCriterion['select']) ? ', '.implode(', ', $leftJoinWhereCriterion['select']) : '')
        .' FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $search['id_search'].'` acp'.
        (Core::isFilledArray($leftJoinWhereCriterion['join']) ? implode("\n", $leftJoinWhereCriterion['join']):'');
        $sql .=
        '
        LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
        ON (
            p.`id_product` = product_attribute_shop.`id_product`' .
            ((!empty($search['add_anchor_to_url']) || !empty($search['priority_on_combination_image'])) && !empty($leftJoinWhereCriterion['lastAttributeCombinationTableId']) ? ' AND product_attribute_shop.`id_product_attribute`=' . $leftJoinWhereCriterion['lastAttributeCombinationTableId'] . '.`id_product_attribute`' : ' AND product_attribute_shop.`default_on` = 1') . ' AND product_attribute_shop.`id_shop`='.(int)Context::getContext()->shop->id.'
        )
        ';
        $sql .= Product::sqlStock('p', ((!empty($search['add_anchor_to_url']) || !empty($search['priority_on_combination_image'])) && !empty($leftJoinWhereCriterion['lastAttributeCombinationTableId']) ? $leftJoinWhereCriterion['lastAttributeCombinationTableId'] : 'product_attribute_shop'), false, Context::getContext()->shop);
        if ($orderBy == 'position') {
            $sql .= ' LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_product` = p.`id_product` AND cp.`id_category` = ps.`id_category_default`) ';
            if (isset($id_category) && $id_category) {
                $sql .= ' LEFT JOIN `'._DB_PREFIX_.'category_product` cp_custom ON (cp_custom.`id_product` = p.`id_product` AND cp_custom.`id_category` = '.(int)($id_category).') ';
            }
        }
        $sql .= (!empty($orderByPrefix) && $orderByPrefix == 'p_sale' && $orderBy == 'quantity' ? ' LEFT JOIN `'._DB_PREFIX_.'product_sale` p_sale ON (p_sale.`id_product` = p.`id_product`) ' : '') . '
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)$id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
        LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (ps.`id_category_default` = cl.`id_category` AND cl.`id_lang` = '.(int)$id_lang . Shop::addSqlRestrictionOnLang('cl') . ')';
        $sql .= '
        LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop ON (image_shop.`id_product` = p.`id_product` AND image_shop.`cover`=1 AND image_shop.`id_shop`='.(int)Context::getContext()->shop->id.')
        ';
        $sql .= '
        LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
        LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
        ';
        if (Core::isFilledArray($leftJoinWhereCriterion['where'])) {
            $sql .= ' WHERE ' . implode("\n AND ", $leftJoinWhereCriterion['where']);
        }
        
        // MOD
        $schemas = false;
        if (isset($context->cookie) AND isset($context->cookie->schemas) and $context->cookie->schemas) {
            $schemas = $context->cookie->schemas;
        }
        $sql .= ($schemas) ? ' AND p.`reference` LIKE "%SPL%"' : '';
        // /MOD

        $sql .= ' GROUP BY ps.`id_product`';
        if ($orderBy == 'position' && isset($id_category) && $id_category) {
            $sql .= ' ORDER BY IFNULL(cp_custom.`position`, cp.`position`) '.pSQL($orderWay);
        } else {
            $sql .= ' ORDER BY '.(!empty($orderByPrefix) ? pSQL($orderByPrefix).'.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay);
        }
        if (!self::isSPAModuleActive()) {
            $sql .= ' LIMIT '.(((int)($p) - 1) * (int)($n)).','.(int)($n);
        }
        $result = SearchEngineDb::query($sql);
        if ((!empty($search['add_anchor_to_url']) || !empty($search['priority_on_combination_image'])) && !empty($leftJoinWhereCriterion['lastAttributeCombinationTableId'])) {
            $min_attribute_price_result = SearchEngineDb::query($sql_min_attribute_price);
            if (Core::isFilledArray($min_attribute_price_result)) {
                foreach ($min_attribute_price_result as $min_attribute_price_result_row) {
                    foreach ($result as &$result_row) {
                        if ($result_row['id_product'] == $min_attribute_price_result_row['id_product']) {
                            $result_row = array_merge($result_row, $min_attribute_price_result_row);
                            $result_row['orderprice'] = $result_row['price'] + $min_attribute_price_result_row['price'];
                            break;
                        }
                    }
                }
            }
        }
        if ($orderBy == 'orderprice') {
            Tools::orderbyPrice($result, $orderWay);
        }
        $nbProducts = 0;
        if (!$result) {
            if ($getTotal) {
                return 0;
            } else {
                return array();
            }
        } else {
            $nbProducts = sizeof($result);
            if (self::isSPAModuleActive()) {
                $hookExecuted = false;
                $spaInstance = Module::getInstanceByName('pm_productsbyattributes');
                $spaInstance->hookActionProductListOverride(array(
                    'nbProducts' => &$nbProducts,
                    'catProducts' => &$result,
                    'hookExecuted' => &$hookExecuted,
                    'splitDone' => &$splitDone,
                    'products_per_page' => $search['products_per_page'],
                    'selected_criterion' => $selected_criterion,
                    'selected_criteria_groups_type' => $selected_criteria_groups_type,
                    'module' => _PM_AS_MODULE_NAME_,
                    'id_search' => (int)$id_search,
                    'p' => (int)$p,
                    'n' => (int)$n,
                ));
            }
        }
        if ($getTotal) {
            $result = $nbProducts;
        } else {
            if (!self::isSPAModuleActive() || !$splitDone) {
                $result = self::getProductsProperties($id_lang, $result, $search, $leftJoinWhereCriterion);
            } else {
                $result = $result;
            }
        }
        pm_advancedsearch4::getModuleInstance()->putInSmartyCache($customCacheKey, $result);
        return $result;
    }
    public static function getProductsProperties($id_lang, $result, $search, $leftJoinWhereCriterionResult)
    {
        $addAnchor = !empty($search['add_anchor_to_url']);
        $imagePriorityOnCombination = !empty($search['priority_on_combination_image']) && !empty($leftJoinWhereCriterionResult['isAttributeSelected']);
        if (is_array($result) && sizeof($result)) {
            foreach ($result as &$row) {
                if (!isset($row['quantity']) || (isset($row['quantity']) && $row['quantity'] == null)) {
                    $row['quantity'] = Product::getQuantity($row['id_product'], $row['id_product_attribute'], isset($row['cache_is_pack']) ? $row['cache_is_pack'] : null);
                }
                if (empty($row['id_image']) && (empty($row['id_product_attribute']) || !$imagePriorityOnCombination)) {
                    $cover = Product::getCover((int)$row['id_product']);
                    if (!empty($cover['id_image'])) {
                        $row['id_image'] = $cover['id_image'];
                    }
                }
                if ($imagePriorityOnCombination && version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
                    $combination_image = Image::getBestImageAttribute((int)Context::getContext()->shop->id, (int)Context::getContext()->language->id, (int)$row['id_product'], (int)$row['id_product_attribute']);
                    if (!empty($combination_image['id_image'])) {
                        $row['cover_image_id'] = (int)$row['id_product'] . '-' . (int)$combination_image['id_image'];
                    }
                }
            }
        }
        $result = Product::getProductsProperties($id_lang, $result);
        if (($addAnchor || $imagePriorityOnCombination) && is_array($result) && sizeof($result)) {
            foreach ($result as &$row) {
                if (!empty($row['id_product_attribute'])) {
                    if ($addAnchor) {
                        $row['link'] = Context::getContext()->link->getProductLink((int)$row['id_product'], $row['link_rewrite'], $row['category'], $row['ean13'], null, null, $row['id_product_attribute'], Configuration::get('PS_REWRITING_SETTINGS'), false, true);
                    }
                    if ($imagePriorityOnCombination && version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
                        $combination_image = Image::getBestImageAttribute((int)Context::getContext()->shop->id, (int)Context::getContext()->language->id, (int)$row['id_product'], (int)$row['id_product_attribute']);
                        if (!empty($combination_image['id_image'])) {
                            $row['id_image'] = (int)$row['id_product'] . '-' . (int)$combination_image['id_image'];
                        } else {
                            $cover = Product::getCover((int)$row['id_product']);
                            if (!empty($cover['id_image'])) {
                                $row['id_image'] = $cover['id_image'];
                            }
                        }
                    }
                }
            }
        }
        if (isset(Context::getContext()->controller) && method_exists(Context::getContext()->controller, 'addColorsToProductList') && !self::isSPAHideColorSquares()) {
            Context::getContext()->controller->addColorsToProductList($result);
        }
        return $result;
    }
    public static function arrayValuesRecursive($array)
    {
        $arrayValues = array();
        if (Core::isFilledArray($array)) {
            foreach ($array as $value) {
                if (is_scalar($value) or is_resource($value)) {
                    $arrayValues[] = $value;
                } elseif (is_array($value)) {
                    $arrayValues = array_merge($arrayValues, self::arrayValuesRecursive($value));
                }
            }
        }
        return $arrayValues;
    }
    private static $allowChildCategorySearchCache = array();
    public static function allowChildCategorySearch($id_search, $id_criterion_group)
    {
        $cacheKey = $id_search.'-allowChildCategorySearch';
        if (isset(self::$allowChildCategorySearchCache[$cacheKey])) {
            return in_array((int)$id_criterion_group, self::$allowChildCategorySearchCache[$cacheKey]);
        }
        $row = SearchEngineDb::row('
            SELECT GROUP_CONCAT(`id_criterion_group`) AS `id_criterion_group_list`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'`
            WHERE `criterion_group_type` = "category" AND `only_children` = 1');
        self::$allowChildCategorySearchCache[$cacheKey] = isset($row['id_criterion_group_list']) ? explode(',', $row['id_criterion_group_list']) : array();
        return in_array((int)$id_criterion_group, self::$allowChildCategorySearchCache[$cacheKey]);
    }
    private static $getCriterionGroupTypeAndDisplayCache = array();
    public static function getCriterionGroupTypeAndDisplay($id_search, $id_criterion_group)
    {
        $cacheKey = $id_search.'-'.$id_criterion_group;
        if (isset(self::$getCriterionGroupTypeAndDisplayCache[$cacheKey])) {
            return self::$getCriterionGroupTypeAndDisplayCache[$cacheKey];
        }
        $row = SearchEngineDb::row('
        SELECT `criterion_group_type`, `display_type`, `range`, `id_criterion_group_linked`, `sort_by`, `sort_way`, `is_combined`, `is_multicriteria`, `visible`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$id_search.'`
        WHERE `id_criterion_group` = '.(int)($id_criterion_group));
        self::$getCriterionGroupTypeAndDisplayCache[$cacheKey] = isset($row['criterion_group_type']) ? $row : false;
        return self::$getCriterionGroupTypeAndDisplayCache[$cacheKey];
    }
    public static function getCriterionGroupsTypeAndDisplay($id_search, $id_criterion_groups)
    {
        $criterion_groups_type = array();
        foreach ($id_criterion_groups as $id_criterion_group) {
            $criterionGroupInfos = self::getCriterionGroupTypeAndDisplay($id_search, $id_criterion_group);
            if (is_array($criterionGroupInfos)) {
                $criterion_groups_type[$id_criterion_group] = $criterionGroupInfos;
            }
        }
        return $criterion_groups_type;
    }
    public static function getHookName($idHook)
    {
        static $return = array();
        if (!isset($return[$idHook])) {
            if ($idHook <= 0) {
                $return[$idHook] = 'displayAdvancedSearch4';
            } else {
                $return[$idHook] = Hook::getNameById($idHook);
            }
            if (preg_match('/displayHome/i', $return[$idHook])) {
                $return[$idHook] = 'home';
            }
        }
        return $return[$idHook];
    }
    public static function getProductFilterSourceURLIdentifier($idLang = null)
    {
        static $return = array();
        if ($idLang == null) {
            $idLang = Context::getContext()->language->id;
        }
        if (!isset($return[$idLang])) {
            $list = Meta::getMetas();
            if (Core::isFilledArray($list)) {
                foreach ($list as $meta) {
                    if (in_array($meta['page'], self::$validPageName)) {
                        $return[$idLang][$meta['page']] = $meta['page'];
                    }
                }
            }
            $list = Meta::getMetasByIdLang($idLang);
            if (Core::isFilledArray($list)) {
                foreach ($list as $meta) {
                    if (in_array($meta['page'], self::$validPageName)) {
                        if (empty($meta['url_rewrite'])) {
                            $return[$idLang][$meta['page']] = $meta['page'];
                        } else {
                            $return[$idLang][$meta['page']] = $meta['url_rewrite'];
                        }
                    }
                }
            }
            $return[$idLang]['jolisearch'] = 'jolisearch';
            $return[$idLang]['prestasearch'] = 'prestasearch';
            $return[$idLang]['subscription'] = 'subscriptions';
        }
        return $return[$idLang];
    }
    public static function getSourceControllerFromUrl($searchQuery, $idLang = null)
    {
        if ($idLang == null) {
            $idLang = Context::getContext()->language->id;
        }
        $searchQuery = trim(strip_tags($searchQuery));
        if (!empty($searchQuery)) {
            $searchQuery = explode('/', $searchQuery);
            $productFilterIdentifierList = self::getProductFilterSourceURLIdentifier($idLang);
            if (in_array($searchQuery[0], $productFilterIdentifierList)) {
                return array_search($searchQuery[0], $productFilterIdentifierList);
            }
        }
        return null;
    }
    public static function getCriterionsFromURL($idSearch, $searchQuery, $idLang = null)
    {
        if ($idLang == null) {
            $idLang = Context::getContext()->language->id;
        }
        $criterionsList = array();
        $searchQuery = trim(strip_tags($searchQuery));
        if (!empty($searchQuery)) {
            $searchQuery = explode('/', $searchQuery);
            $productFilterIdentifierList = self::getProductFilterSourceURLIdentifier($idLang);
            if (in_array($searchQuery[0], $productFilterIdentifierList)) {
                self::$productFilterListSource = array_search($searchQuery[0], $productFilterIdentifierList);
                if (Tools::getIsset('search_query') && Tools::getValue('search_query')) {
                    self::$productFilterListData = Tools::getValue('search_query');
                }
                unset($searchQuery[0]);
            }
            foreach ($searchQuery as $criterionGroup) {
                $criterionsForGroup = explode('-', $criterionGroup);
                $criterionGroupValue = current($criterionsForGroup);
                if (is_array($criterionsForGroup) && sizeof($criterionsForGroup) > 1) {
                    $idCriterionGroup = (int)CriterionGroup::getIdCriterionsGroupByURLIdentifier($idSearch, $idLang, $criterionGroupValue);
                    if ($idCriterionGroup) {
                        $criterionsValues = explode('+', implode('-', array_slice($criterionsForGroup, 1, sizeof($criterionsForGroup) - 1)));
                        foreach ($criterionsValues as $criterionValue) {
                            $idCriterion = Criterion::getIdCriteriongByURLIdentifier($idSearch, $idCriterionGroup, $idLang, $criterionValue);
                            if ($idCriterion) {
                                $criterionsList[$idCriterionGroup][] = $idCriterion;
                            } else {
                                if (preg_match("#:#", $criterionValue) && sizeof(explode(':', $criterionValue)) == 2) {
                                    $criterionsList[$idCriterionGroup][] = str_replace('_', '-', str_replace(':', '~', $criterionValue));
                                }
                            }
                        }
                    }
                }
            }
        }
        return $criterionsList;
    }
    public static function generateURLFromCriterions($idSearch, $criterionsList = array(), $idLang = null, $array = array(), $pagination = false)
    {
        $context = Context::getContext();
        if ($idLang == null) {
            $idLang = $context->language->id;
        }
        $params = array();
        $as4_sq = array();
        $params['id_search'] = (int)$idSearch;
        if ($pagination == true) {
            $nextPage = false;
        } else {
            $nextPage = (int)Tools::getValue('p', Tools::getValue('page', false));
        }
        $nbProducts = (int)Tools::getValue('n', false);
        if (isset($array['p'])) {
            $nextPage = (int)$array['p'];
        } elseif (isset($array['page'])) {
            $nextPage = (int)$array['page'];
        }
        if (isset($array['n'])) {
            $nbProducts = (int)$array['n'];
        } elseif (isset($array['resultsPerPage'])) {
            $nbProducts = (int)$array['resultsPerPage'];
        } elseif (Tools::getIsset('resultsPerPage') && Tools::getValue('resultsPerPage')) {
            $nbProducts = (int)Tools::getValue('resultsPerPage');
        }
        $idSeo = (int)Tools::getValue('id_seo');
        $seoObj = false;
        if ($idSeo && self::supportsSeoPages()) {
            $seoObj = new Seo($idSeo, $idLang);
        }
        $criterionsList = self::cleanArrayCriterion($criterionsList);
        foreach (array_keys($criterionsList) as $idCriterionGroup) {
            if (array_sum($criterionsList[$idCriterionGroup]) == -1) {
                unset($criterionsList[$idCriterionGroup]);
            }
        }
        $objSearch = new Search((int)$idSearch);
        $idCategorySearch = self::getCurrentCategory();
        $idManufacturerSearch = self::getCurrentManufacturer();
        $idSupplierSearch = self::getCurrentSupplier();
        $idCmsSearch = self::getCurrentCMS();
        $isFromHome = (Tools::getValue('productFilterListSource') == 'index') || ($context->shop->getCategory() == $idCategorySearch);
        $forceContextUrlGeneration = false;
        if (!empty($array['from_as4'])) {
            $forceContextUrlGeneration = true;
        }
        if (!sizeof($criterionsList) && !$forceContextUrlGeneration) {
            if ($isFromHome) {
                return $context->shop->getBaseURL(true);
            } elseif (Tools::getIsset('productFilterListSource') && Tools::getValue('productFilterListSource')) {
                $pagesList = self::getProductFilterSourceURLIdentifier();
                if (Tools::getValue('productFilterListSource') == 'jolisearch') {
                    return $context->link->getModuleLink('ambjolisearch', 'jolisearch', array('search_query' => self::$productFilterListData), null, $idLang);
                } elseif (Tools::getValue('productFilterListSource') == 'module-ambjolisearch-jolisearch') {
                    return $context->link->getModuleLink('ambjolisearch', 'jolisearch', array('s' => self::$productFilterListData), null, $idLang);
                } elseif (Tools::getValue('productFilterListSource') == 'prestasearch') {
                    return $context->link->getModuleLink('prestasearch', 'search', array('s' => self::$productFilterListData), null, $idLang);
                } elseif (Tools::getValue('productFilterListSource') == 'subscription') {
                    return $context->link->getModuleLink('pm_subscription', 'productslisting', [], null, $idLang);
                } elseif (isset($pagesList[Tools::getValue('productFilterListSource')])) {
                    if (Tools::getValue('productFilterListSource') == 'search') {
                        return $context->link->getPageLink(Tools::getValue('productFilterListSource'), null, $idLang, array('search_query' => self::$productFilterListData));
                    } else {
                        return $context->link->getPageLink(Tools::getValue('productFilterListSource'), null, $idLang);
                    }
                } else {
                    return $context->shop->getBaseURL(true);
                }
            } else {
                if ($idCategorySearch) {
                    $category = new Category($idCategorySearch, $idLang);
                    return $context->link->getCategoryLink($category, null, $idLang);
                } elseif ($idManufacturerSearch) {
                    $manufacturer = new Manufacturer($idManufacturerSearch, $idLang);
                    return $context->link->getManufacturerLink($manufacturer, null, $idLang);
                } elseif ($idSupplierSearch) {
                    $supplier = new Supplier($idSupplierSearch, $idLang);
                    return $context->link->getSupplierLink($supplier, null, $idLang);
                } elseif ($idCmsSearch) {
                    $cms = new CMS($idCmsSearch, $idLang);
                    return $context->link->getCMSLink($cms, null, $idLang);
                } elseif (Validate::isLoadedObject($seoObj)) {
                    $params['id_seo'] = $idSeo;
                    $params['seo_url'] = $seoObj->seo_url;
                    unset($params['id_search'], $params['as4_sq']);
                    return $context->link->getModuleLink(_PM_AS_MODULE_NAME_, 'seo', $params, null, $idLang);
                }
                return $context->shop->getBaseURL(true);
            }
        }
        if ($idCategorySearch) {
            $categoryCriterionsList = self::getCriterionsWithIdGroupFromIdLinked('category', $idCategorySearch, (int)$idSearch);
            if ($categoryCriterionsList) {
                $idCriterionGroupCategory = (int)current(array_keys($categoryCriterionsList));
                if (isset($criterionsList[$idCriterionGroupCategory]) && $categoryCriterionsList[$idCriterionGroupCategory] == $criterionsList[$idCriterionGroupCategory]) {
                    unset($criterionsList[$idCriterionGroupCategory]);
                }
            }
            if (!sizeof($criterionsList) && !$forceContextUrlGeneration) {
                $category = new Category($idCategorySearch, $idLang);
                $url = $context->link->getCategoryLink($category, null, $idLang);
                if ($nbProducts > 0) {
                    $url .= (!strstr($url, '?') ? '?' : '&').'resultsPerPage=' . (int)$nbProducts;
                }
                if ($nextPage > 1) {
                    $url .= (!strstr($url, '?') ? '?' : '&').'page=' . (int)$nextPage;
                }
                if (!empty($array['order'])) {
                    $url .= (!strstr($url, '?') ? '?' : '&').'order=' . $array['order'];
                }
                return $url;
            }
        }
        if ($idManufacturerSearch) {
            $manufacturerCriterionsList = self::getCriterionsWithIdGroupFromIdLinked('manufacturer', $idManufacturerSearch, (int)$idSearch);
            if ($manufacturerCriterionsList) {
                $idCriterionGroupManufacturer = (int)current(array_keys($manufacturerCriterionsList));
                if (isset($criterionsList[$idCriterionGroupManufacturer]) && $manufacturerCriterionsList[$idCriterionGroupManufacturer] == $criterionsList[$idCriterionGroupManufacturer]) {
                    unset($criterionsList[$idCriterionGroupManufacturer]);
                }
            }
            if (!sizeof($criterionsList) && !$forceContextUrlGeneration) {
                $manufacturer = new Manufacturer($idManufacturerSearch, $idLang);
                $url = $context->link->getManufacturerLink($manufacturer, null, $idLang);
                if ($nbProducts > 0) {
                    $url .= (!strstr($url, '?') ? '?' : '&').'resultsPerPage=' . (int)$nbProducts;
                }
                if ($nextPage > 1) {
                    $url .= (!strstr($url, '?') ? '?' : '&').'page=' . (int)$nextPage;
                }
                if (!empty($array['order'])) {
                    $url .= (!strstr($url, '?') ? '?' : '&').'order=' . $array['order'];
                }
                return $url;
            }
        }
        if ($idSupplierSearch) {
            $supplierCriterionsList = self::getCriterionsWithIdGroupFromIdLinked('supplier', $idSupplierSearch, (int)$idSearch);
            if ($supplierCriterionsList) {
                $idCriterionGroupSupplier = (int)current(array_keys($supplierCriterionsList));
                if (isset($criterionsList[$idCriterionGroupSupplier]) && $supplierCriterionsList[$idCriterionGroupSupplier] == $criterionsList[$idCriterionGroupSupplier]) {
                    unset($criterionsList[$idCriterionGroupSupplier]);
                }
            }
            if (!sizeof($criterionsList) && !$forceContextUrlGeneration) {
                $supplier = new Supplier($idSupplierSearch, $idLang);
                $url = $context->link->getSupplierLink($supplier, null, $idLang);
                if ($nbProducts > 0) {
                    $url .= (!strstr($url, '?') ? '?' : '&').'resultsPerPage=' . (int)$nbProducts;
                }
                if ($nextPage > 1) {
                    $url .= (!strstr($url, '?') ? '?' : '&').'page=' . (int)$nextPage;
                }
                if (!empty($array['order'])) {
                    $url .= (!strstr($url, '?') ? '?' : '&').'order=' . $array['order'];
                }
                return $url;
            }
        }
        if ($idCmsSearch) {
            if (!sizeof($criterionsList) && !$forceContextUrlGeneration) {
                $cms = new CMS($idCmsSearch, $idLang);
                $url = $context->link->getCMSLink($cms, null, $idLang);
                if ($nbProducts > 0) {
                    $url .= (!strstr($url, '?') ? '?' : '&').'resultsPerPage=' . (int)$nbProducts;
                }
                if ($nextPage > 1) {
                    $url .= (!strstr($url, '?') ? '?' : '&').'page=' . (int)$nextPage;
                }
                if (!empty($array['order'])) {
                    $url .= (!strstr($url, '?') ? '?' : '&').'order=' . $array['order'];
                }
                return $url;
            }
        }
        if (Validate::isLoadedObject($seoObj)) {
            $originalCriterionsList = array();
            $criteria = Core::decodeCriteria($seoObj->criteria);
            if (is_array($criteria) && sizeof($criteria)) {
                $originalCriterionsList = SearchEngineUtils::cleanArrayCriterion(PM_AdvancedSearch4::getArrayCriteriaFromSeoArrayCriteria($criteria));
            }
            foreach ($criterionsList as $idCriterionGroup => &$criterionsValues) {
                if (Core::isFilledArray($criterionsValues)) {
                    $criterionGroup = new CriterionGroup($idCriterionGroup, $idSearch, $idLang);
                    if (Validate::isLoadedObject($seoObj) && empty($criterionGroup->visible)) {
                        unset($criterionsList[$idCriterionGroup]);
                        unset($originalCriterionsList[$idCriterionGroup]);
                    }
                }
            }
            if (is_array($criterionsList) && sizeof($criterionsList)) {
                if (is_array($originalCriterionsList) && sizeof($originalCriterionsList)) {
                    foreach ($criterionsList as $arrayDiffKey => $arrayDiffRow) {
                        if (isset($originalCriterionsList[$arrayDiffKey]) && $originalCriterionsList[$arrayDiffKey] == $arrayDiffRow) {
                            unset($criterionsList[$arrayDiffKey]);
                        }
                    }
                }
            }
        }
        ksort($criterionsList);
        foreach ($criterionsList as $idCriterionGroup => &$criterionsValues) {
            if (Core::isFilledArray($criterionsValues)) {
                asort($criterionsValues);
                $criterionGroup = new CriterionGroup($idCriterionGroup, $idSearch, $idLang);
                $urlIdentifierList = array();
                foreach ($criterionsValues as $idCriterion) {
                    if (is_numeric($idCriterion)) {
                        $criterion = new Criterion($idCriterion, $idSearch, $idLang);
                        $urlIdentifierList[] = $criterion->url_identifier;
                    } elseif (preg_match("#~#", $idCriterion) && sizeof(explode('~', $idCriterion)) == 2) {
                        $urlIdentifierList[] = str_replace('-', '_', str_replace('~', ':', $idCriterion));
                    }
                }
                $as4_sq[$idCriterionGroup] = $criterionGroup->url_identifier . '-' . implode('+', $urlIdentifierList);
            }
        }
        if (self::$productFilterListSource) {
            $productFilterIdentifierList = self::getProductFilterSourceURLIdentifier($idLang);
            if (isset($productFilterIdentifierList[self::$productFilterListSource])) {
                $as4_sq = array($productFilterIdentifierList[self::$productFilterListSource]) + $as4_sq;
            }
            if (self::$productFilterListSource == 'search' || self::$productFilterListSource == 'jolisearch') {
                $params['search_query'] = self::$productFilterListData;
            } elseif (self::$productFilterListSource == 'module-ambjolisearch-jolisearch' || self::$productFilterListSource == 'prestasearch') {
                $params['s'] = self::$productFilterListData;
            }
        }
        $params['as4_sq'] = implode('/', $as4_sq);
        if ($nbProducts > 0) {
            $params['resultsPerPage'] = (int)$nbProducts;
        }
        if ($nextPage > 1) {
            $params['page'] = (int)$nextPage;
        }
        if (!empty($array['order'])) {
            $params['order'] = $array['order'];
        }
        if (is_array($params)) {
            $params = array_map('strip_tags', $params);
        }
        if (Validate::isLoadedObject($seoObj)) {
            $seoURL = $seoObj->seo_url;
            if (!empty($params['as4_sq'])) {
                $seoURL .= '/' . $params['as4_sq'];
            }
            $params['id_seo'] = $idSeo;
            $params['seo_url'] = $seoURL;
            unset($params['id_search'], $params['as4_sq']);
            $url = $context->link->getModuleLink(_PM_AS_MODULE_NAME_, 'seo', $params, null, $idLang);
            return $url;
        }
        if ($objSearch->filter_by_emplacement && $idCategorySearch) {
            $category = new Category($idCategorySearch, $idLang);
            $params['id'] = (int)$category->id;
            $params['rewrite'] = $category->link_rewrite;
            return $context->link->getModuleLink(_PM_AS_MODULE_NAME_, 'searchresults-categories', $params, null, $idLang);
        } elseif ($objSearch->filter_by_emplacement && $idManufacturerSearch) {
            $manufacturer = new Manufacturer($idManufacturerSearch, $idLang);
            $params['id'] = (int)$manufacturer->id;
            $params['rewrite'] = $manufacturer->link_rewrite;
            return $context->link->getModuleLink(_PM_AS_MODULE_NAME_, 'searchresults-manufacturers', $params, null, $idLang);
        } elseif ($objSearch->filter_by_emplacement && $idSupplierSearch) {
            $supplier = new Supplier($idSupplierSearch, $idLang);
            $params['id'] = (int)$supplier->id;
            $params['rewrite'] = $supplier->link_rewrite;
            return $context->link->getModuleLink(_PM_AS_MODULE_NAME_, 'searchresults-suppliers', $params, null, $idLang);
        } elseif ($idCmsSearch) {
            $cms = new CMS($idCmsSearch, $idLang);
            $params['id'] = (int)$cms->id;
            $params['rewrite'] = $cms->link_rewrite;
            return $context->link->getModuleLink(_PM_AS_MODULE_NAME_, 'searchresults-cms', $params, null, $idLang);
        } else {
            return $context->link->getModuleLink(_PM_AS_MODULE_NAME_, 'searchresults', $params, null, $idLang);
        }
    }
    private static function getSPAModuleInstance()
    {
        static $moduleInstance = null;
        if ($moduleInstance === null) {
            $module = Module::getInstanceByName('pm_productsbyattributes');
            if (is_object($module) && $module->active) {
                $moduleInstance = $module;
            } else {
                $moduleInstance = false;
            }
        }
        return $moduleInstance;
    }
    public static function isSPAModuleActive()
    {
        static $active = null;
        if ($active === null) {
            $module = self::getSPAModuleInstance();
            $active = (is_object($module) && !empty($module->active));
            if ($active) {
                if (method_exists($module, 'hasAtLeastOneAttributeGroup')) {
                    $active = $module->hasAtLeastOneAttributeGroup();
                } else {
                    $moduleConfig = \pm_productsbyattributes::getModuleConfigurationStatic();
                    $active = (Combination::isFeatureActive() && !empty($moduleConfig['selectedGroups']));
                }
            }
        }
        return $active;
    }
    public static function isSPAHideColorSquares()
    {
        static $colorSquares = null;
        if ($colorSquares === null) {
            $colorSquares = false;
            if (self::isSPAModuleActive()) {
                $colorSquares = self::getSPAModuleInstance()->getHideColorSquaresConf();
            }
        }
        return $colorSquares;
    }
    public static function getLocalStorageCacheKey()
    {
        return Configuration::getGlobalValue('PM_AS4_CACHE_KEY');
    }
    public static function setLocalStorageCacheKey()
    {
        Configuration::updateGlobalValue('PM_AS4_CACHE_KEY', sha1(_THEME_NAME_ . uniqid()));
    }
    public static function getSQLGroups()
    {
        $sqlGroups = '';
        if (Group::isFeatureActive()) {
            $currentGroups = FrontController::getCurrentCustomerGroups();
            $sqlGroups = 'AND cg.`id_group` '.(is_array($currentGroups) && sizeof($currentGroups) ? 'IN ('.implode(',', array_map('intval', $currentGroups)).')' : '= 1');
        }
        return $sqlGroups;
    }
    public static function getBestSellersProductsIds()
    {
        $sqlGroups = self::getSQLGroups();
        self::$productFilterListQuery = '
        SELECT p.id_product
        FROM `'._DB_PREFIX_.'product_sale` ps
        JOIN `'._DB_PREFIX_.'product` p ON (ps.`id_product` = p.`id_product`)
        ' . Shop::addSqlAssociation('product', 'p') . '
        WHERE product_shop.`active` = 1
        AND product_shop.`visibility` IN ("both", "catalog")
        ' . (!empty($sqlGroups) ? '
        AND EXISTS(
            SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp
            JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.id_category = cg.id_category ' . $sqlGroups . ')
            WHERE cp.`id_product` = product_shop.`id_product`
        )' : '');
    }
    public static function getNewProductsIds()
    {
        $sqlGroups = self::getSQLGroups();
        $expirationDate = date('Y-m-d', strtotime('-' . (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20) . ' day'));
        self::$productFilterListQuery = '
        SELECT p.id_product
        FROM `'._DB_PREFIX_.'product` p
        ' . Shop::addSqlAssociation('product', 'p') . '
        WHERE product_shop.`active` = 1
        AND product_shop.`date_add` > "' . pSQL($expirationDate) . '"
        AND product_shop.`visibility` IN ("both", "catalog")
        ' . (!empty($sqlGroups) ? '
        AND EXISTS(
            SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp
            JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.id_category = cg.id_category ' . $sqlGroups . ')
            WHERE cp.`id_product` = product_shop.`id_product`
        )' : '');
    }
    public static function getPricesDropProductsIds()
    {
        $sqlGroups = self::getSQLGroups();
        $currentDate = date('Y-m-d H:i:s');
        $context = Context::getContext();
        $id_address = $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
        $ids = Address::getCountryAndState($id_address);
        if (is_array($ids)) {
            $id_country = (int)$ids['id_country'];
        } else {
            $id_country = (int)Configuration::get('PS_COUNTRY_DEFAULT');
        }
        $ids_product = SpecificPrice::getProductIdByDate($context->shop->id, $context->currency->id, $id_country, $context->customer->id_default_group, $currentDate, $currentDate, 0, false);
        $tab_id_product = array();
        foreach ($ids_product as $product) {
            if (is_array($product)) {
                $tab_id_product[] = (int)$product['id_product'];
            } else {
                $tab_id_product[] = (int)$product;
            }
        }
        self::$productFilterListQuery = '
        SELECT p.id_product
        FROM `'._DB_PREFIX_.'product` p
        ' . Shop::addSqlAssociation('product', 'p') . '
        WHERE product_shop.`active` = 1
        AND product_shop.`show_price` = 1
        AND product_shop.`visibility` IN ("both", "catalog")
        AND product_shop.`id_product` IN ('.((is_array($tab_id_product) && count($tab_id_product)) ? implode(', ', array_map('intval', $tab_id_product)) : 0).')
        ' . (!empty($sqlGroups) ? '
        AND EXISTS(
            SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp
            JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.id_category = cg.id_category ' . $sqlGroups . ')
            WHERE cp.`id_product` = product_shop.`id_product`
        )' : '');
    }
    protected static function getSearchParamFromWord($word)
    {
        if (method_exists(PsSearch::class, 'getSearchParamFromWord')) {
            return " '" . PsSearch::getSearchParamFromWord($word) . "'";
        }
        $word = str_replace(array('%', '_'), array('\\%', '\\_'), $word);
        $startSearch = Configuration::get('PS_SEARCH_START') ? '%' : '';
        $endSearch = Configuration::get('PS_SEARCH_END') ? '' : '%';
        $psSearchMaxWordLength = 15;
        if (method_exists(PsSearch::class, 'getMaximumWordLength')) {
            $psSearchMaxWordLength = PsSearch::getMaximumWordLength();
        } elseif (Configuration::get('PS_SEARCH_MAX_WORD_LENGTH')) {
            $psSearchMaxWordLength = Configuration::get('PS_SEARCH_MAX_WORD_LENGTH');
        } elseif (defined('PS_SEARCH_MAX_WORD_LENGTH')) {
            $psSearchMaxWordLength = PS_SEARCH_MAX_WORD_LENGTH;
        }
        return (
            $word[0] == '-'
            ? " '" . $startSearch . pSQL(Tools::substr($word, 1, $psSearchMaxWordLength)) . $endSearch . "'"
            : " '" . $startSearch  .pSQL(Tools::substr($word, 0, $psSearchMaxWordLength)) . $endSearch . "'"
        );
    }
    public static function getProductsByNativeSearch($expr)
    {
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $context = Context::getContext();
        $intersectArray = array();
        $scoreArray = array();
        $eligibleProducts2 = null;
        $psFuzzyMaxLoop = (int)Configuration::get('PS_SEARCH_FUZZY_MAX_LOOP');
        $psFuzzySearch = (int)Configuration::get('PS_SEARCH_FUZZY');
        $psSearchMinWordLength = (int)Configuration::get('PS_SEARCH_MINWORDLEN');
        if (method_exists(PsSearch::class, 'extractKeyWords')) {
            $expressions = explode(';', $expr);
            $fullWords = array();
            foreach ($expressions as $expression) {
                $words = PsSearch::extractKeyWords($expression, (int)$context->language->id, false, $context->language->iso_code);
                $fullWords = array_merge($fullWords, $words);
            }
            $words = $fullWords;
        } else {
            $expr = Tools::replaceAccentedChars(urldecode($expr));
            $words = explode(' ', PsSearch::sanitize($expr, (int)$context->language->id));
        }
        $sqlQuery = 'SELECT DISTINCT si.`id_product`
                FROM `'._DB_PREFIX_.'search_word` sw
                LEFT JOIN `'._DB_PREFIX_.'search_index` si ON sw.`id_word` = si.`id_word`
                LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` product_shop ON (product_shop.`id_product` = si.`id_product`)
                WHERE sw.`id_lang` = '.(int)$context->language->id .
                ' AND sw.`id_shop` = '.(int)$context->shop->id .
                ' AND product_shop.`active` = 1 ' .
                ' AND product_shop.`visibility` IN ("both", "search") ' .
                ' AND product_shop.`indexed` = 1 ' .
                ' AND sw.`word` LIKE ';
        $fuzzyLoop = 0;
        foreach ($words as $key => $word) {
            if (empty($word) || Tools::strlen($word) < $psSearchMinWordLength) {
                unset($words[$key]);
                continue;
            }
            $sqlParamSearch = self::getSearchParamFromWord($word);
            if ($psFuzzySearch) {
                while (!($result = $db->executeS($sqlQuery . $sqlParamSearch . ";", true, false))) {
                    if (!$psFuzzySearch
                        || $fuzzyLoop++ > $psFuzzyMaxLoop
                        || !($sqlParamSearch = " '" . PsSearch::findClosestWeightestWord($context, $word) . "'")
                    ) {
                        break;
                    }
                }
                if (!$result) {
                    unset($words[$key]);
                    continue;
                }
            } else {
                $result = $db->executeS($sqlQuery . $sqlParamSearch . ";", true, false);
            }
            $productIds = array_column($result, 'id_product');
            if ($eligibleProducts2 === null) {
                $eligibleProducts2 = $productIds;
            } else {
                $eligibleProducts2 = array_intersect($eligibleProducts2, $productIds);
            }
            $scoreArray[] = 'sw.`word` LIKE '. $sqlParamSearch;
        }
        if (!sizeof($words)) {
            self::$productFilterListQuery = '-1';
            return;
        }
        if (!empty($scoreArray) && is_array($scoreArray)) {
            self::$productFilterListAdditionalColumns = ',( ' .
                'SELECT SUM(weight) ' .
                'FROM ' . _DB_PREFIX_ . 'search_word sw ' .
                'LEFT JOIN ' . _DB_PREFIX_ . 'search_index si ON sw.id_word = si.id_word ' .
                'WHERE sw.id_lang = ' . (int)$context->language->id . ' ' .
                'AND sw.id_shop = ' . $context->shop->id . ' ' .
                'AND si.id_product = p.id_product ' .
                'AND (' . implode(' OR ', $scoreArray) . ') ' .
                ') position';
        }
        $sqlGroups = self::getSQLGroups();
        $result = $db->ExecuteS('
        SELECT DISTINCT cp.`id_product`
        FROM `'._DB_PREFIX_.'category_product` cp
        INNER JOIN `'._DB_PREFIX_.'category` c ON cp.`id_category` = c.`id_category`
        INNER JOIN `'._DB_PREFIX_.'product` p ON cp.`id_product` = p.`id_product` '
        . Shop::addSqlAssociation('product', 'p', false) . '
        WHERE c.`active` = 1
        AND product_shop.`active` = 1
        AND product_shop.`visibility` IN ("both", "search")
        AND product_shop.indexed = 1
        ' . (!empty($sqlGroups) ? '
        AND EXISTS(
            SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp
            JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.id_category = cg.id_category ' . $sqlGroups . ')
            WHERE cp.`id_product` = product_shop.`id_product`
        )' : ''), false);
        $eligibleProducts = array();
        while ($row = $db->nextRow($result)) {
            $eligibleProducts[] = (int)$row['id_product'];
        }
        $eligibleProducts = array_unique(array_intersect($eligibleProducts, array_unique($eligibleProducts2)));
        if (sizeof($eligibleProducts)) {
            self::$productFilterListQuery = implode(',', $eligibleProducts);
        } else {
            self::$productFilterListQuery = '-1';
            return;
        }
    }
    public static function getOrderByValue($search, $productSearchQuery = null)
    {
        if ($search instanceof Search) {
            $orderByDefault = $search->products_order_by;
        } else {
            $orderByDefault = $search['products_order_by'];
        }
        if ($productSearchQuery !== null && $productSearchQuery instanceof \PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery) {
            $orderBy = $productSearchQuery->getSortOrder()->toLegacyOrderBy();
        } else {
            $orderBy = Tools::strtolower(Tools::getValue('orderby', self::$orderByValues[(int)($orderByDefault)]));
        }
        if (!in_array($orderBy, self::$orderByValues)) {
            $orderBy = self::$orderByValues[0];
        }
        return $orderBy;
    }
    public static function getOrderWayValue($search, $productSearchQuery = null)
    {
        if ($search instanceof Search) {
            $orderWayDefault = $search->products_order_way;
        } else {
            $orderWayDefault = $search['products_order_way'];
        }
        if ($productSearchQuery !== null && $productSearchQuery instanceof \PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery) {
            $orderWay = $productSearchQuery->getSortOrder()->toLegacyOrderWay();
        } else {
            $orderWay = Tools::strtolower(Tools::getValue('orderway', self::$orderWayValues[(int)($orderWayDefault)]));
        }
        if (!in_array($orderWay, self::$orderWayValues)) {
            $orderWay = self::$orderWayValues[0];
        }
        return $orderWay;
    }
    public static function allowShowPrices()
    {
        static $allowShowPrices = null;
        if ($allowShowPrices !== null) {
            return $allowShowPrices;
        }
        if (Group::isFeatureActive()) {
            $allowShowPrices = (bool)Group::getCurrent()->show_prices;
        } else {
            $allowShowPrices = true;
        }
        return $allowShowPrices;
    }
    public static function lastCriterionStepSelected(Search $searchEngine, $criterions)
    {
        if ($searchEngine->search_method != 3) {
            return false;
        }
        $context = Context::getContext();
        $searchs = SearchEngineUtils::getSearch($searchEngine->id, (int)$context->language->id);
        $searchs = pm_advancedsearch4::getModuleInstance()->getCriterionsGroupsAndCriterionsForSearch($searchs, (int)$context->language->id, $criterions, 0, false);
        $realAvailableGroups = 0;
        $selectedGroupCount = 0;
        foreach ($searchs[0]['criterions'] as $idCriterionGroup => $criterionsList) {
            if (isset($criterions[$idCriterionGroup])) {
                $selectedGroupCount++;
            }
            if (is_array($criterionsList) && sizeof($criterionsList)) {
                $realAvailableGroups++;
            }
        }
        return ($selectedGroupCount == $realAvailableGroups);
    }
}
