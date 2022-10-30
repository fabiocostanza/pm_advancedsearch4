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

use AdvancedSearch\Models\Search;
use AdvancedSearch\SearchEngineUtils;
use AdvancedSearch\SearchProvider\Facets;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use AdvancedSearch\AdvancedSearchProductListingFrontController;
if (!defined('_PS_VERSION_')) {
    exit;
}
class pm_advancedsearch4searchresultsModuleFrontController extends AdvancedSearchProductListingFrontController
{
    protected $idSearch;
    protected $searchInstance;
    protected $currentIdCategory;
    protected $currentCategoryObject;
    protected $currentIdManufacturer;
    protected $currentManufacturerObject;
    protected $currentIdSupplier;
    protected $currentSupplierObject;
    protected $currentIdCms;
    protected $currentCmsObject;
    protected $criterionsList = array();
    public function init()
    {
        if (!isset($this->module) || !is_object($this->module)) {
            $this->module = Module::getInstanceByName(_PM_AS_MODULE_NAME_);
        }
        parent::init();

        if(Tools::getIsset('show_schemas') AND Tools::getValue('show_schemas')) {
            $this->context->cookie->schemas = true;
        } elseif(Tools::getIsset('show_products') AND Tools::getValue('show_products')) {
            $this->context->cookie->schemas = false;
        }

        $this->php_self = 'module-' . _PM_AS_MODULE_NAME_ . '-searchresults';
        if (!headers_sent()) {
            header('X-Robots-Tag: noindex', true);
        }
        $this->idSearch = (int)Tools::getValue('id_search');
        $this->searchInstance = new Search((int)$this->idSearch, (int)$this->context->cookie->id_lang);
        if (!Validate::isLoadedObject($this->searchInstance)) {
            Tools::redirect('404');
        }
        if (!$this->searchInstance->active) {
            if (!headers_sent()) {
                header("Status: 307 Temporary Redirect", false, 307);
            }
            Tools::redirect('index');
        }
        if (Tools::getValue('as4_from') == 'category') {
            $this->currentIdCategory = SearchEngineUtils::getCurrentCategory();
            if (empty($this->currentIdCategory)) {
                Tools::redirect('404');
            }
        } elseif (Tools::getValue('as4_from') == 'manufacturer') {
            $this->currentIdManufacturer = SearchEngineUtils::getCurrentManufacturer();
            if (empty($this->currentIdManufacturer)) {
                Tools::redirect('404');
            }
        } elseif (Tools::getValue('as4_from') == 'supplier') {
            $this->currentIdSupplier = SearchEngineUtils::getCurrentSupplier();
            if (empty($this->currentIdSupplier)) {
                Tools::redirect('404');
            }
        } elseif (Tools::getValue('as4_from') == 'cms') {
            $this->currentIdCms = SearchEngineUtils::getCurrentCMS();
            if (empty($this->currentIdCms)) {
                Tools::redirect('404');
            }
        }
        $this->setCriterions();
        $this->setSmartyVars();
        if (Tools::getValue('order')) {
            try {
                $selectedSortOrder = SortOrder::newFromString(trim(Tools::getValue('order')));
            } catch (Exception $e) {
                $fixedSearchUrl = $this->rewriteOrderParameter();
                if (!headers_sent()) {
                    header('Location:' . $fixedSearchUrl, true, 301);
                }
            }
        }
        if (Tools::getIsset('from-xhr')) {
            $this->doProductSearch('');
        } else {
            $this->template = 'module:' . _PM_AS_MODULE_NAME_ . '/views/templates/front/'.Tools::substr(_PS_VERSION_, 0, 3).'/search-results.tpl';
        }
    }
    protected function rewriteOrderParameter()
    {
        $defaultSearchEngineOrderBy = SearchEngineUtils::getOrderByValue($this->getSearchEngine());
        $defaultSearchEngineOrderWay = SearchEngineUtils::getOrderWayValue($this->getSearchEngine());
        $selectedSortOrder = new SortOrder('product', $defaultSearchEngineOrderBy, $defaultSearchEngineOrderWay);
        return SearchEngineUtils::generateURLFromCriterions($this->idSearch, $this->criterionsList, null, array('order' => $selectedSortOrder->toString()));
    }
    public function getSelectedCriterions()
    {
        return $this->criterionsList;
    }
    protected function setCriterions()
    {
        $searchQuery = trim(strip_tags(Tools::getValue('as4_sq')));
        if (!empty($searchQuery)) {
            $this->criterionsList = SearchEngineUtils::getCriterionsFromURL($this->idSearch, $searchQuery);
            if ($this->searchInstance->filter_by_emplacement) {
                $criterionsFromEmplacement = SearchEngineUtils::getCriteriaFromEmplacement($this->searchInstance->id);
                foreach ($criterionsFromEmplacement as $idCriterionGroup => $idCriterionList) {
                    if (!isset($this->criterionsList[$idCriterionGroup])) {
                        $this->criterionsList[$idCriterionGroup] = $idCriterionList;
                    } else {
                        $this->criterionsList[$idCriterionGroup] = $this->criterionsList[$idCriterionGroup] + $idCriterionList;
                    }
                }
            }
            $this->criterionsList = SearchEngineUtils::cleanArrayCriterion($this->criterionsList);
            $ignoreNoCriterions = false;
            if (!sizeof($this->criterionsList) && empty($this->searchInstance->filter_by_emplacement)) {
                $ignoreNoCriterions = true;
            }
            if (!$ignoreNoCriterions && !sizeof($this->criterionsList)) {
                if (!Tools::getIsset('from-xhr') && !Tools::getIsset('order') && !Tools::getIsset('page')) {
                    Tools::redirect('404');
                }
            } else {
                if (!headers_sent()) {
                    header('Link: <' . SearchEngineUtils::generateURLFromCriterions($this->idSearch, $this->criterionsList) . '>; rel="canonical"', true);
                }
            }
        } else {
            if ($this->searchInstance->filter_by_emplacement) {
                $criterionsFromEmplacement = SearchEngineUtils::getCriteriaFromEmplacement($this->searchInstance->id);
                foreach ($criterionsFromEmplacement as $idCriterionGroup => $idCriterionList) {
                    if (!isset($this->criterionsList[$idCriterionGroup])) {
                        $this->criterionsList[$idCriterionGroup] = $idCriterionList;
                    } else {
                        $this->criterionsList[$idCriterionGroup] = $this->criterionsList[$idCriterionGroup] + $idCriterionList;
                    }
                }
                $this->criterionsList = SearchEngineUtils::getCriteriaFromEmplacement($this->searchInstance->id);
                $this->criterionsList = SearchEngineUtils::cleanArrayCriterion($this->criterionsList);
                if (sizeof($this->criterionsList)) {
                    if (!headers_sent()) {
                        header('Link: <' . SearchEngineUtils::generateURLFromCriterions($this->idSearch, $this->criterionsList) . '>; rel="canonical"', true);
                    }
                }
            }
        }
    }
    protected function getImage($object, $id_image)
    {
        $retriever = new ImageRetriever(
            $this->context->link
        );
        return $retriever->getImage($object, $id_image);
    }
    protected function getTemplateVarCategory()
    {
        $category = $this->objectPresenter->present($this->currentCategoryObject);
        $category['image'] = $this->getImage(
            $this->currentCategoryObject,
            $this->currentCategoryObject->id_image
        );
        if(isset($this->context->cookie) and isset($this->context->cookie->schemas) and $this->context->cookie->schemas) {
            $category['schemas'] = $this->context->cookie->schemas;
        }
        return $category;
    }
    protected function getTemplateVarSubCategories()
    {
        return array_map(function (array $category) {
            $object = new Category(
                $category['id_category'],
                $this->context->language->id
            );
            $category['image'] = $this->getImage(
                $object,
                $object->id_image
            );
            $category['url'] = $this->context->link->getCategoryLink(
                $category['id_category'],
                $category['link_rewrite']
            );
            return $category;
        }, $this->currentCategoryObject->getSubCategories($this->context->language->id));
    }
    protected function setSmartyVars()
    {
        $this->module->setProductFilterContext();
        if (!empty($this->currentIdCategory)) {
            $this->currentCategoryObject = new Category($this->currentIdCategory, $this->context->language->id);
        }
        if (!empty($this->currentIdManufacturer)) {
            $this->currentManufacturerObject = new Manufacturer($this->currentIdManufacturer, $this->context->language->id);
        }
        if (!empty($this->currentIdSupplier)) {
            $this->currentSupplierObject = new Supplier($this->currentIdSupplier, $this->context->language->id);
        }
        if (!empty($this->currentIdCms)) {
            $this->currentCmsObject = new CMS($this->currentIdCms, $this->context->language->id);
        }
        if (!empty($this->currentIdCategory) && !empty($this->searchInstance->keep_category_information)) {
            $this->context->smarty->assign(array(
                'category' => $this->getTemplateVarCategory(),
                'subcategories' => $this->getTemplateVarSubCategories(),
            ));
        }
        $variables = $this->getProductSearchVariables();
        $this->context->smarty->assign(array(
            'listing' => $variables,
            'id_search' => $this->idSearch,
            'as_seo_description' => $this->searchInstance->description,
            'as_seo_title' => $this->searchInstance->title,
        ));
    }
    public function getSearchEngine()
    {
        return $this->searchInstance;
    }
    public function getCriterionsList()
    {
        return $this->criterionsList;
    }
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        if (!empty($this->currentCategoryObject) && Validate::isLoadedObject($this->currentCategoryObject)) {
            foreach ($this->currentCategoryObject->getAllParents() as $category) {
                if ($category->id_parent != 0 && !$category->is_root_category && $category->active) {
                    $breadcrumb['links'][] = [
                        'title' => $category->name,
                        'url' => $this->context->link->getCategoryLink($category),
                    ];
                }
            }
            if ($this->currentCategoryObject->id_parent != 0
                && !$this->currentCategoryObject->is_root_category
                && $category->active
            ) {
                $breadcrumb['links'][] = [
                    'title' => $this->currentCategoryObject->name,
                    'url' => $this->context->link->getCategoryLink($this->currentCategoryObject),
                ];
            }
        }
        if (!empty($this->currentManufacturerObject)
            && Validate::isLoadedObject($this->currentManufacturerObject)
            && $this->currentManufacturerObject->active
            && $this->currentManufacturerObject->isAssociatedToShop()
        ) {
            $breadcrumb['links'][] = [
                'title' => $this->currentManufacturerObject->name,
                'url' => $this->context->link->getManufacturerLink($this->currentManufacturerObject),
            ];
        }
        if (!empty($this->currentSupplierObject)
            && Validate::isLoadedObject($this->currentSupplierObject)
            && $this->currentSupplierObject->active
            && $this->currentSupplierObject->isAssociatedToShop()
        ) {
            $breadcrumb['links'][] = [
                'title' => $this->currentSupplierObject->name,
                'url' => $this->context->link->getSupplierLink($this->currentSupplierObject),
            ];
        }
        if (!empty($this->currentCmsObject)
            && Validate::isLoadedObject($this->currentCmsObject)
            && $this->currentCmsObject->active
            && $this->currentCmsObject->isAssociatedToShop()
        ) {
            $breadcrumb['links'][] = [
                'title' => $this->currentCmsObject->meta_title,
                'url' => $this->context->link->getCMSLink($this->currentCmsObject),
            ];
        }
        $searchQuery = trim(strip_tags(Tools::getValue('as4_sq')));
        $sourceController = SearchEngineUtils::getSourceControllerFromUrl($searchQuery, $this->context->language->id);
        if ($sourceController == 'new-products') {
            $breadcrumb['links'][] = [
                'title' => $this->trans('New products', [], 'Shop.Theme.Catalog'),
                'url' => $this->context->link->getPageLink('new-products', true),
            ];
        } elseif ($sourceController == 'best-sales') {
            $breadcrumb['links'][] = [
                'title' => $this->trans('Best sellers', [], 'Shop.Theme.Catalog'),
                'url' => $this->context->link->getPageLink('best-sales', true),
            ];
        } elseif ($sourceController == 'prices-drop') {
            $breadcrumb['links'][] = [
                'title' => $this->trans('Prices drop', [], 'Shop.Theme.Catalog'),
                'url' => $this->context->link->getPageLink('prices-drop', true),
            ];
        }
        $breadcrumb['links'][] = array(
            'title' => (!empty($this->searchInstance->title) ? $this->searchInstance->title : $this->getTranslator()->trans('Search results', array(), 'Shop.Theme.Catalog')),
            'url' => $this->getCanonicalURL(),
        );
        return $breadcrumb;
    }
    public function getCanonicalURL()
    {
        return SearchEngineUtils::generateURLFromCriterions($this->idSearch, $this->criterionsList);
    }
    public function getListingLabel()
    {
        return $this->getTranslator()->trans('Search results', array(), 'Shop.Theme.Catalog');
    }
    protected function getDefaultProductSearchProvider()
    {
        return new Facets(
            $this->module,
            $this->getTranslator(),
            $this->searchInstance,
            $this->criterionsList
        );
    }
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['meta']['robots'] = 'noindex';
        $page['body_classes']['as4-search-results'] = true;
        $page['body_classes']['as4-search-results-' . (int)$this->idSearch] = true;
        return $page;
    }
    protected function updateQueryString(array $extraParams = null)
    {
        if ($extraParams === null) {
            $extraParams = array();
        }
        if (array_key_exists('q', $extraParams)) {
            return parent::updateQueryString($extraParams);
        }
        return SearchEngineUtils::generateURLFromCriterions($this->getSearchEngine()->id, $this->getCriterionsList(), null, $extraParams);
    }
}
