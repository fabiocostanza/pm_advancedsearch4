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

use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchProviderInterface;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchResult;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrderFactory;
use Symfony\Component\Translation\TranslatorInterface;
if (!defined('_PS_VERSION_')) {
    exit;
}
class As4FullTreeSearchProvider implements ProductSearchProviderInterface
{
    private $module;
    private $translator;
    private $sortOrderFactory;
    public function __construct(PM_AdvancedSearch4 $module, TranslatorInterface $translator)
    {
        $this->module = $module;
        $this->translator = $translator;
        $this->sortOrderFactory = new SortOrderFactory($this->translator);
    }
    public function runQuery(
        ProductSearchContext $context,
        ProductSearchQuery $query
    ) {
        $result = new ProductSearchResult();
        $result->setAvailableSortOrders(
            $this->sortOrderFactory->getDefaultSortOrders()
        );
        $continue = true;
        $realContext = Context::getContext();
        if (As4SearchEngine::isSPAModuleActive()) {
            $pm_productsbyattributes = Module::getInstanceByName('pm_productsbyattributes');
            if (version_compare($pm_productsbyattributes->version, '1.0.4', '>=')) {
                $continue = false;
                $productCount = $pm_productsbyattributes->getCategoryProducts((int)$realContext->controller->getCategory()->id_category, null, null, null, $query->getSortOrder()->toLegacyOrderBy(), $query->getSortOrder()->toLegacyOrderWay(), true, true);
                $productList = $pm_productsbyattributes->getCategoryProducts((int)$realContext->controller->getCategory()->id_category, (int)$context->getIdLang(), (int)$query->getPage(), (int)$query->getResultsPerPage(), $query->getSortOrder()->toLegacyOrderBy(), $query->getSortOrder()->toLegacyOrderWay(), false, true);
                $result->setTotalProductsCount($productCount);
                $result->setProducts($productList);
                $pm_productsbyattributes->splitProductsList($productList);
            }
        }
        if ($continue) {
            $result->setTotalProductsCount($this->module->getCategoryProducts(null, null, null, $query->getSortOrder()->toLegacyOrderBy(), $query->getSortOrder()->toLegacyOrderWay(), true));
            $result->setProducts($this->module->getCategoryProducts((int)$context->getIdLang(), (int)$query->getPage(), (int)$query->getResultsPerPage(), $query->getSortOrder()->toLegacyOrderBy(), $query->getSortOrder()->toLegacyOrderWay()));
        }
        return $result;
    }
}
