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
class pm_advancedsearch4seoModuleFrontController extends ModuleFrontController
{
    protected $idSeo;
    protected $idSearch;
    protected $searchInstance;
    protected $seoUrl;
    protected $pageNb = 1;
    protected $criterions;
    protected $originalCriterions;
    public function init()
    {
        parent::init();
        $this->setSEOTags();
        $this->setProductFilterList();
        $this->setSmartyVars();
        if (!headers_sent()) {
            header('Link: <' . $this->getCanonicalURL() . '>; rel="canonical"', true);
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $this->setTemplate('seo-page.tpl');
        } else {
            $this->setTemplate('module:pm_advancedsearch4/views/templates/front/'.Tools::substr(_PS_VERSION_, 0, 3).'/seo-page.tpl');
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
        $this->idSeo = Tools::getValue('id_seo');
        $this->seoUrl = strip_tags(Tools::getValue('seo_url'));
        $this->pageNb = (int)Tools::getValue('p', 1);
        if ($this->seoUrl && $this->idSeo) {
            $resultSeoUrl = AdvancedSearchSeoClass::getSeoSearchByIdSeo((int)$this->idSeo, (int)$this->context->language->id);
            if (!$resultSeoUrl) {
                Tools::redirect('404');
            }
            $this->idSearch = (int)$resultSeoUrl[0]['id_search'];
            $this->searchInstance = new AdvancedSearchClass((int)$this->idSearch, (int)$this->context->language->id);
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
                if (!headers_sent()) {
                    header('Refresh: 1; URL='.$_SERVER['REQUEST_URI']);
                }
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
                        $this->context->smarty->assign(array(
                            'nobots' => true,
                        ));
                    }
                    unset($arrayDiff);
                } else {
                    $this->context->smarty->assign(array(
                        'nobots' => true,
                    ));
                }
            }
            $this->originalCriterions = $this->criterions;
            $this->criterions += $criterionsList;
            $this->context->smarty->assign(array(
                'page_name'                => 'advancedsearch-seo-' . (int)$this->idSeo,
                'as_is_seo_page'           => true,
                'meta_title'            => $resultSeoUrl[0]['meta_title'] . ((int)$this->pageNb > 1 ? ' ('.$this->pageNb.')' : ''),
                'meta_description'        => $resultSeoUrl[0]['meta_description'],
                'meta_keywords'            => $resultSeoUrl[0]['meta_keywords'],
                'path'                    => $resultSeoUrl[0]['title'],
                'as_seo_title'            => $resultSeoUrl[0]['title'],
                'as_seo_description'    => $resultSeoUrl[0]['description'],
                'as_cross_links'        => AdvancedSearchSeoClass::getCrossLinksSeo((int)$this->context->language->id, $resultSeoUrl[0]['id_seo']),
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
        $nb_products = As4SearchEngine::getProductsSearched(
            $this->idSearch,
            $this->criterions,
            As4SearchEngine::getCriterionGroupsTypeAndDisplay($this->idSearch, array_keys($this->criterions)),
            (int)$this->pageNb,
            (int)Tools::getValue('n', $this->searchInstance->products_per_page),
            true
        );
        $products = As4SearchEngine::getProductsSearched(
            $this->idSearch,
            $this->criterions,
            As4SearchEngine::getCriterionGroupsTypeAndDisplay($this->idSearch, array_keys($this->criterions)),
            (int)$this->pageNb,
            (int)Tools::getValue('n', $this->searchInstance->products_per_page),
            false
        );
        if ($this->pageNb > 1 && !$products) {
            $this->redirectToSeoPageIndex();
        }
        $this->module->_assignPagination($this->searchInstance->products_per_page, $nb_products);
        $this->module->_assignProductSort($this->searchInstance);
        $link_pm = new LinkPM($this->context->link->protocol_link, $this->context->link->protocol_content);
        $this->context->smarty->assign(array(
            'products' => $products,
            'nb_products' => $nb_products,
            'id_search' => $this->idSearch,
            'request' => $link_pm->getPaginationLink(false, false, false, true),
            'link' => $link_pm,
            'as_obj' => $this->module,
        ));
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
    public function getOriginalCriterions()
    {
        return $this->originalCriterions;
    }
    public function getCanonicalURL()
    {
        return As4SearchEngine::generateURLFromCriterions($this->idSearch, $this->getSelectedCriterions());
    }
}
