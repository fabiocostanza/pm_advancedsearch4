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

if (!defined('_PS_VERSION_')) {
    exit;
}
use AdvancedSearch\Core;
use AdvancedSearch\Models\Seo;
use AdvancedSearch\Models\Search;
use AdvancedSearch\SearchEngineUtils;
use AdvancedSearch\SearchProvider\Facets;
use AdvancedSearch\AdvancedSearchProductListingFrontController;
class pm_advancedsearch4seoModuleFrontController extends AdvancedSearchProductListingFrontController
{
    protected $idSeo;
    protected $idSearch;
    protected $searchInstance;
    protected $seoUrl;
    protected $pageNb = 1;
    protected $criterions;
    protected $originalCriterions;
    protected $seoPageInstance;
    protected $indexState = 'index';
    public function init()
    {
        if (!isset($this->module) || !is_object($this->module)) {
            $this->module = Module::getInstanceByName(_PM_AS_MODULE_NAME_);
        }
        parent::init();
        $this->php_self = 'module-' . _PM_AS_MODULE_NAME_ . '-seo';
        $this->setSEOTags();
        $this->setProductFilterList();
        $this->setSmartyVars();
        if (!headers_sent()) {
            header('Link: <' . $this->getCanonicalURL() . '>; rel="canonical"', true);
        }
        if (Tools::getIsset('from-xhr')) {
            $this->doProductSearch('');
        } else {
            $this->template = 'module:' . _PM_AS_MODULE_NAME_ . '/views/templates/front/'.Tools::substr(_PS_VERSION_, 0, 3).'/seo-page.tpl';
        }
    }
    protected function redirectToSeoPageIndex()
    {
        $seoObj = new Seo($this->idSeo, $this->context->language->id);
        if (Validate::isLoadedObject($seoObj)) {
            Tools::redirect($this->context->link->getModuleLink(_PM_AS_MODULE_NAME_, 'seo', array('id_seo' => (int)$seoObj->id, 'seo_url' => $seoObj->seo_url), null, (int)$this->context->language->id));
        } else {
            Tools::redirect('index');
        }
    }
    protected function setSEOTags()
    {
        $this->idSeo = (int)Tools::getValue('id_seo');
        $this->seoUrl = strip_tags(Tools::getValue('seo_url'));
        $this->pageNb = (int)Tools::getValue('page', 1);
        if ($this->seoUrl && $this->idSeo) {
            $resultSeoUrl = Seo::getSeoSearchByIdSeo((int)$this->idSeo, (int)$this->context->language->id);
            if (!$resultSeoUrl) {
                Tools::redirect('404');
            }
            $this->seoPageInstance = new Seo($this->idSeo, $this->context->language->id);
            $this->idSearch = (int)$resultSeoUrl[0]['id_search'];
            $this->searchInstance = new Search((int)$this->idSearch, (int)$this->context->language->id);
            if (!Validate::isLoadedObject($this->searchInstance)) {
                Tools::redirect('404');
            }
            if ($resultSeoUrl[0]['deleted']) {
                if (!headers_sent()) {
                    header("Status: 301 Moved Permanently", false, 301);
                }
                Tools::redirect('index');
            }
            if (!$this->searchInstance->active) {
                if (!headers_sent()) {
                    header("Status: 307 Temporary Redirect", false, 307);
                }
                Tools::redirect('index');
            }
            $seoUrlCheck = current(explode('/', $this->seoUrl));
            if ($resultSeoUrl[0]['seo_url'] != $seoUrlCheck) {
                if (!headers_sent()) {
                    header("Status: 301 Moved Permanently", false, 301);
                }
                $this->redirectToSeoPageIndex();
                die();
            }
            $hasPriceCriterionGroup = false;
            if (is_array($this->criterions) && sizeof($this->criterions)) {
                $selected_criteria_groups_type = SearchEngineUtils::getCriterionGroupsTypeAndDisplay((int)$this->id_search, array_keys($this->criterions));
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
                if (!headers_sent()) {
                    header('Refresh: 1; URL='.$_SERVER['REQUEST_URI']);
                }
                die;
            }
            $criteria = Core::decodeCriteria($resultSeoUrl[0]['criteria']);
            if (is_array($criteria) && sizeof($criteria)) {
                $this->criterions = PM_AdvancedSearch4::getArrayCriteriaFromSeoArrayCriteria($criteria);
                $this->criterions = SearchEngineUtils::cleanArrayCriterion($this->criterions);
            }
            $searchQuery = implode('/', array_slice(explode('/', $this->seoUrl), 1));
            $criterionsList = SearchEngineUtils::getCriterionsFromURL($this->idSearch, $searchQuery);
            if (is_array($criterionsList) && sizeof($criterionsList)) {
                if (is_array($this->criterions) && sizeof($this->criterions)) {
                    $arrayDiff = $criterionsList;
                    foreach ($arrayDiff as $arrayDiffKey => $arrayDiffRow) {
                        if (isset($this->criterions[$arrayDiffKey]) && $this->criterions[$arrayDiffKey] == $arrayDiffRow) {
                            unset($arrayDiff[$arrayDiffKey]);
                        }
                    }
                    if (is_array($arrayDiff) && sizeof($arrayDiff)) {
                        $this->indexState = 'noindex';
                    }
                    unset($arrayDiff);
                } else {
                    $this->indexState = 'noindex';
                }
            }
            $this->originalCriterions = $this->criterions;
            $this->criterions += $criterionsList;
            $this->context->smarty->assign(array(
                'as_is_seo_page' => true,
                'as_cross_links' => Seo::getCrossLinksSeo((int)$this->context->language->id, $resultSeoUrl[0]['id_seo']),
            ));
        } else {
            Tools::redirect('404');
        }
    }
    protected function setProductFilterList()
    {
        $productFilterListSource = Tools::getValue('productFilterListSource');
        if (in_array($productFilterListSource, SearchEngineUtils::$validPageName)) {
            SearchEngineUtils::$productFilterListSource = $productFilterListSource;
            if ($productFilterListSource == 'search' || $productFilterListSource == 'jolisearch' || $productFilterListSource == 'module-ambjolisearch-jolisearch' || $productFilterListSource = 'prestasearch') {
                $productFilterListData = Core::getDataUnserialized(Tools::getValue('productFilterListData'));
                if ($productFilterListData !== false) {
                    SearchEngineUtils::$productFilterListData = $productFilterListData;
                }
            }
            $this->module->setProductFilterContext();
        }
    }
    protected function setSmartyVars()
    {
        $variables = $this->getProductSearchVariables();
        if ($this->pageNb < 1 || ($this->pageNb > 1 && empty($variables['products']))) {
            $this->redirectToSeoPageIndex();
        }
        $this->context->smarty->assign(array(
            'listing' => $variables,
            'id_search' => $this->idSearch,
            'as_seo_description' => $this->seoPageInstance->description,
            'as_seo_footer_description' => $this->seoPageInstance->footer_description,
            'as_seo_title' => $this->seoPageInstance->title,
            'as_see_also_txt' => $this->module->l('See also', 'seo'),
        ));
    }
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->seoPageInstance->title,
            'url' => $this->seoPageInstance->seo_url,
        );
        return $breadcrumb;
    }
    public function getSearchEngine()
    {
        return $this->searchInstance;
    }
    public function getIdSeo()
    {
        return $this->idSeo;
    }
    public function getSelectedCriterions()
    {
        return $this->criterions;
    }
    public function getCriterionsList()
    {
        return $this->getSelectedCriterions();
    }
    public function getOriginalCriterions()
    {
        return $this->originalCriterions;
    }
    public function getCanonicalURL()
    {
        return SearchEngineUtils::generateURLFromCriterions($this->idSearch, $this->getSelectedCriterions());
    }
    public function getListingLabel()
    {
        return $this->seoPageInstance->title;
    }
    protected function getDefaultProductSearchProvider()
    {
        return new Facets(
            $this->module,
            $this->getTranslator(),
            $this->searchInstance,
            $this->getSelectedCriterions()
        );
    }
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['meta']['robots'] = $this->indexState;
        $page['meta']['title'] = $this->seoPageInstance->meta_title;
        $page['meta']['description'] = $this->seoPageInstance->meta_description;
        $page['meta']['keywords'] = $this->seoPageInstance->meta_keywords;
        $page['page_name'] = 'advancedsearch-seo-' . (int)$this->idSeo;
        $page['body_classes']['advancedsearch-seo'] = true;
        $page['body_classes']['advancedsearch-seo-' . (int)$this->idSeo] = true;
        return $page;
    }
    protected function updateQueryString(array $extraParams = null)
    {
        if ($extraParams === null) {
            $extraParams = array();
        }
        return SearchEngineUtils::generateURLFromCriterions($this->getSearchEngine()->id, $this->getCriterionsList(), null, $extraParams);
    }
}
