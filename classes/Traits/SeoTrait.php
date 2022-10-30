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

namespace AdvancedSearch\Traits;
use Db;
use Tools;
use Currency;
use Validate;
use Configuration;
use AdvancedSearch\Core;
use AdvancedSearch\Models\Seo;
use AdvancedSearch\SearchEngineDb;
use AdvancedSearch\SearchEngineSeo;
use AdvancedSearch\Models\Criterion;
use AdvancedSearch\SearchEngineUtils;
use AdvancedSearch\Models\CriterionGroup;
trait SeoTrait
{
    protected function getSeoModuleRoutes()
    {
        return [
            'module-' . _PM_AS_MODULE_NAME_ . '-seo' => array(
                'controller' => 'seo',
                'rule' => 's/{id_seo}/{seo_url}',
                'keywords' => array(
                    'id_seo' => array('regexp' => '[0-9]+', 'param' => 'id_seo'),
                    'seo_url' => array('regexp' => '.+', 'param' => 'seo_url'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => _PM_AS_MODULE_NAME_,
                )
            ),
            'module-' . _PM_AS_MODULE_NAME_ . '-seositemap' => array(
                'controller' => 'seositemap',
                'rule' => 'as4_seositemap-{id_search}.xml',
                'keywords' => array(
                    'id_search' => array('regexp' => '[0-9]+', 'param' => 'id_search')
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => _PM_AS_MODULE_NAME_,
                )
            ),
        ];
    }
    protected function postProcessSeoSearch()
    {
        $id_search = Tools::getValue('id_search', false);
        $id_seo = Tools::getValue('id_seo', false);
        $id_currency = Tools::getValue('id_currency', false);
        if (!$id_currency) {
            $id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        }
        $seoCriterions = explode(',', str_replace('biscriterion_', '', Tools::getValue('criteria')));
        $this->cleanBuffer();
        $this->html = '';
        if (!$id_search) {
            $this->errors[] = $this->l('An error occured', 'SeoTrait');
        } else {
            $seo_key = SearchEngineSeo::getSeoKeyFromCriteria($id_search, $seoCriterions, $id_currency);
            if (!$id_seo) {
                $id_seo = Seo::seoDeletedExists($seo_key);
            }
            if (!$id_seo && Seo::seoExists($seo_key)) {
                $this->errors[] = $this->l('This SEO result page already exists', 'SeoTrait');
            } else {
                $objAdvancedSearchSeoClass = new Seo($id_seo);
                $this->copyFromPost($objAdvancedSearchSeoClass);
                $objAdvancedSearchSeoClass->criteria = Core::encodeCriteria($seoCriterions);
                $objAdvancedSearchSeoClass->seo_key = $seo_key;
                $objAdvancedSearchSeoClass->deleted = 0;
                $error = $objAdvancedSearchSeoClass->validateFields(false, true);
                $errorLang = $objAdvancedSearchSeoClass->validateFieldsLang(false, true);
                if ($error !== true) {
                    $this->errors[] = $error;
                }
                if ($errorLang !== true) {
                    $this->errors[] = $errorLang;
                } elseif ($objAdvancedSearchSeoClass->save()) {
                    $this->success[] = $this->l('Saved', 'SeoTrait');
                    $this->displayJsTags('open');
                    $this->reloadPanels('seo_search_panel_' . (int)$id_search);
                    $this->displaySuccessJs(false);
                    $this->displayCloseDialogIframeJs(false);
                    $this->displayJsTags('close');
                } else {
                    $this->errors[] = $this->l('Error while updating seo search', 'SeoTrait');
                }
            }
        }
        $this->displayErrorsJs(true);
        echo $this->html;
        die();
    }
    protected function postProcessSeoRegenerate()
    {
        $id_search = Tools::getValue('id_search', false);
        $fields_to_regenerate = Tools::getValue('fields_to_regenerate', false);
        $this->cleanBuffer();
        if (!$id_search) {
            $this->errors[] = $this->l('An error occured', 'SeoTrait');
        }
        if (!$fields_to_regenerate || ! sizeof($fields_to_regenerate)) {
            $this->errors[] = $this->l('You must select at least one field to regenerate', 'SeoTrait');
        } else {
            $seoSearchs = Seo::getSeoSearchs((int)$this->context->language->id, false, $id_search);
            foreach ($seoSearchs as $row) {
                $defaultReturnSeoStr = $this->getSeoStrings(Core::decodeCriteria($row['criteria']), $id_search, $row['id_currency'], $fields_to_regenerate);
                if ($defaultReturnSeoStr && is_array($defaultReturnSeoStr) && sizeof($defaultReturnSeoStr)) {
                    $objAdvancedSearchSeoClass = new Seo($row['id_seo']);
                    $objAdvancedSearchSeoClass->id_search = $id_search;
                    foreach ($defaultReturnSeoStr as $id_lang => $fields) {
                        foreach ($fields as $field => $fieldValue) {
                            $objAdvancedSearchSeoClass->{$field}[$id_lang] = $fieldValue;
                        }
                    }
                    $error = $objAdvancedSearchSeoClass->validateFields(false, true);
                    $errorLang = $objAdvancedSearchSeoClass->validateFieldsLang(false, true);
                    if ($error !== true) {
                        $this->errors[] = $error;
                    } elseif ($errorLang !== true) {
                        $this->errors[] = $errorLang;
                    } elseif (!$objAdvancedSearchSeoClass->save()) {
                        $this->errors[] = $this->l('Error while updating seo search', 'SeoTrait');
                    }
                }
            }
            $this->displayJsTags('open');
            $this->reloadPanels('seo_search_panel_' . (int)$id_search);
            $this->displayCloseDialogIframeJs(false);
            $this->displayJsTags('close');
        }
        if (empty($this->errors)) {
            $this->success[] = $this->l('Seo data regenerated successfully', 'SeoTrait');
            $this->displaySuccessJs(true);
        } else {
            $this->displayErrorsJs(true);
        }
        echo $this->html;
        die();
    }
    protected function postProcessMassSeoSearch()
    {
        $id_search = Tools::getValue('id_search', false);
        $id_currency = Tools::getValue('id_currency', false);
        if (!$id_currency) {
            $id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        }
        $this->cleanBuffer();
        $this->html = '';
        if (!$id_search) {
            $this->errors[] = $this->l('An error occured', 'SeoTrait');
        } else {
            $criteria_groups = explode(',', Tools::getValue('criteria_groups', ''));
            $criteria = Tools::getValue('criteria', false);
            $seoIds = array();
            if (!sizeof($criteria_groups) || ! sizeof($criteria)) {
                $this->errors[] = $this->l('Please select at least one criteria', 'SeoTrait');
            } else {
                $criteria_reorder = array();
                foreach ($criteria_groups as $key_criterion_group) {
                    $id_criterion_group = self::parseInt($key_criterion_group);
                    if (isset($criteria[$id_criterion_group]) && sizeof($criteria[$id_criterion_group])) {
                        $criteria_reorder[] = $criteria[$id_criterion_group];
                    }
                }
                $criteria_cartesian = $this->cartesianReOrder($criteria_reorder);
                foreach ($criteria_cartesian as $criteria_final_str) {
                    $criteria_final = explode(',', $criteria_final_str);
                    $resultTotalProducts = $this->countProductFromSeoCriteria($id_search, $criteria_final, $id_currency);
                    if (!$resultTotalProducts) {
                        continue;
                    }
                    $seo_key = SearchEngineSeo::getSeoKeyFromCriteria($id_search, $criteria_final, $id_currency);
                    $cur_id_seo = Seo::seoDeletedExists($seo_key);
                    if ($cur_id_seo) {
                        Seo::undeleteSeoBySeoKey($seo_key);
                    }
                    if (!$cur_id_seo && Seo::seoExists($seo_key)) {
                        continue;
                    }
                    $defaultReturnSeoStr = $this->getSeoStrings($criteria_final, $id_search, $id_currency);
                    $objAdvancedSearchSeoClass = new Seo($cur_id_seo);
                    $objAdvancedSearchSeoClass->id_search = $id_search;
                    $objAdvancedSearchSeoClass->criteria = Core::encodeCriteria($criteria_final);
                    $objAdvancedSearchSeoClass->seo_key = $seo_key;
                    foreach ($defaultReturnSeoStr as $id_lang => $fields) {
                        foreach ($fields as $field => $fieldValue) {
                            $objAdvancedSearchSeoClass->{$field}[$id_lang] = $fieldValue;
                        }
                    }
                    $error = $objAdvancedSearchSeoClass->validateFields(false, true);
                    $errorLang = $objAdvancedSearchSeoClass->validateFieldsLang(false, true);
                    if ($error !== true) {
                        $this->errors[] = $error;
                    } elseif ($errorLang !== true) {
                        $this->errors[] = $errorLang;
                    } else {
                        $objAdvancedSearchSeoClass->save();
                        $seoIds[] = $objAdvancedSearchSeoClass->id;
                    }
                }
                if (Tools::getValue('massSeoSearchCrossLinks', true) && sizeof($seoIds)) {
                    foreach ($seoIds as $id_seo) {
                        foreach ($seoIds as $id_seo2) {
                            if ($id_seo == $id_seo2) {
                                continue;
                            }
                            $row = array('id_seo' => (int)$id_seo, 'id_seo_linked' => (int)$id_seo2);
                            Db::getInstance()->insert('pm_advancedsearch_seo_crosslinks', $row);
                        }
                    }
                }
                $this->success[] = $this->l('Saved', 'SeoTrait');
                $this->displayJsTags('open');
                $this->reloadPanels('seo_search_panel_' . (int)$id_search);
                $this->displayCloseDialogIframeJs(false);
                $this->displayJsTags('close');
            }
        }
        $this->displaySuccessJs(true);
        $this->displayErrorsJs(true);
        echo $this->html;
        die();
    }
    protected function updateSeoPagesCriteria($oldModuleVersion)
    {
        $res = true;
        if (!version_compare($oldModuleVersion, '5.0.0', '<')) {
            return $res;
        }
        $seoPages = Seo::getSeoSearchs(false, 1);
        foreach ($seoPages as $seoPage) {
            if (!preg_match('/^a:\d+/', $seoPage['criteria'])) {
                continue;
            }
            $seoPageObject = new Seo($seoPage['id_seo']);
            $unserialized = Core::decodeCriteria($seoPage['criteria']);
            $seoPageObject->criteria = $unserialized;
            $res &= $seoPageObject->save();
        }
        return $res;
    }
    public function getSeoStrings($criteria, $id_search, $id_currency, $fields_to_get = false)
    {
        $fieldToFill = array('meta_title', 'meta_description', 'meta_keywords', 'title', 'seo_url' );
        $fieldWithGroupName = array('meta_title', 'meta_description' );
        $groupTypeWithoutGroupName = array('category', 'supplier', 'manufacturer' );
        $offersSelectionStr = $this->translateMultiple('offers_selection');
        $betweenLangStr = $this->translateMultiple('from');
        $andLangStr = $this->translateMultiple('to');
        $moreThanLangStr = $this->translateMultiple('more_than');
        $defaultReturnSeoStr = array();
        $newCriteria = array();
        foreach ($criteria as $value) {
            $info_criterion = explode('_', $value);
            if (isset($info_criterion[2])) {
                $id_criterion_group = $info_criterion[1];
                $id_criterion = $info_criterion[2];
            } else {
                $id_criterion_group = $info_criterion[0];
                $id_criterion = $info_criterion[1];
            }
            if (!isset($newCriteria[$id_criterion_group])) {
                $newCriteria[$id_criterion_group] = array();
            }
            $newCriteria[$id_criterion_group][] = $id_criterion;
        }
        foreach ($this->languages as $language) {
            foreach ($criteria as $k => $criterion) {
                $endLoop = (($k + 1) == sizeof($criteria));
                $info_criterion = explode('_', $criterion);
                if (isset($info_criterion[2])) {
                    $id_criterion_group = $info_criterion[1];
                    $id_criterion = $info_criterion[2];
                } else {
                    $id_criterion_group = $info_criterion[0];
                    $id_criterion = $info_criterion[1];
                }
                $objAdvancedSearchCriterionGroupClass = new CriterionGroup($id_criterion_group, $id_search);
                if (preg_match('#~#', $id_criterion)) {
                    $range = explode('~', $id_criterion);
                    $min = $range[0];
                    if ($objAdvancedSearchCriterionGroupClass->criterion_group_type == 'price') {
                        $currency = new Currency($id_currency);
                        if (!empty($range[1])) {
                            $max = $this->formatPrice($range[1], (Validate::isLoadedObject($currency) ? $currency : null));
                            $criterion_value = $betweenLangStr[$language['id_lang']] . ' ' . $min . ' ' . $andLangStr[$language['id_lang']] . ' ' . $max;
                        } else {
                            $min = $this->formatPrice($min, (Validate::isLoadedObject($currency) ? $currency : null));
                            $criterion_value = $moreThanLangStr[$language['id_lang']] . ' ' . $min;
                        }
                    } else {
                        if (!empty($range[1])) {
                            $max = (float)$range[1] . (!empty($objAdvancedSearchCriterionGroupClass->range_sign[$language['id_lang']]) ? ' ' . $objAdvancedSearchCriterionGroupClass->range_sign[$language['id_lang']] : '');
                            $criterion_value = $betweenLangStr[$language['id_lang']] . ' ' . $min . ' ' . $andLangStr[$language['id_lang']] . ' ' . $max;
                        } else {
                            $min = (float)$min . (!empty($objAdvancedSearchCriterionGroupClass->range_sign[$language['id_lang']]) ? ' ' . $objAdvancedSearchCriterionGroupClass->range_sign[$language['id_lang']] : '');
                            $criterion_value = $moreThanLangStr[$language['id_lang']] . ' ' . $min;
                        }
                    }
                } else {
                    $objAdvancedSearchCriterionClass = new Criterion($id_criterion, $id_search, $language['id_lang']);
                    $criterion_value = trim($objAdvancedSearchCriterionClass->value);
                }
                foreach ($fieldToFill as $field) {
                    if ($fields_to_get && ! in_array($field, $fields_to_get)) {
                        continue;
                    }
                    if (!isset($defaultReturnSeoStr[$language['id_lang']])) {
                        $defaultReturnSeoStr[$language['id_lang']] = array();
                    }
                    if (!isset($defaultReturnSeoStr[$language['id_lang']][$field])) {
                        $defaultReturnSeoStr[$language['id_lang']][$field] = '';
                    }
                    if (!$k && $field == 'meta_description') {
                        $defaultReturnSeoStr[$language['id_lang']][$field] .= Configuration::get('PS_SHOP_NAME') . ' ' . $offersSelectionStr[$language['id_lang']] . ' ';
                    }
                    if ($objAdvancedSearchCriterionGroupClass->criterion_group_type != 'price' && in_array($field, $fieldWithGroupName) and ! in_array($objAdvancedSearchCriterionGroupClass->criterion_group_type, $groupTypeWithoutGroupName)) {
                        $defaultReturnSeoStr[$language['id_lang']][$field] .= $objAdvancedSearchCriterionGroupClass->name[$language['id_lang']] . ' ';
                    }
                    if ($field == 'seo_url') {
                        $defaultReturnSeoStr[$language['id_lang']][$field] .= Tools::str2url($criterion_value) . (!$endLoop ? '-' : '');
                    } else {
                        $defaultReturnSeoStr[$language['id_lang']][$field] .= $criterion_value . (!$endLoop && ($field == 'meta_title' || $field == 'meta_description' || $field == 'meta_keywords') ? ', ' : ($endLoop ? '' : ' '));
                    }
                }
            }
        }
        return $defaultReturnSeoStr;
    }
    protected function processFillSeoFields()
    {
        $criteria = Tools::getValue('criteria', false);
        $id_search = Tools::getValue('id_search', false);
        $id_currency = Tools::getValue('id_currency', false);
        if (!$criteria || !$id_search) {
            die;
        }
        $criteria = explode(',', $criteria);
        $defaultReturnSeoStr = $this->getSeoStrings($criteria, $id_search, $id_currency);
        foreach ($defaultReturnSeoStr as $id_lang => $fields) {
            foreach ($fields as $field => $fieldValue) {
                $this->html .= '$("input[name='.$field.'_'.$id_lang.']").val("'.addcslashes($fieldValue, '"').'");';
            }
        }
    }
    protected function checkSeoCriteriaCombination()
    {
        $criteria = Tools::getValue('criteria', false);
        $id_search = Tools::getValue('id_search', false);
        $id_currency = Tools::getValue('id_currency', false);
        $resultTotalProducts = false;
        if ($criteria && $id_search) {
            $criteria = explode(',', $criteria);
            $resultTotalProducts = $this->countProductFromSeoCriteria($id_search, $criteria, $id_currency);
        }
        if (!$resultTotalProducts) {
            $this->html .= '$("input[name=submitSeoSearchForm]").hide();$("#errorCombinationSeoSearchForm").show();';
            $this->html .= '$("#nbProductsCombinationSeoSearchForm").html(\'<p class="alert alert-danger"><b>0 ' . $this->l('result found', 'SeoTrait') . '</b></p>\');';
        } else {
            $this->html .= '$("input[name=submitSeoSearchForm]").show();$("#errorCombinationSeoSearchForm").hide();';
            $this->html .= '$("#nbProductsCombinationSeoSearchForm").html(\'<p class="alert alert-info"><b>' . $resultTotalProducts . ' ' . $this->l('result(s) found(s)', 'SeoTrait') . '</b></p>\');';
        }
    }
    protected function countProductFromSeoCriteria($id_search, $criteria, $id_currency)
    {
        if (self::isFilledArray($criteria) && $criteria[0]) {
            $selected_criteria_groups_type = array();
            $newCriteria = self::getArrayCriteriaFromSeoArrayCriteria($criteria);
            if (sizeof($newCriteria)) {
                $selected_criteria_groups_type = SearchEngineUtils::getCriterionGroupsTypeAndDisplay($id_search, array_keys($newCriteria));
            }
            $search = SearchEngineUtils::getSearch($id_search, (int)$this->context->language->id, false);
            $search = $search[0];
            $resultTotalProducts = SearchEngineDb::row(SearchEngineUtils::getQueryCountResults($search, (int)$this->context->language->id, $newCriteria, $selected_criteria_groups_type, $id_currency));
        }
        $total_product = isset($resultTotalProducts) ? $resultTotalProducts['total'] : 0;
        return $total_product;
    }
    public static function getArrayCriteriaFromSeoArrayCriteria($criteria)
    {
        $newCriteria = array();
        foreach ($criteria as $value) {
            $info_criterion = explode('_', $value);
            if (isset($info_criterion[2])) {
                $id_criterion_group = (int)$info_criterion[1];
                $id_criterion = $info_criterion[2];
            } else {
                $id_criterion_group = (int)$info_criterion[0];
                $id_criterion = $info_criterion[1];
            }
            $id_criterion = preg_replace('/[^0-9.,-~]/', '', $id_criterion);
            if (!isset($newCriteria[$id_criterion_group])) {
                $newCriteria[$id_criterion_group] = array();
            }
            $newCriteria[$id_criterion_group][] = $id_criterion;
        }
        return $newCriteria;
    }
    protected function processRemoveEmptySeo()
    {
        $id_search = Tools::getValue('id_search', false);
        if (!$id_search) {
            die();
        }
        $seoSearchs = Seo::getSeoSearchs((int)$this->context->language->id, false, $id_search);
        foreach ($seoSearchs as $row) {
            $resultTotalProducts = $this->countProductFromSeoCriteria($id_search, Core::decodeCriteria($row['criteria']), $row['id_currency']);
            if (!$resultTotalProducts) {
                $objAdvancedSearchSeoClass = new Seo($row['id_seo']);
                if (!$objAdvancedSearchSeoClass->delete()) {
                    $this->html .= 'show_error("' . $this->l('Error while deleting results page', 'SeoTrait') . ' ' . $row['id_seo'] . '");';
                }
            }
        }
        $this->html .= 'show_success("' . $this->l('Empty results pages have been deleted', 'SeoTrait') . '");reloadPanel("seo_search_panel_' . (int)$id_search . '");';
    }
    protected function displaySeoSearchPanelList()
    {
        $id_search = (int)Tools::getValue('id_search');
        $seoSearchs = Seo::getSeoSearchs((int)$this->context->language->id, false, $id_search);
        foreach ($seoSearchs as &$row) {
            $row['total_products'] = $this->countProductFromSeoCriteria($id_search, Core::decodeCriteria($row['criteria']), $row['id_currency']);
        }
        $vars = array(
            'id_search' => $id_search,
            'rewrite_settings' => Configuration::get('PS_REWRITING_SETTINGS'),
            'seo_searchs' => $seoSearchs,
            'sitemap_url' => $this->context->link->getModuleLink(_PM_AS_MODULE_NAME_, 'seositemap', array('id_search' => (int)$id_search)),
        );
        $this->html .= $this->fetchTemplate('module/seo/search_panel.tpl', $vars);
    }
    protected function displaySeoUrl($id_search)
    {
        $id_search = Tools::getValue('id_search', false);
        $id_seo = Tools::getValue('id_seo', false);
        if (!$id_seo || !$id_search) {
            die;
        }
        $ObjAdvancedSearchSeoClass = new Seo($id_seo, null);
        $seo_url_by_lang = array();
        foreach ($this->languages as $language) {
            $seo_url_by_lang[(int)$language['id_lang']] = $this->context->link->getModuleLink(_PM_AS_MODULE_NAME_, 'seo', array('id_seo' => (int)$ObjAdvancedSearchSeoClass->id, 'seo_url' => $ObjAdvancedSearchSeoClass->seo_url[$language['id_lang']]), null, (int)$language['id_lang']);
        }
        $vars = array(
            'seo_url_by_lang' => $seo_url_by_lang,
            'pm_flags' => $this->displayPMFlags(),
        );
        $this->html .= $this->fetchTemplate('module/seo/url.tpl', $vars);
    }
    protected function displaySeoUrlList()
    {
        $id_search = Tools::getValue('id_search');
        $seoSearchs = Seo::getSeoSearchs((int)$this->context->language->id, false, $id_search);
        if ($seoSearchs && self::isFilledArray($seoSearchs)) {
            $new_SeoSearch = array();
            foreach ($seoSearchs as $row) {
                $ObjAdvancedSearchSeoClass = new Seo($row['id_seo'], null);
                foreach ($this->languages as $language) {
                    $url = $this->context->link->getModuleLink(_PM_AS_MODULE_NAME_, 'seo', array('id_seo' => (int)$ObjAdvancedSearchSeoClass->id, 'seo_url' => $ObjAdvancedSearchSeoClass->seo_url[$language['id_lang']]), null, (int)$language['id_lang']);
                    $title = htmlentities($ObjAdvancedSearchSeoClass->title[$language['id_lang']], ENT_COMPAT, 'UTF-8');
                    $new_SeoSearch[$language['id_lang']][$ObjAdvancedSearchSeoClass->id]['url'] = $url;
                    $new_SeoSearch[$language['id_lang']][$ObjAdvancedSearchSeoClass->id]['title'] = $title;
                }
            }
            $seoSearchs = $new_SeoSearch;
            unset($new_SeoSearch);
        }
        $vars = array(
            'seo_url_list' => $seoSearchs,
            'pm_flags' => $this->displayPMFlags(),
        );
        $this->html .= $this->fetchTemplate('module/seo/url_list.tpl', $vars);
    }
    protected function displaySeoRegenerateForm()
    {
        $vars = array(
            'id_search' => (int)Tools::getValue('id_search'),
        );
        $this->html .= $this->fetchTemplate('module/seo/regenerate_form.tpl', $vars);
    }
    protected function displaySeoSearchForm($params)
    {
        $id_search = (int)Tools::getValue('id_search');
        $criterions_groups_indexed = SearchEngineSeo::getCriterionsGroupsIndexedForSEO($id_search, (int)$this->context->language->id);
        $search = SearchEngineUtils::getSearch($id_search, false, false);
        $criteria = false;
        $default_currency = null;
        if ($params['obj'] && $params['obj']->criteria) {
            $criteria = Core::decodeCriteria($params['obj']->criteria);
        }
        if (Validate::isLoadedObject($params['obj']) && !empty($params['obj']->id_currency)) {
            $default_currency = new Currency($params['obj']->id_currency);
        }
        if (!Validate::isLoadedObject($default_currency)) {
            $default_currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
        foreach ($criterions_groups_indexed as &$criterions_group_indexed) {
            if ($criterions_group_indexed['range'] == 1) {
                if ($criterions_group_indexed['criterion_group_type'] == 'price') {
                    $criterions_group_indexed['range_sign'] = $default_currency->sign;
                }
                $ranges = explode(',', $criterions_group_indexed['range_interval']);
                $criterions = array();
                foreach ($ranges as $krange => $range) {
                    $rangeUp = (isset($ranges[$krange+1]) ? $ranges[$krange+1]:'');
                    $range1 = $range.'~'.$rangeUp;
                    $range2 = $this->getTextualRangeValue($range, $rangeUp, $criterions_group_indexed, $default_currency);
                    $criterions[] = array('id_criterion' => $range1, 'value' => $range2);
                }
                $criterions_group_indexed['criterions'] = $criterions;
            } elseif ($criterions_group_indexed['criterion_group_type'] == 'price') {
                $price_range = SearchEngineUtils::getPriceRangeForSearchBloc($search[0], (int)$criterions_group_indexed['id_criterion_group_linked'], (int)$default_currency->id, (int)$this->getCurrentCustomerGroupId(), (int)$this->context->country->id, array(), array());
                $criterions_group_indexed['price_range'] = $price_range[0];
            } elseif ($criterions_group_indexed['display_type'] == 5 || $criterions_group_indexed['display_type'] == 8) {
                $range = SearchEngineUtils::getCriterionsRange($search[0], (int)$criterions_group_indexed['id_criterion_group'], (int)$this->context->language->id, array(), array(), false, false, $criterions_group_indexed);
                $criterions_group_indexed['range'] = $range[0];
            } else {
                $criterions = SearchEngineUtils::getCriterionsFromCriterionGroup((int)$criterions_group_indexed['id_criterion_group'], $criterions_group_indexed['id_search'], $criterions_group_indexed['sort_by'], $criterions_group_indexed['sort_way'], (int)$this->context->language->id);
                $criterions_group_indexed['criterions'] = $criterions;
            }
        }
        $criteria_values = array();
        if (self::isFilledArray($criteria)) {
            foreach ($criteria as &$criterion) {
                $info_criterion = explode('_', $criterion);
                $id_criterion_group = $info_criterion[0];
                $id_criterion = $info_criterion[1];
                $objAdvancedSearchCriterionGroupClass = new CriterionGroup($id_criterion_group, $id_search, (int)$this->context->language->id);
                if (preg_match('#~#', $id_criterion)) {
                    $range = explode('~', $id_criterion);
                    $min = $range[0];
                    $max = (!empty($range[1]) ? $range[1] : '');
                    $currency = $this->context->currency;
                    if ($objAdvancedSearchCriterionGroupClass->criterion_group_type == 'price' && !empty($params['obj']->id_currency)) {
                        $currency = new Currency($params['obj']->id_currency);
                        if (!Validate::isLoadedObject($currency)) {
                            $currency = $this->context->currency;
                        }
                    }
                    $criterion_value = $this->getTextualRangeValue($min, $max, $objAdvancedSearchCriterionGroupClass, $currency);
                } else {
                    $objAdvancedSearchCriterionClass = new Criterion($id_criterion, $id_search, (int)$this->context->language->id);
                    $criterion_value = trim($objAdvancedSearchCriterionClass->value);
                }
                $criteria_values[$criterion] = $criterion_value;
            }
        }
        $crossLinksSelected = false;
        if ($params['obj'] && Validate::isLoadedObject($params['obj'])) {
            $crossLinksSelected = $params['obj']->getCrossLinksOptionsSelected((int)$this->context->language->id);
        }
        $vars = array(
            'params' => $params,
            'cross_links_selected' => $crossLinksSelected,
            'id_search' => $id_search,
            'criterions_groups_indexed' => $criterions_groups_indexed,
            'criteria' => $criteria,
            'criteria_values' => $criteria_values,
            'currentSeo' => $params['obj'],
            'default_currency_id' => $default_currency->id,
            'default_currency_sign_left' => '',
            'default_currency_sign_right' => $default_currency->getSign('right'),
            'currencies' => Currency::getCurrencies(),
        );
        $this->html .= $this->fetchTemplate('module/seo/new_page.tpl', $vars);
    }
    protected function displayMassSeoSearchForm()
    {
        $id_search = (int)Tools::getValue('id_search');
        $search = SearchEngineUtils::getSearch($id_search, false, false);
        $criterions_groups_indexed = SearchEngineSeo::getCriterionsGroupsIndexedForSEO($id_search, (int)$this->context->language->id);
        $default_currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        foreach ($criterions_groups_indexed as &$criterions_group_indexed) {
            if ($criterions_group_indexed['range'] == 1) {
                if ($criterions_group_indexed['criterion_group_type'] == 'price') {
                    $default_currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                    $criterions_group_indexed['range_sign'] = $default_currency->sign;
                }
                $ranges = explode(',', $criterions_group_indexed['range_interval']);
                $criterions = array();
                foreach ($ranges as $krange => $range) {
                    $rangeUp = (isset($ranges[$krange+1]) ? $ranges[$krange+1] : '');
                    $range1 = $range.'~'.$rangeUp;
                    $range2 = $this->getTextualRangeValue($range, $rangeUp, $criterions_group_indexed, $default_currency);
                    $criterions[] = array('id_criterion' => $range1, 'value' => $range2);
                }
                $criterions_group_indexed['criterions'] = $criterions;
            } elseif ($criterions_group_indexed['criterion_group_type'] == 'price') {
                $price_range = SearchEngineUtils::getPriceRangeForSearchBloc($search[0], (int)$criterions_group_indexed['id_criterion_group_linked'], (int)$default_currency->id, (int)$this->getCurrentCustomerGroupId(), (int)$this->context->country->id, array(), array());
                $criterions_group_indexed['price_range'] = $price_range[0];
            } elseif ($criterions_group_indexed['display_type'] == 5 || $criterions_group_indexed['display_type'] == 8) {
                $range = SearchEngineUtils::getCriterionsRange($search[0], (int)$criterions_group_indexed['id_criterion_group'], (int)$this->context->language->id, array(), array(), false, false, $criterions_group_indexed);
                $criterions_group_indexed['range'] = $range[0];
            } else {
                $criterions = SearchEngineUtils::getCriterionsFromCriterionGroup((int)$criterions_group_indexed['id_criterion_group'], $criterions_group_indexed['id_search'], $criterions_group_indexed['sort_by'], $criterions_group_indexed['sort_way'], (int)$this->context->language->id);
                $criterions_group_indexed['criterions'] = $criterions;
            }
        }
        $vars = array(
            'id_search' => $id_search,
            'criterions_groups_indexed' => $criterions_groups_indexed,
            'default_currency_id' => $default_currency->id,
            'default_currency_sign_left' => '',
            'default_currency_sign_right' => $default_currency->getSign('right'),
            'currencies' => Currency::getCurrencies(),
            'maxCriterionsGroupForMass' => 4,
        );
        $this->html .= $this->fetchTemplate('module/seo/mass_page.tpl', $vars);
    }
    protected function processDeleteSeoSearch()
    {
        $ObjAdvancedSearchSeoClass = new Seo(Tools::getValue('id_seo'));
        $ObjAdvancedSearchSeoClass->deleted = 1;
        if ($ObjAdvancedSearchSeoClass->save()) {
            $this->html .= 'show_success("' . $this->l('The results page has been deleted', 'SeoTrait') . '");reloadPanel("seo_search_panel_' . (int)Tools::getValue('id_search') . '");';
        } else {
            $this->html .= 'show_error("' . $this->l('Error while deleting the results page', 'SeoTrait') . '");';
        }
    }
    protected function processDeleteMassSeo()
    {
        $id_seos = Tools::getValue('seo_group_action', false);
        $id_search = Tools::getValue('id_search', false);
        if (self::isFilledArray($id_seos)) {
            foreach ($id_seos as $id_seo) {
                $objAdvancedSearchSeoClass = new Seo($id_seo);
                $objAdvancedSearchSeoClass->deleted = 1;
                $objAdvancedSearchSeoClass->save();
            }
            $this->html .= 'show_success("' . $this->l('The results page has been deleted', 'SeoTrait') . '");reloadPanel("seo_search_panel_' . (int)$id_search . '");';
        } else {
            $this->html .= 'show_error("' . $this->l('Please select at least one results page', 'SeoTrait') . '");';
        }
    }
    protected function displaySeoPriceSlider()
    {
        $id_search = Tools::getValue('id_search', false);
        $id_criterion_group = Tools::getValue('id_criterion_group', false);
        $id_criterion_group_linked = Tools::getValue('id_criterion_group_linked', false);
        $id_currency = Tools::getValue('id_currency', false);
        $currency = new Currency($id_currency);
        if (!$id_search || !$id_currency) {
            die();
        }
        $search = SearchEngineUtils::getSearch($id_search, false);
        $price_range = SearchEngineUtils::getPriceRangeForSearchBloc($search[0], (int)$id_criterion_group_linked, (int)$id_currency, (int)$this->getCurrentCustomerGroupId(), (int)$this->context->country->id, array(), array());
        $min_price_id_currency = (int)$price_range[0]['min_price_id_currency'];
        $max_price_id_currency = (int)$price_range[0]['max_price_id_currency'];
        if (($min_price_id_currency == 0 && $min_price_id_currency != $id_currency) || ($min_price_id_currency != 0 && $min_price_id_currency != $id_currency)) {
            $price_range[0]['min_price'] = Tools::convertPrice($price_range[0]['min_price'], $id_currency);
        }
        if (($max_price_id_currency == 0 && $max_price_id_currency != $id_currency) || ($max_price_id_currency != 0 && $max_price_id_currency != $id_currency)) {
            $price_range[0]['max_price'] = Tools::convertPrice($price_range[0]['max_price'], $id_currency);
        }
        $price_range[0]['min_price'] = floor($price_range[0]['min_price']);
        $price_range[0]['max_price'] = ceil($price_range[0]['max_price']);
        $vars = array(
            'id_criterion_group' => $id_criterion_group,
            'price_range' => $price_range[0],
            'currency' => $currency,
        );
        $this->html .= $this->fetchTemplate('module/seo/price_slider.tpl', $vars);
    }
    protected function displaySeoSearchOptions()
    {
        if (Tools::getValue('id_seo_excludes')) {
            $id_seo_excludes = explode(',', Tools::getValue('id_seo_excludes', false));
        } else {
            $id_seo_excludes = array();
        }
        if (Tools::getValue('id_seo_origin')) {
            $id_seo_excludes[] = (int)Tools::getValue('id_seo_origin');
        }
        $query_search = Tools::getValue('q', false);
        $limit = Tools::getValue('limit', 100);
        $start = Tools::getValue('start', 0);
        $nbResults = Seo::getCrossLinksAvailable((int)$this->context->language->id, $id_seo_excludes, $query_search, true);
        $results = Seo::getCrossLinksAvailable((int)$this->context->language->id, $id_seo_excludes, $query_search, false, $limit, $start);
        foreach ($results as $key => $value) {
            $this->html .= $key . '=' . $value . "\n";
        }
        if ($nbResults > ($start + $limit)) {
            $this->html .= 'DisplayMore' . "\n";
        }
    }
}
