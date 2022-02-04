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
class pm_advancedsearch4advancedsearch4ModuleFrontController extends ModuleFrontController
{
    private $id_seo = false;
    private $id_search = false;
    private $searchInstance;
    protected $context;
    public $display_column_left = true;
    public $display_column_right = true;
    protected $display_header = true;
    protected $display_footer = true;
    private $criterions                = array();
    private $criterions_hidden        = array();
    private $next_id_criterion_group    = false;
    private $reset                    = false;
    public function __construct()
    {
        parent::__construct();
        if (Tools::getValue('ajaxMode')) {
            $this->ajax = true;
            $this->display_column_left = false;
            $this->display_column_right = false;
            $this->display_header = false;
            $this->display_footer = false;
        }
    }
    public function setMedia()
    {
        parent::setMedia();
        if ((method_exists($this->context, 'getMobileDevice') && $this->context->getMobileDevice() == false || !method_exists($this->context, 'getMobileDevice'))) {
            $this->addCSS(array(
                _THEME_CSS_DIR_.'scenes.css' => 'all',
                _THEME_CSS_DIR_.'category.css' => 'all',
                _THEME_CSS_DIR_.'product_list.css' => 'all',
            ));
            if (Configuration::get('PS_COMPARATOR_MAX_ITEM') > 0) {
                $this->addJS(_THEME_JS_DIR_.'products-comparison.js');
            }
        }
    }
    public function init()
    {
        parent::init();
        $this->setCustomContextLink();
        $this->setSEOTags();
        $this->setProductFilterList();
    }
    private function setCustomContextLink()
    {
        $link_pm = new LinkPM($this->context->link->protocol_link, $this->context->link->protocol_content);
        $this->context->smarty->assign(array(
            'link' => $link_pm
        ));
    }
    private function setProductFilterList()
    {
        $productFilterListSource = Tools::getValue('productFilterListSource');
        if (in_array($productFilterListSource, As4SearchEngine::$validPageName)) {
            As4SearchEngine::$productFilterListSource = $productFilterListSource;
            if ($productFilterListSource == 'search' || $productFilterListSource == 'jolisearch' || $productFilterListSource == 'module-ambjolisearch-jolisearch') {
                $productFilterListData = AdvancedSearchCoreClass::getDataUnserialized(Tools::getValue('productFilterListData'));
                if ($productFilterListData !== false) {
                    As4SearchEngine::$productFilterListData = $productFilterListData;
                }
            }
            $this->module->setProductFilterContext();
        }
    }
    private function setSEOTags()
    {
        $this->id_seo = Tools::getValue('id_seo', false);
        $seo_url = Tools::getValue('seo_url', false);
        if ($seo_url && $this->id_seo) {
            $resultSeoUrl = AdvancedSearchSeoClass::getSeoSearchByIdSeo((int)$this->id_seo, (int)$this->context->language->id);
            if (!$resultSeoUrl) {
                Tools::redirect('404');
            }
            if ($resultSeoUrl[0]['deleted']) {
                header("Status: 301 Moved Permanently", false, 301);
                Tools::redirect('index');
            }
            $pageNumber = (int)Tools::getValue('p');
            $this->context->smarty->assign(array(
                'page_name'                =>    'advancedsearch-seo-'.(int)$this->id_seo,
                'meta_title'            =>    $resultSeoUrl[0]['meta_title'].(!empty($pageNumber) ? ' ('.$pageNumber.')' : ''),
                'meta_description'        =>    $resultSeoUrl[0]['meta_description'],
                'meta_keywords'            =>    $resultSeoUrl[0]['meta_keywords'],
                'as_seo_title'            =>    $resultSeoUrl[0]['title'],
                'as_seo_description'    =>    $resultSeoUrl[0]['description']
            ));
        }
        if (Tools::getValue('ajaxMode')) {
            if (!headers_sent()) {
                header('X-Robots-Tag: noindex, nofollow', true);
            }
            $this->context->smarty->assign(array(
                'nofollow' => true,
                'nobots' => true,
            ));
        } elseif (Tools::getValue('only_products')) {
            if ($this->id_seo && (Tools::getValue('p') || Tools::getValue('n'))) {
                header('X-Robots-Tag: noindex, follow', true);
            } else {
                header('X-Robots-Tag: noindex, nofollow', true);
            }
            $this->context->smarty->assign(array(
                'nofollow' => true,
                'nobots' => true,
            ));
        }
    }
    public function process()
    {
        $seo_url = Tools::getValue('seo_url', false);
        if ($seo_url == 'products-comparison') {
            ob_end_clean();
            header("Status: 301 Moved Permanently", false, 301);
            Tools::redirect('products-comparison.php?ajax='.(int)Tools::getValue('ajax').'&action='.Tools::getValue('action').'&id_product='.(int)Tools::getValue('id_product'));
        }
        if ($seo_url && $this->id_seo) {
            $resultSeoUrl = AdvancedSearchSeoClass::getSeoSearchByIdSeo((int)$this->id_seo, (int)$this->context->language->id);
            if (!$resultSeoUrl) {
                Tools::redirect('404');
            }
            if ($resultSeoUrl[0]['deleted']) {
                header("Status: 301 Moved Permanently", false, 301);
                Tools::redirect('index');
            }
            $this->id_search = $resultSeoUrl[0]['id_search'];
            $this->searchInstance = new AdvancedSearchClass((int)$this->id_search, (int)$this->context->language->id);
            if (!$this->searchInstance->active) {
                header("Status: 307 Temporary Redirect", false, 307);
                Tools::redirect('index');
            }
            $seoUrlCheck = current(explode('/', $seo_url));
            if ($resultSeoUrl[0]['seo_url'] != $seoUrlCheck) {
                header("Status: 301 Moved Permanently", false, 301);
                Tools::redirect($this->context->link->getModuleLink('pm_advancedsearch4', 'seo', array('id_seo' => $this->idSeo, 'seo_url' => $resultSeoUrl[0]['seo_url'])));
                die();
            }
            $criteria = unserialize($resultSeoUrl[0]['criteria']);
            if (is_array($criteria) && sizeof($criteria)) {
                $this->criterions = PM_AdvancedSearch4::getArrayCriteriaFromSeoArrayCriteria($criteria);
            }
            $hasPriceCriterionGroup = false;
            if (is_array($this->criterions) && sizeof($this->criterions)) {
                $selected_criteria_groups_type = As4SearchEngine::getCriterionGroupsTypeAndDisplay((int)$this->id_search, array_keys($this->criterions));
                if (is_array($selected_criteria_groups_type) && sizeof($selected_criteria_groups_type)) {
                    foreach ($selected_criteria_groups_type as $criterionGroup) {
                        if ($criterionGroup['criterion_group_type'] == 'price') {
                            $hasPriceCriterionGroup = true;
                            break;
                        }
                    }
                }
            }
            if ($hasPriceCriterionGroup && $resultSeoUrl[0]['id_currency'] && $this->context->cookie->id_currency != (int)$resultSeoUrl[0]['id_currency']) {
                $this->context->cookie->id_currency = $resultSeoUrl[0]['id_currency'];
                header('Refresh: 1; URL='.$_SERVER['REQUEST_URI']);
                die;
            }
            $this->context->smarty->assign('as_cross_links', AdvancedSearchSeoClass::getCrossLinksSeo((int)$this->context->language->id, $resultSeoUrl[0]['id_seo']));
        } else {
            if (Tools::getValue('setHideCriterionStatus')) {
                ob_end_clean();
                $this->id_search = (int)Tools::getValue('id_search');
                $state = (int)Tools::getValue('state') > 0;
                if (isset($this->context->cookie->hidden_criteria_state)) {
                    $hidden_criteria_state = unserialize($this->context->cookie->hidden_criteria_state);
                    if (is_array($hidden_criteria_state)) {
                        $hidden_criteria_state[$this->id_search] = $state;
                    } else {
                        $hidden_criteria_state = array();
                    }
                    $this->context->cookie->hidden_criteria_state = serialize($hidden_criteria_state);
                } else {
                    $this->context->cookie->hidden_criteria_state = serialize(array($this->id_search => $state));
                }
                die;
            }
            $this->id_search = (int)Tools::getValue('id_search');
            $this->searchInstance = new AdvancedSearchClass((int)$this->id_search, (int)$this->context->language->id);
            $this->criterions = Tools::getValue('as4c', array());
            if (is_array($this->criterions)) {
                $this->criterions = As4SearchEngine::cleanArrayCriterion($this->criterions);
            } else {
                $this->criterions = array();
            }
            $this->criterions_hidden = Tools::getValue('as4c_hidden', array());
            if (is_array($this->criterions_hidden)) {
                $this->criterions_hidden = As4SearchEngine::cleanArrayCriterion($this->criterions_hidden);
            } else {
                $this->criterions_hidden = array();
            }
            $this->next_id_criterion_group = (int)Tools::getValue('next_id_criterion_group', false);
            $this->reset = (int)Tools::getValue('reset', false);
            $this->reset_group = (int)Tools::getValue('reset_group', false);
            if ($this->reset) {
                $this->criterions = array();
            }
            if ($this->reset_group && isset($this->criterions[$this->reset_group])) {
                unset($this->criterions[$this->reset_group]);
                if ($this->searchInstance->step_search) {
                    $criterionsGroups = AdvancedSearchCriterionGroupClass::getCriterionsGroupsFromIdSearch((int)$this->id_search, (int)$this->context->language->id, false);
                    if (AdvancedSearchCoreClass::_isFilledArray($criterionsGroups)) {
                        $deleteAfter = false;
                        foreach ($criterionsGroups as $criterionGroup) {
                            if ((int)$criterionGroup['id_criterion_group'] == $this->reset_group) {
                                $deleteAfter = true;
                            }
                            if ($deleteAfter && isset($this->criterions[(int)$criterionGroup['id_criterion_group']])) {
                                unset($this->criterions[(int)$criterionGroup['id_criterion_group']]);
                            }
                        }
                    }
                }
            }
            if ($this->searchInstance->filter_by_emplacement) {
                $criterionsFromEmplacement = As4SearchEngine::getCriteriaFromEmplacement($this->searchInstance->id, $this->searchInstance->id_category_root);
                foreach ($criterionsFromEmplacement as $idCriterionGroup => $idCriterionList) {
                    if (!isset($this->criterions[$idCriterionGroup])) {
                        $this->criterions[$idCriterionGroup] = $idCriterionList;
                    } elseif (is_array($this->criterions[$idCriterionGroup]) && !sizeof($this->criterions[$idCriterionGroup])) {
                        $this->criterions[$idCriterionGroup] = $idCriterionList;
                    }
                }
            }
            $this->context->cookie->{'next_id_criterion_group_'.(int)$this->id_search} = $this->next_id_criterion_group;
        }
    }
    public function displayAjax()
    {
        $this->displayContent();
    }
    public function displayContent()
    {
        if (!$this->id_search) {
            die;
        }
        if (Tools::getValue('ajaxMode')) {
            $this->ajax = true;
        }
        if (!Tools::getValue('ajaxMode')) {
            echo $this->module->displayAjaxSearchBlocks($this->id_search, 'pm_advancedsearch.tpl', (int)Tools::getValue('with_product', true), $this->criterions, $this->criterions_hidden, true);
        } else {
            try {
                Hook::exec('displayHeader');
            } catch (SmartyException $e) {
            }
            if (Tools::getValue('only_products')) {
                $this->module->displayAjaxSearchBlocks($this->id_search, 'pm_advancedsearch.tpl', (int)Tools::getValue('with_product', true), $this->criterions, $this->criterions_hidden, true);
            } elseif ($this->next_id_criterion_group && !$this->reset) {
                if (is_array($this->criterions) && sizeof($this->criterions)) {
                    $this->module->displayAjaxSearchBlocks($this->id_search, 'pm_advancedsearch.tpl', (int)Tools::getValue('with_product', true), $this->criterions, $this->criterions_hidden);
                } else {
                    $this->module->displayNextStepSearch($this->id_search, $this->next_id_criterion_group, (int)Tools::getValue('with_product', true), $this->criterions, $this->criterions_hidden);
                }
            } else {
                $withProducts = (int)Tools::getValue('with_product', true);
                if ($this->searchInstance->search_method == 3) {
                    $searchs = As4SearchEngine::getSearch($this->searchInstance->id, (int)$this->context->language->id);
                    $searchs = $this->module->getCriterionsGroupsAndCriterionsForSearch($searchs, (int)$this->context->language->id, $this->criterions, 0, false);
                    $realAvailableGroups = 0;
                    $selectedGroupCount = 0;
                    foreach ($searchs[0]['criterions'] as $idCriterionGroup => $criterionsList) {
                        if (isset($this->criterions[$idCriterionGroup])) {
                            $selectedGroupCount++;
                        }
                        if (is_array($criterionsList) && sizeof($criterionsList)) {
                            $realAvailableGroups++;
                        }
                    }
                    if ($selectedGroupCount == $realAvailableGroups) {
                        $withProducts = true;
                    } else {
                        $withProducts = false;
                    }
                }
                $this->module->displayAjaxSearchBlocks($this->id_search, 'pm_advancedsearch.tpl', $withProducts, $this->criterions, $this->criterions_hidden);
            }
        }
        if ($this->ajax) {
            die;
        }
    }
    public function getSearchEngine()
    {
        return $this->searchInstance;
    }
    public function getCriterionsList()
    {
        return $this->criterions;
    }
    public function getHiddenCriterionsList()
    {
        return $this->criterions_hidden;
    }
}
