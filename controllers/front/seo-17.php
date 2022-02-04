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
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchResult;
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
            $this->module = Module::getInstanceByName('pm_advancedsearch4');
        }
        parent::init();
        $this->php_self = 'module-pm_advancedsearch4-seo';
        $this->setSEOTags();
        $this->setProductFilterList();
        $this->setSmartyVars();
        if (Tools::getIsset('from-xhr')) {
            $this->doProductSearch('');
        } else {
            $this->template = 'module:pm_advancedsearch4/views/templates/front/'.Tools::substr(_PS_VERSION_, 0, 3).'/seo-page.tpl';
        }
    }
    protected function redirectToSeoPageIndex()
    {
        $seoObj = new AdvancedSearchSeoClass($this->idSeo, $this->context->language->id);
        if (Validate::isLoadedObject($seoObj)) {
            Tools::redirect($this->context->link->getModuleLink('pm_advancedsearch4', 'seo', array('id_seo' => (int)$seoObj->id, 'seo_url' => $seoObj->seo_url), null, (int)$this->context->language->id));
        } else {
            Tools::redirect('index');
        }
    }
    protected function setSEOTags()
    {
        $this->idSeo = (int)Tools::getValue('id_seo');
        $this->seoUrl = Tools::getValue('seo_url');
        $this->pageNb = (int)Tools::getValue('page', 1);
        if ($this->seoUrl && $this->idSeo) {
            $resultSeoUrl = AdvancedSearchSeoClass::getSeoSearchByIdSeo((int)$this->idSeo, (int)$this->context->language->id);
            if (!$resultSeoUrl) {
                Tools::redirect('404');
            }
            $this->seoPageInstance = new AdvancedSearchSeoClass($this->idSeo, $this->context->language->id);
            $this->idSearch = (int)$resultSeoUrl[0]['id_search'];
            $this->searchInstance = new AdvancedSearchClass((int)$this->idSearch, (int)$this->context->language->id);
            if ($resultSeoUrl[0]['deleted']) {
                header("Status: 301 Moved Permanently", false, 301);
                Tools::redirect('index');
            }
            if (!$this->searchInstance->active) {
                header("Status: 307 Temporary Redirect", false, 307);
                Tools::redirect('index');
            }
            $seoUrlCheck = current(explode('/', $this->seoUrl));
            if ($resultSeoUrl[0]['seo_url'] != $seoUrlCheck) {
                header("Status: 301 Moved Permanently", false, 301);
                $this->redirectToSeoPageIndex();
                die();
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
            $criteria = unserialize($resultSeoUrl[0]['criteria']);
            if (is_array($criteria) && sizeof($criteria)) {
                $this->criterions = PM_AdvancedSearch4::getArrayCriteriaFromSeoArrayCriteria($criteria);
                $this->criterions = As4SearchEngine::cleanArrayCriterion($this->criterions);
            }
            $searchQuery = implode('/', array_slice(explode('/', $this->seoUrl), 1));
            $criterionsList = As4SearchEngine::getCriterionsFromURL($this->idSearch, $searchQuery);
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
                'as_cross_links' => AdvancedSearchSeoClass::getCrossLinksSeo((int)$this->context->language->id, $resultSeoUrl[0]['id_seo']),
            ));
        } else {
            Tools::redirect('404');
        }
    }
    protected function setProductFilterList()
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
            'as_seo_title' => $this->seoPageInstance->title,
            'as_see_also_txt' => $this->module->l('See also', 'seo-17'),
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
        return $this->context->link->getModuleLink('pm_advancedsearch4', 'seo', array('id_seo' => (int)$this->seoPageInstance->id, 'seo_url' => $this->seoPageInstance->seo_url), null, (int)$this->context->language->id);
    }
    public function getListingLabel()
    {
        return $this->seoPageInstance->title;
    }
    protected function getDefaultProductSearchProvider()
    {
        return new As4SearchProvider(
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
        return As4SearchEngine::generateURLFromCriterions($this->getSearchEngine()->id, $this->getCriterionsList(), null, $extraParams);
    }
}
