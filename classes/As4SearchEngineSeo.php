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
abstract class As4SearchEngineSeo
{
    public static function getCriterionsGroupsIndexedForSEO($id_search, $id_lang = false)
    {
        $search = current(As4SearchEngine::getSearch($id_search, $id_lang, false));
        return As4SearchEngineDb::query('
        SELECT acg.* '.((int) $id_lang ? ', acgl.*':'').'
        FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'` acg
        '.($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'pm_advancedsearch_criterion_group_'.(int) $id_search.'_lang` acgl ON (acg.`id_criterion_group` = acgl.`id_criterion_group` AND acgl.`id_lang` = '.(int) $id_lang.')' : '').'
        WHERE acg.`id_search` = '.(int)($id_search).'
        AND `visible` = 1 '.(isset($search['filter_by_emplacement']) && $search['filter_by_emplacement'] == 1 ? ' OR (`visible`=0 AND `criterion_group_type` IN ("category", "supplier", "manufacturer")) ' : '').'
        GROUP BY acg.`id_criterion_group`
        ORDER BY acg.`position`');
    }
    private static function sortSeoCriterion($criterions)
    {
        if (is_array($criterions)) {
            asort($criterions);
            foreach (array_keys($criterions) as $k) {
                if (is_array($criterions[$k])) {
                    $criterions[$k] = self::sortSeoCriterion($criterions[$k]);
                }
            }
        }
        return $criterions;
    }
    public static function getSeoKeyFromCriteria($id_search, $criteria, $id_currency)
    {
        if (!$id_currency) {
            $id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        }
        if (is_array($criteria)) {
            $criteria = self::sortSeoCriterion($criteria);
        }
        $criteria = str_replace('biscriterion_', '', $criteria);
        return md5((int)$id_search . '-' . implode('-', $criteria) . '-' . (int)$id_currency);
    }
    public static function addSeoPageUrlToCriterions($idSearch, &$criterionsList, $selectedCriterionsForSeo)
    {
        if (AdvancedSearchCoreClass::_isFilledArray($criterionsList)) {
            $seoKeysList = array();
            foreach ($criterionsList as &$criterion) {
                $criterion['seo_key'] = self::getSeoKeyFromCriteria($idSearch, array_merge($selectedCriterionsForSeo, array((int)$criterion['id_criterion_group'].'_'.(int)$criterion['id_criterion'])), Context::getContext()->cookie->id_currency);
                $seoKeysList[] = $criterion['seo_key'];
            }
            if (sizeof($seoKeysList)) {
                $seoPageUrlByKeys = AdvancedSearchSeoClass::getSeoPageUrlByKeys($seoKeysList, (int)Context::getContext()->language->id);
                foreach ($criterionsList as &$criterion) {
                    if (isset($seoPageUrlByKeys[$criterion['seo_key']])) {
                        $criterion['id_seo'] = $seoPageUrlByKeys[$criterion['seo_key']]['id_seo'];
                        $criterion['seo_page_url'] = $seoPageUrlByKeys[$criterion['seo_key']]['seo_page_url'];
                    } else {
                        $criterion['id_seo'] = false;
                        $criterion['seo_page_url'] = false;
                    }
                }
            }
        }
    }
}
