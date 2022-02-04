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
abstract class As4SearchEngineIndexation
{
    public static function addCacheProduct($idSearch)
    {
        $sql_insert_multiple = array();
        $sql_insert_multiple_header = 'INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.$idSearch.'` (`id_product`) VALUES ';
        foreach (self::getAllProductsId() as $row) {
            $sql_insert_multiple[] = '('.(int) $row['id_product'].')';
            self::sqlBulkInsert('pm_advancedsearch_cache_product_'.$idSearch, $sql_insert_multiple_header, $sql_insert_multiple, 1000);
        }
        self::sqlBulkInsert('pm_advancedsearch_cache_product_'.$idSearch, $sql_insert_multiple_header, $sql_insert_multiple, 1);
        return true;
    }
    public static function updateCacheProduct($idSearch)
    {
        $sql_insert_multiple = array();
        $sql_insert_multiple_header = 'INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.$idSearch.'` (`id_product`) VALUES ';
        $getAllProductsIdNotCached_result = self::getAllProductsIdNotCached($idSearch);
        if (AdvancedSearchCoreClass::_isFilledArray($getAllProductsIdNotCached_result)) {
            foreach ($getAllProductsIdNotCached_result as $row) {
                $sql_insert_multiple[] = '('.(int)$row['id_product'].')';
                self::sqlBulkInsert('pm_advancedsearch_cache_product_'.$idSearch, $sql_insert_multiple_header, $sql_insert_multiple, 1000);
            }
            self::sqlBulkInsert('pm_advancedsearch_cache_product_'.$idSearch, $sql_insert_multiple_header, $sql_insert_multiple, 1);
        }
        return true;
    }
    public static function getAllIdLang()
    {
        static $idLangList = null;
        if ($idLangList === null) {
            $languages = Language::getLanguages(false);
            $idLangList = array();
            foreach ($languages as $lang) {
                $idLangList[] = (int)$lang['id_lang'];
            }
        }
        return $idLangList;
    }
    public static function getAllProductsId()
    {
        return As4SearchEngineDb::query('SELECT p.`id_product`
        FROM `'._DB_PREFIX_.'product` p
        ' . Shop::addSqlAssociation('product', 'p') . '
        WHERE product_shop.`active` = 1
        GROUP BY p.`id_product`');
    }
    private static $_getAllProductsIdNotCachedCache = array();
    public static function getAllProductsIdNotCached($id_search)
    {
        if (isset(self::$_getAllProductsIdNotCachedCache[$id_search])) {
            return self::$_getAllProductsIdNotCachedCache[$id_search];
        }
        $result = As4SearchEngineDb::query('SELECT p.`id_product`
        FROM `'._DB_PREFIX_.'product` p
        ' . Shop::addSqlAssociation('product', 'p') . '
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int)$id_search.'` acp ON ( acp.id_product = p.id_product )
        WHERE acp.id_product IS NULL
        AND product_shop.`active` = 1
        GROUP BY p.`id_product`');
        if (!AdvancedSearchCoreClass::_isFilledArray($result)) {
            self::$_getAllProductsIdNotCachedCache[$id_search] = $result;
        }
        return $result;
    }
    public static function getAttributeGroups($id_attribute_group, $id_lang = false)
    {
        $result = As4SearchEngineDb::query('SELECT *
        FROM `'._DB_PREFIX_.'attribute_group` ag
        '.($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND `id_lang` = '.(int)($id_lang).')':'').'
        WHERE ag.`id_attribute_group` = '.(int)$id_attribute_group.'
        LIMIT 1');
        if (!$id_lang && $result) {
            $result_lang = As4SearchEngineDb::query('
                SELECT agl.*
                FROM `'._DB_PREFIX_.'attribute_group_lang` agl
                WHERE agl.`id_attribute_group` = '.(int)$id_attribute_group);
            foreach ($result_lang as $row_lang) {
                $result[0]['name'][$row_lang['id_lang']] = $row_lang['public_name'];
            }
        }
        return $result[0];
    }
    public static function getAttributesIdList($id_attribute_group)
    {
        $attributeList = array();
        $result = As4SearchEngineDb::query('SELECT a.id_attribute FROM `'._DB_PREFIX_.'attribute` a WHERE a.`id_attribute_group` = '.(int)$id_attribute_group);
        foreach ($result as $row) {
            $attributeList[] = (int)$row['id_attribute'];
        }
        return $attributeList;
    }
    public static function getFeaturesIdList($id_feature)
    {
        $featureList = array();
        $result = As4SearchEngineDb::query('SELECT fv.id_feature_value FROM `'._DB_PREFIX_.'feature_value` fv WHERE fv.`id_feature` = '.(int)$id_feature);
        foreach ($result as $row) {
            $featureList[] = (int)$row['id_feature_value'];
        }
        return $featureList;
    }
    public static function getCategoryIdList($idSearch)
    {
        $categoryList = array();
        $result = As4SearchEngineDb::query('SELECT c.id_category FROM `'._DB_PREFIX_.'category` c
        INNER JOIN `'._DB_PREFIX_.'category_shop` c_shop ON (c_shop.id_category = c.id_category AND c_shop.`id_shop` IN ('.(int)As4SearchEngine::getShopBySearch($idSearch).'))');
        foreach ($result as $row) {
            $categoryList[] = (int)$row['id_category'];
        }
        return $categoryList;
    }
    public static function getSupplierIdList($idSearch)
    {
        $supplierList = array();
        $result = As4SearchEngineDb::query('SELECT s.id_supplier FROM `'._DB_PREFIX_.'supplier` s
        INNER JOIN `'._DB_PREFIX_.'supplier_shop` s_shop ON (s_shop.id_supplier = s.id_supplier AND s_shop.`id_shop` IN ('.(int)As4SearchEngine::getShopBySearch($idSearch).'))');
        foreach ($result as $row) {
            $supplierList[] = (int)$row['id_supplier'];
        }
        return $supplierList;
    }
    public static function getManufacturerIdList($idSearch)
    {
        $manufacturerList = array();
        $result = As4SearchEngineDb::query('SELECT m.id_manufacturer FROM `'._DB_PREFIX_.'manufacturer` m
        INNER JOIN `'._DB_PREFIX_.'manufacturer_shop` m_shop ON (m_shop.id_manufacturer = m.id_manufacturer AND m_shop.`id_shop` IN ('.(int)As4SearchEngine::getShopBySearch($idSearch).'))');
        foreach ($result as $row) {
            $manufacturerList[] = (int)$row['id_manufacturer'];
        }
        return $manufacturerList;
    }
    public static function getAttributes($id_attribute_group, $id_lang = false, $id_attribute = false, $id_product = false)
    {
        $result = array();
        $resource = As4SearchEngineDb::query('
        SELECT a.*, al.*
        FROM `'._DB_PREFIX_.'attribute` a
        LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute`' . ($id_lang ? ' AND al.`id_lang` = ' . (int)$id_lang : '') . ')'
        . ($id_product ? '
        JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (a.`id_attribute` = pac.`id_attribute`)
        JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pac.`id_product_attribute` = pa.`id_product_attribute` AND pa.`id_product` = '.(int)$id_product.')' : '') . '
        WHERE a.`id_attribute_group` = '.(int)$id_attribute_group.($id_attribute ? ' AND a.`id_attribute` = '.(int)$id_attribute : '')
        . ($id_product ? ' GROUP BY a.id_attribute, al.id_lang' : '')
        . ' ORDER BY a.`position` ' . pSQL(self::$originalSortWay), 1, false);
        while ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->nextRow($resource)) {
            $idAttribute = (int)$row['id_attribute'];
            if (!isset($result[$idAttribute])) {
                $result[$idAttribute] = $row;
                unset($result[$idAttribute]['id_lang']);
                $result[$idAttribute]['name'] = array();
            }
            $result[$idAttribute]['name'][(int)$row['id_lang']] = $row['name'];
        }
        return $result;
    }
    public static function getProductsIdFromAttribute($id_search, $id_attribute)
    {
        $result = As4SearchEngineDb::query('SELECT acp.`id_cache_product`, pa.`id_product_attribute`, pa.`quantity`, pa.`id_product`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $id_search.'` acp
            LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = acp.`id_product`)
            LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product`)
            LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pa.`id_product_attribute` = pac.`id_product_attribute`)
            ' . Shop::addSqlAssociation('product', 'p') . '
            WHERE product_shop.`active` = 1
            AND pac.`id_attribute` = '.(int)$id_attribute.'
            GROUP BY acp.`id_product`');
        return $result;
    }
    public static function getFeaturesFromProduct($id_product, $id_lang, $id_feature = null)
    {
        $result = As4SearchEngineDb::query('
        SELECT fvl.`value`, fp.id_feature FROM '._DB_PREFIX_.'feature_product fp
        LEFT JOIN `'._DB_PREFIX_.'feature_value_lang` fvl ON (fp.`id_feature_value` = fvl.`id_feature_value` AND fvl.`id_lang` = '.(int)$id_lang.')
        WHERE fp.`id_product` = ' . (int)$id_product . ($id_feature ? ' AND fp.`id_feature` = ' . (int)$id_feature : ''));
        return $result;
    }
    public static function getFeature($id_feature, $id_lang = false)
    {
        $result = As4SearchEngineDb::query('
        SELECT *
        FROM `'._DB_PREFIX_.'feature` f
        '.($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'feature_lang` fl ON (f.`id_feature` = fl.`id_feature` AND fl.`id_lang` = '.(int)($id_lang).')':'').'
        WHERE f.`id_feature` = '.(int)$id_feature.'
        LIMIT 1');
        if (!$id_lang && $result) {
            $result_lang = As4SearchEngineDb::query('
                SELECT fl.*
                FROM `'._DB_PREFIX_.'feature_lang` fl
                WHERE fl.`id_feature` = '.(int)$id_feature);
            foreach ($result_lang as $row_lang) {
                $result[0]['name'][$row_lang['id_lang']] = $row_lang['name'];
            }
        }
        return $result[0];
    }
    public static function getFeatureValuesFromValue($id_feature, $feature_value = false, $id_criterion_linked = false)
    {
        $defaultIdLang = (int)Configuration::get('PS_LANG_DEFAULT');
        $resource = As4SearchEngineDb::query('
        SELECT fv.*, fvl.`id_lang`, fvl.`value`
        FROM `'._DB_PREFIX_.'feature_value` fv
        LEFT JOIN `'._DB_PREFIX_.'feature_value_lang` fvl ON (fv.`id_feature_value` = fvl.`id_feature_value`)
        WHERE fv.`id_feature` = ' . (int)$id_feature . ' AND fvl.`id_lang`='.(int)$defaultIdLang.'
        ORDER BY fv.`custom` ASC', 1, false);
        $featureIdentifierList = array();
        $featureIdentifierReverseList = array();
        while ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->nextRow($resource)) {
            if ($row['id_lang'] == null) {
                continue;
            }
            $featureIdentifier = Tools::strtolower(trim($row['value']));
            if (Tools::strlen($featureIdentifier)) {
                if (!isset($featureIdentifierList[$featureIdentifier])) {
                    $featureIdentifierList[$featureIdentifier] = array(
                        'name' => array(
                            (int)$row['id_lang'] => trim($row['value']),
                        ),
                        'id_feature' => (int)$row['id_feature'],
                        'id_feature_value_list' => array(),
                    );
                }
                $featureIdentifierList[$featureIdentifier]['id_feature_value_list'][] = (int)$row['id_feature_value'];
                $featureIdentifierReverseList[(int)$row['id_feature_value']] = $featureIdentifier;
            }
        }
        $resource = As4SearchEngineDb::query('
        SELECT fv.*, fvl.`id_lang`, fvl.`value`
        FROM `'._DB_PREFIX_.'feature_value` fv
        LEFT JOIN `'._DB_PREFIX_.'feature_value_lang` fvl ON (fv.`id_feature_value` = fvl.`id_feature_value`)
        WHERE fv.`id_feature` = ' . (int)$id_feature . ' AND fvl.`id_lang`!='.(int)$defaultIdLang.'
        ORDER BY fv.`custom` ASC', 1, false);
        while ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->nextRow($resource)) {
            if ($row['id_lang'] == null) {
                continue;
            }
            if (isset($featureIdentifierReverseList[(int)$row['id_feature_value']])) {
                $featureIdentifier = $featureIdentifierReverseList[(int)$row['id_feature_value']];
                if (!isset($featureIdentifierList[$featureIdentifier]['name'][(int)$row['id_lang']])) {
                    $featureIdentifierList[$featureIdentifier]['name'][(int)$row['id_lang']] = trim($row['value']);
                }
            }
        }
        if ($id_criterion_linked !== false && isset($featureIdentifierReverseList[(int)$id_criterion_linked])) {
            $featureIdentifier = $featureIdentifierReverseList[(int)$id_criterion_linked];
            if (isset($featureIdentifierList[$featureIdentifier])) {
                return array($featureIdentifierList[$featureIdentifier]);
            }
            return array();
        }
        if ($feature_value !== false) {
            $featureIdentifier = Tools::strtolower(trim($feature_value));
            if (isset($featureIdentifierList[$featureIdentifier])) {
                return array($featureIdentifierList[$featureIdentifier]);
            }
            return array();
        }
        return $featureIdentifierList;
    }
    public static function getProductsIdFromFeatureValue($idSearch, $idFeature, $idFeatureValueList)
    {
        $result = As4SearchEngineDb::query('
            SELECT acp.`id_cache_product`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int)$idSearch.'` acp
            LEFT JOIN `'._DB_PREFIX_.'feature_product` fp ON (fp.`id_feature` = '.(int)$idFeature.' AND acp.`id_product` = fp.`id_product`)
            WHERE fp.`id_feature_value` IN ('.implode(',', array_map('intval', $idFeatureValueList)).')
        ');
        return $result;
    }
    public static function getManufacturers($id_lang = false, $id_manufacturer = false)
    {
        $result = As4SearchEngineDb::query('
        SELECT `name` '.(!$id_lang ? ' as simple_name':'').', `id_manufacturer`, m.`active`
        FROM '._DB_PREFIX_.'manufacturer m
        '.($id_manufacturer ? ' WHERE `id_manufacturer` = '. (int)$id_manufacturer : '').'
        ORDER BY m.`name`');
        if (!$id_lang && $result) {
            foreach ($result as $key => $row) {
                foreach (Language::getLanguages(false) as $row_lang) {
                    $result[$key]['name'][$row_lang['id_lang']] = $row['simple_name'];
                }
            }
        }
        return $result;
    }
    public static function getProductsIdFromManufacturer($id_search, $id_manufacturer)
    {
        return As4SearchEngineDb::query('
        SELECT acp.`id_cache_product`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $id_search.'` acp
        LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = acp.`id_product`)
        ' . Shop::addSqlAssociation('product', 'p') . '
        WHERE product_shop.`active` = 1
        AND p.`id_manufacturer` = '.(int)($id_manufacturer));
    }
    public static function getSuppliers($id_lang = false, $id_supplier = false)
    {
        $result = As4SearchEngineDb::query('
        SELECT `name` '.(!$id_lang ? ' as simple_name':'').', `id_supplier`, s.`active`
        FROM '._DB_PREFIX_.'supplier s
        '.($id_supplier ? ' WHERE `id_supplier` = '. (int)$id_supplier : '').'
        ORDER BY s.`name`');
        if (!$id_lang && $result) {
            foreach ($result as $key => $row) {
                foreach (Language::getLanguages(false) as $row_lang) {
                    $result[$key]['name'][$row_lang['id_lang']] = $row['simple_name'];
                }
            }
        }
        return $result;
    }
    public static function getProductsFieldValues($field, $value = false)
    {
        if ($value) {
            if ($field == 'weight' || $field == 'width' || $field == 'height' || $field == 'depth') {
                $where_clause = 'ROUND(p.`'.pSQL($field).'`, 6) = ROUND('.pSQL($value).', 6)';
            } else {
                $where_clause = 'p.`'.pSQL($field).'` = "'.pSQL($value).'"';
            }
        }
        $valuesList = array();
        $results = As4SearchEngineDb::query('
                SELECT `'.pSQL($field).'` AS value
                FROM `'._DB_PREFIX_.'product` p
                ' . Shop::addSqlAssociation('product', 'p') . '
                WHERE product_shop.`active` = 1'.($value ? ' AND '.$where_clause : '').'
                GROUP BY p.`'.pSQL($field).'`
                ORDER BY p.`'.pSQL($field).'`');
        if (AdvancedSearchCoreClass::_isFilledArray($results)) {
            foreach ($results as $row) {
                $langList = Language::getLanguages(false);
                $valueRow = array();
                foreach ($langList as $langRow) {
                    $valueRow['name'][$langRow['id_lang']] = $row['value'];
                }
                $valuesList[] = $valueRow;
            }
        }
        return $valuesList;
    }
    public static function getProductsIdFromSupplier($id_search, $id_supplier)
    {
        return As4SearchEngineDb::query('
        SELECT acp.`id_cache_product`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $id_search.'` acp
        LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = acp.`id_product`)
        ' . Shop::addSqlAssociation('product', 'p') . '
        JOIN `'._DB_PREFIX_.'product_supplier` psupplier ON (psupplier.`id_product` = p.`id_product` AND psupplier.`id_supplier` = '.(int)$id_supplier . ' AND psupplier.`id_product_attribute` = 0)
        WHERE product_shop.`active` = 1');
    }
    public static function getCategoriesP($id_search, $idCategory = false, $levelDepth = false)
    {
        $result = array();
        $resource = As4SearchEngineDb::query('
        SELECT c.`id_category`, c.`id_parent`, c.`level_depth`, cl.`id_lang`, cl.`name`, c.`active`, c_shop.`position`
        FROM `'._DB_PREFIX_.'category` c
        INNER JOIN `'._DB_PREFIX_.'category_shop` c_shop ON (c_shop.id_category = c.id_category AND c_shop.`id_shop` IN ('.As4SearchEngine::getShopBySearch($id_search).'))
        LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') .
        ' WHERE 1 ' .
        ($idCategory ? ' AND c.`id_category` =' . (int)$idCategory : '') .
        ($levelDepth ? ' AND c.`level_depth` = '. ((int)$levelDepth + 1) : '') .
        ' ORDER BY c.`level_depth` '. pSQL(self::$originalSortWay) . ', c_shop.`position` '. pSQL(self::$originalSortWay), 1, false);
        while ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->nextRow($resource)) {
            $idCategory = (int)$row['id_category'];
            if (!isset($result[$idCategory])) {
                $result[$idCategory] = $row;
                unset($result[$idCategory]['id_lang']);
                $result[$idCategory]['name'] = array();
            }
            $result[$idCategory]['name'][(int)$row['id_lang']] = $row['name'];
        }
        return $result;
    }
    public static function getBooleanCriteria()
    {
        return array(
                array('name'=> Module::getInstanceByName('pm_advancedsearch4')->translateMultiple('yes'),'value'=>1),
                array('name'=> Module::getInstanceByName('pm_advancedsearch4')->translateMultiple('no') ,'value'=>0),
        );
    }
    public static function getConditionCriteria($value = false)
    {
        $return = array(
                'new'=>array('name'=> Module::getInstanceByName('pm_advancedsearch4')->translateMultiple('new'),'value'=>'new'),
                'used'=>array('name'=> Module::getInstanceByName('pm_advancedsearch4')->translateMultiple('used') ,'value'=>'used'),
                'refurbished'=>array('name'=> Module::getInstanceByName('pm_advancedsearch4')->translateMultiple('refurbished') ,'value'=>'refurbished')
        );
        if ($value) {
            return array($return[$value]);
        }
        return $return;
    }
    public static function getBooleanTrueCriteria()
    {
        return array(
                array('name'=> Module::getInstanceByName('pm_advancedsearch4')->translateMultiple('yes'),'value'=>1)
        );
    }
    public static function getChildsCategoriesId($idsSearch)
    {
        $idsReturn = $idsSearch;
        $idCategoryOrigin = array_values($idsSearch);
        while (true) {
            $query = 'SELECT c.`id_category` FROM `'._DB_PREFIX_.'category` c WHERE c.`id_parent` IN ('.implode(',', array_map('intval', $idsSearch)).') AND c.`id_category` NOT IN ( '.implode(',', array_map('intval', $idCategoryOrigin)).')';
            $result = As4SearchEngineDb::query($query);
            if (!$result) {
                return $idsReturn;
            }
            $idsSearch = array();
            foreach ($result as $row) {
                $idsSearch[] = $row['id_category'];
                $idsReturn[] = $row['id_category'];
            }
        }
    }
    private static $_getParentsCategoriesIdCache = array();
    public static function getParentsCategoriesId($idsSearch, $ignore = false)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$_getParentsCategoriesIdCache[$cacheKey])) {
            return self::$_getParentsCategoriesIdCache[$cacheKey];
        }
        $idsReturn = $idsSearch;
        $idsSearch = array_keys($idsSearch);
        while (true) {
            $query = '
            SELECT c.`id_parent`, c.`level_depth` FROM `'._DB_PREFIX_.'category` c
            WHERE c.`id_category` IN ('.implode(',', array_map('intval', $idsSearch)).')'.($ignore ? ' AND c.`id_parent` NOT IN ('.implode(',', array_map('intval', $ignore)).')':'');
            $result = As4SearchEngineDb::query($query);
            if (!$result) {
                self::$_getParentsCategoriesIdCache[$cacheKey] = $idsReturn;
                return self::$_getParentsCategoriesIdCache[$cacheKey];
            }
            $idsSearch = array();
            foreach ($result as $row) {
                $idsReturn[$row['id_parent']] = $row;
                $idsSearch[$row['id_parent']] = $row['id_parent'];
            }
        }
    }
    public static function getProductsIdFromCategory($id_search, $id_category, $recursing_indexing)
    {
        if ($recursing_indexing) {
            $all_childs_categories = self::getChildsCategoriesId(array($id_category));
        } else {
            $all_childs_categories = array($id_category);
        }
        return As4SearchEngineDb::query('
        SELECT DISTINCT acp.`id_cache_product`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $id_search.'` acp
        LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = acp.`id_product`)
        LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (p.`id_product` = cp.`id_product`)
        ' . Shop::addSqlAssociation('product', 'p') . '
        WHERE product_shop.`active` = 1 AND cp.`id_category` IN ('.implode(',', array_map('intval', $all_childs_categories)).')');
    }
    public static function getProductsIdFromProductField($id_search, $field_value, $field)
    {
        $where_clause = '';
        if ($field == 'stock') {
            $field = 'quantity';
            $where_clause .= ' 1 ';
            $where_clause .= ' GROUP BY acp.`id_cache_product` ';
            $where_clause .= ' HAVING SUM(sa.`'.$field.'`) >= '.(int)$field_value;
        } elseif ($field == 'weight' || $field == 'width' || $field == 'height' || $field == 'depth') {
            $where_clause = 'ROUND(p.`'.$field.'`, 6) = ROUND('.pSQL($field_value).', 6)';
        } elseif ($field == 'pack') {
            $where_clause = 'p.id_product IN (SELECT id_pack FROM `'._DB_PREFIX_.'pm_advancedpack`)';
        } elseif ($field == 'new_products') {
            $where_clause = 'DATEDIFF(
                product_shop.`date_add`,
                DATE_SUB(
                    "'.date('Y-m-d').' 00:00:00",
                    INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY
                )
            ) '.($field_value ? '> 0' : '<= 0');
        } elseif ($field == 'prices_drop') {
            $field = 'reduction';
            $now = date('Y-m-d H:i:00');
            $where_clause = 'sp.`'.bqSQL($field).'` > 0 AND
                    (
                        (`from` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' >= `from`)
                        AND
                        (`to` = \'0000-00-00 00:00:00\' OR \''.pSQL($now).'\' <= `to`)
                    )';
        } else {
            $where_clause = 'p.`'.bqSQL($field).'` = "'.pSQL($field_value).'"';
        }
        return As4SearchEngineDb::query('
                SELECT acp.`id_cache_product`
                FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $id_search.'` acp
                LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = acp.`id_product`)
                ' . Shop::addSqlAssociation('product', 'p') . '
                '.($field == 'quantity' ? 'LEFT JOIN `'._DB_PREFIX_.'stock_available` sa ON (sa.`id_product` = acp.`id_product` '.StockAvailable::addSqlShopRestriction(null, null, 'sa') . ')' : '').'
                '.($field == 'reduction' ? 'LEFT JOIN `'._DB_PREFIX_.'specific_price` sp ON (sp.`id_product` = acp.`id_product` AND sp.`id_product_attribute`=0)' : '').'
                WHERE product_shop.`active` = 1 AND '.$where_clause);
    }
    private static $_getCategoriesFromProductCache = array();
    public static function getCategoriesFromProduct($id_product)
    {
        if (isset(self::$_getCategoriesFromProductCache[$id_product])) {
            return self::$_getCategoriesFromProductCache[$id_product];
        }
        $result = As4SearchEngineDb::query('
        SELECT cp.`id_category`, c.`level_depth`
        FROM `'._DB_PREFIX_.'category_product` cp
        LEFT JOIN `'._DB_PREFIX_.'category` c ON (cp.`id_category` = c.`id_category`)
        WHERE cp.`id_product` = '.(int)$id_product);
        $categories = array();
        foreach ($result as $row) {
            $categories[$row['id_category']] = $row;
        }
        self::$_getCategoriesFromProductCache[$id_product] = $categories;
        return self::$_getCategoriesFromProductCache[$id_product];
    }
    public static function getCriterionsGroupsIndexed($id_search, $id_lang = false, $visible = true)
    {
        return As4SearchEngineDb::query('
        SELECT acg.* '.((int) $id_lang ? ', acgl.*':'').'
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'` acg
        '.($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'_lang` acgl ON (acg.`id_criterion_group` = acgl.`id_criterion_group` AND acgl.`id_lang` = '.(int) $id_lang.')' : '').'
        WHERE acg.`id_search` = '.(int)($id_search).'
        '.($visible ? ' AND `visible` = 1' : '').'
        GROUP BY acg.`id_criterion_group`
        ORDER BY acg.`position`');
    }
    public static function getProductsSpecificPrices($id_search, $id_product = false, $id_shop = false, $start = 0, $limit = 10000)
    {
        if ($id_shop == false) {
            $id_shop = As4SearchEngine::getShopBySearch($id_search);
        }
        return As4SearchEngineDb::query('SELECT sp.*, product_shop.`cache_default_attribute`, product_shop.`price` as default_price, acp.`id_cache_product`, '.As4SearchEngine::getScoreQuery($id_shop, 0, 0, 0, true, true, true).'
        FROM `'._DB_PREFIX_.'specific_price` sp
        LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = sp.`id_product`)
        INNER JOIN `'._DB_PREFIX_.'product_shop` product_shop ON (product_shop.`id_product` = p.`id_product` AND product_shop.`id_shop` = '.(int)$id_shop.')
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $id_search.'` acp ON (p.`id_product` = acp.`id_product`)
        WHERE sp.`id_cart`=0
        AND sp.`from_quantity` <= 1
        '.($id_product ? ' AND p.`id_product` = '.(int) $id_product : '').'
        '.(class_exists('AdvancedPack') && AdvancedSearchCoreClass::_isFilledArray(AdvancedPack::getIdsPacks(true)) ? ' AND p.`id_product` NOT IN ('. implode(',', array_map('intval', AdvancedPack::getIdsPacks(true))) . ')' : '').'
        GROUP BY sp.`id_product`, sp.`id_product_attribute`, sp.`id_shop`, sp.`id_currency`, sp.`id_country`, sp.`id_group`, sp.`price`,sp.`reduction`, sp.`reduction_type`, sp.`from`, sp.`to`
        ORDER BY sp.`id_product_attribute` DESC, sp.`from_quantity` DESC, sp.`id_specific_price_rule` ASC, `score` DESC
        LIMIT '.(int)($start*$limit).', '.(int)$limit);
    }
    public static function getProductsPriceFromProductTable($id_search, $id_product = false, $id_shop = false)
    {
        if ($id_shop == false) {
            $id_shop = As4SearchEngine::getShopBySearch($id_search);
        }
        return As4SearchEngineDb::query('SELECT product_shop.`id_shop`, product_shop.`cache_default_attribute`, p.`id_product`, product_shop.`price`, product_shop.`id_tax_rules_group`, acp.`id_cache_product`
        FROM `'._DB_PREFIX_.'product` p
        INNER JOIN `'._DB_PREFIX_.'product_shop` product_shop ON (product_shop.`id_product` = p.`id_product` AND product_shop.`id_shop` = '.(int)$id_shop.')
        LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $id_search.'` acp ON (p.`id_product` = acp.`id_product`)
        WHERE product_shop.`active` = 1 AND product_shop.`id_shop` = '.(int)$id_shop.' '.($id_product ? ' AND p.`id_product` = '.(int) $id_product:''));
    }
    private static $_getDefaultAttributePriceCache = array();
    public static function getDefaultAttributePrice($id_product_attribute, $id_shop)
    {
        $cacheKey = $id_product_attribute.'-'.$id_shop;
        if (isset(self::$_getDefaultAttributePriceCache[$cacheKey])) {
            return self::$_getDefaultAttributePriceCache[$cacheKey];
        }
        $row = As4SearchEngineDb::row('SELECT product_attribute_shop.`price`
            FROM `'._DB_PREFIX_.'product_attribute` pa
            INNER JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop ON (product_attribute_shop.`id_product_attribute` = pa.`id_product_attribute` AND product_attribute_shop.`id_shop` = '.(int)$id_shop.')
            WHERE product_attribute_shop.`id_product_attribute` = '.(int)($id_product_attribute).'
            AND product_attribute_shop.`id_shop` = '.(int)$id_shop);
        self::$_getDefaultAttributePriceCache[$cacheKey] = (isset($row['price']) ? $row['price'] : 0);
        return self::$_getDefaultAttributePriceCache[$cacheKey];
    }
    private static $_getDefaultAttributeCache = array();
    public static function getDefaultAttribute($id_product, $minimum_quantity = 0)
    {
        if (isset(self::$_getDefaultAttributeCache[$id_product.'-'.$minimum_quantity])) {
            return self::$_getDefaultAttributeCache[$id_product.'-'.$minimum_quantity];
        }
        self::$_getDefaultAttributeCache[$id_product.'-'.$minimum_quantity] = Product::getDefaultAttribute($id_product, $minimum_quantity);
        return self::$_getDefaultAttributeCache[$id_product.'-'.$minimum_quantity];
    }
    private static $_productHasAttributesCache = array();
    public static function productHasAttributes($id_product)
    {
        if (isset(self::$_productHasAttributesCache[$id_product])) {
            return self::$_productHasAttributesCache[$id_product];
        }
        self::$_productHasAttributesCache[$id_product] = (bool)As4SearchEngineDb::value('
            SELECT pa.id_product_attribute
            FROM `'._DB_PREFIX_.'product_attribute` pa
            LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` pas ON (pa.`id_product_attribute` = pas.`id_product_attribute`)
            WHERE pa.`id_product` = '.(int)$id_product);
        return self::$_productHasAttributesCache[$id_product];
    }
    public static function setProductsSpecificPrices($id_search, $id_product = false)
    {
        $specific_prices_cache = As4SearchEngineDb::query('
            SELECT * FROM `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$id_search.'` app, `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int)$id_search.'` acp
            WHERE app.`id_cache_product`=acp.`id_cache_product`
            AND app.`is_specific`=1
            '.($id_product != false ? ' AND acp.`id_product`='.(int)$id_product : ''). '
            AND app.`id_shop`='.(int)As4SearchEngine::getShopBySearch($id_search) . '
            GROUP BY acp.`id_product`, app.`id_shop`, app.`id_currency`, app.`id_country`, app.`id_group`');
        if ($id_product == false) {
            As4SearchEngineDb::execute('UPDATE `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$id_search.'` SET `valid_id_specific_price`=0');
        } else {
            As4SearchEngineDb::execute('UPDATE `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$id_search.'` app, `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int)$id_search.'` acp
            SET app.`valid_id_specific_price`=0
            WHERE app.`id_cache_product`=acp.`id_cache_product` AND acp.`id_product`='.(int)$id_product);
        }
        if ($specific_prices_cache && AdvancedSearchCoreClass::_isFilledArray($specific_prices_cache)) {
            foreach ($specific_prices_cache as $row) {
                $defaultIdProductAttribute = null;
                if (!empty(self::$defaultSpecificPriceIDPList[(int)$row['id_cache_product']])) {
                    $defaultIdProductAttribute = (int)self::$defaultSpecificPriceIDPList[(int)$row['id_cache_product']];
                }
                $specific_price = SpecificPrice::getSpecificPrice((int)$row['id_product'], (int)$row['id_shop'], (int)$row['id_currency'], (int)$row['id_country'], (int)$row['id_group'], 1, $defaultIdProductAttribute, 0, 0, 1);
                if ($specific_price && AdvancedSearchCoreClass::_isFilledArray($specific_price)) {
                    As4SearchEngineDb::execute('UPDATE `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$id_search.'` SET `valid_id_specific_price`=1 WHERE `id_group`='.(int)$row['id_group'].' AND `id_cache_product`='.(int)$row['id_cache_product'].' AND `id_specific_price`='.(int)$specific_price['id_specific_price']);
                } else {
                    As4SearchEngineDb::execute('UPDATE `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$id_search.'` SET `has_no_specific`=1 WHERE `id_group`='.(int)$row['id_group'].' AND `id_cache_product`='.(int)$row['id_cache_product'] . ' AND `id_shop`='.(int)$row['id_shop']);
                    As4SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$id_search.'` WHERE `id_group`='.(int)$row['id_group'].' AND `is_specific`=1 AND `id_cache_product`='.(int)$row['id_cache_product'] . ' AND `id_shop`='.(int)$row['id_shop']);
                }
            }
        }
        As4SearchEngineDb::execute('
            UPDATE `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$id_search.'` app
            JOIN (SELECT `id_cache_product`, `id_shop`, `id_group`
                FROM `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$id_search.'`
                GROUP BY `id_cache_product`, `id_shop`, `id_group`
                HAVING SUM(`valid_id_specific_price`)=0) as app2
            ON (app.`id_cache_product`=app2.`id_cache_product` AND app.`id_shop`=app2.`id_shop` AND app.`id_group`=app2.`id_group`)
            SET app.`has_no_specific`=1
        ');
    }
    public static $defaultSpecificPriceIDPList = array();
    public static function setProductsPrices($id_search, $id_criterion_group, $id_product = false)
    {
        $sql_insert_multiple = array();
        $sql_insert_multiple_header = 'INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$id_search.'` (`id_criterion_group`, `id_shop`, `id_country`, `id_currency`, `id_group`, `price_wt`, `reduction_amount`, `reduction_type`, `reduction_tax`, `from`, `to`, `is_specific`, `has_no_specific`, `id_specific_price`, `valid_id_specific_price`, `id_cache_product`) VALUES ';
        $select_limit = 10000;
        $select_iteration = 0;
        $products_price = self::getProductsSpecificPrices($id_search, $id_product, false, $select_iteration, $select_limit);
        $nb_products_specific_price = sizeof($products_price);
        $specificPriceDefaultGroup = array();
        $defaultGroupId = As4SearchEngine::getDefaultGroupId();
        $nb_products_specific_price2 = $nb_products_specific_price;
        $products_price2 = $products_price;
        $select_iteration2 = $select_iteration;
        $select_limit2 = $select_limit;
        while ($nb_products_specific_price2 > 0) {
            foreach ($products_price2 as $row) {
                if (!$row['id_cache_product']) {
                    continue;
                }
                if ($row['id_group'] == $defaultGroupId && !empty($row['reduction_type']) && ((empty($row['to']) || $row['to'] == '0000-00-00 00:00:00') && (empty($row['from']) || $row['from'] == '0000-00-00 00:00:00'))) {
                    $specificPriceDefaultGroup[(int)$row['id_shop']][(int)$row['id_product']] = true;
                }
            }
            if ($nb_products_specific_price2 >= $select_limit2) {
                $products_price2 = self::getProductsSpecificPrices($id_search, $id_product, false, $select_iteration2, $select_limit2);
                $select_iteration2++;
                $nb_products_specific_price2 = sizeof($products_price2);
                if ($nb_products_specific_price2 == 0) {
                    break;
                }
            } else {
                $nb_products_specific_price2 = 0;
                break;
            }
        }
        $has_specific_price = array();
        while ($nb_products_specific_price > 0) {
            foreach ($products_price as $row) {
                if (!$row['id_cache_product']) {
                    continue;
                }
                $price = (float)$row['price'] > 0 ? $row['price'] : $row['default_price'];
                if (isset($row['id_shop']) && $row['id_shop'] > 0) {
                    $id_shop = (int)$row['id_shop'];
                } else {
                    $id_shop = 0;
                }
                if ($id_shop == 0) {
                    $liste_id_shop = array(As4SearchEngine::getShopBySearch($id_search));
                } else {
                    $liste_id_shop = array($id_shop);
                }
                foreach ($liste_id_shop as $id_shop) {
                    if (self::productHasAttributes($row['id_product'])) {
                        if ($row['cache_default_attribute'] == 0) {
                            Product::updateDefaultAttribute($row['id_product']);
                            $cache_default_attribute = self::getDefaultAttribute($row['id_product']);
                            $row['cache_default_attribute'] = $cache_default_attribute;
                        }
                        if (!empty($row['id_product_attribute']) && $row['cache_default_attribute'] && $row['id_product_attribute'] != $row['cache_default_attribute']) {
                            continue;
                        }
                        self::$defaultSpecificPriceIDPList[(int)$row['id_cache_product']] = (int)$row['id_product_attribute'];
                        if ($row['cache_default_attribute']) {
                            $price += self::getDefaultAttributePrice($row['cache_default_attribute'], $id_shop);
                        }
                    }
                    $reduc = 0;
                    if ($row['reduction_type'] == 'amount') {
                        $reduc = Tools::ps_round($row['reduction'], 6);
                    } else {
                        $reduc = Tools::ps_round($price * $row['reduction'], 6);
                    }
                    $price = Tools::ps_round($price, 6);
                    if ($price < 0) {
                        continue;
                    }
                    if (!isset($has_specific_price[$id_shop])) {
                        $has_specific_price[$id_shop] = array();
                    }
                    $has_specific_price[$id_shop][] = (int)$row['id_cache_product'];
                    if (!isset($row['reduction_tax'])) {
                        $row['reduction_tax'] = 1;
                    }
                    $sql_insert_multiple[] = '('.(int)$id_criterion_group.', '.(int)$id_shop.', '.(int)$row['id_country'].', '.(int)$row['id_currency'].', '.(int)$row['id_group'].', '.(float)$price.', '.(float)$reduc.', "'.pSQL($row['reduction_type']).'", '.(int)$row['reduction_tax'].', "'.$row['from'].'", "'.$row['to'].'", 1, 0, '.(int)$row['id_specific_price'].', 0, '.(int)$row['id_cache_product'].')';
                }
                self::sqlBulkInsert('pm_advancedsearch_product_price_'.$id_search, $sql_insert_multiple_header, $sql_insert_multiple, 200);
            }
            if ($nb_products_specific_price >= $select_limit) {
                $select_iteration++;
                $products_price = self::getProductsSpecificPrices($id_search, $id_product, false, $select_iteration, $select_limit);
                $nb_products_specific_price = sizeof($products_price);
                if ($nb_products_specific_price == 0) {
                    break;
                }
            } else {
                $nb_products_specific_price = 0;
                break;
            }
        }
        self::sqlBulkInsert('pm_advancedsearch_product_price_'.$id_search, $sql_insert_multiple_header, $sql_insert_multiple, 200);
        $products = self::getProductsPriceFromProductTable($id_search, $id_product);
        $packList = null;
        if (class_exists('AdvancedPack')) {
            $packList = AdvancedPack::getIdsPacks(true);
        }
        foreach ($products as $row) {
            $id_product = $row['id_product'];
            $price = $row['price'];
            if (isset($packList) && in_array($id_product, $packList)) {
                $price = AdvancedPack::getPackPrice($id_product, false);
            }
            if (isset($row['id_shop']) && $row['id_shop'] > 0) {
                $id_shop = (int)$row['id_shop'];
            } else {
                $id_shop = 0;
            }
            if (isset($specificPriceDefaultGroup[(int)$id_shop][(int)$row['id_product']]) || isset($specificPriceDefaultGroup[0][(int)$row['id_product']])) {
                continue;
            }
            if (self::productHasAttributes($id_product)) {
                if ($row['cache_default_attribute'] == 0) {
                    Product::updateDefaultAttribute($id_product);
                    $cache_default_attribute = self::getDefaultAttribute($id_product);
                    $row['cache_default_attribute'] = $cache_default_attribute;
                }
                if ($row['cache_default_attribute']) {
                    $price += self::getDefaultAttributePrice($row['cache_default_attribute'], $id_shop);
                }
            }
            if ($price < 0) {
                continue;
            }
            if (!isset($has_specific_price[$id_shop])) {
                $has_specific_price[$id_shop] = array();
            }
            $has_no_specific_price_bool = (int)!in_array((int)$row['id_cache_product'], $has_specific_price[$id_shop]);
            $sql_insert_multiple[] = '('.(int) $id_criterion_group.', '.(int)$id_shop.', 0, 0, 0, '.(float) $price.', 0, NULL, 1, "0000-00-00 00:00:00", "0000-00-00 00:00:00", 0, '.(int)$has_no_specific_price_bool.', NULL, 0, '.(int) $row['id_cache_product'].')';
            self::sqlBulkInsert('pm_advancedsearch_product_price_'.$id_search, $sql_insert_multiple_header, $sql_insert_multiple, 200);
        }
        self::sqlBulkInsert('pm_advancedsearch_product_price_'.$id_search, $sql_insert_multiple_header, $sql_insert_multiple, 1);
    }
    public static function criterionsGroupIsIndexed($criterion_group_type, $id_criterion_group_linked, $id_search, $invisible = 0)
    {
        $row = As4SearchEngineDb::row('
            SELECT acg.`id_criterion_group`
            FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'` acg
            WHERE acg.`criterion_group_type` = "'.pSQL($criterion_group_type).'" AND acg.`id_criterion_group_linked` = '.(int)$id_criterion_group_linked.
        ($invisible ? ' AND `visible` = 0' : ''));
        return isset($row) ? $row['id_criterion_group'] : false;
    }
    public static function indexFilterByEmplacement($id_search)
    {
        if (!self::criterionsGroupIsIndexed('manufacturer', 0, $id_search)) {
            self::indexCriterionsGroup($id_search, 'manufacturer', 0, false, 0, false);
        }
        if (!self::criterionsGroupIsIndexed('supplier', 0, $id_search)) {
            self::indexCriterionsGroup($id_search, 'supplier', 0, false, 0, false);
        }
        if (!self::criterionsGroupIsIndexed('category', 0, $id_search)) {
            self::indexCriterionsGroup($id_search, 'category', 0, false, 0, false);
        }
    }
    public static function reindexingCategoriesGroups($objSearch)
    {
        $categoriesGroups = self::getCategoriesCriteriaGroup($objSearch->id);
        if ($categoriesGroups) {
            foreach ($categoriesGroups as $row) {
                self::indexCriterionsGroup($objSearch->id, 'category', $row['id_criterion_group_linked'], $row['id_criterion_group'], $row['visible'], false, true);
            }
        }
    }
    public static function deleteCacheFromIdProduct($id_search, $id_product)
    {
        $idCacheProduct = self::getIdCacheProductFromIdProduct($id_search, $id_product);
        if ($idCacheProduct) {
            As4SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int)$id_search.'` WHERE `id_cache_product` = '.(int)$idCacheProduct);
            As4SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$id_search.'` WHERE `id_cache_product` = '.(int)$idCacheProduct);
        } else {
            As4SearchEngineDb::execute('DELETE acpc.* FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int)$id_search.'` acpc LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $id_search.'` acp ON (acp.`id_cache_product` = acpc.`id_cache_product`) WHERE acp.`id_product` = '.(int)$id_product);
            As4SearchEngineDb::execute('DELETE app.* FROM `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int)$id_search.'` app LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int) $id_search.'` acp ON (acp.`id_cache_product` = app.`id_cache_product`) WHERE acp.`id_product` = '.(int)$id_product);
        }
    }
    public static function deleteCacheCriterionGroup($id_search, $id_criterion_group)
    {
        As4SearchEngineDb::execute('DELETE acpc.* FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int) $id_search.'` acpc LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int) $id_search.'` ac ON (ac.`id_criterion` = acpc.`id_criterion`) WHERE ac.`id_criterion_group` = '.(int)$id_criterion_group);
    }
    public static function deleteCacheCriterion($idSearch, $idCriterion, $idCacheProduct = false)
    {
        As4SearchEngineDb::execute('
            DELETE acpc.* FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'. (int)$idSearch .'` acpc
            WHERE acpc.`id_criterion`=' . (int)$idCriterion
            . ($idCacheProduct ? ' AND acpc.`id_cache_product`=' . (int)$idCacheProduct : ''));
    }
    public static function deleteCachePriceGroup($id_search, $id_criterion_group)
    {
        As4SearchEngineDb::execute('DELETE app.* FROM `'._DB_PREFIX_.'pm_advancedsearch_product_price_'.(int) $id_search.'` app WHERE app.`id_criterion_group` = '.(int)$id_criterion_group);
    }
    public static function addProductToCache($id_search, $id_product)
    {
        Db::getInstance()->insert('pm_advancedsearch_cache_product_'.(int)$id_search, array('id_product' => (int)$id_product));
    }
    public static function desIndexCriterionsFromProduct($id_product)
    {
        $advanced_searchs_id = As4SearchEngine::getSearchsId(false);
        foreach ($advanced_searchs_id as $idSearch) {
            self::deleteCacheFromIdProduct($idSearch, $id_product);
        }
        return true;
    }
    public static function indexCriterionsFromProduct($product, $add = false)
    {
        if (!Validate::isLoadedObject($product)) {
            return;
        }
        if (As4SearchEngineIndexation::$processingAutoReindex && Shop::getContext() != Shop::CONTEXT_SHOP) {
            $advanced_searchs_id = As4SearchEngine::getSearchsIdWithAutoReindex(false);
        } else {
            $advanced_searchs_id = As4SearchEngine::getSearchsId(false);
        }
        foreach ($advanced_searchs_id as $id_search) {
            if (!self::getIdCacheProductFromIdProduct($id_search, $product->id)) {
                self::addProductToCache($id_search, $product->id);
            } elseif (!$add) {
                self::deleteCacheFromIdProduct($id_search, $product->id);
            }
            $criterions_groups_indexed = self::getCriterionsGroupsIndexed($id_search, false, false);
            foreach ($criterions_groups_indexed as $row2) {
                if ($row2['criterion_group_type'] == 'manufacturer' && $product->id_manufacturer) {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, $product->id_manufacturer);
                } elseif ($row2['criterion_group_type'] == 'supplier' && $product->id_supplier) {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, $product->id_supplier);
                } elseif ($row2['criterion_group_type'] == 'feature') {
                    $features = self::getFeaturesFromProduct($product->id, (int)Context::getContext()->language->id, $row2['id_criterion_group_linked']);
                    foreach ($features as $feature) {
                        self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], $feature['id_feature'], $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, false, $feature['value']);
                    }
                } elseif ($row2['criterion_group_type'] == 'attribute') {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], $row2['id_criterion_group_linked'], $row2['id_criterion_group'], $row2['visible'], false, true, $product->id);
                } elseif ($row2['criterion_group_type'] == 'category') {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], $row2['id_criterion_group_linked'], $row2['id_criterion_group'], $row2['visible'], false, true);
                } elseif ($row2['criterion_group_type'] == 'price') {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id);
                } elseif ($row2['criterion_group_type'] == 'on_sale' && $product->on_sale) {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, $product->on_sale);
                } elseif ($row2['criterion_group_type'] == 'available_for_order' && $product->available_for_order) {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, $product->available_for_order);
                } elseif ($row2['criterion_group_type'] == 'online_only' && $product->online_only) {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, $product->online_only);
                } elseif ($row2['criterion_group_type'] == 'stock') {
                    $idShop = As4SearchEngine::getShopBySearch($id_search);
                    $globalStockAvailable = StockAvailable::getQuantityAvailableByProduct($product->id, 0, $idShop);
                    if ($globalStockAvailable > 0) {
                        self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, 1);
                    }
                } elseif ($row2['criterion_group_type'] == 'weight' && $product->weight) {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, false, $product->weight);
                } elseif ($row2['criterion_group_type'] == 'width' && $product->width) {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, false, $product->width);
                } elseif ($row2['criterion_group_type'] == 'height' && $product->height) {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, false, $product->height);
                } elseif ($row2['criterion_group_type'] == 'depth' && $product->depth) {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, false, $product->depth);
                } elseif ($row2['criterion_group_type'] == 'condition' && $product->condition) {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, false, $product->condition);
                } elseif ($row2['criterion_group_type'] == 'pack' && class_exists('AdvancedPack')) {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, AdvancedPack::isValidPack($product->id));
                } elseif ($row2['criterion_group_type'] == 'new_products' && $product->new) {
                    self::indexCriterionsGroup($id_search, $row2['criterion_group_type'], 0, $row2['id_criterion_group'], $row2['visible'], false, true, $product->id, $product->new);
                }
            }
        }
        return true;
    }
    public static function desIndexCriterionsGroup($id_search, $criterions_group_type, $id_criterion_group_linked, $id_criterion_group = false, $force_delete = false, $desindexGroup = true)
    {
        As4SearchEngine::setLocalStorageCacheKey();
        if (!$id_criterion_group) {
            $id_criterion_group = AdvancedSearchCriterionGroupClass::getIdCriterionGroupByTypeAndIdLinked($id_search, $criterions_group_type, $id_criterion_group_linked);
        }
        $objAdvancedSearchCriterionGroupClass = new AdvancedSearchCriterionGroupClass($id_criterion_group, $id_search);
        if (!Validate::isLoadedObject($objAdvancedSearchCriterionGroupClass)) {
            return false;
        } elseif (Validate::isLoadedObject($objAdvancedSearchCriterionGroupClass) && !$objAdvancedSearchCriterionGroupClass->visible) {
            return false;
        }
        if (!$force_delete && $objAdvancedSearchCriterionGroupClass->criterion_group_type == 'category' && $objAdvancedSearchCriterionGroupClass->id_criterion_group_linked == 0 && $objAdvancedSearchCriterionGroupClass->visible) {
            if (!self::criterionsGroupIsIndexed('category', 0, $id_search, true)) {
                $objAdvancedSearchCriterionGroupClass->display_type = 1;
                $objAdvancedSearchCriterionGroupClass->context_type = 0;
                $objAdvancedSearchCriterionGroupClass->is_multicriteria = 0;
                $objAdvancedSearchCriterionGroupClass->filter_option = 0;
                $objAdvancedSearchCriterionGroupClass->is_combined = 0;
                $objAdvancedSearchCriterionGroupClass->range = 0;
                $objAdvancedSearchCriterionGroupClass->show_all_depth = 0;
                $objAdvancedSearchCriterionGroupClass->only_children = 0;
                $objAdvancedSearchCriterionGroupClass->hidden = 0;
                $objAdvancedSearchCriterionGroupClass->position = 100;
                $objAdvancedSearchCriterionGroupClass->visible = 0;
                return $objAdvancedSearchCriterionGroupClass->save();
            }
        }
        $criterions = AdvancedSearchCriterionClass::getCriterionsStatic($id_search, $objAdvancedSearchCriterionGroupClass->id);
        foreach ($criterions as $row) {
            $objAdvancedSearchCriterionClassD = new AdvancedSearchCriterionClass($row['id_criterion'], $id_search);
            $objAdvancedSearchCriterionClassD->delete();
        }
        if ($desindexGroup) {
            $objAdvancedSearchCriterionGroupClass->delete();
        }
        $ObjAdvancedSearchClass = new AdvancedSearchClass($id_search);
        if ($ObjAdvancedSearchClass->filter_by_emplacement && !$force_delete) {
            self::indexFilterByEmplacement($id_search);
        }
        return true;
    }
    public static function removeOldCriterions($idSearch, $idCriterionGroup, $criterionsGroupType, $criterionsGroup = array())
    {
        $idList = array();
        if ($criterionsGroupType == 'attribute') {
            $idList = self::getAttributesIdList($criterionsGroup['id_attribute_group']);
        } elseif ($criterionsGroupType == 'feature') {
            $valuesListFromCriterions = AdvancedSearchCriterionClass::getCriterionsValueListByIdCriterionGroup($idSearch, $idCriterionGroup);
            $duplicateValues = array_count_values($valuesListFromCriterions);
            $idCriterionToMerge = array();
            foreach ($duplicateValues as $duplicateValue => $duplicateValueCount) {
                if ($duplicateValueCount > 1) {
                    $arraySearchResult = false;
                    while ($arraySearchResult = array_search($duplicateValue, $valuesListFromCriterions) and $arraySearchResult !== false) {
                        if (!isset($idCriterionToMerge[$duplicateValue])) {
                            $idCriterionToMerge[$duplicateValue] = array();
                        }
                        $idCriterionToMerge[$duplicateValue][] = $arraySearchResult;
                        unset($valuesListFromCriterions[$arraySearchResult]);
                    }
                }
            }
            foreach ($idCriterionToMerge as $duplicateValue => $idCriterionList) {
                array_shift($idCriterionList);
                foreach ($idCriterionList as $idCriterionToRemove) {
                    $objAdvancedSearchCriterionToRemove = new AdvancedSearchCriterionClass($idCriterionToRemove, $idSearch);
                    $objAdvancedSearchCriterionToRemove->delete();
                    $objAdvancedSearchCriterionToRemove->as4ForceClearCache();
                }
            }
            $idList = self::getFeaturesIdList($criterionsGroup['id_feature']);
        } elseif ($criterionsGroupType == 'category') {
            $idList = self::getCategoryIdList($idSearch);
        } elseif ($criterionsGroupType == 'supplier') {
            $idList = self::getSupplierIdList($idSearch);
        } elseif ($criterionsGroupType == 'manufacturer') {
            $idList = self::getManufacturerIdList($idSearch);
        } else {
            return;
        }
        if (AdvancedSearchCoreClass::_isFilledArray($idList)) {
            $currentCriterionsLinkedList = AdvancedSearchCriterionClass::getCriterionsListByIdCriterionGroup($idSearch, $idCriterionGroup);
            if (AdvancedSearchCoreClass::_isFilledArray($currentCriterionsLinkedList)) {
                $criterionsToRemove = array_diff($currentCriterionsLinkedList, $idList);
                if (AdvancedSearchCoreClass::_isFilledArray($criterionsToRemove)) {
                    if ($criterionsGroupType == 'feature') {
                        foreach (array_keys($criterionsToRemove) as $idCriterionToRemove) {
                            $objAdvancedSearchCriterionToRemove = new AdvancedSearchCriterionClass($idCriterionToRemove, $idSearch);
                            if (is_array($objAdvancedSearchCriterionToRemove->id_criterion_linked)) {
                                foreach ($objAdvancedSearchCriterionToRemove->id_criterion_linked as $k => $idCriterionLinked) {
                                    if (!in_array($idCriterionLinked, $idList)) {
                                        unset($objAdvancedSearchCriterionToRemove->id_criterion_linked[$k]);
                                    }
                                }
                            }
                            if (is_array($objAdvancedSearchCriterionToRemove->id_criterion_linked) && sizeof($objAdvancedSearchCriterionToRemove->id_criterion_linked) > 1) {
                                continue;
                            } else {
                                $objAdvancedSearchCriterionToRemove = new AdvancedSearchCriterionClass($idCriterionToRemove, $idSearch);
                                $objAdvancedSearchCriterionToRemove->delete();
                                $objAdvancedSearchCriterionToRemove->as4ForceClearCache();
                            }
                        }
                    } else {
                        foreach (array_keys($criterionsToRemove) as $idCriterionToRemove) {
                            $objAdvancedSearchCriterionToRemove = new AdvancedSearchCriterionClass($idCriterionToRemove, $idSearch);
                            $objAdvancedSearchCriterionToRemove->delete();
                            $objAdvancedSearchCriterionToRemove->as4ForceClearCache();
                        }
                    }
                }
            }
        }
    }
    public static $processingObjectIndexation = false;
    public static function indexCriterionsGroupFromObject($object, $onlyRemoveOldCriterions = false)
    {
        if (self::$processingObjectIndexation) {
            return;
        }
        self::$processingObjectIndexation = true;
        $groupType = $idObject = $idProduct = $idCriterionGroupLinked = false;
        $removeCriterionsInformations = array();
        $objectClass = get_class($object);
        if (in_array($objectClass, array('Supplier', 'Manufacturer', 'Category', 'FeatureValue', 'Attribute', 'SpecificPrice'))) {
            $groupType = Tools::strtolower($objectClass);
            $idObject = $object->id;
            if ($groupType == 'featurevalue') {
                $groupType = 'feature';
                $idObject = (int)$object->id;
                $idCriterionGroupLinked = (int)$object->id_feature;
                $removeCriterionsInformations = array('id_feature' => $idCriterionGroupLinked);
            } elseif ($groupType == 'attribute') {
                $groupType = 'attribute';
                $idObject = false;
                $idCriterionGroupLinked = (int)$object->id_attribute_group;
                $removeCriterionsInformations = array('id_attribute_group' => $idCriterionGroupLinked);
            } elseif ($groupType == 'specificprice') {
                $groupType = 'price';
                $idProduct = (int)$object->id_product;
            }
        }
        if (!empty($groupType)) {
            if (As4SearchEngineIndexation::$processingAutoReindex && Shop::getContext() != Shop::CONTEXT_SHOP) {
                $searchIds = As4SearchEngine::getSearchsIdWithAutoReindex(false);
            } else {
                $searchIds = As4SearchEngine::getSearchsId(false);
            }
            foreach ($searchIds as $idSearch) {
                $criterionsGroups = AdvancedSearchCriterionGroupClass::getCriterionsGroupByType($idSearch, $groupType);
                foreach ($criterionsGroups as $groupInfos) {
                    if (!empty($idCriterionGroupLinked) && $idCriterionGroupLinked != $groupInfos['id_criterion_group_linked']) {
                        continue;
                    }
                    if ($onlyRemoveOldCriterions) {
                        if ($groupInfos['criterion_group_type'] == 'attribute' ||  $groupInfos['criterion_group_type'] == 'feature') {
                            self::removeOldCriterions($idSearch, $groupInfos['id_criterion_group'], $groupInfos['criterion_group_type'], $removeCriterionsInformations);
                        } elseif ($groupInfos['criterion_group_type'] == 'category' || $groupInfos['criterion_group_type'] == 'supplier' || $groupInfos['criterion_group_type'] == 'manufacturer') {
                            self::removeOldCriterions($idSearch, $groupInfos['id_criterion_group'], $groupInfos['criterion_group_type']);
                        }
                    } else {
                        self::indexCriterionsGroup($idSearch, $groupInfos['criterion_group_type'], $groupInfos['id_criterion_group_linked'], $groupInfos['id_criterion_group'], $groupInfos['visible'], false, true, $idProduct, $idObject);
                        if (self::$needFullReindex) {
                            self::indexCriterionsGroup($idSearch, $groupInfos['criterion_group_type'], $groupInfos['id_criterion_group_linked'], $groupInfos['id_criterion_group'], $groupInfos['visible'], false, true);
                            self::$needFullReindex = false;
                        }
                    }
                }
            }
        }
        self::$processingObjectIndexation = false;
    }
    public static $needFullReindex = false;
    public static $processingAutoReindex = false;
    public static $processingIndexation = false;
    public static $indexationStats = array(
        'errors' => array(),
        'total_criterions' => 0,
        'updated_criterions' => 0,
        'unchanged_criterions' => 0,
        'new_criterions' => 0,
    );
    public static $originalSortWay = 'ASC';
    public static function indexCriterionsGroup($id_search, $criterions_group_type, $id_criterion_group_linked, $id_criterion_group = false, $visible = 1, $checkIfIsIndexed = true, $update = false, $id_product = false, $id_criterion_linked = false, $criterion_value = false)
    {
        As4SearchEngine::setLocalStorageCacheKey();
        self::$processingIndexation = true;
        $context = Context::getContext();
        $defaultIdLang = (int)Configuration::get('PS_LANG_DEFAULT');
        $idCacheProduct = false;
        $objSearch = new AdvancedSearchClass($id_search, (int)$context->language->id);
        $allLangIds = As4SearchEngineIndexation::getAllIdLang();
        self::updateCacheProduct($objSearch->id);
        if ($checkIfIsIndexed && self::criterionsGroupIsIndexed($criterions_group_type, $id_criterion_group_linked, $objSearch->id)) {
            self::desIndexCriterionsGroup($objSearch->id, $criterions_group_type, $id_criterion_group_linked, $id_criterion_group, true, true);
        }
        if ($update) {
            if (!$id_criterion_group) {
                $id_criterion_group = AdvancedSearchCriterionGroupClass::getIdCriterionGroupByTypeAndIdLinked($objSearch->id, $criterions_group_type, $id_criterion_group_linked);
            }
            if (!$id_criterion_group) {
                return;
            }
            if (!$id_product) {
                if ($criterions_group_type == 'price') {
                    self::deleteCachePriceGroup($objSearch->id, $id_criterion_group);
                } elseif (!$id_criterion_linked) {
                    self::deleteCacheCriterionGroup($objSearch->id, $id_criterion_group);
                }
            }
        }
        if ($criterions_group_type == 'attribute') {
            $criterions_group = self::getAttributeGroups($id_criterion_group_linked, false);
        } elseif ($criterions_group_type == 'feature') {
            $criterions_group = self::getFeature($id_criterion_group_linked, false);
        } elseif ($criterions_group_type == 'category') {
            $criterions_group = array(
                'name'    => Module::getInstanceByName('pm_advancedsearch4')->translateMultiple('categories', $id_criterion_group_linked),
            );
        } else {
            $criterions_group = array(
                    'name'    => Module::getInstanceByName('pm_advancedsearch4')->translateMultiple($criterions_group_type),
            );
        }
        if (!isset($criterions_group['name'][$defaultIdLang]) || !trim($criterions_group['name'][$defaultIdLang])) {
            self::$processingIndexation = false;
            return;
        }
        $objAdvancedSearchCriterionGroupClass = new AdvancedSearchCriterionGroupClass($id_criterion_group, $objSearch->id);
        if (!$update) {
            $objAdvancedSearchCriterionGroupClass->name = $criterions_group['name'];
        }
        $objAdvancedSearchCriterionGroupClass->visible = (int)$visible;
        if (!$visible) {
            $objAdvancedSearchCriterionGroupClass->position = 100;
        }
        $objAdvancedSearchCriterionGroupClass->criterion_group_type = $criterions_group_type;
        $objAdvancedSearchCriterionGroupClass->id_criterion_group_linked = $id_criterion_group_linked;
        if ($criterions_group_type == 'on_sale' || $criterions_group_type == 'stock' ||
                $criterions_group_type == 'available_for_order' || $criterions_group_type == 'online_only' || $criterions_group_type == 'pack' || $criterions_group_type == 'new_products' || $criterions_group_type == 'prices_drop') {
            if (!Validate::isLoadedObject($objAdvancedSearchCriterionGroupClass)) {
                $objAdvancedSearchCriterionGroupClass->is_multicriteria = true;
                $objAdvancedSearchCriterionGroupClass->display_type = 4;
            }
        }
        if (!empty($objSearch->step_search) && $objAdvancedSearchCriterionGroupClass->criterion_group_type == 'category' && !empty($objAdvancedSearchCriterionGroupClass->id_criterion_group_linked) && !Validate::isLoadedObject($objAdvancedSearchCriterionGroupClass)) {
            $objAdvancedSearchCriterionGroupClass->only_children = 1;
        }
        if ($objAdvancedSearchCriterionGroupClass->save()) {
            self::$originalSortWay = Tools::strtoupper($objAdvancedSearchCriterionGroupClass->sort_way);
            if ($update && !$id_product) {
                if ($criterions_group_type == 'attribute') {
                    self::removeOldCriterions($objSearch->id, $objAdvancedSearchCriterionGroupClass->id, $criterions_group_type, $criterions_group);
                } elseif ($criterions_group_type == 'category' || $criterions_group_type == 'supplier' || $criterions_group_type == 'manufacturer') {
                    self::removeOldCriterions($objSearch->id, $objAdvancedSearchCriterionGroupClass->id, $criterions_group_type);
                }
            }
            $allowOriginalPosition = false;
            if ($criterions_group_type != 'price') {
                if ($criterions_group_type == 'attribute') {
                    $criterions = self::getAttributes($criterions_group['id_attribute_group'], false, $id_criterion_linked, $id_product);
                    $allowOriginalPosition = true;
                } elseif ($criterions_group_type == 'feature') {
                    $criterions = self::getFeatureValuesFromValue($criterions_group['id_feature'], $criterion_value, $id_criterion_linked);
                } elseif ($criterions_group_type == 'manufacturer') {
                    $criterions = self::getManufacturers(false, $id_criterion_linked);
                } elseif ($criterions_group_type == 'supplier') {
                    $criterions = self::getSuppliers(false, $id_criterion_linked);
                } elseif ($criterions_group_type == 'category' || $criterions_group_type == 'subcategory') {
                    $criterions = self::getCategoriesP($objSearch->id, $id_criterion_linked, $id_criterion_group_linked);
                    $allowOriginalPosition = true;
                } elseif ($criterions_group_type == 'on_sale' || $criterions_group_type == 'stock' ||
                    $criterions_group_type == 'available_for_order' || $criterions_group_type == 'online_only' || $criterions_group_type == 'pack' || $criterions_group_type == 'prices_drop') {
                    $criterions = self::getBooleanTrueCriteria();
                } elseif ($criterions_group_type == 'new_products') {
                    $criterions = self::getBooleanCriteria();
                } elseif ($criterions_group_type == 'weight' || $criterions_group_type == 'width' ||
                        $criterions_group_type == 'height' || $criterions_group_type == 'depth') {
                    $criterions = self::getProductsFieldValues($criterions_group_type, $criterion_value);
                } elseif ($criterions_group_type == 'condition') {
                    $criterions = self::getConditionCriteria($criterion_value);
                }
                $sql_insert_multiple = array();
                $sql_insert_multiple_header = 'INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_cache_product_criterion_'.(int)$objSearch->id.'` (`id_cache_product`, `id_criterion`) VALUES ';
                $position = -1;
                $current_criterion_keys = array_keys($criterions);
                if ($id_product) {
                    $idCacheProduct = self::getIdCacheProductFromIdProduct($objSearch->id, $id_product);
                }
                $conf = AdvancedSearchCoreClass::getModuleConfigurationStatic();
                while (sizeof($criterions)) {
                    $position++;
                    $current_criterion = array_shift($current_criterion_keys);
                    $row = $criterions[$current_criterion];
                    unset($criterions[$current_criterion]);
                    if ($criterions_group_type == 'attribute') {
                        $current_id_criterion_linked = $row['id_attribute'];
                    } elseif ($criterions_group_type == 'feature') {
                        $current_id_criterion_linked = $row['id_feature_value_list'];
                    } elseif ($criterions_group_type == 'manufacturer') {
                        $current_id_criterion_linked = $row['id_manufacturer'];
                    } elseif ($criterions_group_type == 'supplier') {
                        $current_id_criterion_linked = $row['id_supplier'];
                    } elseif ($criterions_group_type == 'category' || $criterions_group_type == 'subcategory') {
                        $current_id_criterion_linked = $row['id_category'];
                    } elseif ($criterions_group_type == 'on_sale' || $criterions_group_type == 'stock' || $criterions_group_type == 'available_for_order' || $criterions_group_type == 'online_only' || $criterions_group_type == 'pack' || $criterions_group_type == 'new_products' || $criterions_group_type == 'prices_drop') {
                        $current_id_criterion_linked = 0;
                        $criterion_value = $row['name'][(int)$context->language->id];
                    } elseif (in_array($criterions_group_type, array('width', 'weight', 'depth', 'height'))) {
                        $current_id_criterion_linked = 0;
                        $criterion_value = $row['name'][(int)$context->language->id];
                    } elseif ($criterions_group_type == 'condition') {
                        $current_id_criterion_linked = 0;
                        $criterion_value = $row['name'][(int)$context->language->id];
                    }
                    if ($criterions_group_type == 'feature') {
                        $criterion_value = $row['name'][$defaultIdLang];
                    }
                    $id_criterion = false;
                    if ($update) {
                        if ($id_criterion_linked) {
                            $id_criterion = AdvancedSearchCriterionClass::getIdCriterionByTypeAndIdLinked($objSearch->id, $objAdvancedSearchCriterionGroupClass->id, $id_criterion_linked);
                        }
                        if (!$id_criterion && $criterion_value) {
                            $id_criterion = AdvancedSearchCriterionClass::getIdCriterionByTypeAndValue($objSearch->id, $objAdvancedSearchCriterionGroupClass->id, (int)$context->language->id, $criterion_value);
                        }
                        if (!$id_criterion && $current_id_criterion_linked) {
                            if (is_array($current_id_criterion_linked)) {
                                foreach ($current_id_criterion_linked as $current_id_criterion_linked_value) {
                                    $id_criterion = AdvancedSearchCriterionClass::getIdCriterionByTypeAndIdLinked($objSearch->id, $objAdvancedSearchCriterionGroupClass->id, $current_id_criterion_linked_value);
                                    if ($id_criterion) {
                                        $current_id_criterion_linked = $current_id_criterion_linked_value;
                                        break;
                                    }
                                }
                            } else {
                                $id_criterion = AdvancedSearchCriterionClass::getIdCriterionByTypeAndIdLinked($objSearch->id, $objAdvancedSearchCriterionGroupClass->id, $current_id_criterion_linked);
                            }
                        }
                    }
                    $objAdvancedSearchCriterionClassOld = false;
                    $objAdvancedSearchCriterionClass = new AdvancedSearchCriterionClass($id_criterion, $objSearch->id);
                    if ($id_criterion) {
                        $objAdvancedSearchCriterionClassOld = clone($objAdvancedSearchCriterionClass);
                    }
                    if ($objAdvancedSearchCriterionGroupClass->visible && !empty($conf['autoSyncActiveStatus']) && isset($row['active'])) {
                        $objAdvancedSearchCriterionClass->visible = (int)$row['active'];
                    }
                    $objAdvancedSearchCriterionClass->value = $row['name'];
                    foreach (array_keys($objAdvancedSearchCriterionClass->value) as $idLang) {
                        if (!in_array($idLang, $allLangIds)) {
                            unset($objAdvancedSearchCriterionClass->value[$idLang]);
                        }
                    }
                    foreach ($objAdvancedSearchCriterionClass->value as $idLang => $criterionTmpValue) {
                        $criterionTmpValue = str_replace(',', '.', $criterionTmpValue);
                        $objAdvancedSearchCriterionClass->decimal_value[$idLang] = sprintf('%1$.6f', (float)$criterionTmpValue);
                    }
                    if ($criterions_group_type == 'attribute' && $row['color']) {
                        $objAdvancedSearchCriterionClass->color = $row['color'];
                    }
                    $objAdvancedSearchCriterionClass->id_criterion_group = $objAdvancedSearchCriterionGroupClass->id;
                    $objAdvancedSearchCriterionClass->id_criterion_linked = $current_id_criterion_linked;
                    if ($criterions_group_type == 'category' || $criterions_group_type == 'subcategory') {
                        $objAdvancedSearchCriterionClass->level_depth = $row['level_depth'];
                        $objAdvancedSearchCriterionClass->id_parent = $row['id_parent'];
                    }
                    if (!$update) {
                        $objAdvancedSearchCriterionClass->position = $position;
                    }
                    if ($allowOriginalPosition && isset($row['position']) && $objAdvancedSearchCriterionGroupClass->sort_by == 'o_position') {
                        $objAdvancedSearchCriterionClass->position = (int)$row['position'];
                    }
                    if (!$id_product) {
                        if ($criterions_group_type == 'attribute') {
                            $productsIdCache = self::getProductsIdFromAttribute($objSearch->id, $row['id_attribute']);
                        } elseif ($criterions_group_type == 'feature') {
                            $productsIdCache = self::getProductsIdFromFeatureValue($objSearch->id, $criterions_group['id_feature'], $row['id_feature_value_list']);
                        } elseif ($criterions_group_type == 'manufacturer') {
                            $productsIdCache = self::getProductsIdFromManufacturer($objSearch->id, $row['id_manufacturer']);
                        } elseif ($criterions_group_type == 'supplier') {
                            $productsIdCache = self::getProductsIdFromSupplier($objSearch->id, $row['id_supplier']);
                        } elseif ($criterions_group_type == 'category' || $criterions_group_type == 'subcategory') {
                            $productsIdCache = self::getProductsIdFromCategory($objSearch->id, $row['id_category'], $objSearch->recursing_indexing);
                        } elseif ($criterions_group_type == 'on_sale' || $criterions_group_type == 'stock' || $criterions_group_type == 'available_for_order' || $criterions_group_type == 'online_only' || $criterions_group_type == 'condition' || $criterions_group_type == 'pack' || $criterions_group_type == 'new_products' || $criterions_group_type == 'prices_drop') {
                            $productsIdCache = self::getProductsIdFromProductField($objSearch->id, $row['value'], $criterions_group_type);
                        } elseif ($criterions_group_type == 'weight' || $criterions_group_type == 'width' || $criterions_group_type == 'height' || $criterions_group_type == 'depth') {
                            $productsIdCache = self::getProductsIdFromProductField($objSearch->id, $row['name'][$defaultIdLang], $criterions_group_type);
                        }
                    } else {
                        $productsIdCache = array(array('id_cache_product'=> $idCacheProduct));
                    }
                    self::$indexationStats['total_criterions']++;
                    if ($objAdvancedSearchCriterionClassOld === false) {
                        $criterionHasNotChanged = false;
                        self::$indexationStats['new_criterions']++;
                    } else {
                        $criterionHasNotChanged = ($objAdvancedSearchCriterionClassOld->getHashIdentifier() == $objAdvancedSearchCriterionClass->getHashIdentifier());
                        if ($criterionHasNotChanged) {
                            self::$indexationStats['unchanged_criterions']++;
                        } else {
                            self::$indexationStats['updated_criterions']++;
                        }
                    }
                    if ($criterions_group_type == 'feature' && $objAdvancedSearchCriterionClassOld !== false && !$criterionHasNotChanged) {
                        if (is_array($objAdvancedSearchCriterionClassOld->id_criterion_linked) && sizeof($objAdvancedSearchCriterionClassOld->id_criterion_linked) > 1 && $objAdvancedSearchCriterionClassOld->value != $objAdvancedSearchCriterionClass->value) {
                            $forceCriterionToBeAdded = true;
                            foreach ($objAdvancedSearchCriterionClass->value as $idLangCriterionTmp => $criterionValueTmp) {
                                if (isset($objAdvancedSearchCriterionClassOld->value[$idLangCriterionTmp]) && $objAdvancedSearchCriterionClassOld->value[$idLangCriterionTmp] == $criterionValueTmp) {
                                    $forceCriterionToBeAdded = false;
                                    break;
                                }
                            }
                            if ($forceCriterionToBeAdded) {
                                $objAdvancedSearchCriterionClass->id = null;
                                self::$needFullReindex = true;
                            }
                        }
                    }
                    if ($criterionHasNotChanged || $objAdvancedSearchCriterionClass->save()) {
                        if (!$criterionHasNotChanged) {
                            self::deleteCacheCriterion($objSearch->id, $objAdvancedSearchCriterionClass->id, $idCacheProduct);
                        }
                        foreach ($productsIdCache as $row) {
                            $sql_insert_multiple[] = '('.(int)$row['id_cache_product'].', '.(int)$objAdvancedSearchCriterionClass->id.')';
                            self::sqlBulkInsert('pm_advancedsearch_cache_product_criterion_'.(int)$objSearch->id, $sql_insert_multiple_header, $sql_insert_multiple, 1000);
                        }
                    }
                    unset($productsIdCache, $objAdvancedSearchCriterionClass, $objAdvancedSearchCriterionClassOld);
                }
                unset($criterions);
                self::sqlBulkInsert('pm_advancedsearch_cache_product_criterion_'.(int)$objSearch->id, $sql_insert_multiple_header, $sql_insert_multiple, 1);
                if ($update && $criterions_group_type == 'feature') {
                    self::removeOldCriterions($objSearch->id, $objAdvancedSearchCriterionGroupClass->id, $criterions_group_type, $criterions_group);
                }
            } elseif ($criterions_group_type == 'price') {
                self::setProductsPrices($objSearch->id, $objAdvancedSearchCriterionGroupClass->id, $id_product);
                self::setProductsSpecificPrices($objSearch->id, $id_product);
            }
        }
        self::$processingIndexation = false;
        $objAdvancedSearchCriterionClass = new AdvancedSearchCriterionClass(false, $objSearch->id);
        $objAdvancedSearchCriterionClass->as4ForceClearCache(true);
        $objAdvancedSearchCriterionGroupClass->as4ForceClearCache();
        AdvancedSearchCriterionClass::forceUniqueUrlIdentifier($objSearch->id, $objAdvancedSearchCriterionGroupClass->id);
        AdvancedSearchCriterionGroupClass::forceUniqueUrlIdentifier($objSearch->id);
        return $objAdvancedSearchCriterionGroupClass->id;
    }
    private static $_sqlBulkInsertCount = array();
    public static function sqlBulkInsert($tableName, $sql_insert_multiple_header, &$sql_insert_multiple, $size)
    {
        if (!isset(self::$_sqlBulkInsertCount[$tableName])) {
            self::$_sqlBulkInsertCount[$tableName] = 1;
        } else {
            self::$_sqlBulkInsertCount[$tableName]++;
        }
        if (self::$_sqlBulkInsertCount[$tableName] >= $size && count($sql_insert_multiple)) {
            if (self::$_sqlBulkInsertCount[$tableName] > 2) {
                As4SearchEngineDb::execute('LOCK TABLES `' . _DB_PREFIX_ . $tableName . '` WRITE');
            }
            As4SearchEngineDb::execute($sql_insert_multiple_header.implode(',', $sql_insert_multiple));
            if (self::$_sqlBulkInsertCount[$tableName] > 2) {
                As4SearchEngineDb::execute('UNLOCK TABLES');
            }
            $sql_insert_multiple = array();
            unset(self::$_sqlBulkInsertCount[$tableName]);
        }
    }
    public static function optimizedSearchTables($idSearch, $optimizeAll = false)
    {
        if ($optimizeAll) {
            $tableToOptimize = array(
                'category',
                'feature_value',
                'feature_value_lang',
                'feature_product',
                'image',
                'image_lang',
                'manufacturer',
                'pm_advancedsearch_criterion_'.(int)$idSearch,
                'pm_advancedsearch_criterion_'.(int)$idSearch.'_lang',
                'pm_advancedsearch_criterion_'.(int)$idSearch.'_link',
                'pm_advancedsearch_criterion_'.(int)$idSearch.'_list',
                'pm_advancedsearch_cache_product_criterion_'.(int)$idSearch,
                'pm_advancedsearch_cache_product_'.(int)$idSearch,
                'pm_advancedsearch_product_price_'.(int)$idSearch,
                'product',
                'product_attribute',
                'product_attribute_combination',
                'supplier',
                'specific_price',
                'specific_price_priority',
                'tax',
                'tax_rule',
                'category_shop',
                'image_shop',
                'product_attribute_shop',
                'product_shop',
                'specific_price_rule',
                'stock_available',
            );
        } else {
            $tableToOptimize = array(
                'pm_advancedsearch_criterion_'.(int)$idSearch,
                'pm_advancedsearch_criterion_'.(int)$idSearch.'_lang',
                'pm_advancedsearch_criterion_'.(int)$idSearch.'_link',
                'pm_advancedsearch_criterion_'.(int)$idSearch.'_list',
                'pm_advancedsearch_cache_product_criterion_'.(int)$idSearch,
                'pm_advancedsearch_cache_product_'.(int)$idSearch,
                'pm_advancedsearch_product_price_'.(int)$idSearch,
            );
        }
        foreach ($tableToOptimize as $tableName) {
            As4SearchEngineDb::execute('ANALYZE TABLE `'._DB_PREFIX_.$tableName.'`');
        }
    }
    private static $_getIdCacheProductFromIdProductCache = array();
    public static function getIdCacheProductFromIdProduct($id_search, $id_product)
    {
        $cacheKey = (int)$id_search.'-'.(int)$id_product;
        if (isset(self::$_getIdCacheProductFromIdProductCache[$cacheKey])) {
            return self::$_getIdCacheProductFromIdProductCache[$cacheKey];
        }
        $row = As4SearchEngineDb::row('
        SELECT `id_cache_product`
        FROM `'._DB_PREFIX_.'pm_advancedsearch_cache_product_'.(int)$id_search.'`
        WHERE `id_product` = '.(int)$id_product);
        if (isset($row['id_cache_product'])) {
            self::$_getIdCacheProductFromIdProductCache[$cacheKey] = $row['id_cache_product'];
            return self::$_getIdCacheProductFromIdProductCache[$cacheKey];
        } else {
            return false;
        }
    }
    private static $_isColorAttributesGroupCache = array();
    public static function isColorAttributesGroup($id_attribute_group)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$_isColorAttributesGroupCache[$cacheKey])) {
            return self::$_isColorAttributesGroupCache[$cacheKey];
        }
        static $colorGroupList = null;
        if ($colorGroupList === null) {
            $result = As4SearchEngineDb::query('SELECT `id_attribute_group` FROM `'._DB_PREFIX_.'attribute_group` WHERE `is_color_group`=1');
            $colorGroupList = array();
            if (AdvancedSearchCoreClass::_isFilledArray($result)) {
                foreach ($result as $row) {
                    $colorGroupList[] = (int)$row['id_attribute_group'];
                }
            }
        }
        self::$_isColorAttributesGroupCache[$cacheKey] = in_array((int)$id_attribute_group, $colorGroupList);
        return self::$_isColorAttributesGroupCache[$cacheKey];
    }
    public static function getCategoriesCriteriaGroup($id_search)
    {
        $results = As4SearchEngineDb::query('
        SELECT *
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int)$id_search.'`
        WHERE `criterion_group_type` = "category"');
        return $results;
    }
    public static function getAvailableAttributesGroups($id_lang)
    {
        $ignoreGroup = '';
        $ap5ModuleInstance = Module::getInstanceByName('pm_advancedpack');
        if (Validate::isLoadedObject($ap5ModuleInstance) && AdvancedPack::getPackAttributeGroupId() !== false) {
            $ignoreGroup = 'WHERE ag.`id_attribute_group` != '.(int)AdvancedPack::getPackAttributeGroupId();
        }
        $result = As4SearchEngineDb::query('
        SELECT *
        FROM `'._DB_PREFIX_.'attribute_group` ag
        '.($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND `id_lang` = '.(int)($id_lang).')':'').'
         '. $ignoreGroup .'
        ORDER BY `name` ASC');
        if (!$id_lang && $result) {
            foreach ($result as $key => $row) {
                $result_lang = As4SearchEngineDb::query('
                    SELECT agl.*
                    FROM `'._DB_PREFIX_.'attribute_group_lang` agl
                    WHERE agl.`id_attribute_group` = '.(int)$row['id_attribute_group']);
                foreach ($result_lang as $row_lang) {
                    $result[$key]['name'][$row_lang['id_lang']] = $row_lang['name'];
                }
            }
        }
        return $result;
    }
    public static function getAvailableFeaturesGroups($id_lang)
    {
        $result = As4SearchEngineDb::query('
        SELECT *
        FROM `'._DB_PREFIX_.'feature` f
        ' . Shop::addSqlAssociation('feature', 'f') . '
        '.($id_lang ? 'JOIN `'._DB_PREFIX_.'feature_lang` fl ON (f.`id_feature` = fl.`id_feature` AND fl.`id_lang` = '.(int)($id_lang).')':'').'
        GROUP BY f.`id_feature`
        ORDER BY fl.`name` ASC');
        if (!$id_lang && $result) {
            foreach ($result as $key => $row) {
                $result_lang = As4SearchEngineDb::query('
                    SELECT fl.*
                    FROM `'._DB_PREFIX_.'feature_lang` fl
                    WHERE fl.`id_feature` = '.(int)$row['id_feature']);
                foreach ($result_lang as $row_lang) {
                    $result[$key]['name'][$row_lang['id_lang']] = $row_lang['name'];
                }
            }
        }
        return $result;
    }
    public static function getAvailableCategoriesLevelDepth()
    {
        return As4SearchEngineDb::query('
        SELECT c.`level_depth`
        FROM `'._DB_PREFIX_.'category` c
        WHERE c.`level_depth` > 0
        GROUP BY c.`level_depth`
        ORDER BY c.`level_depth`
        ');
    }
    public static function reindexAllSearchs($fromCron = false)
    {
        AdvancedSearchCoreClass::_changeTimeLimit(0);
        if ($fromCron) {
            $advanced_searchs_id = As4SearchEngine::getSearchsId(false, Context::getContext()->shop->id);
        } else {
            $advanced_searchs_id = As4SearchEngine::getSearchsId(false);
        }
        foreach ($advanced_searchs_id as $idSearch) {
            self::reindexSpecificSearch($idSearch);
        }
    }
    public static function reindexSpecificSearch($idSearch)
    {
        self::updateCacheProduct($idSearch);
        $criterions_groups_indexed = self::getCriterionsGroupsIndexed($idSearch, (int)Context::getContext()->language->id, false);
        if (AdvancedSearchCoreClass::_isFilledArray($criterions_groups_indexed)) {
            foreach ($criterions_groups_indexed as $row2) {
                self::indexCriterionsGroup($idSearch, $row2['criterion_group_type'], $row2['id_criterion_group_linked'], $row2['id_criterion_group'], $row2['visible'], false, true);
            }
        }
        self::optimizedSearchTables($idSearch, true);
        PM_AdvancedSearch4::clearSmartyCache((int)$idSearch);
    }
    public static function reindexSpecificCriterionGroup($idSearch, $idCriterionGroup)
    {
        AdvancedSearchCoreClass::_changeTimeLimit(0);
        $objCritGroup = new AdvancedSearchCriterionGroupClass($idCriterionGroup, $idSearch);
        self::indexCriterionsGroup($idSearch, $objCritGroup->criterion_group_type, $objCritGroup->id_criterion_group_linked, $objCritGroup->id, $objCritGroup->visible, false, true);
        self::optimizedSearchTables($idSearch);
        PM_AdvancedSearch4::clearSmartyCache((int)$idSearch);
    }
}
