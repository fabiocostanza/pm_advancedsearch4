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

namespace AdvancedSearch\SearchProvider;
use Tools;
use PM_AdvancedSearch4;
use AdvancedSearch\Models\Search;
use AdvancedSearch\SearchEngineUtils;
use Symfony\Component\Translation\TranslatorInterface;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrderFactory;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchResult;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchProviderInterface;
if (!defined('_PS_VERSION_')) {
    exit;
}
class Facets implements ProductSearchProviderInterface
{
    private $module;
    private $translator;
    private $sortOrderFactory;
    private $idSearch;
    private $criterionsList;
    private $searchInstance;
    public function __construct(PM_AdvancedSearch4 $module, TranslatorInterface $translator, $searchInstance, $criterionsList)
    {
        $this->module = $module;
        $this->translator = $translator;
        $this->searchInstance = $searchInstance;
        $this->idSearch = is_object($searchInstance) ? $searchInstance->id : null;
        $this->criterionsList = $criterionsList;
        $this->sortOrderFactory = new SortOrderFactory($this->translator);
    }
    public function getSortOrders($includeAll = false, $includeDefaultSortOrders = true)
    {
        $config = PM_AdvancedSearch4::getModuleConfigurationStatic();
        $sortOrders = array();
        if ($includeDefaultSortOrders) {
            $sortOrders = $this->sortOrderFactory->getDefaultSortOrders();
        }
        if ($includeAll ||
            ((version_compare(_PS_VERSION_, '1.7.3.1', '>=') && !empty($config['sortOrders']['product.position.asc']))
            || (version_compare(_PS_VERSION_, '1.7.3.1', '<') && !empty($config['sortOrders']['product.position.desc'])))
        ) {
            if (version_compare(_PS_VERSION_, '1.7.3.1', '>=')) {
                $sortOrders[] = (new SortOrder('product', 'position', 'asc'))->setLabel($this->module->l('Relevance (reverse)', 'facets'));
            } else {
                $sortOrders[] = (new SortOrder('product', 'position', 'desc'))->setLabel($this->module->l('Relevance (reverse)', 'facets'));
            }
        }
        if ($includeDefaultSortOrders) {
            usort($sortOrders, function ($a, $b) {
                if ($a->getField() == $b->getField()) {
                    if ($a->getDirection() == $b->getDirection()) {
                        return 0;
                    }
                    return ($a->getDirection() > $b->getDirection()) ? -1 : 1;
                }
                return ($a->getField() > $b->getField()) ? -1 : 1;
            });
        }
        if ($includeAll || !empty($config['sortOrders']['product.sales.asc'])) {
            $sortOrders[] = (new SortOrder('product', 'sales', 'asc'))->setLabel($this->module->l('Sales, Lower first', 'facets'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.sales.desc'])) {
            $sortOrders[] = (new SortOrder('product', 'sales', 'desc'))->setLabel($this->module->l('Sales, Highest first', 'facets'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.quantity.asc'])) {
            $sortOrders[] = (new SortOrder('product', 'quantity', 'asc'))->setLabel($this->module->l('Quantity, Lower first', 'facets'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.quantity.desc'])) {
            $sortOrders[] = (new SortOrder('product', 'quantity', 'desc'))->setLabel($this->module->l('Quantity, Highest first', 'facets'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.manufacturer_name.asc'])) {
            $sortOrders[] = (new SortOrder('product', 'manufacturer_name', 'asc'))->setLabel($this->module->l('Brand, A to Z', 'facets'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.manufacturer_name.desc'])) {
            $sortOrders[] = (new SortOrder('product', 'manufacturer_name', 'desc'))->setLabel($this->module->l('Brand, Z to A', 'facets'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.date_add.desc'])) {
            $sortOrders[] = (new SortOrder('product', 'date_add', 'desc'))->setLabel($this->module->l('New products first', 'facets'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.date_add.asc'])) {
            $sortOrders[] = (new SortOrder('product', 'date_add', 'asc'))->setLabel($this->module->l('Old products first', 'facets'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.date_upd.desc'])) {
            $sortOrders[] = (new SortOrder('product', 'date_upd', 'desc'))->setLabel($this->module->l('Latest updated products first', 'facets'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.date_upd.asc'])) {
            $sortOrders[] = (new SortOrder('product', 'date_upd', 'asc'))->setLabel($this->module->l('Oldest updated products first', 'facets'));
        }
        return $sortOrders;
    }
    public function runQuery(
        ProductSearchContext $context,
        ProductSearchQuery $query
    ) {
        $query->setResultsPerPage((int)Tools::getValue('resultsPerPage', $this->searchInstance->products_per_page));
        $result = new ProductSearchResult();
        $sortOrders = $this->getSortOrders();
        $result->setAvailableSortOrders(
            $sortOrders
        );
        if (!$result->getCurrentSortOrder()) {
            $currentSearchEngine = new Search($this->idSearch);
            if ((Tools::getIsset('order') || Tools::getIsset('orderby')) && $query->getSortOrder() != null) {
                $defaultSortOrder = SearchEngineUtils::getOrderByValue($currentSearchEngine, $query);
                $defaultSortWay = SearchEngineUtils::getOrderWayValue($currentSearchEngine, $query);
            } else {
                $defaultSortOrder = SearchEngineUtils::getOrderByValue($currentSearchEngine);
                $defaultSortWay = SearchEngineUtils::getOrderWayValue($currentSearchEngine);
            }
            $sortOrderSet = false;
            foreach ($sortOrders as $sortOrder) {
                if ($sortOrder->getField() == $defaultSortOrder) {
                    if ($sortOrder->getDirection() == $defaultSortWay) {
                        $sortOrderSet = true;
                        $query->setSortOrder($sortOrder);
                        break;
                    }
                }
            }
        }
        $nbProducts = SearchEngineUtils::getProductsSearched(
            $this->idSearch,
            $this->criterionsList,
            SearchEngineUtils::getCriterionGroupsTypeAndDisplay($this->idSearch, array_keys($this->criterionsList)),
            null,
            null,
            true,
            $query
        );
        $result->setTotalProductsCount($nbProducts);
        $withProducts = (!Tools::getIsset('with_product') || Tools::getValue('with_product')) || SearchEngineUtils::lastCriterionStepSelected($this->searchInstance, $this->criterionsList);
        if ($withProducts) {
            $products = SearchEngineUtils::getProductsSearched(
                $this->idSearch,
                $this->criterionsList,
                SearchEngineUtils::getCriterionGroupsTypeAndDisplay($this->idSearch, array_keys($this->criterionsList)),
                (int)$query->getPage(),
                (int)$query->getResultsPerPage(),
                false,
                $query
            );
            $result->setProducts($products);
        }
        return $result;
    }
}
