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

use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchProviderInterface;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchResult;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrderFactory;
use Symfony\Component\Translation\TranslatorInterface;
if (!defined('_PS_VERSION_')) {
    exit;
}
class As4SearchProvider implements ProductSearchProviderInterface
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
        $this->idSearch = $searchInstance->id;
        $this->criterionsList = $criterionsList;
        $this->sortOrderFactory = new SortOrderFactory($this->translator);
    }
    public function getSortOrders($includeAll = false, $includeDefaultSortOrders = true)
    {
        $config = pm_advancedsearch4::getModuleConfigurationStatic();
        if ($includeDefaultSortOrders) {
            $sortOrders = $this->sortOrderFactory->getDefaultSortOrders();
            if (version_compare(_PS_VERSION_, '1.7.3.1', '>=')) {
                $sortOrders[] = (new SortOrder('product', 'position', 'asc'))->setLabel($this->module->l('Relevance (reverse)', 'as4searchprovider'));
            } else {
                $sortOrders[] = (new SortOrder('product', 'position', 'desc'))->setLabel($this->module->l('Relevance (reverse)', 'as4searchprovider'));
            }
            usort($sortOrders, function ($a, $b) {
                if ($a->getField() == $b->getField()) {
                    if ($a->getDirection() == $b->getDirection()) {
                        return 0;
                    }
                    return ($a->getDirection() < $b->getDirection()) ? -1 : 1;
                }
                return ($a->getField() < $b->getField()) ? -1 : 1;
            });
        } else {
            $sortOrders = array();
        }
        if ($includeAll || !empty($config['sortOrders']['product.sales.asc'])) {
            $sortOrders[] = (new SortOrder('product', 'sales', 'asc'))->setLabel($this->module->l('Sales, Lower first', 'as4searchprovider'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.sales.desc'])) {
            $sortOrders[] = (new SortOrder('product', 'sales', 'desc'))->setLabel($this->module->l('Sales, Highest first', 'as4searchprovider'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.quantity.asc'])) {
            $sortOrders[] = (new SortOrder('product', 'quantity', 'asc'))->setLabel($this->module->l('Quantity, Lower first', 'as4searchprovider'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.quantity.desc'])) {
            $sortOrders[] = (new SortOrder('product', 'quantity', 'desc'))->setLabel($this->module->l('Quantity, Highest first', 'as4searchprovider'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.manufacturer_name.asc'])) {
            $sortOrders[] = (new SortOrder('product', 'manufacturer_name', 'asc'))->setLabel($this->module->l('Brand, A to Z', 'as4searchprovider'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.manufacturer_name.desc'])) {
            $sortOrders[] = (new SortOrder('product', 'manufacturer_name', 'desc'))->setLabel($this->module->l('Brand, Z to A', 'as4searchprovider'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.date_add.desc'])) {
            $sortOrders[] = (new SortOrder('product', 'date_add', 'desc'))->setLabel($this->module->l('New products first', 'as4searchprovider'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.date_add.asc'])) {
            $sortOrders[] = (new SortOrder('product', 'date_add', 'asc'))->setLabel($this->module->l('Old products first', 'as4searchprovider'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.date_upd.desc'])) {
            $sortOrders[] = (new SortOrder('product', 'date_upd', 'desc'))->setLabel($this->module->l('Latest updated products first', 'as4searchprovider'));
        }
        if ($includeAll || !empty($config['sortOrders']['product.date_upd.asc'])) {
            $sortOrders[] = (new SortOrder('product', 'date_upd', 'asc'))->setLabel($this->module->l('Oldest updated products first', 'as4searchprovider'));
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
            $currentSearchEngine = new AdvancedSearchClass($this->idSearch);
            if ((Tools::getIsset('order') || Tools::getIsset('orderby')) && $query->getSortOrder() != null) {
                $defaultSortOrder = As4SearchEngine::getOrderByValue($currentSearchEngine, $query);
                $defaultSortWay = As4SearchEngine::getOrderWayValue($currentSearchEngine, $query);
            } else {
                $defaultSortOrder = As4SearchEngine::getOrderByValue($currentSearchEngine);
                $defaultSortWay = As4SearchEngine::getOrderWayValue($currentSearchEngine);
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
        $nbProducts = As4SearchEngine::getProductsSearched(
            $this->idSearch,
            $this->criterionsList,
            As4SearchEngine::getCriterionGroupsTypeAndDisplay($this->idSearch, array_keys($this->criterionsList)),
            null,
            null,
            true,
            $query
        );
        $products = As4SearchEngine::getProductsSearched(
            $this->idSearch,
            $this->criterionsList,
            As4SearchEngine::getCriterionGroupsTypeAndDisplay($this->idSearch, array_keys($this->criterionsList)),
            (int)$query->getPage(),
            (int)$query->getResultsPerPage(),
            false,
            $query
        );
        $result->setProducts($products);
        $result->setTotalProductsCount($nbProducts);
        return $result;
    }
}
