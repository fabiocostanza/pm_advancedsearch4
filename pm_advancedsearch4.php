<?php
/**
 * Advanced Search 5 Pro
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2022 - http://www.presta-module.com
 * @license   see file: LICENSE.txt
 * @version   5.0.2
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
// Define a constant containing the current module name to avoid hardcoded "pm_xxxxx" strings
if (!defined('_PM_AS_MODULE_NAME_')) {
    define('_PM_AS_MODULE_NAME_', basename(__DIR__));
}
require_once dirname(__FILE__).'/vendor/autoload.php';
use AdvancedSearch\Core;
use AdvancedSearch\Models\Seo;
use AdvancedSearch\Models\Search;
use AdvancedSearch\SearchEngineDb;
use AdvancedSearch\Models\Criterion;
use AdvancedSearch\SearchEngineUtils;
use AdvancedSearch\Models\CriterionGroup;
use AdvancedSearch\SearchProvider\Facets;
use AdvancedSearch\SearchEngineIndexation;
use AdvancedSearch\SearchProvider\FullTree;
use AdvancedSearch\Traits\SupportsSeoPages;
use AdvancedSearch\Traits\SupportsStepSearch;
use AdvancedSearch\Traits\SupportsImageCriterionGroup;
class PM_AdvancedSearch4 extends Core implements PrestaShop\PrestaShop\Core\Module\WidgetInterface
{
    use SupportsSeoPages;
    use SupportsStepSearch;
    use SupportsImageCriterionGroup;
    use \AdvancedSearch\Traits\SeoTrait;
    use \AdvancedSearch\Traits\StepSearchTrait;
    use \AdvancedSearch\Traits\ImageCriterionGroupTrait;
    protected $errors = array();
    private $options_show_hide_crit_method;
    private $options_launch_search_method;
    private $options_defaut_order_by;
    private $options_defaut_order_way;
    private $options_criteria_group_type;
    protected $allowFileExtension = [
        'gif',
        'jpg',
        'jpeg',
        'png',
        'svg',
    ];
    private $sortableCriterion = array('attribute', 'feature', 'manufacturer', 'supplier', 'category', 'subcategory', 'weight', 'width', 'height', 'depth', 'condition');
    private $originalPositionSortableCriterion = array('attribute', 'category', 'subcategory');
    private $criteriaGroupLabels;
    private $criterionGroupIsTemplatisable = array('attribute', 'feature', 'manufacturer', 'supplier', 'category' );
    protected $criteria_group_type_interal_name = array(
        1 => 'select',
        2 => 'image',
        3 => 'link',
        4 => 'checkbox',
        5 => 'slider',
        7 => 'colorsquare',
        8 => 'range',
        9 => 'level_depth',
    );
    private $display_vertical_search_block = array();
    public static $modulePrefix = 'as4';
    protected $defaultConfiguration = array(
        'fullTree' => true,
        'autoReindex' => true,
        'autoSyncActiveStatus' => true,
        'moduleCache' => false,
        'maintenanceMode' => false,
        'blurEffect' => true,
        'sortOrders' => array(),
        'mobileVisible' => false,
    );
    protected $copyright_link = array(
        'link'    => '',
        'img'    => '//www.presta-module.com/img/logo-module.JPG'
    );
    protected $support_link = false;
    protected $cssJsToLoad = array(
        'core',
        'plupload',
        'codemirrorcore',
        'codemirrorcss',
        'datatables',
        'colorpicker',
        'jgrowl',
        'multiselect',
        'tiny_mce',
        'form',
    );
    protected $fileToCheck = array(
        'views/css',
        'views/css/pm_advancedsearch4_dynamic.css',
        'search_files',
        'search_files/criterions',
        'search_files/criterions_group',
        'uploads/temp',
    );
    public $templatePrefix = '';
    const INSTALL_SQL_BASE_FILE = 'install_base.sql';
    const INSTALL_SQL_DYN_FILE = 'install_dyn.sql';
    const DYN_CSS_FILE = 'views/css/pm_advancedsearch4_dynamic.css';
    const ADVANCED_CSS_FILE = 'views/css/pm_advancedsearch4_advanced.css';
    public function __construct()
    {
        $this->name = 'pm_advancedsearch4';
        $this->author = 'Presta-Module';
        $this->tab = 'search_filter';
        $this->need_instance = 0;
        $this->module_key = 'e0578dd1826016f7acb8045ad15372b4';
        $this->version = '5.0.2';
        $this->bootstrap = true;
        $this->ps_versions_compliancy['min'] = '1.7.4.0';
        $this->controllers = array(
            'advancedsearch4',
            'searchresults',
        );
        if (self::supportsSeoPages()) {
            $this->controllers[] = 'seo';
        }
        parent::__construct();
        $this->templatePrefix = Tools::substr(_PS_VERSION_, 0, 3) . '/';
        $this->registerFrontSmartyObjects();
        if ($this->onBackOffice()) {
            if (Configuration::get('PM_' . self::$modulePrefix . '_UPDATE_THEME')) {
                Configuration::updateValue('PM_' . self::$modulePrefix . '_UPDATE_THEME', 0);
                $this->registerToAllHooks();
                $this->updateModulesHooksPositions();
                $this->createOrUpdateAllControllers(true);
            }
            $this->displayName = 'Advanced Search 5 Pro';
            $this->description = $this->l('Install one or more search engines by filters, wherever you want on your store. Improve the customer experience with ultra-customizable multi-criteria filters. The pro version also allows you to generate bulk SEO pages (faceted search) and offer a step-by-step search.');
            $this->options_show_hide_crit_method = array(
                1 => $this->l('On mouse over'),
                2 => $this->l('On click'),
                3 => $this->l('In an overflow block'),
            );
            $this->options_launch_search_method = array(
                1 => $this->l('Instant search (upon change)'),
                2 => $this->l('Search by submission (after clicking on a button)'),
                4 => $this->l('Search by submission (after clicking on a button + redirection to a page dedicated to the results)'),
                3 => $this->l('When the last criterion is selected'),
            );
            $this->options_defaut_order_by = array(
                0 => $this->l('Name'),
                1 => $this->l('Price'),
                4 => $this->l('Position inside category'),
                5 => $this->l('Brand'),
                6 => $this->l('Quantity'),
                2 => $this->l('Added date') .' ('.$this->l('Recommended for large catalogs').')',
                3 => $this->l('Modified date').' ('.$this->l('Recommended for large catalogs').')',
                8 => $this->l('Sales'),
            );
            $this->options_defaut_order_way = array(
                0 => $this->l('Ascending'),
                1 => $this->l('Descending'),
            );
            $this->options_criteria_group_type = array(
                1 => $this->l('Drop-down menu'),
                3 => $this->l('Links'),
                4 => $this->l('Checkboxes'),
                5 => $this->l('Slider'),
                8 => $this->l('Numerical range'),
                //6 => $this->l('Search box'),
                2 => $this->l('Images') . (!self::supportsImageCriterionGroup() ? ' ' . $this->l('(PRO)') : ''),
            );
            $doc_url_tab = array();
            $doc_url_tab['fr'] = '#/fr/advancedsearch4/';
            $doc_url_tab['en'] = '#/en/advancedsearch4/';
            $doc_url = $doc_url_tab['en'];
            if ($this->isoLang == 'fr') {
                $doc_url = $doc_url_tab['fr'];
            }
            $forum_url_tab = array();
            $forum_url_tab['fr'] = 'http://www.prestashop.com/forums/topic/113804-module-pm-advanced-search-4-elu-meilleur-module-2012/';
            $forum_url_tab['en'] = 'http://www.prestashop.com/forums/topic/113831-module-pm-advancedsearch-4-winner-at-the-best-module-awards-2012/';
            $forum_url = $forum_url_tab['en'];
            if ($this->isoLang == 'fr') {
                $forum_url = $forum_url_tab['fr'];
            }
            $this->support_link = array(
                array('link' => $forum_url, 'target' => '_blank', 'label' => $this->l('Forum topic')),
                
                array('link' => 'https://addons.prestashop.com/contact-form.php?id_product=27782778', 'target' => '_blank', 'label' => $this->l('Support contact')),
            );
            $this->display_vertical_search_block = array();
            foreach (array('displayLeftColumn', 'displayRightColumn') as $hookName) {
                if (Hook::getIdByName($hookName) !== false) {
                    $this->display_vertical_search_block[] = Hook::getIdByName($hookName);
                }
            }
            $this->criteriaGroupLabels = array(
                'category' => $this->l('category'),
                'feature' => $this->l('feature'),
                'attribute' => $this->l('attribute'),
                'supplier' => $this->l('supplier'),
                'manufacturer' => $this->l('brand'),
                'price' => $this->l('price'),
                'weight' => $this->l('product properties'),
                'on_sale' => $this->l('product properties'),
                'stock' => $this->l('product properties'),
                'available_for_order' => $this->l('product properties'),
                'online_only' => $this->l('product properties'),
                'condition' => $this->l('product properties'),
                'width' => $this->l('product properties'),
                'height' => $this->l('product properties'),
                'depth' => $this->l('product properties'),
                'pack' => $this->l('product properties'),
                'subscription' => $this->l('product properties'),
                'new_products' => $this->l('product properties'),
                'prices_drop' => $this->l('product properties'),
                'subcategory' => $this->l('category')
            );
        }
        register_shutdown_function(array($this, 'customShutdownProcess'));
    }
    private static $idProductToAdd = array();
    private static $idProductToUpdate = array();
    private static $idProductToDelete = array();
    public function customShutdownProcess()
    {
        self::$idProductToAdd = array_unique(self::$idProductToAdd);
        self::$idProductToUpdate = array_unique(self::$idProductToUpdate);
        self::$idProductToDelete = array_unique(self::$idProductToDelete);
        SearchEngineIndexation::$processingAutoReindex = true;
        foreach (self::$idProductToAdd as $idProduct) {
            $product = new Product((int)$idProduct);
            if (Validate::isLoadedObject($product)) {
                SearchEngineIndexation::indexCriterionsFromProduct($product, true);
            }
        }
        foreach (self::$idProductToUpdate as $idProduct) {
            $product = new Product((int)$idProduct);
            if (Validate::isLoadedObject($product)) {
                SearchEngineIndexation::indexCriterionsFromProduct($product);
            }
        }
        foreach (self::$idProductToDelete as $idProduct) {
            $product = new Product((int)$idProduct);
            if (Validate::isLoadedObject($product)) {
                SearchEngineIndexation::desIndexCriterionsFromProduct($product->id);
            }
        }
        SearchEngineIndexation::$processingAutoReindex = false;
    }
    public function hookActionMetaPageSave($params)
    {
        Db::getInstance()->delete('configuration', '`name` LIKE "PS_ROUTE_module-pm_advancedsearch%"');
    }
    public function hookModuleRoutes()
    {
        $searchResultsCategoryPrefix = '{id}-{rewrite}';
        $searchResultsSupplierPrefix = 'supplier/{id}-{rewrite}';
        $searchResultsManufacturerPrefix = 'brand/{id}-{rewrite}';
        $searchResultsCmsPrefix = 'content/{id}-{rewrite}';
        if (version_compare(_PS_VERSION_, '1.7.5.0', '<')) {
            $searchResultsSupplierPrefix = '{id}__{rewrite}';
            $searchResultsManufacturerPrefix = '{id}_{rewrite}';
        }
        $categoryRoute = Configuration::get('PS_ROUTE_category_rule', null, null, $this->context->shop->id);
        $supplierRoute = Configuration::get('PS_ROUTE_supplier_rule', null, null, $this->context->shop->id);
        $manufacturerRoute = Configuration::get('PS_ROUTE_manufacturer_rule', null, null, $this->context->shop->id);
        $cmsRoute = Configuration::get('PS_ROUTE_cms_rule', null, null, $this->context->shop->id);
        if (!empty($categoryRoute) && preg_match('/{id}/', $categoryRoute)) {
            $searchResultsCategoryPrefix = $categoryRoute;
        }
        if (!empty($supplierRoute) && preg_match('/{id}/', $supplierRoute)) {
            $searchResultsSupplierPrefix = $supplierRoute;
        }
        if (!empty($manufacturerRoute) && preg_match('/{id}/', $manufacturerRoute)) {
            $searchResultsManufacturerPrefix = $manufacturerRoute;
        }
        if (!empty($cmsRoute) && preg_match('/{id}/', $cmsRoute)) {
            $searchResultsCmsPrefix = $cmsRoute;
        }
        $as4SqRegeXp = '[_a-zA-Z0-9\x{0600}-\x{06FF}\pL\pS/.:+-]*';
        $defaultRewritePattern = '[_a-zA-Z0-9\pL\pS-]*';
        if (defined('Dispatcher::REWRITE_PATTERN')) {
            $defaultRewritePattern = Dispatcher::REWRITE_PATTERN;
        }
        $routes = array(
            'module-pm_advancedsearch4-cron' => array(
                'controller' => 'cron',
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                )
            ),
            'module-pm_advancedsearch4-searchresults' => array(
                'controller' => 'searchresults',
                'rule' => 's-{id_search}/{as4_sq}',
                'keywords' => array(
                    'id_search' => array('regexp' => '[0-9]+', 'param' => 'id_search'),
                    'as4_sq' => array('regexp' => $as4SqRegeXp, 'param' => 'as4_sq'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                )
            ),
            'module-pm_advancedsearch4-searchresults-categories' => array(
                'controller' => 'searchresults',
                'rule' => $searchResultsCategoryPrefix . '/s-{id_search}/{as4_sq}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_category_search'),
                    'id_search' => array('regexp' => '[0-9]+', 'param' => 'id_search'),
                    'as4_sq' => array('regexp' => $as4SqRegeXp, 'param' => 'as4_sq'),
                    'rewrite' => array('regexp' => $defaultRewritePattern),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                    'as4_from' => 'category',
                )
            ),
            'module-pm_advancedsearch4-searchresults-suppliers' => array(
                'controller' => 'searchresults',
                'rule' => $searchResultsSupplierPrefix . '/s-{id_search}/{as4_sq}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_supplier_search'),
                    'id_search' => array('regexp' => '[0-9]+', 'param' => 'id_search'),
                    'as4_sq' => array('regexp' => $as4SqRegeXp, 'param' => 'as4_sq'),
                    'rewrite' => array('regexp' => $defaultRewritePattern),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                    'as4_from' => 'supplier',
                )
            ),
            'module-pm_advancedsearch4-searchresults-manufacturers' => array(
                'controller' => 'searchresults',
                'rule' => $searchResultsManufacturerPrefix . '/s-{id_search}/{as4_sq}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_manufacturer_search'),
                    'id_search' => array('regexp' => '[0-9]+', 'param' => 'id_search'),
                    'as4_sq' => array('regexp' => $as4SqRegeXp, 'param' => 'as4_sq'),
                    'rewrite' => array('regexp' => $defaultRewritePattern),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                    'as4_from' => 'manufacturer',
                )
            ),
            'module-pm_advancedsearch4-searchresults-cms' => array(
                'controller' => 'searchresults',
                'rule' => $searchResultsCmsPrefix . '/s-{id_search}/{as4_sq}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_cms_search'),
                    'id_search' => array('regexp' => '[0-9]+', 'param' => 'id_search'),
                    'as4_sq' => array('regexp' => $as4SqRegeXp, 'param' => 'as4_sq'),
                    'rewrite' => array('regexp' => $defaultRewritePattern),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                    'as4_from' => 'cms',
                ),
            ),
            'layered_rule' => array(
                'controller' => '404',
                'rule' => 'disablethisrule-'.uniqid(),
                'keywords' => array(),
                'params' => array()
            ),
        );
        if (self::supportsSeoPages()) {
            $seoRoutes = $this->getSeoModuleRoutes();
            return array_merge($seoRoutes, $routes);
        }
        return $routes;
    }
    protected function registerToAllHooks()
    {
        $valid_hooks = SearchEngineUtils::$valid_hooks;
        foreach ($valid_hooks as $hook_name) {
            if (!$this->registerHook($hook_name)) {
                return false;
            }
        }
        if (!$this->registerHook('moduleRoutes') || !$this->registerHook('displayHeader') || !$this->registerHook('updateProduct') || !$this->registerHook('addProduct') || !$this->registerHook('deleteProduct')) {
            return false;
        }
        if (!$this->registerHook('actionAdminProductsControllerSaveAfter')) {
            return false;
        }
        if (!$this->registerHook('actionObjectAddAfter') || !$this->registerHook('actionObjectUpdateAfter') || !$this->registerHook('actionObjectDeleteAfter')) {
            return false;
        }
        if (!$this->registerHook('actionObjectLanguageAddAfter')) {
            return false;
        }
        if (!$this->registerHook('displayBeforeBodyClosingTag')) {
            return false;
        }
        if (!$this->registerHook('actionAdminProductsControllerCoreSaveAfter')) {
            return false;
        }
        if (!$this->registerHook('actionShopDataDuplication') && !$this->registerHook('actionObjectShopDeleteAfter')) {
            return false;
        }
        if (!$this->registerHook('actionMetaPageSave')) {
            return false;
        }
        return true;
    }
    protected function updateModulesHooksPositions()
    {
        $hookList = array(
            'displayLeftColumn',
            'displayRightColumn',
            'productSearchProvider',
        );
        foreach ($hookList as $hookName) {
            $idHook = Hook::getIdByName($hookName);
            if ($idHook) {
                foreach (Shop::getContextListShopID() as $idShop) {
                    Db::getInstance()->execute('
                        UPDATE `' . _DB_PREFIX_ . 'hook_module`
                        SET `position`=0
                        WHERE `id_module` = ' . (int)$this->id . '
                        AND `id_hook` = ' . (int)$idHook . '
                        AND `id_shop` = ' . (int)$idShop);
                }
                $this->cleanPositions($idHook, Shop::getContextListShopID());
            }
        }
    }
    protected function createOrUpdateAllControllers($forceSetLayout = false)
    {
        if (method_exists($this, 'installControllers') && is_callable(array($this, 'installControllers'))) {
            $this->installControllers();
            if ($forceSetLayout) {
                $this->updateControllersLayoutSettings(array('cron', 'seositemap'));
            }
        }
    }
    public function install()
    {
        if (!parent::install() || !$this->installDB()) {
            return false;
        }
        if (!$this->registerToAllHooks()) {
            return false;
        }
        $this->checkIfModuleIsUpdate(true, false);
        return true;
    }
    public function installDB()
    {
        if (!Tools::file_exists_cache(dirname(__FILE__) . '/sql/' . self::INSTALL_SQL_BASE_FILE)) {
            return false;
        } elseif (!$sql = Tools::file_get_contents(dirname(__FILE__) . '/sql/' . self::INSTALL_SQL_BASE_FILE)) {
            return false;
        }
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('MYSQL_ENGINE', _MYSQL_ENGINE_, $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);
        foreach ($sql as $query) {
            if (empty(trim($query))) {
                continue;
            }
            if (!SearchEngineDb::execute(trim($query))) {
                return false;
            }
        }
        return true;
    }
    public function installDBCache($id_search, $with_drop = true)
    {
        if (!Tools::file_exists_cache(dirname(__FILE__) . '/sql/' . self::INSTALL_SQL_DYN_FILE)) {
            return false;
        } elseif (!$sql = Tools::file_get_contents(dirname(__FILE__) . '/sql/' . self::INSTALL_SQL_DYN_FILE)) {
            return false;
        }
        $sql = str_replace('ID_SEARCH', $id_search, $sql);
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('MYSQL_ENGINE', _MYSQL_ENGINE_, $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);
        foreach ($sql as $query) {
            if (empty(trim($query))) {
                continue;
            }
            if (!$with_drop && preg_match('#^DROP#i', trim($query))) {
                continue;
            }
            if (!SearchEngineDb::execute(trim($query))) {
                return false;
            }
        }
        return true;
    }
    protected function updateControllersLayoutSettings($ignoreList = array())
    {
        $themeRepository = (new PrestaShop\PrestaShop\Core\Addon\Theme\ThemeManagerBuilder($this->context, Db::getInstance()))->buildRepository();
        $themeManager = (new PrestaShop\PrestaShop\Core\Addon\Theme\ThemeManagerBuilder($this->context, Db::getInstance()))->build();
        $theme = $themeRepository->getInstanceByName($this->context->shop->theme->getName());
        $defaultCategoryLayout = $theme->getLayoutNameForPage('category');
        $currentThemeLayouts = $theme->getPageLayouts();
        if (empty($currentThemeLayouts) || empty($defaultCategoryLayout)) {
            return;
        }
        foreach ($this->controllers as $controllerName) {
            if (in_array($controllerName, $ignoreList)) {
                continue;
            }
            $completeControllerName = Tools::strtolower('module-' . $this->name . '-' . $controllerName);
            $currentThemeLayouts[$completeControllerName] = $defaultCategoryLayout;
        }
        $this->context->shop->theme->setPageLayouts($currentThemeLayouts);
        $themeManager->saveTheme($this->context->shop->theme);
    }
    public function checkIfModuleIsUpdate($updateDb = false, $displayConfirm = true)
    {
        $isUpdate = true;
        $firstInstall = false;
        $currentModuleLastVersion = Configuration::get('PM_' . self::$modulePrefix . '_LAST_VERSION');
        if (empty($currentModuleLastVersion)) {
            $firstInstall = true;
            $this->updateModulesHooksPositions();
        }
        if (Configuration::get('AS4_LAST_VERSION', false) !== false && Configuration::get('PM_' . self::$modulePrefix . '_LAST_VERSION', false) === false) {
            Configuration::updateValue('PM_' . self::$modulePrefix . '_LAST_VERSION', Configuration::get('AS4_LAST_VERSION', false));
            Configuration::deleteByName('AS4_LAST_VERSION');
        }
        if (!$updateDb && $this->version != Configuration::get('PM_' . self::$modulePrefix . '_LAST_VERSION', false)) {
            return false;
        }
        if ($updateDb) {
            $oldModuleVersion = Configuration::get('PM_' . self::$modulePrefix . '_LAST_VERSION', false);
            $this->createOrUpdateAllControllers($firstInstall);
            $newHooksList = array(
                'actionAdminProductsControllerSaveAfter',
                'moduleRoutes',
                'actionObjectAddAfter',
                'actionObjectUpdateAfter',
                'actionObjectDeleteAfter',
                'actionObjectLanguageAddAfter',
                'displayAdvancedSearch4',
                'actionShopDataDuplication',
                'actionObjectShopDeleteAfter',
                'displayBeforeBodyClosingTag',
                'productSearchProvider',
                'displayNavFullWidth',
                'actionAdminProductsControllerCoreSaveAfter',
            );
            foreach ($newHooksList as $newHookName) {
                if (!$this->isRegisteredInHook($newHookName)) {
                    $this->registerHook($newHookName);
                }
            }
            Configuration::updateValue('PM_' . self::$modulePrefix . '_LAST_VERSION', $this->version);
            if (!Configuration::getGlobalValue('PM_AS4_SECURE_KEY')) {
                Configuration::updateGlobalValue('PM_AS4_SECURE_KEY', Tools::strtoupper(Tools::passwdGen(16)));
            }
            if (!SearchEngineUtils::getLocalStorageCacheKey()) {
                SearchEngineUtils::setLocalStorageCacheKey();
            }
            $this->installDB();
            $config = $this->getModuleConfiguration();
            if (version_compare($currentModuleLastVersion, '5.0.0', '<') && version_compare($this->version, '5.0.0', '>=') && !$firstInstall) {
                if (!isset($config['maintenanceMode'])) {
                    $config['maintenanceMode'] = (bool)Configuration::get('PM_' . self::$modulePrefix . '_MAINTENANCE');
                }
            }
            foreach ($this->defaultConfiguration as $configKey => $configValue) {
                if (!isset($config[$configKey])) {
                    $config[$configKey] = $configValue;
                }
            }
            $this->setModuleConfiguration($config);
            $this->updateSearchTable($oldModuleVersion);
            if (self::supportsSeoPages()) {
                $this->updateSeoPagesCriteria($oldModuleVersion);
            }
            $this->generateCss();
            $this->pmClearCache();
            if ($displayConfirm) {
                $this->context->controller->confirmations[] = $this->l('Module updated successfully');
            }
        }
        return $isUpdate;
    }
    public function updateSearchTable($oldModuleVersion = null)
    {
        $advancedSearchIds = SearchEngineUtils::getSearchsId(false, false, false);
        $toAdd = array();
        $toChange = array();
        $toRemove = array();
        $indexToAdd = array();
        $primaryToAdd = array();
        if (self::supportsSeoPages()) {
            $toAdd[] = [
                'pm_advancedsearch_seo_lang',
                'footer_description',
                'text NOT NULL',
                'description',
            ];
        }
        foreach ($advancedSearchIds as $idSearch) {
            $toRemove[] = [
                'pm_advancedsearch_product_price_'.(int)$idSearch,
                'id_criterion_group',
            ];
        }
        if (is_array($toAdd) && count($toAdd)) {
            foreach ($toAdd as $infos) {
                $this->columnExists($infos[0], $infos[1], true, $infos[2], (isset($infos[3]) ? $infos[3] : false));
            }
        }
        if (is_array($toChange) && count($toChange)) {
            foreach ($toChange as $infos) {
                $resultset = SearchEngineDb::queryNoCache("SHOW COLUMNS FROM `" . bqSQL(_DB_PREFIX_ . $infos[0]) . "` WHERE `Field` = '" . pSQL($infos[1]) . "'");
                foreach ($resultset as $row) {
                    if ($row['Type'] != $infos[2]) {
                        SearchEngineDb::execute('ALTER TABLE `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` CHANGE `' . bqSQL($infos[1]) . '` `' . bqSQL($infos[1]) . '` ' . $infos[2] . '');
                    }
                }
            }
        }
        if (is_array($indexToAdd) && count($indexToAdd)) {
            foreach ($indexToAdd as $infos) {
                $result = SearchEngineDb::queryNoCache('SHOW INDEX FROM `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` WHERE `Key_name` = "'.pSQL($infos[1]).'"');
                if (!self::isFilledArray($result)) {
                    SearchEngineDb::execute('ALTER TABLE  `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` ADD INDEX `' . bqSQL($infos[1]) . '` ( '.$infos[2].' )');
                }
            }
        }
        if (is_array($primaryToAdd) && count($primaryToAdd)) {
            foreach ($primaryToAdd as $infos) {
                $result = SearchEngineDb::queryNoCache('SHOW INDEX FROM `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` WHERE `Key_name` = "PRIMARY"');
                if (!self::isFilledArray($result)) {
                    if (isset($infos[2])) {
                        $result = SearchEngineDb::queryNoCache('SHOW INDEX FROM `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` WHERE `column_name` = "'.pSQL($infos[2]).'"');
                        if (self::isFilledArray($result)) {
                            SearchEngineDb::execute('ALTER TABLE  `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` DROP INDEX `'.bqSQL($infos[2]).'`');
                        }
                    }
                    SearchEngineDb::execute('ALTER TABLE  `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` ADD PRIMARY KEY ('.$infos[1].')');
                }
            }
        }
        if (is_array($toRemove) && count($toRemove)) {
            foreach ($toRemove as $infos) {
                if ($this->columnExists($infos[0], $infos[1])) {
                    SearchEngineDb::execute('ALTER TABLE `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` DROP `'.bqSQL($infos[1]).'`');
                }
            }
        }
    }
    protected function columnExists($table, $column, $createIfNotExist = false, $type = false, $insertAfter = false)
    {
        $resultset = SearchEngineDb::queryNoCache("SHOW COLUMNS FROM `" . bqSQL(_DB_PREFIX_ . $table) . "`");
        foreach ($resultset as $row) {
            if ($row['Field'] == $column) {
                return true;
            }
        }
        if ($createIfNotExist && SearchEngineDb::execute('ALTER TABLE `' . bqSQL(_DB_PREFIX_ . $table) . '` ADD `' . bqSQL($column) . '` ' . $type . ' ' . ($insertAfter ? ' AFTER `' . bqSQL($insertAfter) . '`' : '') . '')) {
            return true;
        }
        return false;
    }
    public function updateAdvancedStyles($css_styles)
    {
        if (Shop::isFeatureActive()) {
            Configuration::updateGlobalValue('PM_'.self::$modulePrefix.'_ADVANCED_STYLES', self::getDataSerialized($css_styles));
        } else {
            Configuration::updateValue('PM_'.self::$modulePrefix.'_ADVANCED_STYLES', self::getDataSerialized($css_styles));
        }
        $this->generateCss();
    }
    public function getAdvancedStylesDb()
    {
        if (Shop::isFeatureActive()) {
            $advanced_css_file_db = Configuration::getGlobalValue('PM_'.self::$modulePrefix.'_ADVANCED_STYLES');
        } else {
            $advanced_css_file_db = Configuration::get('PM_'.self::$modulePrefix.'_ADVANCED_STYLES');
        }
        if ($advanced_css_file_db !== false) {
            return self::getDataUnserialized($advanced_css_file_db);
        }
        return false;
    }
    public function displayAdvancedConfig()
    {
        if (Shop::isFeatureActive()) {
            $advanced_css_file = str_replace('.css', '-'.$this->context->shop->id.'.css', dirname(__FILE__) . '/' . self::ADVANCED_CSS_FILE);
        } else {
            $advanced_css_file = dirname(__FILE__) . '/' . self::ADVANCED_CSS_FILE;
        }
        if ($this->getAdvancedStylesDb() == false) {
            if (Tools::file_exists_cache($advanced_css_file) && is_readable($advanced_css_file) && Tools::strlen(Tools::file_get_contents($advanced_css_file)) > 0) {
                $this->updateAdvancedStyles(Tools::file_get_contents($advanced_css_file));
            } else {
                $this->updateAdvancedStyles("/* Advanced Search 5 Pro - Advanced Styles Content */\n");
            }
        }
        $vars = array(
            'advanced_styles' => $this->getAdvancedStylesDb()
        );
        return $this->fetchTemplate('module/tabs/advanced_styles.tpl', $vars);
    }
    public function displayMaintenance()
    {
        $advanced_searchs_id = SearchEngineUtils::getSearchsId(false, $this->context->shop->id);
        $criteriaGroupToReindex = array();
        if (self::isFilledArray($advanced_searchs_id)) {
            $key = 0;
            foreach ($advanced_searchs_id as $idSearch) {
                $criterions_groups_indexed = SearchEngineIndexation::getCriterionsGroupsIndexed($idSearch, (int)$this->context->language->id, false);
                if (self::isFilledArray($criterions_groups_indexed)) {
                    foreach ($criterions_groups_indexed as $criterions_group_indexed) {
                        $criteriaGroupToReindex[$key] = array(
                            'id_search' => (int)$idSearch,
                            'id_criterion_group' => (int)$criterions_group_indexed['id_criterion_group'],
                        );
                        $key++;
                    }
                }
            }
        }
        $vars = array(
            'groups_to_reindex' => $criteriaGroupToReindex,
        );
        return $this->fetchTemplate('module/tabs/maintenance.tpl', $vars);
    }
    public function generateCss()
    {
        $advanced_searchs = SearchEngineUtils::getAllSearchs((int)$this->context->language->id, false, false);
        $css = array();
        foreach ($advanced_searchs as $advanced_search) {
            $criterions_groups_indexed = SearchEngineIndexation::getCriterionsGroupsIndexed($advanced_search['id_search'], (int)$this->context->language->id);
            if (self::isFilledArray($criterions_groups_indexed)) {
                foreach ($criterions_groups_indexed as $criterions_group) {
                    if ($advanced_search['show_hide_crit_method'] == 3 && $criterions_group['overflow_height']) {
                        $css[] = '#PM_ASCriterions_' . $advanced_search['id_search'] . '_' . $criterions_group['id_criterion_group'] . ' .PM_ASCriterionsGroupOuter {overflow:auto;max-height:' . (int)$criterions_group['overflow_height'] . 'px;}';
                    }
                }
            }
        }
        $advanced_styles = "\n".$this->getAdvancedStylesDb();
        if (is_writable(dirname(__FILE__) . '/views/css/') && is_writable(dirname(__FILE__) . '/' . self::DYN_CSS_FILE)) {
            if (sizeof($css)) {
                file_put_contents(dirname(__FILE__) . '/' . self::DYN_CSS_FILE, implode(" ", $css).$advanced_styles);
            } else {
                file_put_contents(dirname(__FILE__) . '/' . self::DYN_CSS_FILE, ''.$advanced_styles);
            }
        } else {
            if (!is_writable(dirname(__FILE__) . '/views/css/')) {
                $this->errors[] = $this->showWarning($this->l('Please set write permision to folder:'). ' '.dirname(__FILE__) . '/views/css/');
            }
            if (!is_writable(dirname(__FILE__) . '/' . self::DYN_CSS_FILE)) {
                $this->errors[] = $this->l('Please set write permision to file:'). ' '.dirname(__FILE__) . '/' . self::DYN_CSS_FILE;
            }
        }
    }
    public function getCriterionUnitByType($criterionGroupType)
    {
        foreach ($this->getCriterionsGroupsValue() as $groupUnit => $groups) {
            foreach ($groups as $group) {
                if ($group['type'] == $criterionGroupType) {
                    return $groupUnit;
                }
            }
        }
        return false;
    }
    public function getCriterionUnitTranslations()
    {
        return array(
            'associations' => $this->l('Product associations'),
            'attribute' => $this->l('Product attributes'),
            'feature' => $this->l('Product features'),
            'product_properties' => $this->l('Product properties'),
            'shipping' => $this->l('Package properties'),
        );
    }
    public function getCriterionsGroupsValue()
    {
        static $criterions_groups = null;
        if ($criterions_groups !== null) {
            return $criterions_groups;
        }
        $id_lang = (int)$this->context->language->id;
        $criterions_groups = array();
        $criterions_groups['associations'][] = array('id' => 0, 'name' => $this->l('All category levels'), 'type' => 'category');
        $criterions_groups['associations'][] = array('id' => 0, 'name' => $this->l('Subcategories'), 'type' => 'subcategory');
        $categories_level_depth = SearchEngineIndexation::getAvailableCategoriesLevelDepth();
        foreach ($categories_level_depth as $category_level_depth) {
            $criterions_groups['associations'][] = array('id' => $category_level_depth['level_depth'], 'name' => $this->l('Categories level') . ' ' . $category_level_depth['level_depth'], 'type' => 'category');
        }
        $criterions_groups['associations'][] = array('id' => 0, 'name' => $this->l('Brand'), 'type' => 'manufacturer');
        $criterions_groups['associations'][] = array('id' => 0, 'name' => $this->l('Supplier'), 'type' => 'supplier');
        $attributes_groups = SearchEngineIndexation::getAvailableAttributesGroups($id_lang);
        foreach ($attributes_groups as $row) {
            $criterions_groups['attribute'][] = array('id' => $row['id_attribute_group'], 'name' => $row['public_name'], 'internal_name' => (!empty($row['name']) ? $row['name'] : ''), 'type' => 'attribute');
        }
        $features = SearchEngineIndexation::getAvailableFeaturesGroups($id_lang);
        foreach ($features as $row) {
            $criterions_groups['feature'][] = array('id' => $row['id_feature'], 'name' => $row['name'], 'type' => 'feature');
        }
        $criterions_groups['product_properties'][] = array('id' => 0, 'name' => $this->l('Price'), 'type' => 'price');
        $criterions_groups['product_properties'][] = array('id' => 0, 'name' => $this->l('Prices drop'), 'type' => 'prices_drop');
        $criterions_groups['product_properties'][] = array('id' => 0, 'name' => $this->l('On sale'), 'type' => 'on_sale');
        $criterions_groups['product_properties'][] = array('id' => 0, 'name' => $this->l('New products'), 'type' => 'new_products');
        $ap5ModuleInstance = Module::getInstanceByName('pm_advancedpack');
        if (Validate::isLoadedObject($ap5ModuleInstance)) {
            $criterions_groups['product_properties'][] = array('id' => 0, 'name' => $this->l('Is a pack'), 'type' => 'pack');
        }
        $subModuleInstance = Module::getInstanceByName('pm_subscription');
        if (Validate::isLoadedObject($subModuleInstance)) {
            $criterions_groups['product_properties'][] = array('id' => 0, 'name' => $this->l('Subscription available'), 'type' => 'subscription');
        }
        $criterions_groups['product_properties'][] = array('id' => 0, 'name' => $this->l('In stock'), 'type' => 'stock');
        $criterions_groups['product_properties'][] = array('id' => 0, 'name' => $this->l('Available for order'), 'type' => 'available_for_order');
        $criterions_groups['product_properties'][] = array('id' => 0, 'name' => $this->l('Online only'), 'type' => 'online_only');
        $criterions_groups['product_properties'][] = array('id' => 0, 'name' => $this->l('Condition'), 'type' => 'condition');
        $criterions_groups['shipping'][] = array('id' => 0, 'name' => $this->l('Width'), 'type' => 'width');
        $criterions_groups['shipping'][] = array('id' => 0, 'name' => $this->l('Height'), 'type' => 'height');
        $criterions_groups['shipping'][] = array('id' => 0, 'name' => $this->l('Depth'), 'type' => 'depth');
        $criterions_groups['shipping'][] = array('id' => 0, 'name' => $this->l('Weight'), 'type' => 'weight');
        return $criterions_groups;
    }
    protected function postProcessSearch()
    {
        $id_search = Tools::getValue('id_search', false);
        $ObjAdvancedSearchClass = new Search($id_search);
        $reindexing_categories = false;
        $this->cleanOutput();
        if ($id_search && Tools::getValue('recursing_indexing') != $ObjAdvancedSearchClass->recursing_indexing) {
            $reindexing_categories = true;
        }
        $this->copyFromPost($ObjAdvancedSearchClass);
        if (empty($ObjAdvancedSearchClass->internal_name) || trim($ObjAdvancedSearchClass->internal_name) == '') {
            $ObjAdvancedSearchClass->internal_name = ' ';
        }
        $ObjAdvancedSearchClass->remind_selection = 0;
        if (Tools::getValue('remind_selection_results') && Tools::getValue('remind_selection_block')) {
            $ObjAdvancedSearchClass->remind_selection = 3;
        } elseif (Tools::getValue('remind_selection_results') && !Tools::getValue('remind_selection_block')) {
            $ObjAdvancedSearchClass->remind_selection = 1;
        } elseif (!Tools::getValue('remind_selection_results') && Tools::getValue('remind_selection_block')) {
            $ObjAdvancedSearchClass->remind_selection = 2;
        }
        if (Tools::getValue('id_hook_widget') && $ObjAdvancedSearchClass->id_hook == -2) {
            $ObjAdvancedSearchClass->id_hook = (int)Tools::getValue('id_hook_widget');
        }
        if (!self::supportsStepSearch()) {
            $ObjAdvancedSearchClass->step_search = 0;
        }
        $this->errors = $this->retroValidateController($ObjAdvancedSearchClass);
        if (!sizeof($this->errors) && !$ObjAdvancedSearchClass->save()) {
            $this->errors[] = $this->l('Error while saving');
        }
        if (!sizeof($this->errors)) {
            if (!$id_search) {
                if (!$this->installDBCache($ObjAdvancedSearchClass->id)) {
                    $this->errors[] = $this->l('Error while making cache table');
                }
                if (!sizeof($this->errors) && !SearchEngineIndexation::addCacheProduct($ObjAdvancedSearchClass->id)) {
                    $this->errors[] = $this->l('Error while creating products index');
                }
            } else {
                if (!SearchEngineIndexation::updateCacheProduct($id_search)) {
                    $this->errors[] = $this->l('Error while creating products index');
                }
            }
            if (trim($ObjAdvancedSearchClass->internal_name) == '') {
                $ObjAdvancedSearchClass->internal_name = $this->l('Search engine') . ' ' . $ObjAdvancedSearchClass->id;
                $ObjAdvancedSearchClass->update();
            }
            SearchEngineIndexation::indexFilterByEmplacement($ObjAdvancedSearchClass->id);
            if ($reindexing_categories) {
                SearchEngineIndexation::reindexingCategoriesGroups($ObjAdvancedSearchClass);
            }
            $this->generateCss();
            self::clearSmartyCache((int)$ObjAdvancedSearchClass->id);
            $this->displayJsTags('open');
            if (!$id_search) {
                $this->html .= 'parent.parent.addTabPanel("#wrapAsTab", ' . json_encode($ObjAdvancedSearchClass->id . ' - ' . $ObjAdvancedSearchClass->internal_name) . ',' . $ObjAdvancedSearchClass->id . ', true);';
                $this->html .= 'parent.parent.loadTabPanel("#wrapAsTab","li#TabSearchAdminPanel' . $ObjAdvancedSearchClass->id . '");';
            } else {
                $this->html .= 'parent.parent.loadTabPanel("#wrapAsTab","li#TabSearchAdminPanel' . $ObjAdvancedSearchClass->id . '");';
                $this->html .= 'parent.parent.updateSearchNameIntoTab("li#TabSearchAdminPanel' . $ObjAdvancedSearchClass->id . '", '.json_encode($ObjAdvancedSearchClass->id . ' - ' . $ObjAdvancedSearchClass->internal_name).');';
            }
            $this->success[] = $this->l('Search has been updated successfully');
            $this->displaySuccessJs(false);
            $this->displayCloseDialogIframeJs(false);
            $this->displayJsTags('close');
        }
        $this->displayErrorsJs(true);
        $this->echoOutput(true);
    }
    protected function postProcessSearchVisibility()
    {
        $id_search = Tools::getValue('id_search', false);
        $ObjAdvancedSearchClass = new Search($id_search);
        $this->cleanOutput();
        $this->errors = $this->retroValidateController($ObjAdvancedSearchClass);
        if (!sizeof($this->errors)) {
            $this->copyFromPost($ObjAdvancedSearchClass);
            if (Tools::getValue('bool_cat')) {
                $ObjAdvancedSearchClass->categories_association = Tools::getValue('categories_association');
            } else {
                $ObjAdvancedSearchClass->categories_association = array();
            }
            if (Tools::getValue('bool_prod')) {
                $ObjAdvancedSearchClass->products_association = Tools::getValue('products_association');
            } else {
                $ObjAdvancedSearchClass->products_association = array();
            }
            if (Tools::getValue('bool_cat_prod')) {
                $ObjAdvancedSearchClass->product_categories_association = Tools::getValue('product_categories_association');
            } else {
                $ObjAdvancedSearchClass->product_categories_association = array();
            }
            if (Tools::getValue('bool_manu')) {
                $ObjAdvancedSearchClass->manufacturers_association = Tools::getValue('manufacturers_association');
            } else {
                $ObjAdvancedSearchClass->manufacturers_association = array();
            }
            if (Tools::getValue('bool_supp')) {
                $ObjAdvancedSearchClass->suppliers_association = Tools::getValue('suppliers_association');
            } else {
                $ObjAdvancedSearchClass->suppliers_association = array();
            }
            if (Tools::getValue('bool_cms')) {
                $ObjAdvancedSearchClass->cms_association = Tools::getValue('cms_association');
            } else {
                $ObjAdvancedSearchClass->cms_association = array();
            }
            if (Tools::getValue('bool_spe')) {
                $ObjAdvancedSearchClass->special_pages_association = Tools::getValue('special_pages_association');
            } else {
                $ObjAdvancedSearchClass->special_pages_association = array();
            }
            if (!$ObjAdvancedSearchClass->save()) {
                $this->errors[] = $this->l('Error while saving');
            }
            if (!sizeof($this->errors)) {
                self::clearSmartyCache((int)$ObjAdvancedSearchClass->id);
                $this->displayJsTags('open');
                $this->html .= 'parent.parent.loadTabPanel("#wrapAsTab","li#TabSearchAdminPanel' . $ObjAdvancedSearchClass->id . '");';
                $this->displayCloseDialogIframeJs(false);
                $this->displayJsTags('close');
                $this->success[] = $this->l('Search has been updated successfully');
            }
        }
        $this->displayErrorsJs(true);
        $this->displaySuccessJs(true);
        $this->echoOutput(true);
    }
    protected function postProcessCriteria()
    {
        $id_search = Tools::getValue('id_search', false);
        $id_criterion = Tools::getValue('id_criterion', false);
        $key_criterions_group = Tools::getValue('key_criterions_group', false);
        $this->html = '';
        if (!$id_search || !$id_criterion) {
            $this->errors[] = $this->l('An error occured');
        } else {
            $objAdvancedSearchCriterionClass = new Criterion($id_criterion, $id_search);
            $update = $this->uploadImageLang($objAdvancedSearchCriterionClass, 'icon', '/modules/pm_advancedsearch4/search_files/criterions/', '-' . $id_search);
            if (is_array($update) && sizeof($update)) {
                foreach ($update as $error) {
                    $this->errors[] = $error;
                }
            } elseif ($update) {
                $objAdvancedSearchCriterionClass->save();
            }
            $this->displayCloseDialogIframeJs(true);
            $this->displayJsTags('open');
            $this->html .= 'parent.parent.getCriterionGroupActions("' . $key_criterions_group . '", true);';
            $this->displayJsTags('close');
            $this->success[] = $this->l('Saved');
        }
        $this->displayErrorsJs(true);
        $this->displaySuccessJs(true);
        $this->cleanBuffer();
        echo $this->html;
        die();
    }
    protected function cartesianReOrder($array)
    {
        $current = array_shift($array);
        if (count($array) > 0) {
            $results = array();
            $temp = $this->cartesianReOrder($array);
            foreach ($current as $value) {
                foreach ($temp as $value2) {
                    $results[] = $value . ',' . $value2;
                }
            }
            return $results;
        } else {
            return $current;
        }
    }
    public static function parseInt($string)
    {
        if (preg_match('/(\d+)/', $string, $array)) {
            return $array[1];
        } else {
            return 0;
        }
    }
    public function saveAdvancedConfig()
    {
        $this->cleanOutput();
        $this->updateAdvancedStyles(Tools::getValue('advancedConfig'));
        $this->html .= $this->displayConfirmation($this->l('Styles updated successfully'));
    }
    protected function postProcess()
    {
        if (Tools::getValue('submitAdvancedConfig')) {
            $this->saveAdvancedConfig();
        } elseif (Tools::getIsset('submitSearch')) {
            $this->postProcessSearch();
        } elseif (Tools::getIsset('submitSearchVisibility')) {
            $this->postProcessSearchVisibility();
        } elseif (Tools::getIsset('submitCriteria')) {
            $this->postProcessCriteria();
        } elseif (Tools::getIsset('submitSeoSearchForm')) {
            if (!self::supportsSeoPages()) {
                $this->infos[] = $this->l('You need to upgrade to the PRO version to use SEO Pages. Please contact us to upgrade.');
                $this->displayInfosJs(true);
                $this->displayCloseDialogIframeJs(true);
                echo $this->html;
                die;
            }
            $this->postProcessSeoSearch();
        } elseif (Tools::getIsset('submitMassSeoSearchForm')) {
            if (!self::supportsSeoPages()) {
                $this->infos[] = $this->l('You need to upgrade to the PRO version to use SEO Pages. Please contact us to upgrade.');
                $this->displayInfosJs(true);
                $this->displayCloseDialogIframeJs(true);
                echo $this->html;
                die;
            }
            $this->postProcessMassSeoSearch();
        } elseif (Tools::getIsset('submitSeoRegenerate')) {
            if (!self::supportsSeoPages()) {
                $this->infos[] = $this->l('You need to upgrade to the PRO version to use SEO Pages. Please contact us to upgrade.');
                $this->displayInfosJs(true);
                $this->displayCloseDialogIframeJs(true);
                echo $this->html;
                die;
            }
            $this->postProcessSeoRegenerate();
        } elseif (Tools::getIsset('action') && Tools::getValue('action') == 'orderSearchEngine') {
            $this->cleanOutput();
            $order = Tools::getValue('order') ? explode(',', Tools::getValue('order')) : array();
            foreach ($order as $position => $searchIdentifier) {
                $idSearch = self::parseInt($searchIdentifier);
                if (!empty($idSearch)) {
                    Db::getInstance()->update('pm_advancedsearch', array('position' => (int)$position), 'id_search = ' . (int)$idSearch);
                }
            }
            $this->html .= $this->l('Saved');
            $this->echoOutput(true);
        } elseif (Tools::getIsset('action') && Tools::getValue('action') == 'orderCriterion') {
            $this->cleanOutput();
            $order = Tools::getValue('order') ? explode(',', Tools::getValue('order')) : array();
            $id_search = Tools::getValue('id_search');
            foreach ($order as $position => $id_criterion) {
                if (!trim($id_criterion)) {
                    continue;
                }
                $row = array('position' => (int)$position);
                Db::getInstance()->update('pm_advancedsearch_criterion_' . (int)$id_search, $row, 'id_criterion = ' . (int)self::parseInt($id_criterion));
                self::clearSmartyCache((int)$id_search);
            }
            $this->html .= $this->l('Saved');
            $this->echoOutput(true);
        } elseif (Tools::getIsset('action') && Tools::getValue('action') == 'orderCriterionGroup') {
            $this->cleanOutput();
            $order = Tools::getValue('order') ? explode(',', Tools::getValue('order')) : array();
            $id_search = Tools::getValue('id_search');
            $auto_hide = Tools::getValue('auto_hide');
            $hidden = false;
            foreach ($order as $position => $key_criterions_group) {
                if ($key_criterions_group == "hide_after_".$id_search) {
                    if ($auto_hide == 'true') {
                        $hidden = true;
                    }
                    continue;
                }
                if (!trim($key_criterions_group)) {
                    continue;
                }
                $row = array('position' => (int)$position, 'hidden' => (int)$hidden);
                $infos_criterions_group = explode('-', $key_criterions_group);
                list($criterions_group_type, $id_criterion_group_linked, $id_search, $id_criterion_group) = $infos_criterions_group;
                if (!$criterions_group_type || !$id_search || !$id_criterion_group) {
                    continue;
                }
                Db::getInstance()->update('pm_advancedsearch_criterion_group_' . (int)$id_search, $row, 'id_criterion_group = ' . (int)$id_criterion_group);
                self::clearSmartyCache((int)$id_search);
            }
            $this->html .= $this->l('Saved');
            $this->echoOutput(true);
        } elseif (Tools::getIsset('submitModuleConfiguration') && Tools::isSubmit('submitModuleConfiguration')) {
            $config = $this->getModuleConfiguration();
            foreach (array('fullTree', 'autoReindex', 'autoSyncActiveStatus', 'moduleCache', 'maintenanceMode', 'blurEffect', 'mobileVisible') as $configKey) {
                $config[$configKey] = (bool)Tools::getValue($configKey);
            }
            $searchProvider = new Facets(
                $this,
                $this->getTranslator(),
                new Search(),
                null
            );
            $sortOrders = $searchProvider->getSortOrders(true, false);
            $config['sortOrders'] = array();
            foreach ($sortOrders as $sortOrder) {
                $config['sortOrders'][$sortOrder->toString()] = (bool)Tools::getValue(str_replace('.', '_', $sortOrder->toString()));
            }
            $this->setModuleConfiguration($config);
            $this->context->controller->confirmations[] = $this->l('Module configuration successfully saved');
            $this->processClearAllCache(false);
        }
        parent::postProcess();
    }
    protected function postSaveProcess($params)
    {
        parent::postSaveProcess($params);
        if ($params['class'] == 'AdvancedSearch\Models\CriterionGroup' && Tools::isSubmit('submitCriteriaGroupOptions')) {
            $this->generateCss();
            if (Validate::isLoadedObject($params['obj'])) {
                $this->displayJsTags('open');
                $this->html .= 'parent.parent.updateCriterionGroupName("'.(int)$params['obj']->id.'", '.json_encode($params['obj']->name[(int)$this->context->language->id]).');';
                $this->displayJsTags('close');
            }
            $this->displayCloseDialogIframeJs(true);
        }
    }
    protected function postDuplicateProcess($params)
    {
        parent::postDuplicateProcess($params);
        if ($params['class'] == 'AdvancedSearch\Models\Search') {
            $this->generateCss();
            $this->html .= 'addTabPanel("#wrapAsTab", ' . json_encode($params['obj']->id . ' - ' . $params['obj']->internal_name) . ',' . (int)$params['obj']->id . ', true);';
        }
    }
    protected function postDeleteProcess($params)
    {
        parent::postDeleteProcess($params);
        if ($params['class'] == 'AdvancedSearch\Models\Search') {
            $this->html .= 'removeTabPanel("#wrapAsTab","li#TabSearchAdminPanel' . Tools::getValue('id_search') . '","ul#asTab");';
            if (!sizeof(SearchEngineUtils::getSearchsId(false, $this->context->shop->id))) {
                $this->html .= 'parent.parent.location.reload();';
            }
        }
    }
    protected function renderConfigurationForm()
    {
        $config = $this->getModuleConfiguration();
        $searchProvider = new Facets(
            $this,
            $this->getTranslator(),
            new Search(),
            null
        );
        $sortOrders = $searchProvider->getSortOrders(true, false);
        $vars = array(
            'config' => $config,
            'sort_orders' => $sortOrders,
            'pmAdminMaintenanceLink' => $this->context->link->getAdminLink('AdminMaintenance'),
            'default_config' => $this->defaultConfiguration,
        );
        return $this->fetchTemplate('module/tabs/configuration.tpl', $vars);
    }
    public function displaySearchAdminPanel()
    {
        $id_search = (int) Tools::getValue('id_search');
        $advanced_search = SearchEngineUtils::getSearch($id_search, (int)$this->context->language->id, false);
        if (!isset($advanced_search[0])) {
            return;
        }
        $advanced_search = $advanced_search[0];
        $criterions_groups = $this->getCriterionsGroupsValue();
        $criterionsUnitGroupsTranslations = $this->getCriterionUnitTranslations();
        $criterions_groups_indexed = SearchEngineIndexation::getCriterionsGroupsIndexed($advanced_search['id_search'], (int)$this->context->language->id);
        $keys_criterions_group_indexed = array();
        $criterions_groups_to_reindex = SearchEngineIndexation::getCriterionsGroupsIndexed($advanced_search['id_search'], (int)$this->context->language->id, false);
        $criteriaGroupToReindex = array();
        if (self::isFilledArray($criterions_groups_to_reindex)) {
            foreach ($criterions_groups_to_reindex as $criterions_group_indexed) {
                $criteriaGroupToReindex[] = array(
                    'id_search' => (int)$id_search,
                    'id_criterion_group' => (int)$criterions_group_indexed['id_criterion_group'],
                );
            }
        }
        foreach ($criterions_groups_indexed as $k => $criterions_group_indexed) {
            $criterionGroupUnit = $this->getCriterionUnitByType($criterions_group_indexed['criterion_group_type']);
            $criterions_groups_indexed[$k]['criterion_group_unit'] = $criterionGroupUnit;
            $criterions_groups_indexed[$k]['unique_id'] = $criterions_group_indexed['criterion_group_type'] . '-' . (int)$criterions_group_indexed['id_criterion_group_linked'] . '-' . (int)$advanced_search['id_search'] . '-' . (int)$criterions_group_indexed['id_criterion_group'];
            $keys_criterions_group_indexed[] = $criterions_group_indexed['criterion_group_type'].'-'.(int)$criterions_group_indexed['id_criterion_group_linked'];
        }
        foreach ($criterions_groups as &$groupList) {
            foreach ($groupList as $k => $criterions_group) {
                if ($criterions_group['type'] != 'category' && in_array($criterions_group['type'].'-'.(int)$criterions_group['id'], $keys_criterions_group_indexed)) {
                    unset($groupList[$k]);
                    continue;
                }
                $groupList[$k]['unique_id'] = $criterions_group['type'] . '-' . (int)$criterions_group['id'] . '-' . (int)$advanced_search['id_search'];
            }
        }
        $vars = array(
            'id_search' => (int)$id_search,
            'search_engine' => $advanced_search,
            'groups_to_reindex' => $criteriaGroupToReindex,
            'criterions_groups_indexed' => $criterions_groups_indexed,
            'criteria_group_labels' => $this->criteriaGroupLabels,
            'criterions_groups' => $criterions_groups,
            'criterions_unit_groups_translations' => $criterionsUnitGroupsTranslations,
        );
        $this->html .= $this->fetchTemplate('module/tabs/search_engine.tpl', $vars);
    }
    protected function processIndexCriterionsGroup()
    {
        self::changeTimeLimit(0);
        $key_criterions_group = Tools::getValue('key_criterions_group', false);
        if (!$key_criterions_group) {
            die;
        }
        $infos_criterions_group = explode('-', $key_criterions_group);
        list($criterions_group_type, $id_criterion_group_linked, $id_search) = $infos_criterions_group;
        if (!$criterions_group_type || !$id_search) {
            die;
        }
        $id_criterion_group = SearchEngineIndexation::indexCriterionsGroup($id_search, $criterions_group_type, $id_criterion_group_linked, false, true, false);
        SearchEngineIndexation::optimizedSearchTables($id_search);
        self::clearSmartyCache((int)$id_search);
        $new_key_criterions_group_indexed = $criterions_group_type . '-' . (int)$id_criterion_group_linked . '-' . (int)$id_search . '-' . (int)$id_criterion_group;
        $this->html .= '$(".indexedCriterionGroups #'.$key_criterions_group.'").attr("id", "'.$new_key_criterions_group_indexed.'");';
        $key_criterions_group = $new_key_criterions_group_indexed;
        $this->html .= '$("#'.$key_criterions_group.' .loadingOnConnectList").hide().remove();';
        $this->html .= 'setCriterionGroupActions("'.$key_criterions_group.'");';
        $this->html .= '$("#'.$key_criterions_group.'").attr("rel",'.(int)$id_criterion_group.');';
        $this->html .= 'getCriterionGroupActions("'.$key_criterions_group.'");';
        $this->html .= 'saveCriterionsGroupSorting('. (int)$id_search .');';
        $criterions_groups_to_reindex = SearchEngineIndexation::getCriterionsGroupsIndexed((int)$id_search, (int)$this->context->language->id, false);
        $criteriaGroupToReindex = array();
        if (self::isFilledArray($criterions_groups_to_reindex)) {
            foreach ($criterions_groups_to_reindex as $criterions_group_indexed) {
                $criteriaGroupToReindex[] = array(
                    'id_search' => (int)$id_search,
                    'id_criterion_group' => (int)$criterions_group_indexed['id_criterion_group'],
                );
            }
        }
        $this->html .= 'var criteriaGroupToReindex'.(int)$id_search.' = '. json_encode($criteriaGroupToReindex) .';';
    }
    protected function processDesindexCriterionsGroup()
    {
        $key_criterions_group = Tools::getValue('key_criterions_group', false);
        if (!$key_criterions_group) {
            die;
        }
        $infos_criterions_group = explode('-', $key_criterions_group);
        list($criterions_group_type, $id_criterion_group_linked, $id_search, $id_criterion_group) = $infos_criterions_group;
        if (!$criterions_group_type || !$id_search) {
            die;
        }
        SearchEngineIndexation::desIndexCriterionsGroup($id_search, $criterions_group_type, $id_criterion_group_linked, $id_criterion_group);
        SearchEngineIndexation::optimizedSearchTables($id_search);
        self::clearSmartyCache((int)$id_search);
        $this->html .= '$("#'.$key_criterions_group.' .loadingOnConnectList").hide().remove();';
        $criterions_groups_to_reindex = SearchEngineIndexation::getCriterionsGroupsIndexed((int)$id_search, (int)$this->context->language->id, false);
        $criteriaGroupToReindex = array();
        if (self::isFilledArray($criterions_groups_to_reindex)) {
            foreach ($criterions_groups_to_reindex as $criterions_group_indexed) {
                $criteriaGroupToReindex[] = array(
                    'id_search' => (int)$id_search,
                    'id_criterion_group' => (int)$criterions_group_indexed['id_criterion_group'],
                );
            }
        }
        $this->html .= 'var criteriaGroupToReindex'.(int)$id_search.' = '. json_encode($criteriaGroupToReindex) .';';
    }
    protected function processClearAllCache($outputConfirmation = true)
    {
        $advanced_searchs_id = SearchEngineUtils::getSearchsId(false);
        foreach ($advanced_searchs_id as $idSearch) {
            self::clearSmartyCache($idSearch);
        }
        SearchEngineUtils::setLocalStorageCacheKey();
        if ($outputConfirmation) {
            $this->html .= 'show_success("'.addcslashes($this->l('Cache has been flushed'), '"').'");';
        }
    }
    protected function processClearAllTables()
    {
        $advanced_searchs_id = SearchEngineUtils::getSearchsId(false);
        SearchEngineUtils::clearAllTables();
        foreach ($advanced_searchs_id as $idSearch) {
            $this->html .= 'removeTabPanel("#wrapAsTab","li#TabSearchAdminPanel'.$idSearch.'","ul#asTab");';
        }
        $this->html .= 'show_success("'.addcslashes($this->l('Clear done'), '"').'"); $("#msgNoResults").slideDown();';
    }
    protected function processReindexSpecificSearch()
    {
        self::changeTimeLimit(0);
        $id_search = Tools::getValue('id_search');
        SearchEngineIndexation::reindexSpecificSearch($id_search);
        $this->html .= '$( "#progressbarReindexSpecificSearch'.(int)$id_search.'" ).progressbar( "option", "value", 100 );show_success("'.addcslashes($this->l('Indexation done'), '"').'")';
    }
    protected function processDeleteCriterionImg()
    {
        $id_search = Tools::getValue('id_search');
        $id_criterion = Tools::getValue('id_criterion');
        $id_lang = Tools::getValue('id_lang');
        $objCriterion = new Criterion($id_criterion, $id_search);
        $file_name = $objCriterion->icon[$id_lang];
        $file_name_final_path = _PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions/'.$file_name;
        $objCriterion->icon[$id_lang] = '';
        if (Core::isRealFile($file_name_final_path)) {
            unlink($file_name_final_path);
        }
        if ($objCriterion->save()) {
            $this->html .= 'show_success("'.addcslashes($this->l('Criterion image deleted'), '"').'");';
        } else {
            $this->html .= 'show_error("'.addcslashes($this->l('An error occured'), '"').'");';
        }
    }
    protected function processSaveCriterionImg()
    {
        $id_search = Tools::getValue('id_search');
        $id_criterion = Tools::getValue('id_criterion');
        $id_lang = Tools::getValue('id_lang');
        $file_name = Tools::getValue('file_name');
        $file_name_temp_path = _PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/uploads/temp/'.$file_name;
        $file_name_final_path = _PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions/';
        if (Core::isRealFile($file_name_temp_path)) {
            rename($file_name_temp_path, $file_name_final_path . $file_name);
            $objCriterion = new Criterion($id_criterion, $id_search);
            $objCriterion->icon[$id_lang] = $file_name;
            foreach (Language::getLanguages(false) as $lang) {
                if (empty($objCriterion->icon[$lang['id_lang']])) {
                    $new_temp_file_lang = uniqid(self::$modulePrefix . mt_rand()).'.'.self::getFileExtension($file_name);
                    copy($file_name_final_path . $file_name, $file_name_final_path . $new_temp_file_lang);
                    $objCriterion->icon[$lang['id_lang']] = $new_temp_file_lang;
                }
            }
            if ($objCriterion->save()) {
                $this->html .= 'ok';
            } else {
                $this->html .= 'ko';
            }
        } else {
            $this->html .= 'ko';
        }
    }
    protected function processDeleteCustomCriterion()
    {
        $objCriterion = new Criterion((int)Tools::getValue('id_criterion'), (int)Tools::getValue('id_search'));
        if (Validate::isLoadedObject($objCriterion)) {
            if ($objCriterion->delete()) {
                $this->html .= 'parent.show_success("' . $this->l('Successfully deleted') . '");';
                $this->html .= 'this.location.reload(true);';
            } else {
                $this->html .= 'parent.show_error("' . $this->l('Error while updating criterion') . '");';
            }
        } else {
            $this->html .= 'parent.show_error("' . $this->l('Error while deleting criterion') . '");';
        }
    }
    protected function processAddCustomCriterion()
    {
        $objCriterion = new Criterion(null, (int)Tools::getValue('id_search'));
        $objCriterion->id_criterion_group = (int)Tools::getValue('id_criterion_group');
        $objCriterion->id_criterion_linked = 0;
        $objCriterion->is_custom = 1;
        $this->copyFromPost($objCriterion);
        $validationErrors = $this->retroValidateController($objCriterion);
        if (!self::isFilledArray($validationErrors)) {
            if ($objCriterion->save()) {
                $this->html .= 'parent.show_success("' . $this->l('Saved') . '");';
                $this->html .= 'this.location.reload(true);';
            } else {
                $this->html .= 'parent.show_error("' . $this->l('Error while adding criterion') . '");';
            }
        } else {
            $this->html .= 'parent.show_error("' . $this->l('Error while adding criterion') . '");';
            foreach ($validationErrors as $error) {
                $this->html .= 'parent.show_error("' . $error . '");';
            }
        }
    }
    protected function processUpdateCustomCriterion()
    {
        $objCriterion = new Criterion((int)Tools::getValue('id_criterion'), (int)Tools::getValue('id_search'));
        $this->copyFromPost($objCriterion);
        if (Validate::isLoadedObject($objCriterion)) {
            if ($objCriterion->save()) {
                $this->html .= 'parent.show_success("' . $this->l('Saved') . '");';
                $this->html .= 'this.location.reload(true);';
            } else {
                $this->html .= 'parent.show_error("' . $this->l('Error while updating criterion') . '");';
            }
        } else {
            $this->html .= 'parent.show_error("' . $this->l('Error while updating criterion') . '");';
        }
    }
    protected function processAddCustomCriterionToGroup()
    {
        $idSearch = (int)Tools::getValue('id_search');
        $idCriterionGroup = (int)Tools::getValue('id_criterion_group');
        $criterionsGroupList = explode(',', Tools::getValue('criterionsGroupList'));
        $newCriterionsGroupList = array();
        if (self::isFilledArray($criterionsGroupList)) {
            foreach ($criterionsGroupList as $criterionsGroupListRow) {
                $criterionsGroupListRow = explode('-', $criterionsGroupListRow);
                $idCriterion = (int)$criterionsGroupListRow[0];
                $idCriterionParent = (int)$criterionsGroupListRow[1];
                if (!$idCriterionParent) {
                    continue;
                }
                $newCriterionsGroupList[$idCriterionParent][] = $idCriterion;
            }
        }
        $customCriterionList = Criterion::getCustomCriterionsLinkIdsByGroup($idSearch, $idCriterionGroup);
        $idCriterionParentToDelete = array();
        foreach ($customCriterionList as $idCriterionParent => $currentCriterionsGroupList) {
            $idCriterionParentToDelete[] = (int)$idCriterionParent;
        }
        foreach ($newCriterionsGroupList as $idCriterionParent => $currentCriterionsGroupList) {
            $idCriterionParentToDelete[] = (int)$idCriterionParent;
        }
        if (sizeof($idCriterionParentToDelete)) {
            SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_list` WHERE `id_criterion_parent` IN (' . implode(',', array_map('intval', $idCriterionParentToDelete)) . ')');
        }
        foreach ($customCriterionList as $idCriterionParent => $currentCriterionsGroupList) {
            Criterion::populateCriterionsLink($idSearch, $idCriterionParent);
        }
        $sqlInsertMultiple = array();
        $sqlInsertMultipleHeader = 'INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_list` (`id_criterion_parent`, `id_criterion`) VALUES ';
        foreach ($newCriterionsGroupList as $idCriterionParent => $currentCriterionsGroupList) {
            if (self::isFilledArray($currentCriterionsGroupList)) {
                foreach ($currentCriterionsGroupList as $idCriterion) {
                    $sqlInsertMultiple[] = '('. (int)$idCriterionParent. ', '. (int)$idCriterion .')';
                    SearchEngineIndexation::sqlBulkInsert('pm_advancedsearch_criterion_'.(int)$idSearch.'_list', $sqlInsertMultipleHeader, $sqlInsertMultiple, 1000);
                }
            }
        }
        SearchEngineIndexation::sqlBulkInsert('pm_advancedsearch_criterion_'.(int)$idSearch.'_list', $sqlInsertMultipleHeader, $sqlInsertMultiple, 1);
        foreach ($newCriterionsGroupList as $idCriterionParent => $currentCriterionsGroupList) {
            Criterion::populateCriterionsLink($idSearch, $idCriterionParent, false, $currentCriterionsGroupList);
        }
        SearchEngineIndexation::createVirtualCriterionRelation($idSearch, $idCriterionGroup);
        $this->html .= 'parent.show_success("' . $this->l('Saved') . '");';
    }
    protected function processEnableAllCriterions()
    {
        $objCriterionGoup = new CriterionGroup((int)Tools::getValue('id_criterion_group'), (int)Tools::getValue('id_search'));
        if (Validate::isLoadedObject($objCriterionGoup)) {
            if (CriterionGroup::enableAllCriterions((int)Tools::getValue('id_search'), (int)Tools::getValue('id_criterion_group'))) {
                $this->html .= '$("i[id^=imgActiveCriterion]").attr("data-current-mi-icon", "done").html("done");';
                $this->html .= 'parent.show_success("' . $this->l('Saved') . '");';
            } else {
                $this->html .= 'parent.show_error("' . $this->l('Error while updating criteria status') . '");';
            }
        } else {
            $this->html .= 'parent.show_error("' . $this->l('Error while updating criteria status') . '");';
        }
    }
    protected function processDisableAllCriterions()
    {
        $objCriterionGoup = new CriterionGroup((int)Tools::getValue('id_criterion_group'), (int)Tools::getValue('id_search'));
        if (Validate::isLoadedObject($objCriterionGoup)) {
            if (CriterionGroup::disableAllCriterions((int)Tools::getValue('id_search'), (int)Tools::getValue('id_criterion_group'))) {
                $this->html .= '$("i[id^=imgActiveCriterion]").attr("data-current-mi-icon", "close").html("close");';
                $this->html .= 'parent.show_success("' . $this->l('Saved') . '");';
            } else {
                $this->html .= 'parent.show_error("' . $this->l('Error while updating criteria status') . '");';
            }
        } else {
            $this->html .= 'parent.show_error("' . $this->l('Error while updating criteria status') . '");';
        }
    }
    protected function processActiveCriterion()
    {
        $ObjAdvancedSearchCriterionClass = new Criterion(Tools::getValue('id_criterion'), Tools::getValue('id_search'));
        $ObjAdvancedSearchCriterionClass->visible = ($ObjAdvancedSearchCriterionClass->visible ? 0 : 1);
        if ($ObjAdvancedSearchCriterionClass->save()) {
            $this->html .= '$("#imgActiveCriterion' . $ObjAdvancedSearchCriterionClass->id . '").attr("data-current-mi-icon", "' . ($ObjAdvancedSearchCriterionClass->visible ? 'done' : 'close') . '").html("' . ($ObjAdvancedSearchCriterionClass->visible ? 'done' : 'close') . '");';
            $this->html .= 'parent.show_success("' . $this->l('Saved') . '");';
        } else {
            $this->html .= 'parent.show_error("' . $this->l('Error while updating search') . '");';
        }
    }
    protected function processActiveSearch()
    {
        $ObjAdvancedSearchClass = new Search(Tools::getValue('id_search'));
        $ObjAdvancedSearchClass->active = ($ObjAdvancedSearchClass->active ? 0 : 1);
        if ($ObjAdvancedSearchClass->save()) {
            if ($ObjAdvancedSearchClass->active) {
                $this->html .= '
                    $("#searchStatusLabel' . $ObjAdvancedSearchClass->id . '").html("' . $this->l('enabled') . '");
                    $(".status_search_' . $ObjAdvancedSearchClass->id . ' i").text("check_circle");
                    $(".status_search_' . $ObjAdvancedSearchClass->id . '").toggleClass("enabled_search");
                ';
            } else {
                $this->html .= '
                    $("#searchStatusLabel' . $ObjAdvancedSearchClass->id . '").html("' . $this->l('disabled') . '");
                    $(".status_search_' . $ObjAdvancedSearchClass->id . ' i").text("cancel");
                    $(".status_search_' . $ObjAdvancedSearchClass->id . '").toggleClass("enabled_search");
                ';
            }
            $this->html .= 'show_success("' . $this->l('Saved') . '");';
        } else {
            $this->html .= 'show_error("' . $this->l('Error while updating search') . '");';
        }
    }
    protected function displayCmsOptions()
    {
        $query = Tools::getValue('q', false);
        if (trim($query)) {
            $limit = Tools::getValue('limit', 100);
            $start = Tools::getValue('start', 0);
            $items = SearchEngineDb::query('
                SELECT c.`id_cms`, cl.`meta_title`
                FROM `'._DB_PREFIX_.'cms` c
                LEFT JOIN `'._DB_PREFIX_.'cms_lang` cl ON (c.`id_cms` = cl.`id_cms`)
                WHERE (cl.`meta_title` LIKE \'%'.pSQL($query).'%\')
                AND cl.`id_lang` = '.(int)$this->context->language->id.'
                AND c.`active` = 1
                ORDER BY cl.`meta_title`
                '.($limit? 'LIMIT '.$start.', '.(int)$limit : ''));
            if ($items) {
                foreach ($items as $row) {
                    $this->html .= $row['id_cms']. '=' .$row['meta_title']. "\n";
                }
            }
        }
    }
    public function getContent()
    {
        $this->baseConfigUrl = $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name;
        if (Tools::getValue('makeUpdate')) {
            $this->checkIfModuleIsUpdate(true);
        }
        $moduleIsUpToDate = $this->checkIfModuleIsUpdate(false);
        $permissionsErrors = $this->checkPermissions();
        if (!$this->checkDbMaxAllowedPacket()) {
            $this->context->controller->warnings[] = $this->l('We\'ve detected that your database has a max_allowed_packet lower than 32MB. This can cause issues with bigger requests (e.g. if you combine a lot of criteria groups).');
        }
        if (!sizeof($permissionsErrors)) {
            if ($moduleIsUpToDate) {
                if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                    $this->preProcess();
                    $this->postProcess();
                }
            }
        }
        $config = $this->getModuleConfiguration();
        if ($this->facetedSearchIsEnabled()) {
            $this->context->controller->warnings[] = sprintf($this->l('"%s" is not compatible with Advanced Search.'), $this->getNativeFacetedSearchModuleDisplayName()) . $this->l('You must disable it to prevent unexpected behaviour on your shop.');
        }
        if ($this->isInMaintenance()) {
            $maintenanceIps = Configuration::get('PS_MAINTENANCE_IP');
            $this->context->smarty->assign([
                'pmAdminMaintenanceLink' => $this->context->link->getAdminLink('AdminMaintenance'),
            ]);
            $this->context->controller->warnings[] = sprintf($this->l('%s is currently running in maintenance mode.'), $this->displayName);
        }
        $vars = array(
            'module_configuration' => $config,
            'module_display_name' => $this->displayName,
            'module_is_up_to_date' => $moduleIsUpToDate,
            'permissions_errors' => $permissionsErrors,
            'context_is_shop' => (Shop::getContext() == Shop::CONTEXT_SHOP),
            'css_js_assets' => $this->loadCssJsLibraries(),
            'rating_invite' => $this->showRating(true),
            'parent_content' => parent::getContent(),
            'search_engines' => array(),
            'cache_alert' => false,
        );
        if (!sizeof($permissionsErrors)) {
            if ($moduleIsUpToDate) {
                if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                    $advanced_searchs = SearchEngineUtils::getAllSearchs((int)$this->context->language->id, false);
                    if (self::isFilledArray($advanced_searchs) && isset($config['moduleCache']) && $config['moduleCache'] == false) {
                        $this->context->controller->errors[] = $this->l('Cache is currently disabled. We really recommend you to enable it when you are in production mode.');
                    }
                    $vars['search_engines'] = $advanced_searchs;
                    $vars['configuration_tab'] = $this->renderConfigurationForm();
                    $vars['advanced_styles_tab'] = $this->displayAdvancedConfig();
                    $vars['maintenance_tab'] = $this->displayMaintenance();
                    $vars['cron_tab'] = $this->displayCrontab();
                }
            }
        }
        return $this->fetchTemplate('module/content.tpl', $vars);
    }
    protected function displayCrontab()
    {
        $cronUrls = array();
        $searchEngines = SearchEngineUtils::getAllSearchs((int)$this->context->language->id, false);
        if (Core::isFilledArray($searchEngines)) {
            foreach ($searchEngines as $searchEngine) {
                $cronUrls[] = $this->context->link->getModuleLink($this->name, 'cron', array('secure_key' => Configuration::getGlobalValue('PM_AS4_SECURE_KEY'), 'id_search' => (int)$searchEngine['id_search']));
            }
        }
        $vars = array(
            'main_cron_url' => $this->context->link->getModuleLink($this->name, 'cron', array('secure_key' => Configuration::getGlobalValue('PM_AS4_SECURE_KEY'))),
            'cron_urls' => $cronUrls,
        );
        return $this->fetchTemplate('module/tabs/cron.tpl', $vars);
    }
    protected function displaySearchForm($params)
    {
        $searchType = array(
            0 => $this->l('Filter'),
            1 => $this->l('Whole catalog'),
            2 => $this->l('Step by step') . (!self::supportsStepSearch() ? ' ' . $this->l('(PRO)') : ''),
        );
        if (self::supportsStepSearch()) {
            if (!empty($params['obj']->step_search)) {
                $this->setStepSearchType($params);
            }
        } else {
            if (!empty($params['obj']->filter_by_emplacement)) {
                $params['obj']->search_type = 0;
            } else {
                $params['obj']->search_type = 1;
            }
        }
        $publicHookLabel = array(
            'displayhome' => $this->l('Homepage'),
            'displaytop' => $this->l('Top of page'),
            'displaynavfullwidth' => $this->l('Top of page full width'),
            'displayleftcolumn' => $this->l('Left column'),
            'displayrightcolumn' => $this->l('Right column'),
        );
        $hooks = $hooksId = $widgetHooksList = array();
        $valid_hooks = SearchEngineUtils::$valid_hooks;
        foreach ($valid_hooks as $hook_name) {
            if ($hook_name == 'displayAdvancedSearch4') {
                continue;
            }
            $id_hook = Hook::getIdByName($hook_name);
            if (!$id_hook) {
                continue;
            }
            $hooks[$id_hook] = (!empty($publicHookLabel[$hook_name]) ? $publicHookLabel[$hook_name] . ' (' . $hook_name . ')' : $hook_name);
            $hooksId[$id_hook] = $hook_name;
        }
        $hooks[-1] = $this->l('Custom (advanced user only)');
        if (!Validate::isLoadedObject($params['obj']) && empty($params['obj']->id_hook)) {
            $params['obj']->id_hook = array_search('displayleftcolumn', $hooksId);
        }
        $seo_searchs = [];
        if (self::supportsSeoPages()) {
            $seo_searchs = Seo::getSeoSearchs(false, 0, (int)$params['obj']->id);
        }
        $categorySelect = array(0 => $this->l('-- User-context category --')) + $this->getCategoryTreeForSelect();
        $whereToSearch = array(
            0 => $this->l('Search into the whole catalog (no context)'),
            1 => $this->l('Search from the current page (use context)'),
        );
        $params['obj']->remind_selection_results = 0;
        $params['obj']->remind_selection_block = 0;
        if ($params['obj']->remind_selection == 1) {
            $params['obj']->remind_selection_results = 1;
        } elseif ($params['obj']->remind_selection == 2) {
            $params['obj']->remind_selection_block = 1;
        } elseif ($params['obj']->remind_selection == 3) {
            $params['obj']->remind_selection_results = 1;
            $params['obj']->remind_selection_block = 1;
        }
        $hooks[-2] = $this->l('Widget (advanced user only)');
        foreach (Hook::getHooks(false, true) as $displayHook) {
            if (is_array($displayHook) && !empty($displayHook['name']) && !preg_match('/^displayAdmin|^displayBackOffice|^displayInvoice|^displayPDF|^displayAdvancedSearch4|^displayOverrideTemplate|PostProcess$/i', $displayHook['name']) && !in_array(Tools::strtolower($displayHook['name']), $valid_hooks)) {
                $widgetHooksList[(int)$displayHook['id_hook']] = $displayHook['name'];
            }
        }
        $params['obj']->id_hook_widget = null;
        if (in_array($params['obj']->id_hook, array_keys($widgetHooksList))) {
            $params['obj']->id_hook_widget = $params['obj']->id_hook;
            $params['obj']->id_hook = -2;
        }
        $themeLayoutPreferencesLink = $this->context->link->getAdminLink('AdminThemes') . '&display=configureLayouts';
        $config = $this->getModuleConfiguration();
        $orderBy = $this->options_defaut_order_by;
        $allowedSorts = [];
        foreach ($orderBy as $idOrderBy => $labelOrderBy) {
            $concatenatedOrderBy = 'product.' . SearchEngineUtils::$orderByValues[$idOrderBy];
            $allowedSorts[$idOrderBy] = [
                'asc' => false,
                'desc' => false,
            ];
            $ascending = $concatenatedOrderBy . '.asc';
            if (in_array($ascending, array_keys($config['sortOrders']))) {
                if ($config['sortOrders'][$ascending] === true) {
                    $allowedSorts[$idOrderBy]['asc'] = true;
                }
            } else {
                $allowedSorts[$idOrderBy]['asc'] = true;
            }
            $descending = $concatenatedOrderBy . '.desc';
            if (in_array($descending, array_keys($config['sortOrders']))) {
                if ($config['sortOrders'][$descending] === true) {
                    $allowedSorts[$idOrderBy]['desc'] = true;
                }
            } else {
                $allowedSorts[$idOrderBy]['desc'] = true;
            }
            if ($allowedSorts[$idOrderBy]['asc'] === false && $allowedSorts[$idOrderBy]['desc'] === false) {
                unset($allowedSorts[$idOrderBy]);
            }
        }
        $vars = array(
            'params' => $params,
            'searchType' => $searchType,
            'hooksId' => $hooksId,
            'hooksList' => $hooks,
            'widgetHooksList' => $widgetHooksList,
            'seo_searchs' => $seo_searchs,
            'category_select' => $categorySelect,
            'where_to_search' => $whereToSearch,
            'spa_module_is_active' => SearchEngineUtils::isSPAModuleActive(),
            'products_per_page' => Configuration::get('PS_PRODUCTS_PER_PAGE'),
            'options_order_by' => $this->options_defaut_order_by,
            'options_order_by_allowed' => $allowedSorts,
            'options_order_way' => $this->options_defaut_order_way,
            'options_hide_criterion_method' => $this->options_show_hide_crit_method,
            'options_search_method' => $this->options_launch_search_method,
            'default_search_results_selector' => '#content-wrapper',
            'theme_layout_preferences_link' => $themeLayoutPreferencesLink,
            'supportsStepSearch' => self::supportsStepSearch(),
        );
        return $this->fetchTemplate('module/search_engine/new.tpl', $vars);
    }
    protected function displayVisibilityForm($params)
    {
        if ($params['obj']->id) {
            $categoriesAssociation = SearchEngineUtils::getCategoriesAssociation($params['obj']->id, (int)$this->context->language->id);
            $cmsAssociation = SearchEngineUtils::getCMSAssociation($params['obj']->id, (int)$this->context->language->id);
            $manufacturersAssociation = SearchEngineUtils::getManufacturersAssociation($params['obj']->id);
            $suppliersAssociation = SearchEngineUtils::getSuppliersAssociation($params['obj']->id);
            $productsAssociation = SearchEngineUtils::getProductsAssociation($params['obj']->id, (int)$this->context->language->id);
            $productsCategoriesAssociation = SearchEngineUtils::getProductsCategoriesAssociation($params['obj']->id, (int)$this->context->language->id);
            $specialPagesAssociation = SearchEngineUtils::getSpecialPagesAssociation($params['obj']->id);
        } else {
            $categoriesAssociation = array();
            $cmsAssociation = array();
            $manufacturersAssociation = array();
            $suppliersAssociation = array();
            $productsAssociation = array();
            $productsCategoriesAssociation = array();
            $specialPagesAssociation = array();
        }
        $vars = array(
            'params' => $params,
            'categories_association' => $categoriesAssociation,
            'root_category_id' => Category::getRootCategory()->id,
            'products_association' => $productsAssociation,
            'product_categories_association' => $productsCategoriesAssociation,
            'manufacturers_association' => $manufacturersAssociation,
            'suppliers_association' => $suppliersAssociation,
            'cms_association' => $cmsAssociation,
            'special_pages_association' => $specialPagesAssociation,
        );
        return $this->fetchTemplate('module/search_engine/visibility.tpl', $vars);
    }
    public function displayCriterionGroupForm($params)
    {
        $searchEngine = new Search($params['obj']->id_search);
        if (in_array($params['obj']->criterion_group_type, $this->criterionGroupIsTemplatisable)) {
            if ($params['obj']->criterion_group_type == 'attribute' && SearchEngineIndexation::isColorAttributesGroup($params['obj']->id_criterion_group_linked)) {
                $this->options_criteria_group_type[3] = $this->l('Links with color squares');
                $this->options_criteria_group_type[7] = $this->l('Color squares');
            } elseif ($params['obj']->criterion_group_type == 'category' && $params['obj']->id_criterion_group_linked == 0) {
                $this->options_criteria_group_type[9] = $this->l('Level Depth');
            }
        }
        $displayTypeClass = array();
        foreach (array_keys($this->options_criteria_group_type) as $key) {
            $displayTypeClass[$key] = 'display_type-'.$key;
        }
        $newCustomCriterion = new Criterion(null, $params['obj']->id_search);
        $criterionsSortByOptions = array(
            'position'   => $this->l('Custom position'),
            'o_position' => $this->l('Position in catalog'),
            'alphabetic' => $this->l('Alphabetic'),
            'numeric'    => $this->l('Numeric'),
            'nb_product' => $this->l('Products count'),
        );
        if (!in_array($params['obj']->criterion_group_type, $this->originalPositionSortableCriterion)) {
            unset($criterionsSortByOptions['o_position']);
        }
        $criterionsSortWayOptions = array(
            'ASC' => $this->l('Ascending'),
            'DESC' => $this->l('Descending'),
        );
        if ($searchEngine->filter_by_emplacement) {
            $contextType = array(
                2 => $this->l('Root category'),
                1 => $this->l('Parent category'),
                0 => $this->l('Current category (context)'),
            );
        } else {
            $contextType = array(
                2 => $this->l('Root category'),
            );
        }
        $vars = array(
            'params' => $params,
            'search_engine' => $searchEngine,
            'display_type_type' => $displayTypeClass,
            'display_type' => $this->options_criteria_group_type,
            'supportsImageCriterionGroup' => self::supportsImageCriterionGroup(),
            'context_type' => $contextType,
            'display_vertical_search_block' => $this->display_vertical_search_block,
            'sortable_criterion_group' => $this->sortableCriterion,
            'new_custom_criterion' => $newCustomCriterion,
            'is_color_group' => SearchEngineIndexation::isColorAttributesGroup($params['obj']->id_criterion_group_linked),
            'criterions_sort_by' => $criterionsSortByOptions,
            'criterions_sort_way' => $criterionsSortWayOptions,
            'pm_load_function' => Tools::getValue('pm_load_function'),
            'criteria_group_labels' => $this->criteriaGroupLabels,
        );
        if (in_array($params['obj']->criterion_group_type, $this->sortableCriterion)) {
            $vars['criterions_list_rendered'] = $this->displaySortCriteriaPanel($params['obj']);
        }
        return $this->fetchTemplate('module/criterion_group/new.tpl', $vars);
    }
    protected function displaySortCriteriaPanel($objCrit = false)
    {
        if (Tools::getValue('pm_load_function') == 'displaySortCriteriaPanel') {
            $objCrit = new CriterionGroup(Tools::getValue('id_criterion_group'), Tools::getValue('id_search'));
            if (Tools::getValue('sort_way')) {
                $objCrit->sort_by = Tools::getValue('sort_by');
                $objCrit->sort_way = Tools::getValue('sort_way');
                $objCrit->save();
                $displayType = (int)Tools::getValue('display_type', $objCrit->display_type);
                if ($objCrit->sort_by == 'o_position') {
                    SearchEngineIndexation::indexCriterionsGroup($objCrit->id_search, $objCrit->criterion_group_type, $objCrit->id_criterion_group_linked, $objCrit->id, $objCrit->visible, false, true);
                }
                $msgConfirm = $this->l('Specific sort:');
                if ($objCrit->sort_by == 'position' && $displayType != 9) {
                    $msgConfirm .= '<br />'.$this->l('Now, you can sort criteria by dragging and dropping');
                } elseif ($objCrit->sort_by == 'o_position' || $displayType == 9) {
                    $msgConfirm .= '<br />'.$this->l('Criteria will automaticaly inherit position');
                }
                $this->infos[] = $msgConfirm;
                $this->displayInfosJs(true);
            }
        }
        $criterions = SearchEngineUtils::getCriterionsFromCriterionGroup($objCrit->id, $objCrit->id_search, $objCrit->sort_by, $objCrit->sort_way, (int)$this->context->language->id);
        $hasCustomCriterions = false;
        foreach ($criterions as &$row) {
            $objCritClass = new Criterion($row['id_criterion'], $objCrit->id_search);
            $row['obj'] = $objCritClass;
            if ($objCrit->criterion_group_type == 'category') {
                $row['parent_name'] = SearchEngineUtils::getCategoryName((int)$row['id_parent'], (int)$this->context->language->id);
            }
            if (!empty($row['is_custom'])) {
                $hasCustomCriterions = true;
            }
            if ((!isset($row['is_custom']) || isset($row['is_custom']) && !$row['is_custom'])) {
                $customCriterionsList = Criterion::getCustomCriterions($objCrit->id_search, $objCrit->id, (int)$this->context->language->id);
                if (is_array($customCriterionsList) && sizeof($customCriterionsList)) {
                    $customCriterionsList = array(0 => $this->l('None')) + $customCriterionsList;
                }
                $row['custom_criterions_list'] = $customCriterionsList;
                $row['custom_criterions_obj'] = (object)array('custom_group_link_id_'.(int)$row['id_criterion'] => Criterion::getCustomMasterIdCriterion((int)$objCrit->id_search, (int)$row['id_criterion']));
            }
        }
        $config = $this->getModuleConfiguration();
        $autoSyncActiveStatus = (!empty($config['autoSyncActiveStatus']) && in_array($objCrit->criterion_group_type, array('category', 'manufacturer', 'supplier')));
        $vars = array(
            'auto_sync_active_status' => $autoSyncActiveStatus,
            'criterion_group' => $objCrit,
            'criterions' => $criterions,
            'is_color_group' => SearchEngineIndexation::isColorAttributesGroup($objCrit->id_criterion_group_linked),
            'pm_load_function' => Tools::getValue('pm_load_function'),
            'has_custom_criterions' => $hasCustomCriterions,
            'supportsImageCriterionGroup' => self::supportsImageCriterionGroup(),
        );
        return $this->fetchTemplate('module/criterion_group/criterions_list.tpl', $vars);
    }
    public function translateMultiple($type, $category_level_depth = false)
    {
        $return = array();
        /*
        $this->l('Brand');
        $this->l('Manufacturer');
        $this->l('Supplier');
        $this->l('Categories level');
        $this->l('Categories');
        $this->l('Subcategories');
        $this->l('Price');
        $this->l('offers a selection of');
        $this->l('Between');
        $this->l('and');
        $this->l('From');
        $this->l('More than');
        $this->l('to');
        $this->l('On sale');
        $this->l('In stock');
        $this->l('Available for order');
        $this->l('Online only');
        $this->l('Weight');
        $this->l('Width');
        $this->l('Height');
        $this->l('Depth');
        $this->l('Condition');
        $this->l('New');
        $this->l('Used');
        $this->l('Refurbished');
        $this->l('Yes');
        $this->l('No');
        $this->l('Is a pack');
        $this->l('Duplicated from %s');
        */
        $toTranslate = array(
            'brand' => 'Brand',
            'manufacturer' => 'Brand',
            'supplier' => 'Supplier',
            'categories' => ($category_level_depth ? 'Categories level' : 'Categories'),
            'subcategory' => 'Subcategories',
            'price' => 'Price',
            'offers_selection' => 'offers a selection of',
            'between' => 'Between',
            'and' => 'and',
            'from' => 'From',
            'more_than' => 'More than',
            'to' => 'to',
            'on_sale' => 'On sale',
            'stock' =>  'In stock',
            'available_for_order' => 'Available for order',
            'online_only' => 'Online only',
            'weight' => 'Weight',
            'width' => 'Width',
            'height' => 'Height',
            'depth' => 'Depth',
            'condition' => 'Condition',
            'new' => 'New',
            'used' => 'Used',
            'refurbished' => 'Refurbished',
            'yes' => 'Yes',
            'no' => 'No',
            'pack' => 'Is a pack',
            'subscription' => 'Subscription available',
            'new_products' => 'New products',
            'prices_drop' => 'Prices drop',
            'duplicated_from' => 'Duplicated from %s',
        );
        foreach ($this->languages as $language) {
            $langObj = new Language((int)$language['id_lang']);
            $return[$language['id_lang']] = self::getCustomModuleTranslation($this->name, $toTranslate[$type], $langObj);
            if ($category_level_depth) {
                $return[$language['id_lang']] .= ' ' . $category_level_depth;
            }
        }
        return $return;
    }
    protected function assignProductSort($search)
    {
        if ($search instanceof Search) {
            $orderByDefault = $search->products_order_by;
            $orderWayDefault = $search->products_order_way;
        } else {
            $orderByDefault = $search['products_order_by'];
            $orderWayDefault = $search['products_order_way'];
        }
        $stock_management = (int)(Configuration::get('PS_STOCK_MANAGEMENT')) ? true : false;
        $orderBy = SearchEngineUtils::getOrderByValue($search);
        $orderWay = SearchEngineUtils::getOrderWayValue($search);
        $this->context->smarty->assign(array(
            'orderby' => $orderBy,
            'orderway' => $orderWay,
            'orderbydefault' => SearchEngineUtils::$orderByValues[(int)($orderByDefault)],
            'orderwayposition' => SearchEngineUtils::$orderWayValues[(int)($orderWayDefault)],
            'orderwaydefault' => SearchEngineUtils::$orderWayValues[(int)($orderWayDefault)],
            'stock_management' => (int)$stock_management
        ));
    }
    protected function assignPagination($products_per_page, $nbProducts = 10)
    {
        $nArray = array_unique(array((int)$products_per_page, 20, 40, 80));
        asort($nArray);
        $this->n = abs((int)(Tools::getValue('n', ((isset($this->context->cookie->nb_item_per_page) && $this->context->cookie->nb_item_per_page >= 10) ? $this->context->cookie->nb_item_per_page : (int)$products_per_page))));
        $this->p = abs((int)(Tools::getValue('p', 1)));
        $range = 2;
        if ($this->p < 0) {
            $this->p = 0;
        }
        if (isset($this->context->cookie->nb_item_per_page) && $this->n != $this->context->cookie->nb_item_per_page && in_array($this->n, $nArray)) {
            $this->context->cookie->nb_item_per_page = $this->n;
        }
        if ($this->p > ($nbProducts / $this->n)) {
            $this->p = ceil($nbProducts / $this->n);
        }
        $pages_nb = ceil($nbProducts / (int)$this->n);
        $start = (int)($this->p - $range);
        if ($start < 1) {
            $start = 1;
        }
        $stop = (int)($this->p + $range);
        if ($stop > $pages_nb) {
            $stop = (int)$pages_nb;
        }
        $this->context->smarty->assign(array(
            'nb_products' => (int)$nbProducts,
            'pages_nb' => (int)$pages_nb,
            'p' => (int)$this->p,
            'n' => (int)$this->n,
            'nArray' => $nArray,
            'range' => (int)$range,
            'start' => (int)$start,
            'stop' => (int)$stop,
            'products_per_page' => (int)$products_per_page
        ));
    }
    public function getTextualRangeValue($rangeStart, $rangeEnd, $criterionGroup, $currency)
    {
        $fakeCurrency = clone($currency);
        $fakeCurrency->sign = '';
        $fakeCurrency->blank = '';
        $isPriceGroup = false;
        $rangeSign = '';
        if (is_array($criterionGroup)) {
            $rangeSign = $criterionGroup['range_sign'];
            $isPriceGroup = (!empty($criterionGroup['criterion_group_type']) && $criterionGroup['criterion_group_type'] == 'price');
        } elseif (is_object($criterionGroup)) {
            $rangeSign = $criterionGroup->range_sign;
            $isPriceGroup = (!empty($criterionGroup->criterion_group_type) && $criterionGroup->criterion_group_type == 'price');
        }
        $textualRange = '';
        if ($rangeEnd) {
            $textualRange .= $this->l('From') . ' ';
        } else {
            $textualRange .= $this->l('More than') . ' ';
        }
        if ($isPriceGroup) {
            $textualRange .= $this->formatPrice($rangeStart, ($rangeEnd ? $fakeCurrency : $currency));
        } else {
            $textualRange .= $rangeStart . ($rangeEnd ? '' : ' ' . $rangeSign);
        }
        if ($rangeEnd) {
            $textualRange .= ' ' . $this->l('to') . ' ';
            if ($isPriceGroup) {
                $textualRange .= $this->formatPrice($rangeEnd, $currency);
            } else {
                $textualRange .= $rangeEnd . ' ' . $rangeSign;
            }
        }
        return $textualRange;
    }
    public function getCriterionsGroupsAndCriterionsForSearch($result, $id_lang, $selected_criterion = array(), $with_products = false, $id_criterion_group = 0)
    {
        if (!Core::isFilledArray($result)) {
            return $result;
        }
        static $return = array();
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset($return[$cacheKey])) {
            return $return[$cacheKey];
        }
        $currency = $this->context->currency;
        $hidden_criteria_state = (isset($this->context->cookie->hidden_criteria_state) ? Core::decodeCriteria($this->context->cookie->hidden_criteria_state) : array());
        $selected_criteria_groups_type = array();
        if (!$selected_criterion || (is_array($selected_criterion) && ! sizeof($selected_criterion))) {
            $reinit_selected_criterion = true;
        }
        $idSeo = (int)Tools::getValue('id_seo', null);
        $seoPage = null;
        $seoCriterions = null;
        if (self::supportsSeoPages() && !empty($idSeo)) {
            $seoPage = new Seo($idSeo);
            if (!Validate::isLoadedObject($seoPage)) {
                $seoPage = null;
                $idSeo = null;
            } else {
                $seoCriterions = Core::decodeCriteria($seoPage->criteria);
            }
        }
        foreach ($result as $key => $row) {
            if (!isset($result[$key]['seo_criterion_groups'])) {
                $result[$key]['seo_criterion_groups'] = array();
            }
            if (self::supportsSeoPages() && $row['filter_by_emplacement'] && $seoPage !== null && !empty($seoPage->criteria)) {
                $result[$key]['id_seo'] = (int)$seoPage->id;
                if (Core::isFilledArray($seoCriterions)) {
                    foreach ($seoCriterions as $critere_seo) {
                        $critere_seo = explode('_', $critere_seo);
                        $id_criterion_group_seo = (int)$critere_seo[0];
                        $result[$key]['seo_criterion_groups'][] = $id_criterion_group_seo;
                    }
                }
                $result[$key]['seo_criterion_groups'] = array_unique($result[$key]['seo_criterion_groups']);
            }
            if (isset($reinit_selected_criterion)) {
                $selected_criterion = array();
            }
            if (self::supportsSeoPages() && $this->context->controller instanceof pm_advancedsearch4seoModuleFrontController && $this->context->controller->getIdSeo()) {
                $selected_criterion = $this->context->controller->getSelectedCriterions();
            }
            if ($this->context->controller instanceof pm_advancedsearch4searchresultsModuleFrontController && $this->context->controller->getSearchEngine()->id != $row['id_search']) {
                $selected_criterion = array();
            }
            if (self::supportsSeoPages() && Tools::getValue('seo_url')) {
                $result[$key]['keep_category_information'] = 0;
                $row['keep_category_information'] = 0;
            }
            $result[$key]['selected_criterion_from_emplacement'] = array();
            if ($row['filter_by_emplacement']) {
                $result[$key]['selected_criterion_from_emplacement'] = SearchEngineUtils::getCriteriaFromEmplacement($row['id_search'], $row['id_category_root']);
            }
            if ($row['filter_by_emplacement'] && (!$selected_criterion || (is_array($selected_criterion) && ! sizeof($selected_criterion)))) {
                $selected_criterion = $result[$key]['selected_criterion_from_emplacement'];
            }
            if (self::supportsSeoPages() && $row['filter_by_emplacement'] && $seoPage !== null && !empty($seoPage->criteria)) {
                $result[$key]['id_seo'] = (int)$seoPage->id;
                if (self::isFilledArray($seoCriterions)) {
                    foreach ($seoCriterions as $critere_seo) {
                        $critere_seo = explode('_', $critere_seo);
                        $id_criterion_group_seo = (int)$critere_seo[0];
                        if (!preg_match('#~#', $critere_seo[1])) {
                            $id_criterion_value = (int)$critere_seo[1];
                        } else {
                            $id_criterion_value = $critere_seo[1];
                        }
                        if (isset($selected_criterion[$id_criterion_group_seo])) {
                            if (!in_array($id_criterion_value, $selected_criterion[$id_criterion_group_seo])) {
                                $selected_criterion[$id_criterion_group_seo][] = $id_criterion_value;
                            }
                        } else {
                            $selected_criterion[$id_criterion_group_seo] = array();
                            $selected_criterion[$id_criterion_group_seo][] = $id_criterion_value;
                        }
                    }
                }
            }
            if (is_array($selected_criterion) && sizeof($selected_criterion)) {
                $selected_criterion = SearchEngineUtils::cleanArrayCriterion($selected_criterion);
                $selected_criteria_groups_type = SearchEngineUtils::getCriterionGroupsTypeAndDisplay($row['id_search'], array_keys($selected_criterion));
            }
            if (self::supportsStepSearch() && $row['step_search'] && is_array($selected_criterion) && sizeof($selected_criterion)) {
                $selected_criterion_groups = array_keys($selected_criterion);
            }
            $current_selected_criterion = $selected_criterion;
            if (!$id_criterion_group) {
                $result[$key]['criterions_groups'] = CriterionGroup::getCriterionsGroupsFromIdSearch($row['id_search'], $id_lang, false);
            } else {
                $result[$key]['criterions_groups'] = CriterionGroup::getCriterionsGroup($row['id_search'], array((int)$id_criterion_group), $id_lang);
            }
            $selectedCriterionsForSeo = array();
            if (self::supportsSeoPages() && self::isFilledArray($current_selected_criterion)) {
                $currentSelectedCriterionTmp = $current_selected_criterion;
                if (self::isFilledArray($result[$key]['selected_criterion_from_emplacement'])) {
                    foreach ($result[$key]['selected_criterion_from_emplacement'] as $idCriterionGroupTmp => $selectedCriterionsTmp) {
                        foreach ($result[$key]['criterions_groups'] as $criterionGroupTmp) {
                            if (
                                $idCriterionGroupTmp != (int)$criterionGroupTmp['id_criterion_group']
                                || empty($criterionGroupTmp['visible'])
                                || !isset($currentSelectedCriterionTmp[$idCriterionGroupTmp])
                            ) {
                                continue;
                            }
                            foreach ($selectedCriterionsTmp as $idCriterionTmp) {
                                $criterionIndex = array_search($idCriterionTmp, $currentSelectedCriterionTmp[$idCriterionGroupTmp]);
                                if ($criterionIndex === false) {
                                    continue;
                                }
                                unset($currentSelectedCriterionTmp[$idCriterionGroupTmp][$criterionIndex]);
                                if (!sizeof($currentSelectedCriterionTmp[$idCriterionGroupTmp])) {
                                    unset($currentSelectedCriterionTmp[$idCriterionGroupTmp]);
                                }
                            }
                        }
                    }
                }
                foreach ($currentSelectedCriterionTmp as $idCriterionGroupSeo => $idCriterionsForSeo) {
                    foreach (array_values($idCriterionsForSeo) as $idCriterionForSeo) {
                        $selectedCriterionsForSeo[] = (int)$idCriterionGroupSeo.'_'.(int)$idCriterionForSeo;
                    }
                }
                unset($currentSelectedCriterionTmp);
                $selectedCriterionsForSeo = array_unique($selectedCriterionsForSeo);
            }
            $result[$key]['criterionsGroupsMini'] = array();
            foreach ($result[$key]['criterions_groups'] as $criterionGroup) {
                $result[$key]['criterionsGroupsMini'][(int)$criterionGroup['id_criterion_group']] = $criterionGroup['name'];
            }
            foreach ($result[$key]['criterions_groups'] as &$criterionGroup) {
                if (!isset($criterionGroup['is_skipped'])) {
                    $criterionGroup['is_skipped'] = false;
                }
            }
            foreach ($selected_criterion as $k => $v) {
                if (array_sum($v) == -1) {
                    foreach ($result[$key]['criterions_groups'] as &$criterionGroup) {
                        if ($criterionGroup['id_criterion_group'] == $k) {
                            $criterionGroup['is_skipped'] = true;
                        }
                    }
                    unset($selected_criterion[$k]);
                    unset($current_selected_criterion[$k]);
                }
            }
            foreach ($result[$key]['criterions_groups'] as $groupKey => &$criterionGroup) {
                if (isset($result[$key]['criterions_groups'][$groupKey+1])) {
                    $nextGroup = $result[$key]['criterions_groups'][$groupKey+1];
                    $nextHasSelectedCriterions = (isset($selected_criterion[$nextGroup['id_criterion_group']]) && sizeof($selected_criterion[$nextGroup['id_criterion_group']]) > 0);
                } else {
                    $nextHasSelectedCriterions = false;
                }
                $criterionGroup['next_group_have_selected_values'] = $nextHasSelectedCriterions;
            }
            foreach ($result[$key]['criterions_groups'] as &$tmpCriterionGroupRow) {
                if ($tmpCriterionGroupRow['criterion_group_type'] == 'attribute') {
                    if ($tmpCriterionGroupRow['display_type'] == 7) {
                        $tmpCriterionGroupRow['is_color_attribute'] = true;
                    } else {
                        $tmpCriterionGroupRow['is_color_attribute'] = SearchEngineIndexation::isColorAttributesGroup((int)$tmpCriterionGroupRow['id_criterion_group_linked']);
                    }
                } else {
                    $tmpCriterionGroupRow['is_color_attribute'] = false;
                }
            }
            $result[$key]['criterions_groups_selected'] = array();
            if (is_array($current_selected_criterion) && sizeof($current_selected_criterion)) {
                $result[$key]['criterions_groups_selected'] = CriterionGroup::getCriterionsGroup($row['id_search'], array_keys($current_selected_criterion), $id_lang);
                foreach ($current_selected_criterion as $id_criterion_group_selected => $selected_criteria) {
                    foreach ($selected_criteria as $criterion_value) {
                        if (!isset($result[$key]['criterions_selected'][$id_criterion_group_selected])) {
                            $result[$key]['criterions_selected'][$id_criterion_group_selected] = array();
                        }
                        if (preg_match('#~#', $criterion_value)) {
                            $range = explode('~', $criterion_value);
                            $rangeUp = (isset($range[1]) ? $range[1] : '');
                            $groupInfo = CriterionGroup::getCriterionGroupTypeAndRangeSign($row['id_search'], $id_criterion_group_selected, $id_lang);
                            $result[$key]['criterions_selected'][$id_criterion_group_selected][] = array(
                                'value' => $this->getTextualRangeValue($range[0], $rangeUp, $groupInfo, $currency),
                                'id_criterion' => $criterion_value,
                                'visible' => 1,
                            );
                        } else {
                            $result[$key]['criterions_selected'][$id_criterion_group_selected][] = Criterion::getCriterionValueById($row['id_search'], $id_lang, $criterion_value);
                        }
                    }
                }
            }
            if (!Tools::getValue('only_products')) {
                $result[$key]['advanced_search_open'] = 0;
                if (isset($hidden_criteria_state[$row['id_search']])) {
                    $result[$key]['advanced_search_open'] = $hidden_criteria_state[$row['id_search']];
                }
                if (!sizeof($result[$key]['criterions_groups'])) {
                    continue;
                }
                $prev_id_criterion_group = false;
                foreach ($result[$key]['criterions_groups'] as $key2 => $row2) {
                    if ($row2['visible'] == 0) {
                        continue;
                    }
                    if ($row2['criterion_group_type'] == 'subcategory') {
                        if (
                            !($this->context->controller instanceof pm_advancedsearch4advancedsearch4ModuleFrontController)
                            && !($this->context->controller instanceof pm_advancedsearch4searchresultsModuleFrontController)
                            && $this->context->controller->php_self != 'index'
                            && $this->context->controller->php_self != 'category'
                            && $this->context->controller->php_self != 'product'
                        ) {
                            continue;
                        }
                    }
                    if (
                        !$row2['visible']
                        && !isset($selected_criterion[$row2['id_criterion_group']])
                        && (
                            ($row2['criterion_group_type'] == 'manufacturer' && !Tools::getValue('id_manufacturer'))
                            || ($row2['criterion_group_type'] == 'supplier' && !Tools::getValue('id_supplier'))
                            || ($row2['criterion_group_type'] == 'category' && !Tools::getValue('id_category'))
                        )
                    ) {
                        continue;
                    }
                    if (self::supportsStepSearch() && $this->isFirstStep($row, $row2, (isset($selected_criterion_groups) ? $selected_criterion_groups : null), $prev_id_criterion_group, $result, $key, $key2)) {
                        continue;
                    }
                    if ($row2['range'] == 1) {
                        $ranges = explode(',', $row2['range_interval']);
                        $criteria_formated = array();
                        foreach ($ranges as $krange => $range) {
                            $rangeUp = (isset($ranges[$krange+1]) ? $ranges[$krange+1] : '');
                            $range1 = $range.'~'.$rangeUp;
                            $range2 = $this->getTextualRangeValue($range, $rangeUp, $row2, $currency);
                            if (is_array($selected_criterion)) {
                                $citeria4count = $selected_criterion;
                            } else {
                                $citeria4count = array();
                            }
                            $citeria4count[$row2['id_criterion_group']] = array($range1);
                            $selected_criteria_groups_type2 = SearchEngineUtils::getCriterionGroupsTypeAndDisplay($row['id_search'], array_keys($citeria4count));
                            $nb_products = SearchEngineUtils::getProductsSearched((int)$row['id_search'], SearchEngineUtils::cleanArrayCriterion($citeria4count), $selected_criteria_groups_type2, null, null, true);
                            if (!$row['display_empty_criteria'] && !$nb_products) {
                                continue;
                            }
                            $criteria_formated[$range1] = array('id_criterion' => $range1, 'value' => $range2, 'nb_product'=> $nb_products);
                        }
                        $result[$key]['criterions'][$row2['id_criterion_group']] = $criteria_formated;
                    } elseif ($row2['criterion_group_type'] == 'price') {
                        $range_selected_criterion = SearchEngineUtils::cleanArrayCriterion($selected_criterion);
                        unset($range_selected_criterion[$row2['id_criterion_group']]);
                        $result[$key]['criterions'][$row2['id_criterion_group']] = SearchEngineUtils::getPriceRangeForSearchBloc($row, (int)$row2['id_criterion_group'], (int)$this->context->currency->id, (int)$this->context->country->id, (int)$this->getCurrentCustomerGroupId(), (self::supportsStepSearch() && $row['step_search'] && !$id_criterion_group && $key2 == 0 ? array() : $range_selected_criterion), (self::supportsStepSearch() && $row['step_search'] && !$id_criterion_group && $key2 == 0 ? array() : $selected_criteria_groups_type));
                        $min_price_id_currency = (int)$result[$key]['criterions'][$row2['id_criterion_group']][0]['min_price_id_currency'];
                        $max_price_id_currency = (int)$result[$key]['criterions'][$row2['id_criterion_group']][0]['max_price_id_currency'];
                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['min'] = $result[$key]['criterions'][$row2['id_criterion_group']][0]['min_price'];
                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['max'] = $result[$key]['criterions'][$row2['id_criterion_group']][0]['max_price'];
                        if (($min_price_id_currency == 0 && $min_price_id_currency != $this->context->cookie->id_currency) || ($min_price_id_currency != 0 && $min_price_id_currency != $this->context->cookie->id_currency)) {
                            $result[$key]['criterions'][$row2['id_criterion_group']][0]['min'] = floor(Tools::convertPrice($result[$key]['criterions'][$row2['id_criterion_group']][0]['min_price'], null, $this->context->cookie->id_currency));
                        }
                        if (($max_price_id_currency == 0 && $max_price_id_currency != $this->context->cookie->id_currency) || ($max_price_id_currency != 0 && $max_price_id_currency != $this->context->cookie->id_currency)) {
                            $result[$key]['criterions'][$row2['id_criterion_group']][0]['max'] = ceil(Tools::convertPrice($result[$key]['criterions'][$row2['id_criterion_group']][0]['max_price'], null, $this->context->cookie->id_currency));
                        }
                        if (self::supportsStepSearch() && $this->isStepSearchSliderUnavailable($row, $result, $key, $row2)) {
                            unset($result[$key]['criterions_groups'][$key2]);
                            continue;
                        }
                        $currencyPrecision = null;
                        if (version_compare(_PS_VERSION_, '1.7.6.0', '>=')) {
                            $locale = $this->context->getCurrentLocale();
                            if (is_int($currency)) {
                                $currency = Currency::getCurrencyInstance($this->context->currency);
                            }
                            $isoCode = is_array($currency) ? $currency['iso_code'] : $currency->iso_code;
                            $priceSpecification = $locale->getPriceSpecification($isoCode);
                            $currencySymbol = $priceSpecification->getCurrencySymbol();
                            $currencyFormat = $priceSpecification->getPositivePattern();
                            $currencyPrecision = (int)$this->context->currency->precision;
                            $currencyIsoCode = $isoCode;
                        } else {
                            $cldr = Tools::getCldr($this->context);
                            $cldrCurrency = new \ICanBoogie\CLDR\Currency($cldr->getRepository(), $this->context->currency->iso_code);
                            $localizedCurrency = $cldrCurrency->localize($cldr->getCulture());
                            $currencySymbol = $localizedCurrency->locale['currencies'][$localizedCurrency->target->code]['symbol'];
                            $currencyFormat = $currency->format;
                            $currencyIsoCode = $currency->iso_code;
                        }
                        $result[$key]['criterions_groups'][$key2]['currency_symbol'] = $currencySymbol;
                        $result[$key]['criterions_groups'][$key2]['currency_iso_code'] = $currencyIsoCode;
                        $result[$key]['criterions_groups'][$key2]['currency_precision'] = $currencyPrecision;
                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['step'] = ((float)$row2['range_nb'] <= 0 ? 1 : $row2['range_nb']);
                        SearchEngineUtils::setupMinMaxUsingStep($result[$key]['criterions'][$row2['id_criterion_group']][0]['step'], $result[$key]['criterions'][$row2['id_criterion_group']][0]['min'], $result[$key]['criterions'][$row2['id_criterion_group']][0]['max']);
                        if (Core::isFilledArray($selected_criterion) && isset($selected_criterion[$row2['id_criterion_group']]) && sizeof($selected_criterion[$row2['id_criterion_group']])) {
                            $range = explode('~', $selected_criterion[$row2['id_criterion_group']][0]);
                            $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_min'] = $range[0];
                            if (isset($range[1])) {
                                $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max'] = $range[1];
                            } else {
                                $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max'] = $result[$key]['criterions'][$row2['id_criterion_group']][0]['max'];
                            }
                        }
                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_min_currency_formated'] = '';
                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max_currency_formated'] = '';
                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['min_currency_formated'] = '';
                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['max_currency_formated'] = '';
                        if (isset($result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_min'])) {
                            $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_min_currency_formated']= $this->formatPrice($result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_min']);
                        }
                        if (isset($result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max'])) {
                            $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max_currency_formated']= $this->formatPrice($result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max']);
                        }
                        if (isset($result[$key]['criterions'][$row2['id_criterion_group']][0]['min'])) {
                            $result[$key]['criterions'][$row2['id_criterion_group']][0]['min_currency_formated']= $this->formatPrice($result[$key]['criterions'][$row2['id_criterion_group']][0]['min']);
                        }
                        if (isset($result[$key]['criterions'][$row2['id_criterion_group']][0]['max'])) {
                            $result[$key]['criterions'][$row2['id_criterion_group']][0]['max_currency_formated']= $this->formatPrice($result[$key]['criterions'][$row2['id_criterion_group']][0]['max']);
                        }
                    } elseif ($row2['criterion_group_type'] == 'subcategory' || $row2['display_type'] == 9) {
                        $idCategoryStart = false;
                        $currentIdCategory = SearchEngineUtils::getCurrentCategory();
                        if (empty($currentIdCategory)) {
                            if ($this->context->controller instanceof CategoryController) {
                                $currentIdCategory = (int)$this->context->controller->getCategory()->id;
                            } elseif ($this->context->controller instanceof ProductController) {
                                $currentIdCategory = (int)$this->context->controller->getProduct()->id_category_default;
                            } elseif ($this->context->controller instanceof IndexController) {
                                $currentIdCategory = (int)$this->context->shop->getCategory();
                            }
                        }
                        if ($row2['criterion_group_type'] == 'subcategory' || empty($row2['context_type'])) {
                            $idCategoryStart = (int)$currentIdCategory;
                        } elseif ($row2['context_type'] == 1) {
                            $idCategoryStart = (int)$currentIdCategory;
                            if ((int)$this->context->shop->getCategory() != $idCategoryStart) {
                                $currentCategoryObj = new Category($currentIdCategory);
                                if (Validate::isLoadedObject($currentCategoryObj) && !empty($currentCategoryObj->id_parent) && $currentCategoryObj->id_parent > 0) {
                                    $idCategoryStart = (int)$currentCategoryObj->id_parent;
                                }
                            }
                        } elseif ($row2['context_type'] == 2) {
                            $idCategoryStart = (int)$this->context->shop->getCategory();
                        }
                        $criteriaList = SearchEngineUtils::getCriterionsForSearchBloc($row, $row2['id_criterion_group'], SearchEngineUtils::cleanArrayCriterion($selected_criterion), $selected_criteria_groups_type, true, $row2, $result[$key]['criterions_groups']);
                        if (empty($idCategoryStart) && isset($selected_criterion[$row2['id_criterion_group']]) && self::isFilledArray($selected_criterion[$row2['id_criterion_group']]) && sizeof($selected_criterion[$row2['id_criterion_group']]) == 1) {
                            $currentParentIdCriterion = current($selected_criterion[$row2['id_criterion_group']]);
                            foreach ($criteriaList as $criteria) {
                                if ((int)$criteria['id_criterion'] == $currentParentIdCriterion) {
                                    $idCategoryStart = (int)$criteria['id_parent'];
                                    break;
                                }
                            }
                        }
                        $selected_criterions = array();
                        if (isset($selected_criterion[$row2['id_criterion_group']])) {
                            foreach ($selected_criterion[$row2['id_criterion_group']] as $selected_crit) {
                                $selected_criterions[] = $selected_crit;
                            }
                        }
                        $childrensList = array();
                        $linkedList = array();
                        $parentsList = array();
                        foreach ($criteriaList as $criterion_row) {
                            if (!isset($childrensList[$criterion_row['id_parent']])) {
                                $childrensList[$criterion_row['id_parent']] = array();
                            }
                            if (isset($selected_criterions) && count($selected_criterions) > 0 && ($selected_criterions[0] == $criterion_row['id_criterion'])) {
                                $selected_criterions[0] = (int)$criterion_row['id_criterion_linked'];
                            }
                            $childrensList[$criterion_row['id_parent']][] = $criterion_row;
                            $linkedList[$criterion_row['id_criterion_linked']][] = $criterion_row['id_parent'];
                            $parentsList[$criterion_row['id_criterion']][] = $criterion_row['id_parent'];
                        }
                        krsort($childrensList);
                        if (isset($selected_criterions) && count($selected_criterions) > 0) {
                            $this->recursiveGetParents($selected_criterions, $linkedList, $idCategoryStart, $selected_criterions[0]);
                        }
                        foreach ($criteriaList as $r => &$criterion_row) {
                            if (!empty($criterion_row['is_custom'])) {
                                continue;
                            }
                            if ($row2['criterion_group_type'] == 'category' && empty($row2['context_type'])) {
                                if ((int)$criterion_row['id_criterion_linked'] == (int)$idCategoryStart) {
                                    continue;
                                }
                                unset($criteriaList[$r]);
                            } else {
                                if ((int)$criterion_row['id_parent'] != (int)$idCategoryStart) {
                                    unset($criteriaList[$r]);
                                    continue;
                                }
                            }
                        }
                        $result[$key]['criterions'][$row2['id_criterion_group']] = $criteriaList;
                        $result[$key]['criterions_childrens'][$row2['id_criterion_group']] = $childrensList;
                        $result[$key]['selected_criterions_ld'][$row2['id_criterion_group']] = $selected_criterions;
                    } elseif ($row2['display_type'] == 5 || $row2['display_type'] == 8) {
                        $range_selected_criterion = SearchEngineUtils::cleanArrayCriterion($selected_criterion);
                        unset($range_selected_criterion[$row2['id_criterion_group']]);
                        $result[$key]['criterions'][$row2['id_criterion_group']] = SearchEngineUtils::getCriterionsRange($row, (int)$row2['id_criterion_group'], (int)$id_lang, $range_selected_criterion, $selected_criteria_groups_type, (int)$this->context->cookie->id_currency, (int)$this->getCurrentCustomerGroupId(), $row2);
                        if (self::supportsStepSearch() && $this->isStepSearchSliderUnavailable($row, $result, $key, $row2)) {
                            unset($result[$key]['criterions_groups'][$key2]);
                            continue;
                        }
                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['step'] = ((float)$row2['range_nb'] <= 0 ? 1 : $row2['range_nb']);
                        $result[$key]['criterions_groups'][$key2]['left_range_sign'] = '';
                        $result[$key]['criterions_groups'][$key2]['right_range_sign'] = (isset($row2['range_sign']) && Tools::strlen($row2['range_sign']) > 0 ? ' '.$row2['range_sign'] : '');
                        SearchEngineUtils::setupMinMaxUsingStep($result[$key]['criterions'][$row2['id_criterion_group']][0]['step'], $result[$key]['criterions'][$row2['id_criterion_group']][0]['min'], $result[$key]['criterions'][$row2['id_criterion_group']][0]['max']);
                        if (Core::isFilledArray($selected_criterion) && isset($selected_criterion[$row2['id_criterion_group']]) && sizeof($selected_criterion[$row2['id_criterion_group']])) {
                            $range = explode('~', $selected_criterion[$row2['id_criterion_group']][0]);
                            $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_min'] = $range[0];
                            if (isset($range[1])) {
                                $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max'] = $range[1];
                            } else {
                                $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max'] = $result[$key]['criterions'][$row2['id_criterion_group']][0]['max'];
                            }
                        }
                    } else {
                        $criteria = SearchEngineUtils::getCriterionsForSearchBloc($row, $row2['id_criterion_group'], SearchEngineUtils::cleanArrayCriterion($selected_criterion), $selected_criteria_groups_type, true, $row2, $result[$key]['criterions_groups']);
                        if (self::supportsSeoPages() && ($row2['display_type'] == 3 || $row2['display_type'] == 4 || $row2['display_type'] == 7)) {
                            \AdvancedSearch\SearchEngineSeo::addSeoPageUrlToCriterions($row['id_search'], $criteria, $selectedCriterionsForSeo);
                        }
                        $criteria_formated = array();
                        if ($criteria && sizeof($criteria)) {
                            $criteria_formated = array();
                            foreach ($criteria as $rowCriteria) {
                                $criteria_formated[$rowCriteria['id_criterion']] = $rowCriteria;
                            }
                        }
                        $result[$key]['criterions'][$row2['id_criterion_group']] = $criteria_formated;
                        if (
                            $row['filter_by_emplacement']
                            && $seoPage === null
                            && (SearchEngineUtils::getCurrentManufacturer() || SearchEngineUtils::getCurrentSupplier())
                        ) {
                            $preSelectedCriterionEmplacement = SearchEngineUtils::getCriteriaFromEmplacement($row['id_search']);
                            if (
                                is_array($preSelectedCriterionEmplacement)
                                && isset($preSelectedCriterionEmplacement[$row2['id_criterion_group']])
                                && Core::isFilledArray($preSelectedCriterionEmplacement[$row2['id_criterion_group']])
                            ) {
                                $result[$key]['criterions_groups'][$key2]['is_preselected_by_emplacement'] = true;
                            }
                        }
                    }
                    $result[$key]['selected_criterions'][$row2['id_criterion_group']]['is_selected'] = false;
                    if (
                        !$row['step_search']
                        || $key2 == 0
                        || (
                            isset($selected_criterion_groups)
                            && (
                                in_array($row2['id_criterion_group'], $selected_criterion_groups)
                                || ($prev_id_criterion_group && in_array($prev_id_criterion_group, $selected_criterion_groups))
                                || !sizeof($result[$key]['criterions'][$prev_id_criterion_group])
                            )
                        )
                    ) {
                        $result[$key]['selected_criterions'][$row2['id_criterion_group']]['is_selected'] = true;
                    }
                    $prev_id_criterion_group = $row2['id_criterion_group'];
                }
            }
            if ($with_products) {
                $result[$key]['products'] = SearchEngineUtils::getProductsSearched((int)$row['id_search'], SearchEngineUtils::cleanArrayCriterion($selected_criterion), $selected_criteria_groups_type, (int)Tools::getValue('p', 1), (int)Tools::getValue('n', $row['products_per_page']), false);
            }
            if (
                $with_products
                || (isset($row['display_nb_result_on_blc']) && $row['display_nb_result_on_blc'])
                || (isset($row['hide_criterions_group_with_no_effect']) && $row['hide_criterions_group_with_no_effect'])
            ) {
                $result[$key]['total_products'] = SearchEngineUtils::getProductsSearched((int)$row['id_search'], SearchEngineUtils::cleanArrayCriterion($selected_criterion), $selected_criteria_groups_type, null, null, true);
            }
            $result[$key]['selected_criterion'] = $selected_criterion;
            if (isset($row['hide_criterions_group_with_no_effect']) && $row['hide_criterions_group_with_no_effect']) {
                foreach ($result[$key]['criterions_groups'] as $criterions_group_key => $criterions_group) {
                    if ($criterions_group['criterion_group_type'] != 'attribute') {
                        if ($criterions_group['criterion_group_type'] == 'category' && $criterions_group['display_type'] == 9) {
                            continue;
                        }
                        if (isset($result[$key]['selected_criterion'][$criterions_group['id_criterion_group']]) && self::isFilledArray($result[$key]['selected_criterion'][$criterions_group['id_criterion_group']])) {
                            continue;
                        }
                        if (isset($result[$key]['criterions'][$criterions_group['id_criterion_group']]) && sizeof($result[$key]['criterions'][$criterions_group['id_criterion_group']]) == 1) {
                            foreach ($result[$key]['criterions'][$criterions_group['id_criterion_group']] as $criterion_group) {
                                $removeCriterionGroup = (isset($criterion_group['nb_product']) && $criterion_group['nb_product'] == $result[$key]['total_products']);
                                if (!$removeCriterionGroup && !isset($criterion_group['nb_product']) && $criterions_group['display_type'] == 5 && isset($criterion_group['min'], $criterion_group['max']) && $criterion_group['min'] == $criterion_group['max']) {
                                    if ($criterions_group['criterion_group_type'] == 'price') {
                                        $removeCriterionGroup = true;
                                    } else {
                                        $selected_criterion[(int)$criterions_group['id_criterion_group']] = array($criterion_group['min'] . '~' . $criterion_group['max']);
                                        $selected_criteria_groups_type[(int)$criterions_group['id_criterion_group']] = $criterions_group;
                                        $nb_product = SearchEngineUtils::getProductsSearched((int)$row['id_search'], SearchEngineUtils::cleanArrayCriterion($selected_criterion), $selected_criteria_groups_type, null, null, true);
                                        $removeCriterionGroup = ($nb_product == $result[$key]['total_products']);
                                    }
                                }
                                if ($removeCriterionGroup) {
                                    unset($result[$key]['criterions_groups'][$criterions_group_key]);
                                    unset($result[$key]['criterions'][$criterions_group['id_criterion_group']]);
                                }
                            }
                        }
                    }
                }
            }
            $result[$key]['nb_visible_criterions_groups'] = 0;
            foreach ($result[$key]['criterions_groups'] as $criterions_group_key => $criterions_group) {
                $result[$key]['criterions_groups'][$criterions_group_key]['display_group'] = !empty($criterions_group['visible']);
                $idCriterionGroup = (int)$criterions_group['id_criterion_group'];
                $criterions = (isset($result[$key]['criterions'][$idCriterionGroup]) ? $result[$key]['criterions'][$idCriterionGroup] : null);
                if (
                    in_array($this->criteria_group_type_interal_name[$criterions_group['display_type']], ['slider', 'range'])
                    && $criterions != null
                    && isset($criterions[0], $criterions[0]['cur_min'], $criterions[0]['cur_max'])
                    && $criterions[0]['cur_min'] == 0
                    && $criterions[0]['cur_max'] == 0
                ) {
                    $result[$key]['criterions_groups'][$criterions_group_key]['display_group'] = false;
                    continue;
                }
                if (
                    in_array($this->criteria_group_type_interal_name[$criterions_group['display_type']], ['slider', 'range'])
                    && $criterions != null
                    && isset($criterions[0], $criterions[0]['min'], $criterions[0]['max'])
                    && $criterions[0]['min'] == 0
                    && $criterions[0]['max'] == 0
                ) {
                    $result[$key]['criterions_groups'][$criterions_group_key]['display_group'] = false;
                    continue;
                }
                if ($result[$key]['hide_empty_crit_group'] && !self::isFilledArray($criterions)) {
                    $result[$key]['criterions_groups'][$criterions_group_key]['display_group'] = false;
                    continue;
                }
                if (!empty($criterions_group['visible'])) {
                    $result[$key]['nb_visible_criterions_groups']++;
                }
            }
            if (empty($result[$key]['id_seo']) && !empty($result[$key]['hide_empty_crit_group']) && empty($result[$key]['display_empty_criteria'])
                && (isset($result[$key]['total_products']) && $result[$key]['total_products'] == 0)
                && (
                    ($result[$key]['filter_by_emplacement'] && $result[$key]['selected_criterion'] == $result[$key]['selected_criterion_from_emplacement'])
                    ||
                    (!$result[$key]['filter_by_emplacement'] && !self::isFilledArray($result[$key]['selected_criterion']))
                )
            ) {
                unset($result[$key]);
            }
            $result[$key]['hasOneVisibleSelectedCriterion'] = false;
            $currentSelection = (isset($result[$key]['selected_criterion']) && is_array($result[$key]['selected_criterion']) ? $result[$key]['selected_criterion'] : array());
            if (is_array($result[$key]['criterions_groups_selected'])) {
                foreach ($result[$key]['criterions_groups_selected'] as $criterionGroup) {
                    if ($result[$key]['hasOneVisibleSelectedCriterion']) {
                        break;
                    }
                    $idCriterionGroup = $criterionGroup['id_criterion_group'];
                    if (isset($result[$key]['criterions'][$idCriterionGroup])
                        && $criterionGroup['visible']
                        && isset($currentSelection[$idCriterionGroup])
                        && is_array($currentSelection[$idCriterionGroup])
                        && count($currentSelection[$idCriterionGroup])
                    ) {
                        foreach ($result[$key]['criterions_selected'][$idCriterionGroup] as $criterion) {
                            if (!empty($criterion['visible'])
                                && (isset($criterion['id_criterion'])
                                    && isset($currentSelection[$idCriterionGroup])
                                    && is_array($currentSelection[$idCriterionGroup])
                                    && in_array($criterion['id_criterion'], $currentSelection[$idCriterionGroup])
                                    || isset($criterion['min'])
                                )
                            ) {
                                $result[$key]['hasOneVisibleSelectedCriterion'] = true;
                                break;
                            }
                        }
                    }
                }
            }
            $result[$key]['productFilterListSource'] = SearchEngineUtils::$productFilterListSource;
        }
        $return[$cacheKey] = $result;
        return $result;
    }
    protected function recursiveGetParents(&$selected_criterions = array(), $linkedList = array(), $id_parent = 0, $current = array())
    {
        if (isset($linkedList[(int)$current]) && (int)$linkedList[(int)$current][0] != (int)$id_parent) {
            $selected_criterions[] = (int)$linkedList[(int)$current][0];
            $this->recursiveGetParents($selected_criterions, $linkedList, (int)$id_parent, (int)$linkedList[(int)$current][0]);
        }
    }
    protected function assignForProductsResults()
    {
        $this->context->smarty->assign(array('comparator_max_item' => (int)(Configuration::get('PS_COMPARATOR_MAX_ITEM'))));
        if (self::supportsSeoPages() && Tools::getIsset('id_seo') && (int)Tools::getValue('id_seo') > 0) {
            $resultSeoUrl = Seo::getSeoSearchByIdSeo((int)Tools::getValue('id_seo'), (int)$this->context->language->id);
            if (self::isFilledArray($resultSeoUrl)) {
                $this->context->smarty->assign(array(
                    'as_seo_title'       => $resultSeoUrl[0]['title'],
                    'as_seo_description' => $resultSeoUrl[0]['description'],
                ));
            }
        }
        $this->context->smarty->assign(array(
            'categorySize' => $this->getImageSize('category'),
            'mediumSize' => $this->getImageSize('medium'),
            'thumbSceneSize' => $this->getImageSize('thumb_scene'),
            'homeSize' => $this->getImageSize('home'),
            'static_token' => Tools::getToken(false),
        ));
    }
    protected function getImageSize($imageType)
    {
        $img_size = Image::getSize($imageType);
        if (!self::isFilledArray($img_size)) {
            $img_size = Image::getSize(ImageType::getFormattedName($imageType));
        } else {
            return $img_size;
        }
        if (!self::isFilledArray($img_size)) {
            $img_size = Image::getSize($imageType.'_default');
        }
        return $img_size;
    }
    protected function getLocationName($id_lang)
    {
        $location_name = false;
        $idCategory = SearchEngineUtils::getCurrentCategory();
        if ($idCategory) {
            $location_name = SearchEngineUtils::getCategoryName($idCategory, $id_lang);
        }
        return $location_name;
    }
    public function getNextIdCriterionGroup($id_search)
    {
        if (isset($this->context->cookie->{'next_id_criterion_group_'.(int)$id_search})) {
            return $this->context->cookie->{'next_id_criterion_group_'.(int)$id_search};
        }
        return '';
    }
    protected function fixPaginationLinks($pageContent)
    {
        $pageContent = str_replace(array(
            '?controller=advancedsearch4?fc=module',
            '?controller=advancedsearch4?',
        ), array(
            '?controller=advancedsearch4&fc=module',
            '?controller=advancedsearch4&',
        ), $pageContent);
        return $pageContent;
    }
    protected function getCacheId($name = null)
    {
        $cacheId = parent::getCacheId($name);
        if (SearchEngineUtils::$productFilterListQuery) {
            $cacheId .= '|' . sha1(SearchEngineUtils::$productFilterListQuery);
        }
        return $cacheId;
    }
    public function putInSmartyCache($cacheId, $data)
    {
        if (!is_object($this->context->smarty)) {
            return;
        }
        $config = $this->getModuleConfiguration();
        if (!empty($config['moduleCache'])) {
            $cacheId = $this->getCacheId($cacheId);
            $this->context->smarty->assign('serialized_data', self::getDataSerialized(serialize($data)));
            $templatePath = 'module:pm_advancedsearch4/views/templates/hook/'.Tools::substr(_PS_VERSION_, 0, 3).'/cache.tpl';
            $this->fetch($templatePath, $cacheId);
        }
    }
    public function getFromSmartyCache($cacheId)
    {
        $config = $this->getModuleConfiguration();
        if (!empty($config['moduleCache'])) {
            $cacheId = $this->getCacheId($cacheId);
            $templatePath = 'module:pm_advancedsearch4/views/templates/hook/'.Tools::substr(_PS_VERSION_, 0, 3).'/cache.tpl';
            if ($this->isCached($templatePath, $cacheId)) {
                $cacheData = unserialize(self::getDataUnserialized(trim(strip_tags($this->fetch($templatePath, $cacheId)))));
                if ($cacheData !== false) {
                    return $cacheData;
                }
            }
        }
        return null;
    }
    public function setSmartyVarsForTpl(Search $searchEngine, $selectedCriterions = array())
    {
        $with_product = false;
        $searchs = SearchEngineUtils::getSearch($searchEngine->id, (int)$this->context->language->id);
        $searchs = $this->getCriterionsGroupsAndCriterionsForSearch($searchs, (int)$this->context->language->id, $selectedCriterions, $with_product, false);
        $hookName = SearchEngineUtils::getHookName($searchs[0]['id_hook']);
        $this->context->smarty->assign(array(
            'hideAS4Form' => (((empty($hookName) && isset($searchs[0]) && isset($searchs[0]['id_hook']) && $searchs[0]['id_hook'] != -1) || $hookName == 'home') ? false : false),
            'ajaxMode' => Tools::getValue('ajaxMode'),
            'as_searchs' => $searchs,
            'as_search' => current($searchs),
            'hookName' => $hookName,
            'as_selected_criterion' => $selectedCriterions,
        ));
        $this->includeAssets();
    }
    public function displaySearchBlock($hookName, $tplName, $selected_criterion = array(), $specific_id_search = false, $fromWidget = false)
    {
        if ($this->context->controller instanceof pm_advancedsearch4searchresultsModuleFrontController) {
            $selected_criterion = $this->context->controller->getSelectedCriterions();
        }
        $inlineJsTplName = Tools::substr(_PS_VERSION_, 0, 3) . '/pm_advancedsearch_js.tpl';
        $newHookName = Hook::getRetroHookName($hookName);
        if ($newHookName == false) {
            $newHookName = $hookName;
        }
        $searchs = SearchEngineUtils::getSearchsFromHook($newHookName, (int)$this->context->language->id, $fromWidget);
        if ($specific_id_search !== false) {
            $newSearchs = array();
            if (sizeof($searchs)) {
                foreach ($searchs as $search) {
                    if ($search['id_search'] == (int)$specific_id_search) {
                        $newSearchs[] = $search;
                    }
                }
            }
            $searchs = $newSearchs;
        }
        if (!empty($searchs)) {
            $this->includeAssets();
            $searchs = $this->getCriterionsGroupsAndCriterionsForSearch($searchs, (int)$this->context->language->id, $selected_criterion, false);
            $config = $this->getModuleConfiguration();
            $this->context->smarty->assign(array(
                'as_searchs' => $searchs,
                'hookName' => $hookName,
                'as_selected_criterion' => $selected_criterion,
                'as_mobileVisible' => (bool) $config['mobileVisible'],
            ));
        } else {
            return '';
        }
        if ($inlineJsTplName) {
            self::$displayBeforeBodyClosingTagContent .= $this->display(__FILE__, $inlineJsTplName);
        }
        return $this->display(__FILE__, $this->templatePrefix . $tplName);
    }
    public function assignSearchVar()
    {
        $searchs = SearchEngineUtils::getSearchsFromHook(-1, (int)$this->context->language->id);
        if (self::isFilledArray($searchs)) {
            $this->includeAssets();
            foreach ($searchs as $search) {
                if ($this->context->controller instanceof pm_advancedsearch4searchresultsModuleFrontController && $this->context->controller->getSearchEngine()->id == (int)$search['id_search']) {
                    $selectedCriterions = $this->context->controller->getCriterionsList();
                } else {
                    $selectedCriterions = array();
                }
                $search['next_id_criterion_group'] = $this->getNextIdCriterionGroup((int)$search['id_search']);
                $search = $this->getCriterionsGroupsAndCriterionsForSearch(array(0 => $search), (int)$this->context->language->id, $selectedCriterions, false);
                if (isset($search[0])) {
                    $this->context->smarty->assign(array(
                        'as_searchs' => $search,
                        'hookName' => 'home',
                        'as_selected_criterion' => $selectedCriterions,
                    ));
                    $this->context->smarty->assign($search[0]['smarty_var_name'], $this->context->smarty->fetch($this->getTemplatePath('views/templates/hook/'.$this->templatePrefix.'pm_advancedsearch.tpl')));
                }
            }
        }
    }
    public function setProductFilterContext()
    {
        if (isset($this->context->controller) && is_object($this->context->controller)) {
            if (isset($this->context->controller->php_self) && in_array($this->context->controller->php_self, SearchEngineUtils::$validPageName)) {
                SearchEngineUtils::$productFilterListSource = $this->context->controller->php_self;
            } elseif (get_class($this->context->controller) == 'IqitSearchSearchiqitModuleFrontController') {
                SearchEngineUtils::$productFilterListSource = 'search';
            } elseif (get_class($this->context->controller) == 'PrestaSearchSearchModuleFrontController') {
                SearchEngineUtils::$productFilterListSource = 'prestasearch';
            } elseif (get_class($this->context->controller) == 'pm_subscriptionProductsListingModuleFrontController') {
                SearchEngineUtils::$productFilterListSource = 'subscription';
            }
        }
        if (SearchEngineUtils::$productFilterListSource == 'best-sales') {
            SearchEngineUtils::getBestSellersProductsIds();
        } elseif (SearchEngineUtils::$productFilterListSource == 'new-products') {
            SearchEngineUtils::getNewProductsIds();
        } elseif (SearchEngineUtils::$productFilterListSource == 'prices-drop') {
            SearchEngineUtils::getPricesDropProductsIds();
        } elseif (SearchEngineUtils::$productFilterListSource == 'subscription') {
            $idProductListSubscription = [];
            foreach (\PmSubscription\Models\Product::getProductsAvailable() as $subscriptionProduct) {
                $idProductListSubscription[] = (int)$subscriptionProduct['id_product'];
            }
            if (!empty($idProductListSubscription)) {
                SearchEngineUtils::$productFilterListQuery = implode(',', array_map('intval', $idProductListSubscription));
            }
        } elseif (SearchEngineUtils::$productFilterListSource == 'search'
            || SearchEngineUtils::$productFilterListSource == 'jolisearch'
            || SearchEngineUtils::$productFilterListSource == 'module-ambjolisearch-jolisearch'
            || SearchEngineUtils::$productFilterListSource == 'prestasearch'
        ) {
            if (empty(SearchEngineUtils::$productFilterListData) && Tools::getIsset('s') && Tools::getValue('s')) {
                SearchEngineUtils::$productFilterListData = Tools::getValue('s');
            } elseif (empty(SearchEngineUtils::$productFilterListData) && Tools::getIsset('search_query') && Tools::getValue('search_query')) {
                SearchEngineUtils::$productFilterListData = Tools::getValue('search_query');
            }
            if (SearchEngineUtils::$productFilterListSource == 'search' && SearchEngineUtils::$productFilterListData) {
                SearchEngineUtils::getProductsByNativeSearch(SearchEngineUtils::$productFilterListData);
            } elseif (SearchEngineUtils::$productFilterListSource == 'jolisearch') {
                if (empty(SearchEngineUtils::$productFilterListQuery)) {
                    $ambJoliSearch = Module::getInstanceByName('ambjolisearch');
                    if (Validate::isLoadedObject($ambJoliSearch) && $ambJoliSearch->active) {
                        if (!class_exists('AmbjolisearchjolisearchModuleFrontController')) {
                            require_once _PS_ROOT_DIR_ . '/modules/ambjolisearch/controllers/front/jolisearch.php';
                        }
                        SearchEngineUtils::$productFilterListQuery = implode(',', AmbjolisearchjolisearchModuleFrontController::find(
                            $this->context->language->id,
                            SearchEngineUtils::$productFilterListData,
                            1,
                            -1,
                            'position',
                            'desc',
                            false,
                            true,
                            null,
                            true
                        ));
                        if (empty(SearchEngineUtils::$productFilterListQuery)) {
                            SearchEngineUtils::$productFilterListQuery = '-1';
                        }
                    } else {
                        SearchEngineUtils::$productFilterListSource = false;
                        SearchEngineUtils::$productFilterListData = false;
                        SearchEngineUtils::$productFilterListQuery = false;
                    }
                }
            } elseif (SearchEngineUtils::$productFilterListSource == 'module-ambjolisearch-jolisearch') {
                if (empty(SearchEngineUtils::$productFilterListQuery)) {
                    $ambJoliSearch = Module::getInstanceByName('ambjolisearch');
                    if (Validate::isLoadedObject($ambJoliSearch) && $ambJoliSearch->active) {
                        $ambJoliSearch->setAdvancedSearch4Results(SearchEngineUtils::$productFilterListData);
                        if (empty(SearchEngineUtils::$productFilterListQuery)) {
                            SearchEngineUtils::$productFilterListQuery = '-1';
                        }
                        SearchEngineUtils::$productFilterListSource = 'module-ambjolisearch-jolisearch';
                    } else {
                        SearchEngineUtils::$productFilterListSource = false;
                        SearchEngineUtils::$productFilterListData = false;
                        SearchEngineUtils::$productFilterListQuery = false;
                    }
                }
            } elseif (SearchEngineUtils::$productFilterListSource == 'prestasearch') {
                SearchEngineUtils::$productFilterListSource = 'prestasearch';
                $prestaSearchModule = Module::getInstanceByName('prestasearch');
                if (Validate::isLoadedObject($prestaSearchModule) && $prestaSearchModule->active && method_exists($prestaSearchModule, 'getFoundProductIDs')) {
                    if (SearchEngineUtils::$productFilterListData) {
                        $prestaSearchModule->search_query = SearchEngineUtils::$productFilterListData;
                        $results = $prestaSearchModule->getFullSearchResults(
                            1,
                            (int)Configuration::get('PS_PRODUCTS_PER_PAGE'),
                            'position',
                            'desc',
                            $prestaSearchModule->xhr == false ? true : false
                        );
                    }
                    $idProductList = array_map('intval', $prestaSearchModule->getFoundProductIDs());
                    if (!empty($idProductList)) {
                        SearchEngineUtils::$productFilterListQuery = implode(',', $idProductList);
                    } else {
                        SearchEngineUtils::$productFilterListQuery = '-1';
                    }
                }
            }
        }
    }
    public function hookDisplayHeader()
    {
        if ($this->isInMaintenance()) {
            return;
        }
        if (!Tools::getValue('ajaxMode')) {
            $this->assignSearchVar();
        }
        $this->includeAssets();
    }
    public function includeAssets()
    {
        $this->registerFrontSmartyObjects();
        if (!Tools::getValue('ajaxMode')) {
            Media::addJsDef(array(
                'ASSearchUrl' => $this->context->link->getModuleLink($this->name, 'advancedsearch4'),
                'as4_orderBySalesAsc' => $this->l('Sales: Lower first'),
                'as4_orderBySalesDesc' => $this->l('Sales: Highest first'),
            ));
            $this->setProductFilterContext();
            $this->context->controller->registerStylesheet('modules-'.$this->name.'-css-scenes', _THEME_CSS_DIR_.'scenes.css', array('media' => 'all', 'priority' => 900));
            $this->context->controller->registerStylesheet('modules-'.$this->name.'-css-category', _THEME_CSS_DIR_.'category.css', array('media' => 'all', 'priority' => 900));
            $this->context->controller->registerStylesheet('modules-'.$this->name.'-css-product_list', _THEME_CSS_DIR_.'product_list.css', array('media' => 'all', 'priority' => 900));
            if (Configuration::get('PS_COMPARATOR_MAX_ITEM') > 0) {
                $this->context->controller->registerJavascript('modules-'.$this->name.'-js-comparison', _THEME_JS_DIR_ . 'products-comparison.js', array('position' => 'bottom', 'priority' => 150));
            }
            $ui_slider_path = Media::getJqueryUIPath('ui.slider', 'base', true);
            foreach ($ui_slider_path['css'] as $ui_slider_css_path => $media) {
                $this->context->controller->registerStylesheet('modules-'.$this->name.'-css-ui_slider', $ui_slider_css_path, array('media' => $media, 'priority' => 900));
            }
            $this->context->controller->registerStylesheet('modules-'.$this->name.'-css-main', 'modules/'.$this->name.'/views/css/'.$this->name.'-17.css', array('media' => 'all', 'priority' => 900));
            $this->context->controller->registerStylesheet('modules-'.$this->name.'-css-dyn', 'modules/'.$this->name.'/'.self::DYN_CSS_FILE, array('media' => 'all', 'priority' => 900));
            $this->context->controller->addJqueryUI(array('ui.slider', 'ui.core'));
            $this->context->controller->registerJavascript('selectize/selectize.min.js', 'modules/'.$this->name.'/views/js/selectize/selectize.min.js', array('position' => 'bottom', 'priority' => 150));
            $this->context->controller->registerStylesheet('selectize/selectize.css', 'modules/'.$this->name.'/views/css/selectize/selectize.css', array('media' => 'all', 'priority' => 900));
            $this->context->controller->registerJavascript('modules-'.$this->name.'-js-2', 'modules/'.$this->name.'/views/js/jquery.ui.touch-punch.min.js', array('position' => 'bottom', 'priority' => 150));
            $this->context->controller->registerJavascript('modules-'.$this->name.'-js-3', 'modules/'.$this->name.'/views/js/jquery.form.js', array('position' => 'bottom', 'priority' => 150));
            $this->context->controller->registerJavascript('modules-'.$this->name.'-js-4', 'modules/'.$this->name.'/views/js/as4_plugin-17.js', array('position' => 'bottom', 'priority' => 160));
            $this->context->controller->registerJavascript('modules-'.$this->name.'-js-5', 'modules/'.$this->name.'/views/js/pm_advancedsearch.js', array('position' => 'bottom', 'priority' => 150));
        }
        $config = $this->getModuleConfiguration();
        if (!empty($this->context->cookie->nb_item_per_page)) {
            $as4_localCacheKey = sha1(SearchEngineUtils::getLocalStorageCacheKey() . $this->context->cookie->nb_item_per_page);
        } else {
            $as4_localCacheKey = SearchEngineUtils::getLocalStorageCacheKey();
        }
        $this->context->smarty->assign(array(
            'ASSearchUrlForm' => $this->context->link->getModuleLink($this->name, 'advancedsearch4'),
            'as4_productFilterListSource' => SearchEngineUtils::$productFilterListSource,
            'as4_productFilterListData' => (isset(SearchEngineUtils::$productFilterListSource) && (SearchEngineUtils::$productFilterListSource == 'search' || SearchEngineUtils::$productFilterListSource == 'jolisearch' || SearchEngineUtils::$productFilterListSource == 'prestasearch' || SearchEngineUtils::$productFilterListSource == 'module-ambjolisearch-jolisearch') && !empty(SearchEngineUtils::$productFilterListData) ? self::getDataSerialized(SearchEngineUtils::$productFilterListData) : ''),
            'as_obj' => $this,
            'as_path' => $this->_path,
            'col_img_dir' => _PS_COL_IMG_DIR_,
            'as_location_name' => $this->getLocationName((int)$this->context->language->id),
            'as_criteria_group_type_interal_name'=> $this->criteria_group_type_interal_name,
            'as4_localCacheKey' => $as4_localCacheKey,
            'as4_localCache' => !empty($config['moduleCache']),
            'as4_blurEffect' => !empty($config['blurEffect']),
            'tpl_dir' => _PS_THEME_DIR_,
        ));
    }
    public function renderWidget($hookName, array $configuration)
    {
        if ($this->isInMaintenance()) {
            return;
        }
        if (isset($configuration['id_search_engine'])) {
            return $this->displaySearchBlock('-1', 'pm_advancedsearch.tpl', array(), (int)$configuration['id_search_engine'], true);
        } else {
            return $this->displaySearchBlock($hookName, 'pm_advancedsearch.tpl', array(), false, true);
        }
    }
    public function getWidgetVariables($hookName, array $configuration)
    {
        return array();
    }
    public function hookDisplayHome($params)
    {
        if ($this->isInMaintenance()) {
            return;
        }
        return $this->displaySearchBlock('home', 'pm_advancedsearch.tpl');
    }
    public function hookDisplayTop($params)
    {
        if ($this->isInMaintenance()) {
            return;
        }
        return $this->displaySearchBlock('top', 'pm_advancedsearch.tpl');
    }
    public function hookDisplayNavFullWidth($params)
    {
        if ($this->isInMaintenance()) {
            return;
        }
        return $this->displaySearchBlock('displayNavFullWidth', 'pm_advancedsearch.tpl');
    }
    public function hookDisplayLeftColumn($params)
    {
        if ($this->isInMaintenance()) {
            return;
        }
        return $this->displaySearchBlock('leftcolumn', 'pm_advancedsearch.tpl');
    }
    public function hookDisplayRightColumn($params)
    {
        if ($this->isInMaintenance()) {
            return;
        }
        return $this->displaySearchBlock('rightcolumn', 'pm_advancedsearch.tpl');
    }
    public function hookDisplayAdvancedSearch4($params)
    {
        if ($this->isInMaintenance() || !isset($params['id_search_engine'])) {
            return;
        }
        return $this->displaySearchBlock('-1', 'pm_advancedsearch.tpl', array(), (int)$params['id_search_engine']);
    }
    public function hookActionObjectAddAfter($params)
    {
        $conf = $this->getModuleConfiguration();
        if (!empty($conf['autoReindex']) && isset($params['object']) && Validate::isLoadedObject($params['object'])) {
            SearchEngineIndexation::$processingAutoReindex = true;
            SearchEngineIndexation::indexCriterionsGroupFromObject($params['object']);
            SearchEngineIndexation::$processingAutoReindex = false;
        }
    }
    public function hookActionObjectUpdateAfter($params)
    {
        $conf = $this->getModuleConfiguration();
        if (isset($params['object']) && is_object($params['object']) && $params['object'] instanceof Shop) {
            if (isset($this->context->controller) && is_object($this->context->controller) && $this->context->controller instanceof AdminThemesController) {
                if (Tools::getValue('action') == 'resetToDefaults' || Tools::getValue('action') == 'enableTheme' || Tools::getValue('action') == 'ThemeInstall') {
                    Configuration::updateValue('PM_' . self::$modulePrefix . '_UPDATE_THEME', 1);
                }
            }
        }
        if (!empty($conf['autoReindex']) && isset($params['object']) && Validate::isLoadedObject($params['object'])) {
            SearchEngineIndexation::$processingAutoReindex = true;
            SearchEngineIndexation::indexCriterionsGroupFromObject($params['object']);
            SearchEngineIndexation::$processingAutoReindex = false;
        }
    }
    public function hookActionObjectDeleteAfter($params)
    {
        $conf = $this->getModuleConfiguration();
        if ($params['object'] instanceof Language) {
            $res = Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_lang` WHERE `id_lang`='.(int)$params['object']->id);
            if (self::supportsSeoPages()) {
                $res &= Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_seo_lang` WHERE `id_lang`='.(int)$params['object']->id);
            }
            $advanced_searchs_id = SearchEngineUtils::getSearchsId(false);
            foreach ($advanced_searchs_id as $idSearch) {
                $res &= Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_group_'. (int)$idSearch .'_lang` WHERE `id_lang`='.(int)$params['object']->id);
                $res &= Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_'. (int)$idSearch .'_lang` WHERE `id_lang`='.(int)$params['object']->id);
            }
            return;
        }
        if (!empty($conf['autoReindex']) && isset($params['object']) && Validate::isLoadedObject($params['object'])) {
            SearchEngineIndexation::$processingAutoReindex = true;
            if ($params['object'] instanceof SpecificPrice && !empty($params['object']->id_product)) {
                SearchEngineIndexation::indexCriterionsGroupFromObject($params['object']);
            } else {
                SearchEngineIndexation::indexCriterionsGroupFromObject($params['object'], true);
            }
            SearchEngineIndexation::$processingAutoReindex = false;
        }
    }
    public function hookActionObjectLanguageAddAfter($params)
    {
        $lang = $params['object'];
        if (Validate::isLoadedObject($lang)) {
            $advanced_searchs_id = SearchEngineUtils::getSearchsId(false);
            $res = Db::getInstance()->Execute('
                INSERT IGNORE INTO `' . _DB_PREFIX_ . 'pm_advancedsearch_lang`
                (
                    SELECT `id_search`, "'. (int)$lang->id .'" AS `id_lang`, `title`, `description`
                    FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_lang`
                    WHERE `id_lang` = '. (int)$this->defaultLanguage .'
                )
            ');
            if (self::supportsSeoPages()) {
                $res &= Db::getInstance()->Execute('
                    INSERT IGNORE INTO `' . _DB_PREFIX_ . 'pm_advancedsearch_seo_lang`
                    (
                        SELECT `id_seo`, "'. (int)$lang->id .'" AS `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `title`, `description`, `footer_description`, `seo_url`
                        FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_seo_lang`
                        WHERE `id_lang` = '. (int)$this->defaultLanguage .'
                    )
                ');
            }
            foreach ($advanced_searchs_id as $idSearch) {
                $res &= Db::getInstance()->Execute('
                    INSERT IGNORE INTO `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_group_'. (int)$idSearch .'_lang`
                    (
                        SELECT `id_criterion_group`, "'. (int)$lang->id .'" AS `id_lang`, `name`, `url_identifier`, `url_identifier_original`, `icon`, `range_sign`, `range_interval`, `all_label`
                        FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_group_'. (int)$idSearch .'_lang`
                        WHERE `id_lang` = '. (int)$this->defaultLanguage .'
                    )
                ');
                $res &= Db::getInstance()->Execute('
                    INSERT IGNORE INTO `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_'. (int)$idSearch .'_lang`
                    (
                        SELECT `id_criterion`, "'. (int)$lang->id .'" AS `id_lang`, `value`, `decimal_value`, `url_identifier`, `url_identifier_original`, `icon`
                        FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_'. (int)$idSearch .'_lang`
                        WHERE `id_lang` = '. (int)$this->defaultLanguage .'
                    )
                ');
            }
        }
    }
    public function hookUpdateProduct($params)
    {
        $conf = $this->getModuleConfiguration();
        if (empty($conf['autoReindex'])) {
            return;
        }
        $product = false;
        if (isset($params['product']) && Validate::isLoadedObject($params['product'])) {
            $product = $params['product'];
        } elseif (isset($params['id_product'])) {
            $product = new Product($params['id_product']);
        }
        if (Validate::isLoadedObject($product)) {
            self::$idProductToUpdate[] = (int)$product->id;
        }
    }
    public function hookAddProduct($params)
    {
        $conf = $this->getModuleConfiguration();
        if (empty($conf['autoReindex'])) {
            return;
        }
        $product = false;
        if (isset($params['product']) && Validate::isLoadedObject($params['product'])) {
            $product = $params['product'];
        } elseif (isset($params['id_product'])) {
            $product = new Product($params['id_product']);
        }
        if (Validate::isLoadedObject($product)) {
            self::$idProductToAdd[] = (int)$product->id;
        }
    }
    public function hookActionAdminProductsControllerSaveAfter($params)
    {
        $conf = $this->getModuleConfiguration();
        if (!empty($conf['autoReindex']) && isset($params['return']) && Validate::isLoadedObject($params['return'])) {
            self::$idProductToUpdate[] = (int)$params['return']->id;
        }
    }
    public function hookActionAdminProductsControllerCoreSaveAfter($params)
    {
        return $this->hookActionAdminProductsControllerSaveAfter($params);
    }
    public function hookDeleteProduct($params)
    {
        $conf = $this->getModuleConfiguration();
        if (empty($conf['autoReindex'])) {
            return;
        }
        $product = false;
        if (isset($params['product']) && Validate::isLoadedObject($params['product'])) {
            $product = $params['product'];
        } elseif (isset($params['id_product'])) {
            $product = new Product($params['id_product']);
        }
        if (Validate::isLoadedObject($product)) {
            self::$idProductToDelete[] = (int)$product->id;
        }
    }
    private static $displayBeforeBodyClosingTagContent = '';
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        return self::$displayBeforeBodyClosingTagContent;
    }
    public function hookProductSearchProvider($params)
    {
        $query = $params['query'];
        if ($query->getIdCategory() && $this->isFullTreeModeEnabled() && !SearchEngineUtils::isSPAModuleActive()) {
            $searchProvider = new FullTree($this, $this->getTranslator());
            return $searchProvider;
        }
        return null;
    }
    public function hookActionShopDataDuplication($params)
    {
        if (Tools::getIsset('importData')) {
            $importData = Tools::getValue('importData');
            if (isset($importData['product'])) {
                $query = new DbQuery();
                $query->select('id_search');
                $query->from('pm_advancedsearch_shop');
                $query->where('id_shop = '.(int)$params['old_id_shop']);
                $categoryList = Tools::getValue('categoryBox');
                if (is_array($categoryList)) {
                    $importData['categoryList'] = $categoryList;
                }
                $search_engines = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);
                if (is_array($search_engines) && count($search_engines)) {
                    foreach ($search_engines as $search_engine) {
                        $ObjAdvancedSearchClass = new Search((int)$search_engine['id_search']);
                        $ObjAdvancedSearchClass->duplicate((int)$params['new_id_shop'], $importData);
                    }
                }
            }
        }
    }
    public function hookActionObjectShopDeleteAfter($params)
    {
        $query = new DbQuery();
        $query->select('id_search');
        $query->from('pm_advancedsearch_shop');
        $query->where('id_shop = '.(int)$params['object']->id);
        $search_engines = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);
        if (is_array($search_engines) && count($search_engines)) {
            foreach ($search_engines as $search_engine) {
                $ObjAdvancedSearchClass = new Search((int)$search_engine['id_search']);
                $ObjAdvancedSearchClass->delete();
            }
        }
    }
    public function getCategoryProducts(
        $idLang,
        $pageNumber,
        $productPerPage,
        $orderBy = null,
        $orderWay = null,
        $getTotal = false,
        $active = true,
        $random = false,
        $randomNumberProducts = 1,
        $checkAccess = true,
        Context $context = null
    ) {
        if (!$context) {
            $context = $this->context;
        }
        $category = $context->controller->getCategory();
        if ($checkAccess && !$category->checkAccess((int)$context->customer->id)) {
            return false;
        }
        $front = in_array($context->controller->controller_type, array('front', 'modulefront'));
        $idSupplier = (int)Tools::getValue('id_supplier');
        $includeProductTable = !empty($idSupplier);
        $schemas = false;
        if (isset($context->cookie) AND isset($context->cookie->schemas) and $context->cookie->schemas) {
            $schemas = $context->cookie->schemas;
        }
        if (isset($schemas_pr) AND is_bool($schemas_pr)) {
            $schemas = $schemas_pr;
        }
        if ($getTotal) {
            $sql = 'SELECT COUNT(DISTINCT cp.`id_product`) AS total
                    FROM `'._DB_PREFIX_.'category_product` cp
                    '.str_replace('INNER JOIN', 'STRAIGHT_JOIN', Shop::addSqlAssociation('product', 'cp')).'
                    ' . ($includeProductTable ? 'JOIN `'._DB_PREFIX_.'product` p ON (
                        p.`id_product` = product_shop.`id_product`
                    )' : '') . '
                    LEFT JOIN `'._DB_PREFIX_.'category` c ON (
                        c.`id_category` = cp.`id_category`
                        AND c.`nleft` >= '.(int)$category->nleft.'
                        AND c.`nright` <= '.(int)$category->nright.'
                    )
                    WHERE c.`id_category` > 0 '.
                (($front and !$schemas) ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').
                ($active ? ' AND product_shop.`active` = 1' : '').
                ($idSupplier ? ' AND p.`id_supplier` = '.(int)$idSupplier : '').
                (($front AND $schemas) ? ' AND p.`reference` LIKE "%SPL%"' : ' AND p.`reference` NOT LIKE "%SPL%"');
            return (int)SearchEngineDb::value($sql);
        }
        if ($pageNumber < 1) {
            $pageNumber = 1;
        }
        $orderBy  = Validate::isOrderBy($orderBy) ? Tools::strtolower($orderBy) : 'position';
        $orderWay = Validate::isOrderWay($orderWay) ? Tools::strtoupper($orderWay) : 'ASC';
        $orderByPrefix = false;
        if ($orderBy == 'id_product' || $orderBy == 'date_add' || $orderBy == 'date_upd') {
            $orderByPrefix = 'product_shop';
        } elseif ($orderBy == 'name') {
            $orderByPrefix = 'pl';
        } elseif ($orderBy == 'manufacturer' || $orderBy == 'manufacturer_name') {
            $orderByPrefix = 'm';
            $orderBy = 'name';
            $includeProductTable = true;
        } elseif ($orderBy == 'position') {
            $orderByPrefix = 'cp';
        } elseif ($orderBy == 'sales') {
            $orderByPrefix = 'p_sale';
            $orderBy = 'quantity';
        } elseif ($orderBy == 'quantity') {
            $orderByPrefix = 'stock';
        } elseif ($orderBy == 'reference') {
            $includeProductTable = true;
        }
        if ($orderBy == 'price') {
            $orderBy = 'orderprice';
        }
        $nbDaysNewProduct = Configuration::get('PS_NB_DAYS_NEW_PRODUCT');
        if (!Validate::isUnsignedInt($nbDaysNewProduct)) {
            $nbDaysNewProduct = 20;
        }
        $sql = '
        SELECT p.*, product_shop.*, image_shop.`id_image` id_image,
        stock.`out_of_stock`, IFNULL( stock.`quantity`, 0 ) AS quantity
        FROM (
            SELECT product_shop.*'.(Combination::isFeatureActive() ? ', IFNULL(product_attribute_shop.`id_product_attribute`, 0) AS id_product_attribute,
                    product_attribute_shop.`minimal_quantity` AS product_attribute_minimal_quantity' : '').', pl.`description`, pl.`description_short`, pl.`available_now`,
                    pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`,
                    DATEDIFF(product_shop.`date_add`, DATE_SUB("'.date('Y-m-d').' 00:00:00",
                    INTERVAL '.(int)$nbDaysNewProduct.' DAY)) > 0 AS new,
                    ' . (Combination::isFeatureActive() ? '(product_shop.`price` + IFNULL(product_attribute_shop.`price`, 0))' : 'product_shop.`price`') . ' AS orderprice
                    ' . ($orderByPrefix == 'stock' && $orderBy == 'quantity' ? ', IFNULL(stock.`quantity`, 0) AS quantity ' : '') . '
                FROM `'._DB_PREFIX_.'category_product` cp
                '.str_replace('INNER JOIN', 'STRAIGHT_JOIN', Shop::addSqlAssociation('product', 'cp')).
                (Combination::isFeatureActive() ? ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
                ON (product_shop.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.`id_shop`='.(int)$context->shop->id.')':'').'
                LEFT JOIN `'._DB_PREFIX_.'category` c ON (c.`id_category` = cp.`id_category`
                    AND c.`nleft` >= '.(int)$category->nleft.' AND c.`nright` <= '.(int)$category->nright.')
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
                    ON (product_shop.`id_product` = pl.`id_product`
                    AND pl.`id_lang` = '.(int)$idLang.Shop::addSqlRestrictionOnLang('pl').')
                ' . ($includeProductTable ? 'LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = product_shop.`id_product`)' : '') . '
                ' . ($orderByPrefix == 'm' && $orderBy == 'name' ?
                    ' LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`) ' : '') . '
                ' . ($orderByPrefix == 'p_sale' && $orderBy == 'quantity' ?
                    ' LEFT JOIN `'._DB_PREFIX_.'product_sale` p_sale ON (p_sale.`id_product` = product_shop.`id_product`) '
                    : ''
                ) . '
                ' . ($orderByPrefix == 'stock' && $orderBy == 'quantity' ?
                    Product::sqlStock('product_shop', 0) : '') . '
                WHERE c.`id_category` > 0'
                    .($active ? ' AND product_shop.`active` = 1' : '')
                    .($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '')
                    .($idSupplier ? ' AND p.id_supplier = '.(int)$idSupplier : '')
                    .' GROUP BY cp.`id_product`';
        if ($random === true) {
            $sql .= ' ORDER BY RAND() LIMIT '.(int)$randomNumberProducts;
        } else {
            $sql .= ' ORDER BY '.(!empty($orderByPrefix) ? $orderByPrefix.'.' : '').'`'.bqSQL($orderBy).'` '.pSQL($orderWay).'
            LIMIT '.(((int)$pageNumber - 1) * (int)$productPerPage).','.(int)$productPerPage;
        }
        $sql .= ') AS `product_shop`
            LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = product_shop.`id_product`)
            LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
                ON (image_shop.`id_product` = product_shop.`id_product` AND image_shop.`cover`=1 AND image_shop.`id_shop`='.(int)$context->shop->id.')
            '.Product::sqlStock('product_shop', 0).'
        ';
        $result = SearchEngineDb::query($sql);
        if (!$result) {
            return array();
        }
        if ($orderBy == 'orderprice') {
            Tools::orderbyPrice($result, $orderWay);
        }
        return Product::getProductsProperties($idLang, $result);
    }
    public function reindexCriteriaGroup()
    {
        $id_search = Tools::getValue('id_search');
        $id_criterion_group = Tools::getValue('id_criterion_group');
        SearchEngineIndexation::reindexSpecificCriterionGroup($id_search, $id_criterion_group);
    }
    public function cronTask($idSearch = false)
    {
        self::changeTimeLimit(0);
        $start_memory = memory_get_usage();
        $time_start = microtime(true);
        if (!empty($idSearch)) {
            SearchEngineIndexation::reindexSpecificSearch($idSearch);
        } else {
            SearchEngineIndexation::reindexAllSearchs(true);
        }
        return array(
            'result' => true,
            'source' => (Tools::isPHPCLI() ? 'cli' : 'web'),
            'id_search' => (!empty($idSearch) ? $idSearch : false),
            'elasped_time' => round((microtime(true) - $time_start)*1000, 2),
            'memory_usage' => round((memory_get_usage() - $start_memory)/1024/1024, 2),
            'indexation_stats' => array(
                'criterions' => array(
                    'total' => SearchEngineIndexation::$indexationStats['total_criterions'],
                    'new' => SearchEngineIndexation::$indexationStats['new_criterions'],
                    'updated' => SearchEngineIndexation::$indexationStats['updated_criterions'],
                    'unchanged' => SearchEngineIndexation::$indexationStats['unchanged_criterions'],
                ),
            ),
        );
    }
    public static function clearSmartyCache($idSearch, $idCriterionGroup = null)
    {
        $smarty = Context::getContext()->smarty;
        $module = Module::getInstanceByName(_PM_AS_MODULE_NAME_);
        $templatePath = null;
        $cacheIdToClear = array();
        $forceFileSystemClear = ($smarty instanceof SmartyCustom && method_exists($smarty, 'check_template_invalidation') && is_callable(array($smarty, 'check_template_invalidation')));
        if (empty($idCriterionGroup)) {
            $cacheIdToClear[] = 'pm_advancedsearch|' . (int)$idSearch;
        } else {
            $cacheIdToClear[] = 'pm_advancedsearch|' . (int)$idSearch . '|customCache|getCriterionsForSearchBloc|' . (int)$idCriterionGroup;
            $cacheIdToClear[] = 'pm_advancedsearch|' . (int)$idSearch . '|customCache|getCriterionsRange|' . (int)$idCriterionGroup;
            $cacheIdToClear[] = 'pm_advancedsearch|' . (int)$idSearch . '|customCache|getPriceRangeForSearchBloc|' . (int)$idCriterionGroup;
        }
        foreach ($cacheIdToClear as $cacheId) {
            Tools::clearCache($smarty, $templatePath, $cacheId);
            if ($forceFileSystemClear) {
                $smarty->check_template_invalidation($templatePath, $cacheId, null);
            }
        }
    }
    public static function getModuleInstance()
    {
        static $module = null;
        if (!isset($module)) {
            $module = Module::getInstanceByName(_PM_AS_MODULE_NAME_);
        }
        return $module;
    }
    public function isFullTreeModeEnabled()
    {
        $conf = $this->getModuleConfiguration();
        return (isset($conf['fullTree']) && $conf['fullTree'] && $this->context->controller instanceof CategoryController && !$this->facetedSearchIsEnabled());
    }
    protected function facetedSearchIsEnabled()
    {
        $ps_facetedsearch_module = Module::getInstanceByName('ps_facetedsearch');
        return is_object($ps_facetedsearch_module) && isset($ps_facetedsearch_module->active) && $ps_facetedsearch_module->active == true;
    }
    protected function getNativeFacetedSearchModuleDisplayName()
    {
        $ps_facetedsearch_module = Module::getInstanceByName('ps_facetedsearch');
        if (!empty($ps_facetedsearch_module->displayName)) {
            return $ps_facetedsearch_module->displayName;
        }
        return null;
    }
    protected function checkDbMaxAllowedPacket()
    {
        $result = Db::getInstance()->executeS("SHOW VARIABLES LIKE 'max_allowed_packet'");
        if (empty($result) || !is_array($result)) {
            return false;
        }
        $result = current($result);
        if (!empty($result['Value']) && is_numeric($result['Value'])) {
            if ($result['Value'] > 33554432) {
                return true;
            }
        }
        return false;
    }
}
