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
abstract class AdvancedSearchProductListingFrontController extends ProductListingFrontController
{
    public function getListingLabel()
    {
        return $this->getTranslator()->trans('Search results', array(), 'Shop.Theme.Catalog');
    }
    protected function getProductSearchQuery()
    {
        $query = new ProductSearchQuery();
        return $query;
    }
    protected function getDefaultProductSearchProvider()
    {
        return new As4SearchProvider(
            $this->module,
            $this->getTranslator(),
            $this->getSearchEngine(),
            $this->getCriterionsList()
        );
    }
    private function getProductSearchProviderFromModules($query)
    {
        return null;
    }
    protected function getProductSearchVariables()
    {
        $context = $this->getProductSearchContext();
        $query = $this->getProductSearchQuery();
        $provider = $this->getProductSearchProviderFromModules($query);
        if (null === $provider) {
            $provider = $this->getDefaultProductSearchProvider();
        }
        $resultsPerPage = (int) Tools::getValue('resultsPerPage');
        if ($resultsPerPage <= 0 || $resultsPerPage > 36) {
            $resultsPerPage = Configuration::get('PS_PRODUCTS_PER_PAGE');
        }
        $query
            ->setResultsPerPage($resultsPerPage)
            ->setPage(max((int) Tools::getValue('page'), 1))
        ;
        if (Tools::getValue('order')) {
            $encodedSortOrder = Tools::getValue('order');
        } else {
            $encodedSortOrder = Tools::getValue('orderby', null);
        }
        if ($encodedSortOrder) {
            try {
                $selectedSortOrder = SortOrder::newFromString($encodedSortOrder);
            } catch (Exception $e) {
                $defaultSearchEngineOrderBy = As4SearchEngine::getOrderByValue($this->getSearchEngine());
                $defaultSearchEngineOrderWay = As4SearchEngine::getOrderWayValue($this->getSearchEngine());
                $selectedSortOrder = new SortOrder('product', $defaultSearchEngineOrderBy, $defaultSearchEngineOrderWay);
            }
            $query->setSortOrder($selectedSortOrder);
        }
        $encodedFacets = Tools::getValue('q');
        $query->setEncodedFacets($encodedFacets);
        $result = $provider->runQuery(
            $context,
            $query
        );
        if (!$result->getCurrentSortOrder()) {
            $result->setCurrentSortOrder($query->getSortOrder());
        }
        $products = $this->prepareMultipleProductsForTemplate(
            $result->getProducts()
        );
        if ($provider instanceof FacetsRendererInterface) {
            $rendered_facets = $provider->renderFacets(
                $context,
                $result
            );
            $rendered_active_filters = $provider->renderActiveFilters(
                $context,
                $result
            );
        } else {
            $rendered_facets = $this->renderFacets(
                $result
            );
            $rendered_active_filters = $this->renderActiveFilters(
                $result
            );
        }
        $pagination = $this->getTemplateVarPagination(
            $query,
            $result
        );
        $sort_orders = $this->getTemplateVarSortOrders(
            $result->getAvailableSortOrders(),
            $query->getSortOrder()->toString()
        );
        $sort_selected = false;
        if (!empty($sort_orders)) {
            foreach ($sort_orders as $order) {
                if (isset($order['current']) && true === $order['current']) {
                    $sort_selected = $order['label'];
                    break;
                }
            }
        }
        $currentUrlParams = array(
            'q' => $result->getEncodedFacets(),
        );
        if ((Tools::getIsset('order') || Tools::getIsset('orderby')) && $result->getCurrentSortOrder() != null) {
            $currentUrlParams['order'] = $result->getCurrentSortOrder()->toString();
        }
        $searchVariables = array(
            'result' => $result,
            'label' => $this->getListingLabel(),
            'products' => $products,
            'sort_orders' => $sort_orders,
            'sort_selected' => $sort_selected,
            'pagination' => $pagination,
            'rendered_facets' => $rendered_facets,
            'rendered_active_filters' => $rendered_active_filters,
            'js_enabled' => $this->ajax,
            'current_url' => $this->updateQueryString($currentUrlParams),
        );
        Hook::exec('actionProductSearchComplete', $searchVariables);
        if (version_compare(_PS_VERSION_, '1.7.1.0', '>=')) {
            Hook::exec('filterProductSearch', array('searchVariables' => &$searchVariables));
            Hook::exec('actionProductSearchAfter', $searchVariables);
        }
        return $searchVariables;
    }
    protected function getTemplateVarPagination(
        ProductSearchQuery $query,
        ProductSearchResult $result
    ) {
        $pagination = parent::getTemplateVarPagination($query, $result);
        foreach ($pagination['pages'] as &$p) {
            $p['url'] = $this->updateQueryString(array(
                'page' => $p['page'],
                'order' => $query->getSortOrder()->toString(),
                'from_as4' => $this->getSearchEngine()->id,
            ));
        }
        return $pagination;
    }
    protected function getTemplateVarSortOrders(array $sortOrders, $currentSortOrderURLParameter)
    {
        $sortOrders = parent::getTemplateVarSortOrders($sortOrders, $currentSortOrderURLParameter);
        foreach ($sortOrders as &$order) {
            $order['url'] = $this->updateQueryString(array(
                'order' => $order['urlParameter'],
                'page' => null,
                'from_as4' => $this->getSearchEngine()->id,
            ));
        }
        return $sortOrders;
    }
    protected function getAjaxProductSearchVariables()
    {
        $data = parent::getAjaxProductSearchVariables();
        $data['id_search'] = null;
        $data['remind_selection'] = null;
        if (method_exists($this, 'getIdSeo')) {
            $data['id_seo'] = (int)$this->getIdSeo();
        }
        $searchEngine = $this->getSearchEngine();
        if (Tools::getIsset('with_product') && !Tools::getValue('with_product')) {
            $data['rendered_products_top'] = null;
            $data['rendered_products'] = null;
            $data['rendered_products_bottom'] = null;
        } else {
            if (!empty($searchEngine->redirect_one_product) && $searchEngine->search_method == 2 && !empty($data['products']) && is_array($data['products']) && sizeof($data['products']) == 1) {
                $product = current($data['products']);
                if (!empty($product['url'])) {
                    $data['redirect_to_url'] = $product['url'];
                } elseif (!empty($product['link'])) {
                    $data['redirect_to_url'] = $product['link'];
                }
            }
            if ($searchEngine->search_method == 3) {
                $searchs = As4SearchEngine::getSearch($searchEngine->id, (int)$this->context->language->id);
                $criterions = $this->getCriterionsList();
                $searchs = $this->module->getCriterionsGroupsAndCriterionsForSearch($searchs, (int)$this->context->language->id, $criterions, 0, false);
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
                if ($selectedGroupCount != $realAvailableGroups) {
                    $data['rendered_products_top'] = null;
                    $data['rendered_products'] = null;
                    $data['rendered_products_bottom'] = null;
                }
            }
        }
        if (Validate::isLoadedObject($searchEngine)) {
            $data['id_search'] = $searchEngine->id;
            $data['remind_selection'] = (int)$searchEngine->remind_selection;
        }
        return $data;
    }
    protected function renderFacets(ProductSearchResult $result)
    {
        $this->assignGeneralPurposeVariables();
        $this->module->setSmartyVarsForTpl($this->getSearchEngine(), $this->getCriterionsList());
        return $this->module->display('pm_advancedsearch4.php', 'views/templates/hook/1.7/pm_advancedsearch.tpl');
    }
    protected function renderActiveFilters(ProductSearchResult $result)
    {
        $this->assignGeneralPurposeVariables();
        $this->module->setSmartyVarsForTpl($this->getSearchEngine(), $this->getCriterionsList());
        return $this->module->display('pm_advancedsearch4.php', 'views/templates/hook/1.7/pm_advancedsearch_selection_block.tpl');
    }
}
