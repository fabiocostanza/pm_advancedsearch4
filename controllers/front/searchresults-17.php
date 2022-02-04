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

use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchResult;
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
    protected $currentIdSupplier;
    protected $criterionsList = array();
    public function init()
    {
        if (!isset($this->module) || !is_object($this->module)) {
            $this->module = Module::getInstanceByName('pm_advancedsearch4');
        }
        parent::init();

        if(Tools::getIsset('show_schemas') AND Tools::getValue('show_schemas')) {
            $this->context->cookie->schemas = true;
        } elseif(Tools::getIsset('show_products') AND Tools::getValue('show_products')) {
            $this->context->cookie->schemas = false;
        }

        $this->php_self = 'module-pm_advancedsearch4-searchresults';
        if (!headers_sent()) {
            header('X-Robots-Tag: noindex', true);
        }
        $this->idSearch = (int)Tools::getValue('id_search');
        $this->searchInstance = new AdvancedSearchClass((int)$this->idSearch, (int)$this->context->cookie->id_lang);
        if (!Validate::isLoadedObject($this->searchInstance)) {
            Tools::redirect('404');
        } else {
            if (!$this->searchInstance->active) {
                header("Status: 307 Temporary Redirect", false, 307);
                Tools::redirect('index');
            }
        }
        $this->currentIdCategory = As4SearchEngine::getCurrentCategory();
        $this->currentIdManufacturer = As4SearchEngine::getCurrentManufacturer();
        $this->currentIdSupplier = As4SearchEngine::getCurrentSupplier();
        if (Tools::getValue('as4_from') == 'category' && empty($this->currentIdCategory)) {
            Tools::redirect('404');
        } elseif (Tools::getValue('as4_from') == 'manufacturer' && empty($this->currentIdManufacturer)) {
            Tools::redirect('404');
        } elseif (Tools::getValue('as4_from') == 'supplier' && empty($this->currentIdSupplier)) {
            Tools::redirect('404');
        }
        $this->setCriterions();
        $this->setSmartyVars();
        if (Tools::getValue('order')) {
            try {
                $selectedSortOrder = SortOrder::newFromString(trim(Tools::getValue('order')));
            } catch (Exception $e) {
                $fixedSearchUrl = $this->rewriteOrderParameter();
                header('Location:' . $fixedSearchUrl, true, 301);
            }
        }
        if (Tools::getIsset('from-xhr')) {
            $this->doProductSearch('');
        } else {
            $this->template = 'module:pm_advancedsearch4/views/templates/front/'.Tools::substr(_PS_VERSION_, 0, 3).'/search-results.tpl';
        }
    }
    protected function rewriteOrderParameter()
    {
        $defaultSearchEngineOrderBy = As4SearchEngine::getOrderByValue($this->getSearchEngine());
        $defaultSearchEngineOrderWay = As4SearchEngine::getOrderWayValue($this->getSearchEngine());
        $selectedSortOrder = new SortOrder('product', $defaultSearchEngineOrderBy, $defaultSearchEngineOrderWay);
        return As4SearchEngine::generateURLFromCriterions($this->idSearch, $this->criterionsList, null, array('order' => $selectedSortOrder->toString()));
    }
    public function getSelectedCriterions()
    {
        return $this->criterionsList;
    }
    protected function setCriterions()
    {
        $searchQuery = trim(Tools::getValue('as4_sq'));
        if (!empty($searchQuery)) {
            $this->criterionsList = As4SearchEngine::getCriterionsFromURL($this->idSearch, $searchQuery);
            if ($this->searchInstance->filter_by_emplacement) {
                $criterionsFromEmplacement = As4SearchEngine::getCriteriaFromEmplacement($this->searchInstance->id);
                foreach ($criterionsFromEmplacement as $idCriterionGroup => $idCriterionList) {
                    if (!isset($this->criterionsList[$idCriterionGroup])) {
                        $this->criterionsList[$idCriterionGroup] = $idCriterionList;
                    } else {
                        $this->criterionsList[$idCriterionGroup] = $this->criterionsList[$idCriterionGroup] + $idCriterionList;
                    }
                }
            }
            $this->criterionsList = As4SearchEngine::cleanArrayCriterion($this->criterionsList);
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
                    header('Link: <' . As4SearchEngine::generateURLFromCriterions($this->idSearch, $this->criterionsList) . '>; rel="canonical"', true);
                }
            }
        } else {
            if ($this->searchInstance->filter_by_emplacement) {
                $criterionsFromEmplacement = As4SearchEngine::getCriteriaFromEmplacement($this->searchInstance->id);
                foreach ($criterionsFromEmplacement as $idCriterionGroup => $idCriterionList) {
                    if (!isset($this->criterionsList[$idCriterionGroup])) {
                        $this->criterionsList[$idCriterionGroup] = $idCriterionList;
                    } else {
                        $this->criterionsList[$idCriterionGroup] = $this->criterionsList[$idCriterionGroup] + $idCriterionList;
                    }
                }
                $this->criterionsList = As4SearchEngine::getCriteriaFromEmplacement($this->searchInstance->id);
                $this->criterionsList = As4SearchEngine::cleanArrayCriterion($this->criterionsList);
                if (sizeof($this->criterionsList)) {
                    if (!headers_sent()) {
                        header('Link: <' . As4SearchEngine::generateURLFromCriterions($this->idSearch, $this->criterionsList) . '>; rel="canonical"', true);
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
        if (!empty($this->currentIdCategory) && !empty($this->searchInstance->keep_category_information)) {
            $this->currentCategoryObject = new Category($this->currentIdCategory, $this->context->language->id);
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

    /**
     * @return mixed
     */
    public function getCurrentCategoryObject()
    {
        return $this->currentCategoryObject;
    }

    public function getCriterionsList()
    {
        return $this->criterionsList;
    }
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadCrumbTitle = (!empty($this->searchInstance->title) ? $this->searchInstance->title : $this->getTranslator()->trans('Search results', array(), 'Shop.Theme.Catalog'));
        $breadcrumb['links'][] = array(
            'title' => $breadCrumbTitle,
            'url' => $this->getCanonicalURL(),
        );
        return $breadcrumb;
    }
    public function getCanonicalURL()
    {
        return As4SearchEngine::generateURLFromCriterions($this->idSearch, $this->criterionsList);
    }
    public function getListingLabel()
    {
        return $this->getTranslator()->trans('Search results', array(), 'Shop.Theme.Catalog');
    }
    protected function getDefaultProductSearchProvider()
    {
        return new As4SearchProvider(
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
        return As4SearchEngine::generateURLFromCriterions($this->getSearchEngine()->id, $this->getCriterionsList(), null, $extraParams);
    }
}
