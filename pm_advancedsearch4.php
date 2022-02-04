<?php
/**
 * Advanced Search 4
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2020 - http://www.presta-module.com
 * @license   Commercial
 * @version   4.12.10
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
if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
    include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/LinkPM.php');
}
include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/As4SearchEngineLogger.php');
include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/As4SearchEngineDb.php');
include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/As4SearchEngine.php');
include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/As4SearchEngineIndexation.php');
include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/As4SearchEngineSeo.php');
include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/AdvancedSearchClass.php');
include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/AdvancedSearchCriterionGroupClass.php');
include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/AdvancedSearchCriterionClass.php');
include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/AdvancedSearchSeoClass.php');
include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/AdvancedSearchCoreClass.php');
include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/AdvancedSearchWidgetProxy.php');
if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
    include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/controllers/front/AdvancedSearchProductListingFrontController.php');
    include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/search_provider/As4SearchProvider.php');
    include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/classes/search_provider/As4FullTreeSearchProvider.php');
}
class PM_AdvancedSearch4 extends AdvancedSearchWidgetProxy
{
    protected $errors = array();
    private $options_show_hide_crit_method;
    private $options_launch_search_method;
    private $options_defaut_order_by;
    private $options_defaut_order_way;
    private $options_criteria_group_type;
    protected $allowFileExtension = array('gif', 'jpg', 'jpeg', 'png' );
    private $sortableCriterion = array('attribute', 'feature', 'manufacturer', 'supplier', 'category', 'subcategory', 'weight', 'width', 'height', 'depth', 'condition');
    private $originalPositionSortableCriterion = array('attribute', 'category', 'subcategory');
    private $criteriaGroupLabels;
    private $criterionGroupIsTemplatisable = array('attribute', 'feature', 'manufacturer', 'supplier', 'category' );
    private $display_vertical_search_block = array();
    public $_require_maintenance = true;
    public static $_module_prefix = 'as4';
    protected $_defaultConfiguration = array(
        'fullTree' => true,
        'autoReindex' => true,
        'autoSyncActiveStatus' => true,
        'moduleCache' => false,
        'blurEffect' => true,
        'sortOrders' => array(),
    );
    protected $_copyright_link = array(
        'link'    => '',
        'img'    => '//www.presta-module.com/img/logo-module.JPG'
    );
    protected $_support_link = false;
    protected $_css_js_to_load = array(
        'core',
        'jquerytiptip',
        'plupload',
        'codemirrorcore',
        'codemirrorcss',
        'datatables',
        'colorpicker',
        'jgrowl',
        'multiselect',
        'tiny_mce',
        'form',
        'chosen',
    );
    protected $_file_to_check = array(
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
        As4SearchEngineLogger::log("Start");
        $this->name = 'pm_advancedsearch4';
        $this->author = 'Presta-Module';
        $this->tab = 'search_filter';
        $this->need_instance = 0;
        $this->module_key = 'e0578dd1826016f7acb8045ad15372b4';
        $this->version = '4.12.10';
        $this->ps_versions_compliancy['min'] = '1.6.0.1';
        $this->controllers = array(
            'advancedsearch4',
            'seo',
            'searchresults',
        );
        parent::__construct();
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $this->templatePrefix = Tools::substr(_PS_VERSION_, 0, 3) . '/';
        }
        $this->registerFrontSmartyObjects();
        if ($this->_onBackOffice()) {
            if (Configuration::get('PM_' . self::$_module_prefix . '_UPDATE_THEME')) {
                Configuration::updateValue('PM_' . self::$_module_prefix . '_UPDATE_THEME', 0);
                $this->registerToAllHooks();
                $this->updateModulesHooksPositions();
                $this->createOrUpdateAllControllers(true);
            }
            $this->displayName = $this->l('Advanced Search 4');
            $this->description = $this->l('Multi-layered search engine and search by steps');
            $this->options_show_hide_crit_method = array(
                1 => $this->l('On mouse hover'),
                2 => $this->l('On click'),
                3 => $this->l('In an overflow block'),
            );
            $this->options_launch_search_method = array(
                1 => $this->l('Instant search (change)'),
                2 => $this->l('Search on submit (submit)'),
                3 => $this->l('When the last criterion is selected'),
            );
            $this->options_defaut_order_by = array(
                0 => $this->l('Name'),
                1 => $this->l('Price'),
                4 => $this->l('Position inside category'),
                5 => (version_compare(_PS_VERSION_, '1.7.0.0', '>=') ? $this->l('Brand') : $this->l('Manufacturer')),
                6 => $this->l('Quantity'),
                2 => $this->l('Added date') .' ('.$this->l('Recommended for heavy catalog').')',
                3 => $this->l('Modified date').' ('.$this->l('Recommended for heavy catalog').')',
                8 => $this->l('Sales'),
            );
            $this->options_defaut_order_way = array(
                0 => $this->l('Ascending'),
                1 => $this->l('Descending'),
            );
            $this->options_criteria_group_type = array(
                1 => $this->l('Selectbox'),
                3 => $this->l('Link'),
                4 => $this->l('Checkbox'),
                5 => $this->l('Slider'),
                8 => $this->l('Numerical range'),
                //6 => $this->l('Search box'),
                2 => $this->l('Image'),
            );
            $doc_url_tab = array();
            $doc_url_tab['fr'] = '#/fr/advancedsearch4/';
            $doc_url_tab['en'] = '#/en/advancedsearch4/';
            $doc_url = $doc_url_tab['en'];
            if ($this->_iso_lang == 'fr') {
                $doc_url = $doc_url_tab['fr'];
            }
            $forum_url_tab = array();
            $forum_url_tab['fr'] = 'http://www.prestashop.com/forums/topic/113804-module-pm-advanced-search-4-elu-meilleur-module-2012/';
            $forum_url_tab['en'] = 'http://www.prestashop.com/forums/topic/113831-module-pm-advancedsearch-4-winner-at-the-best-module-awards-2012/';
            $forum_url = $forum_url_tab['en'];
            if ($this->_iso_lang == 'fr') {
                $forum_url = $forum_url_tab['fr'];
            }
            $this->_support_link = array(
                array('link' => $forum_url, 'target' => '_blank', 'label' => $this->l('Forum topic')),

                array('link' => 'http://addons.prestashop.com/contact-community.php?id_product=2778', 'target' => '_blank', 'label' => $this->l('Support contact')),
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
                'manufacturer' => (version_compare(_PS_VERSION_, '1.7.0.0', '>=') ? $this->l('brand') : $this->l('manufacturer')),
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
        As4SearchEngineIndexation::$processingAutoReindex = true;
        foreach (self::$idProductToAdd as $idProduct) {
            $product = new Product((int)$idProduct);
            if (Validate::isLoadedObject($product)) {
                As4SearchEngineIndexation::indexCriterionsFromProduct($product, true);
            }
        }
        foreach (self::$idProductToUpdate as $idProduct) {
            $product = new Product((int)$idProduct);
            if (Validate::isLoadedObject($product)) {
                As4SearchEngineIndexation::indexCriterionsFromProduct($product);
            }
        }
        foreach (self::$idProductToDelete as $idProduct) {
            $product = new Product((int)$idProduct);
            if (Validate::isLoadedObject($product)) {
                As4SearchEngineIndexation::desIndexCriterionsFromProduct($product->id);
            }
        }
        As4SearchEngineIndexation::$processingAutoReindex = false;
    }
    public function hookModuleRoutes()
    {
        $searchResultsCategoryPrefix = '{id}-{rewrite}';
        $searchResultsSupplierPrefix = '{id}__{rewrite}';
        $searchResultsManufacturerPrefix = '{id}_{rewrite}';
        $categoryRoute = Configuration::get('PS_ROUTE_category_rule', null, null, $this->context->shop->id);
        $supplierRoute = Configuration::get('PS_ROUTE_supplier_rule', null, null, $this->context->shop->id);
        $manufacturerRoute = Configuration::get('PS_ROUTE_manufacturer_rule', null, null, $this->context->shop->id);
        if (!empty($categoryRoute) && preg_match('/{id}/', $categoryRoute)) {
            $searchResultsCategoryPrefix = $categoryRoute;
        }
        if (!empty($supplierRoute) && preg_match('/{id}/', $supplierRoute)) {
            $searchResultsSupplierPrefix = $supplierRoute;
        }
        if (!empty($manufacturerRoute) && preg_match('/{id}/', $manufacturerRoute)) {
            $searchResultsManufacturerPrefix = $manufacturerRoute;
        }
        $as4SqRegeXp = '[_a-zA-Z0-9\pL\pS/.:+-]*';
        return array(
            'module-pm_advancedsearch4-seo' => array(
                'controller' => 'seo',
                'rule' => 's/{id_seo}/{seo_url}',
                'keywords' => array(
                    'id_seo' => array('regexp' => '[0-9]+', 'param' => 'id_seo'),
                    'seo_url' => array('regexp' => '.+', 'param' => 'seo_url'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'pm_advancedsearch4',
                )
            ),
            'module-pm_advancedsearch4-seositemap' => array(
                'controller' => 'seositemap',
                'rule' => 'as4_seositemap-{id_search}.xml',
                'keywords' => array(
                    'id_search' => array('regexp' => '[0-9]+', 'param' => 'id_search')
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'pm_advancedsearch4',
                )
            ),
            'module-pm_advancedsearch4-cron' => array(
                'controller' => 'cron',
                'params' => array(
                    'fc' => 'module',
                    'module' => 'pm_advancedsearch4',
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
                    'module' => 'pm_advancedsearch4',
                )
            ),
            'module-pm_advancedsearch4-searchresults-categories' => array(
                'controller' =>    'searchresults',
                'rule' =>        $searchResultsCategoryPrefix . '/s-{id_search}/{as4_sq}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_category_search'),
                    'id_search' => array('regexp' => '[0-9]+', 'param' => 'id_search'),
                    'as4_sq' =>    array('regexp' => $as4SqRegeXp, 'param' => 'as4_sq'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*'),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'pm_advancedsearch4',
                    'as4_from' => 'category',
                )
            ),
            'module-pm_advancedsearch4-searchresults-suppliers' => array(
                'controller' =>    'searchresults',
                'rule' =>        $searchResultsSupplierPrefix . '/s-{id_search}/{as4_sq}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_supplier_search'),
                    'id_search' => array('regexp' => '[0-9]+', 'param' => 'id_search'),
                    'as4_sq' =>    array('regexp' => $as4SqRegeXp, 'param' => 'as4_sq'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*'),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'pm_advancedsearch4',
                    'as4_from' => 'supplier',
                )
            ),
            'module-pm_advancedsearch4-searchresults-manufacturers' => array(
                'controller' =>    'searchresults',
                'rule' =>        $searchResultsManufacturerPrefix . '/s-{id_search}/{as4_sq}',
                'keywords' => array(
                    'id' => array('regexp' => '[0-9]+', 'param' => 'id_manufacturer_search'),
                    'id_search' => array('regexp' => '[0-9]+', 'param' => 'id_search'),
                    'as4_sq' =>    array('regexp' => $as4SqRegeXp, 'param' => 'as4_sq'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*'),
                    'meta_keywords' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'pm_advancedsearch4',
                    'as4_from' => 'manufacturer',
                )
            ),
            'layered_rule' => array(
                'controller' =>    '404',
                'rule' => 'disablethisrule-'.uniqid(),
                'keywords' => array(),
                'params' => array()
            ),
        );
    }
    private function registerToAllHooks()
    {
        $valid_hooks = As4SearchEngine::$valid_hooks;
        foreach ($valid_hooks as $hook_name) {
            if (!$this->registerHook($hook_name)) {
                return false;
            }
        }
        if (!$this->registerHook('moduleRoutes') || !$this->registerHook('header') || !$this->registerHook('updateProduct') || !$this->registerHook('addProduct') || !$this->registerHook('deleteProduct')) {
            return false;
        }
        if (!$this->registerHook('actionAdminProductsControllerSaveAfter')) {
            return false;
        }
        if (!$this->registerHook('actionObjectAddAfter') || !$this->registerHook('actionObjectUpdateAfter') || !$this->registerHook('actionObjectDeleteAfter')) {
            return false;
        }
        if (!$this->registerHook('actionProductListOverride')) {
            return false;
        }
        if (!$this->registerHook('actionObjectLanguageAddAfter')) {
            return false;
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=') && !$this->registerHook('displayBeforeBodyClosingTag')) {
            return false;
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=') && !$this->registerHook('actionAdminProductsControllerCoreSaveAfter')) {
            return false;
        }
        if (!$this->registerHook('actionShopDataDuplication') && !$this->registerHook('actionObjectShopDeleteAfter')) {
            return false;
        }
        return true;
    }
    private function updateModulesHooksPositions()
    {
        $hookList = array('displayLeftColumn', 'displayRightColumn');
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $hookList[] = 'productSearchProvider';
        }
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
    private function createOrUpdateAllControllers($forceSetLayout = false)
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
            return (false);
        } elseif (!$sql = Tools::file_get_contents(dirname(__FILE__) . '/sql/' . self::INSTALL_SQL_BASE_FILE)) {
            return (false);
        }
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('MYSQL_ENGINE', _MYSQL_ENGINE_, $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);
        foreach ($sql as $query) {
            if (!As4SearchEngineDb::execute(trim($query))) {
                return (false);
            }
        }
        return true;
    }
    public function installDBCache($id_search, $with_drop = true)
    {
        if (!Tools::file_exists_cache(dirname(__FILE__) . '/sql/' . self::INSTALL_SQL_DYN_FILE)) {
            return (false);
        } elseif (!$sql = Tools::file_get_contents(dirname(__FILE__) . '/sql/' . self::INSTALL_SQL_DYN_FILE)) {
            return (false);
        }
        $sql = str_replace('ID_SEARCH', $id_search, $sql);
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('MYSQL_ENGINE', _MYSQL_ENGINE_, $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);
        foreach ($sql as $query) {
            if (!$with_drop && preg_match('#^DROP#i', trim($query))) {
                continue;
            }
            if (!As4SearchEngineDb::execute(trim($query))) {
                return (false);
            }
        }
        return true;
    }
    private static function removeOldHtAccessRules()
    {
        $path = _PS_ROOT_DIR_.'/.htaccess';
        $htaccessContent = Tools::file_get_contents($path);
        $start = strpos($htaccessContent, '#START AS4 RULES (Do not remove)');
        $end = strpos($htaccessContent, '#END AS4 RULES');
        if ($start !== false && $end !== false) {
            $toReplace = Tools::substr($htaccessContent, $start, $end + Tools::strlen('#END AS4 RULES'));
            if (!empty($toReplace)) {
                $newHtaccesContent = trim(str_replace($toReplace, '', $htaccessContent));
                file_put_contents($path, $newHtaccesContent);
                Tools::generateHtaccess();
            }
        }
    }
    private function updateControllersLayoutSettings($ignoreList = array())
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
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
        } else {
            if (isset($this->context) && !empty($this->context->theme) && is_object($this->context->theme) && Validate::isLoadedObject($this->context->theme) && method_exists($this->context->theme, 'hasColumns')) {
                $columnsSettingsCategory = $this->context->theme->hasColumns('category');
                if (is_array($columnsSettingsCategory) && isset($columnsSettingsCategory['left_column']) && isset($columnsSettingsCategory['right_column'])) {
                    foreach ($this->controllers as $controllerName) {
                        if (in_array($controllerName, $ignoreList)) {
                            continue;
                        }
                        $completeControllerName = Tools::strtolower('module-' . $this->name . '-' . $controllerName);
                        $idMeta = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_meta` FROM `'._DB_PREFIX_.'meta` WHERE `page` = "'.pSQL($completeControllerName).'"');
                        if (!empty($idMeta)) {
                            Db::getInstance()->autoExecute(_DB_PREFIX_ . 'theme_meta', array(
                                array('id_theme' => (int)$this->context->theme->id, 'id_meta' => (int)$idMeta, 'left_column' => (int)$columnsSettingsCategory['left_column'], 'right_column' => (int)$columnsSettingsCategory['right_column']),
                            ), 'REPLACE');
                        }
                    }
                }
            }
        }
    }
    public function checkIfModuleIsUpdate($updateDb = false, $displayConfirm = true)
    {
        $isUpdate = true;
        $firstInstall = false;
        $currentModuleLastVersion = Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION');
        if (empty($currentModuleLastVersion)) {
            $firstInstall = true;
            $this->updateModulesHooksPositions();
        }
        if (Configuration::get('AS4_LAST_VERSION', false) !== false && Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false) === false) {
            Configuration::updateValue('PM_' . self::$_module_prefix . '_LAST_VERSION', Configuration::get('AS4_LAST_VERSION', false));
            Configuration::deleteByName('AS4_LAST_VERSION');
        }
        if (!$updateDb && $this->version != Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false)) {
            return false;
        }
        if ($updateDb) {
            $oldModuleVersion = Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false);
            if (Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false) !== false && version_compare(Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false), '4.8', '>=') && version_compare(Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false), '4.9.1', '<=')) {
                $updateShopTable = true;
            } else {
                $updateShopTable = false;
            }
            $this->createOrUpdateAllControllers($firstInstall);
            self::removeOldHtAccessRules();
            $newHooksList = array(
                'actionAdminProductsControllerSaveAfter',
                'actionProductListOverride',
                'moduleRoutes',
                'actionObjectAddAfter',
                'actionObjectUpdateAfter',
                'actionObjectDeleteAfter',
                'actionObjectLanguageAddAfter',
                'displayAdvancedSearch4',
                'actionShopDataDuplication',
                'actionObjectShopDeleteAfter',
            );
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                $newHooksList[] = 'displayBeforeBodyClosingTag';
                $newHooksList[] = 'productSearchProvider';
                $newHooksList[] = 'displayNavFullWidth';
                $newHooksList[] = 'actionAdminProductsControllerCoreSaveAfter';
            }
            foreach ($newHooksList as $newHookName) {
                if (!$this->isRegisteredInHook($newHookName)) {
                    $this->registerHook($newHookName);
                }
            }
            Configuration::updateValue('PM_' . self::$_module_prefix . '_LAST_VERSION', $this->version);
            if (!Configuration::getGlobalValue('PM_AS4_SECURE_KEY')) {
                Configuration::updateGlobalValue('PM_AS4_SECURE_KEY', Tools::strtoupper(Tools::passwdGen(16)));
            }
            if (!As4SearchEngine::getLocalStorageCacheKey()) {
                As4SearchEngine::setLocalStorageCacheKey();
            }
            $this->installDB();
            $config = $this->_getModuleConfiguration();
            foreach ($this->_defaultConfiguration as $configKey => $configValue) {
                if (!isset($config[$configKey])) {
                    $config[$configKey] = $configValue;
                }
            }
            $this->_setModuleConfiguration($config);
            $this->updateSearchTable($updateShopTable, $oldModuleVersion);
            $this->generateCss();
            $this->pmClearCache();
            if ($displayConfirm) {
                $this->context->controller->confirmations[] = $this->l('Module updated successfully');
            }
        }
        return $isUpdate;
    }
    public function updateSearchTable($updateShopTable = false, $oldModuleVersion = false)
    {
        $advanced_searchs_id = As4SearchEngine::getSearchsId(false, false, false);
        $toAdd = array();
        $toChange = array();
        $toRemove = array();
        $indexToAdd = array();
        $primaryToAdd = array();
        $toChange[] = array('pm_advancedsearch', 'id_hook', 'int(11) NOT NULL');
        if (is_array($advanced_searchs_id) && count($advanced_searchs_id)) {
            foreach ($advanced_searchs_id as $idSearch) {
                $this->installDBCache($idSearch, false);
                $result = As4SearchEngineDb::queryNoCache('SHOW INDEX FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_product_price_' . (int)$idSearch . '` WHERE `column_name` = "id_criterion_group"');
                if (self::_isFilledArray($result)) {
                    As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_product_price_' . (int)$idSearch . '` DROP INDEX `id_product` , ADD INDEX `id_product` ( `id_currency` , `id_country` , `id_group` , `price` , `price_wt` , `from` , `to` ) ');
                }
                $result = As4SearchEngineDb::queryNoCache('SHOW INDEX FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_product_price_' . (int)$idSearch . '` WHERE `Key_name` = "PRIMARY" AND `Column_name` = "reduction_amount"');
                $result2 = As4SearchEngineDb::queryNoCache('SHOW INDEX FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_product_price_' . (int)$idSearch . '` WHERE `Key_name` = "PRIMARY"');
                if (!self::_isFilledArray($result) && self::_isFilledArray($result2)) {
                    As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_product_price_' . (int)$idSearch . '` DROP PRIMARY KEY');
                }
                $result = As4SearchEngineDb::queryNoCache('SHOW INDEX FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_cache_product_criterion_' . (int)$idSearch . '` WHERE `key_name` = "id_criterion2"');
                if (!self::_isFilledArray($result)) {
                    As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_cache_product_criterion_' . (int)$idSearch . '` ADD INDEX `id_criterion2` ( `id_criterion`)');
                    As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_cache_product_criterion_' . (int)$idSearch . '` ADD INDEX `id_cache_product` ( `id_cache_product`)');
                }
                $result = As4SearchEngineDb::queryNoCache('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_product_price_' . (int)$idSearch . '` WHERE `Field` = "price"');
                if (self::_isFilledArray($result)) {
                    As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_product_price_' . (int)$idSearch . '` DROP `price`');
                }
                $toAdd[] = array('pm_advancedsearch_criterion_' . (int)$idSearch, 'visible', 'tinyint(4)  NOT NULL DEFAULT "1"', 'level_depth' );
                $toAdd[] = array('pm_advancedsearch_criterion_' . (int)$idSearch, 'id_parent', 'int(10) unsigned DEFAULT NULL', 'level_depth' );
                $toAdd[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch, 'show_all_depth', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'is_multicriteria' );
                $toAdd[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch, 'only_children', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'is_multicriteria' );
                $toAdd[] = array('pm_advancedsearch_criterion_group_'. (int)$idSearch,'hidden','tinyint(3) unsigned NOT NULL DEFAULT "0"');
                $toAdd[] = array('pm_advancedsearch_criterion_group_'. (int)$idSearch,'range_nb','decimal(10,2) unsigned NOT NULL DEFAULT "15"');
                $toAdd[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch, 'range', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'is_multicriteria' );
                $toAdd[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch, 'sort_by', 'varchar(10) default "position"', 'is_multicriteria' );
                $toAdd[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch, 'sort_way', 'varchar(4) default "ASC"', 'is_multicriteria' );
                $toAdd[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch.'_lang', 'range_sign', 'varchar(32) default NULL', 'icon' );
                $toAdd[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch.'_lang', 'range_interval', 'varchar(255) default NULL', 'icon' );
                $toAdd[] = array('pm_advancedsearch_product_price_' . (int)$idSearch, 'reduction_amount', 'decimal(20,6) NOT NULL default "0"', 'price_wt' );
                $toAdd[] = array('pm_advancedsearch_product_price_' . (int)$idSearch, 'is_specific', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'to' );
                $toAdd[] = array('pm_advancedsearch_product_price_' . (int)$idSearch, 'id_shop', 'int(10) unsigned DEFAULT "0"', 'id_criterion_group' );
                $toAdd[] = array('pm_advancedsearch_product_price_' . (int)$idSearch, 'id_specific_price', 'int(10) unsigned DEFAULT "0"', 'is_specific' );
                $toAdd[] = array('pm_advancedsearch_product_price_' . (int)$idSearch, 'valid_id_specific_price', 'int(10) unsigned DEFAULT "0"', 'id_specific_price' );
                $toAdd[] = array('pm_advancedsearch_product_price_' . (int)$idSearch, 'reduction_type', 'enum(\'amount\',\'percentage\')', 'reduction_amount' );
                $toAdd[] = array('pm_advancedsearch_product_price_' . (int)$idSearch, 'has_no_specific', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'is_specific' );
                $toAdd[] = array('pm_advancedsearch_criterion_' . (int)$idSearch, 'is_custom', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'visible' );
                $toAdd[] = array('pm_advancedsearch_criterion_group_'. (int)$idSearch, 'filter_option','tinyint(3) unsigned NOT NULL DEFAULT "0"', 'is_multicriteria');
                $toAdd[] = array('pm_advancedsearch_criterion_group_'. (int)$idSearch, 'is_combined','tinyint(3) unsigned NOT NULL DEFAULT "0"', 'filter_option');
                $toAdd[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch.'_lang', 'all_label', 'varchar(255) default NULL');
                $toAdd[] = array('pm_advancedsearch_criterion_group_'. (int)$idSearch, 'css_classes','varchar(255) DEFAULT "col-xs-12 col-sm-3"', 'max_display');
                $toAdd[] = array('pm_advancedsearch_product_price_' . (int)$idSearch, 'reduction_tax', 'tinyint(1) unsigned NOT NULL DEFAULT "1"', 'reduction_type' );
                $toAdd[] = array('pm_advancedsearch_criterion_group_'. (int)$idSearch . '_lang', 'url_identifier', 'varchar(255) DEFAULT NULL', 'name');
                $toAdd[] = array('pm_advancedsearch_criterion_group_'. (int)$idSearch . '_lang', 'url_identifier_original', 'varchar(255) DEFAULT NULL', 'url_identifier');
                $toAdd[] = array('pm_advancedsearch_criterion_'. (int)$idSearch . '_lang', 'url_identifier', 'varchar(255) DEFAULT NULL', 'value');
                $toAdd[] = array('pm_advancedsearch_criterion_'. (int)$idSearch . '_lang', 'url_identifier_original', 'varchar(255) DEFAULT NULL', 'url_identifier');
                $toAdd[] = array('pm_advancedsearch_criterion_'. (int)$idSearch . '_lang', 'decimal_value', 'decimal(20,6) DEFAULT NULL', 'value');
                $toAdd[] = array('pm_advancedsearch_criterion_group_'. (int)$idSearch, 'context_type', 'tinyint(3) unsigned NOT NULL DEFAULT "2"', 'display_type');
                $toChange[] = array('pm_advancedsearch_criterion_' . (int)$idSearch, 'position', 'int(10) unsigned DEFAULT "0"' );
                $toChange[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch . '_lang', 'icon', 'varchar(32) NOT NULL');
                $toChange[] = array('pm_advancedsearch_criterion_' . (int)$idSearch . '_lang', 'icon', 'varchar(32) NOT NULL');
                $indexToAdd[] = array('pm_advancedsearch_criterion_' . (int)$idSearch . '_lang', 'value','`value`');
                $indexToAdd[] = array('pm_advancedsearch_criterion_' . (int)$idSearch . '_lang', 'decimal_value','`decimal_value`');
                $indexToAdd[] = array('pm_advancedsearch_criterion_' . (int)$idSearch . '_lang', 'url_identifier','`url_identifier`');
                $indexToAdd[] = array('pm_advancedsearch_criterion_' . (int)$idSearch . '_lang', 'url_identifier_original','`url_identifier_original`');
                $indexToAdd[] = array('pm_advancedsearch_criterion_' . (int)$idSearch, 'is_custom', '`is_custom`');
                $indexToAdd[] = array('pm_advancedsearch_criterion_' . (int)$idSearch, 'gcfsb_1', '`id_criterion`, `id_criterion_group`, `visible`, `position`');
                $indexToAdd[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch . '_lang', 'url_identifier','`url_identifier`');
                $indexToAdd[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch . '_lang', 'url_identifier_original','`url_identifier_original`');
                $indexToAdd[] = array('pm_advancedsearch_product_price_' . (int)$idSearch, 'is_specific','`is_specific`');
                $indexToAdd[] = array('pm_advancedsearch_product_price_' . (int)$idSearch, 'valid_id_specific_price','`valid_id_specific_price`');
                $indexToAdd[] = array('pm_advancedsearch_product_price_' . (int)$idSearch, 'has_no_specific','`has_no_specific`');
                $indexToAdd[] = array('feature_value_lang', 'id_feature_value','`id_feature_value`');
                $indexToAdd[] = array('feature_value_lang', 'id_lang','`id_lang`');
                $indexToAdd[] = array('pm_advancedsearch', 'position', '`position`');
                $primaryToAdd[] = array(
                    'pm_advancedsearch_criterion_group_' . (int)$idSearch.'_lang',
                    '`id_criterion_group`, `id_lang`',
                    'id_criterion_group',
                );
                $primaryToAdd[] = array(
                    'pm_advancedsearch_criterion_' . (int)$idSearch.'_lang',
                    '`id_criterion`, `id_lang`',
                    'id_criterion',
                );
                $primaryToAdd[] = array(
                    'pm_advancedsearch_cache_product_criterion_' . (int)$idSearch,
                    '`id_criterion`, `id_cache_product`',
                    'id_criterion',
                );
                $primaryToAdd[] = array(
                    'pm_advancedsearch_product_price_' . (int)$idSearch,
                    '`id_cache_product` , `id_currency` , `id_country` , `id_group` , `price_wt` , `reduction_amount` , `from` , `to`',
                    'id_product',
                );
                $toRemove[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch, 'is_collapsed');
                $toRemove[] = array('pm_advancedsearch_criterion_group_' . (int)$idSearch, 'width');
            }
        }
        $toAdd[] = array('pm_advancedsearch', 'keep_category_information', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'step_search' );
        $toAdd[] = array('pm_advancedsearch', 'display_empty_criteria', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'step_search' );
        $toAdd[] = array('pm_advancedsearch', 'recursing_indexing', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'step_search' );
        $toAdd[] = array('pm_advancedsearch', 'search_results_selector', 'varchar(64) NOT NULL DEFAULT "#center_column"', 'step_search' );
        $toAdd[] = array('pm_advancedsearch', 'smarty_var_name', 'varchar(64) NOT NULL', 'step_search' );
        $toAdd[] = array('pm_advancedsearch', 'insert_in_center_column', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'step_search' );
        $toAdd[] = array('pm_advancedsearch', 'reset_group', 'tinyint(3) unsigned NOT NULL DEFAULT "1"', 'step_search' );
        $toAdd[] = array('pm_advancedsearch', 'unique_search', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'step_search' );
        $toAdd[] = array('pm_advancedsearch', 'scrolltop_active', 'tinyint(3) unsigned NOT NULL DEFAULT "1"', 'unique_search' );
        $toAdd[] = array('pm_advancedsearch', 'id_category_root', 'int(10) unsigned NOT NULL DEFAULT "0"', 'scrolltop_active' );
        $toAdd[] = array('pm_advancedsearch', 'redirect_one_product', 'tinyint(3) unsigned NOT NULL DEFAULT "1"', 'id_category_root' );
        $toAdd[] = array('pm_advancedsearch', 'add_anchor_to_url', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'redirect_one_product' );
        $toAdd[] = array('pm_advancedsearch', 'step_search_next_in_disabled', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'step_search' );
        $toAdd[] = array('pm_advancedsearch', 'position', 'smallint(4) unsigned NULL DEFAULT "0"', 'step_search_next_in_disabled' );
        $toAdd[] = array('pm_advancedsearch', 'hide_criterions_group_with_no_effect', 'tinyint(3) unsigned NOT NULL DEFAULT "0"', 'step_search' );
        $toAdd[] = array('pm_advancedsearch', 'css_classes','varchar(255) DEFAULT "col-xs-12"', 'internal_name');
        $toAdd[] = array('pm_advancedsearch', 'search_results_selector_css','varchar(255) DEFAULT ""', 'css_classes');
        $toChange[] = array('pm_advancedsearch_seo', 'criteria', 'TEXT NOT NULL' );
        $toRemove[] = array('pm_advancedsearch', 'dynamic_criterion');
        $toRemove[] = array('pm_advancedsearch', 'share');
        $toRemove[] = array('pm_advancedsearch', 'collapsable_criterias');
        $toRemove[] = array('pm_advancedsearch', 'width');
        $toRemove[] = array('pm_advancedsearch', 'height');
        $toRemove[] = array('pm_advancedsearch', 'background_color');
        $toRemove[] = array('pm_advancedsearch', 'border_radius');
        $toRemove[] = array('pm_advancedsearch', 'border_size');
        $toRemove[] = array('pm_advancedsearch', 'border_color');
        $toRemove[] = array('pm_advancedsearch', 'font_size_title');
        $toRemove[] = array('pm_advancedsearch', 'color_group_title');
        $toRemove[] = array('pm_advancedsearch', 'font_size_group_title');
        $toRemove[] = array('pm_advancedsearch', 'color_title');
        $toRemove[] = array('pm_advancedsearch', 'save_selection');
        $primaryToAdd[] = array(
            'pm_advancedsearch_seo_lang',
            '`id_seo`, `id_lang`',
            'id_seo',
        );
        $primaryToAdd[] = array(
            'pm_advancedsearch_lang',
            '`id_search`, `id_lang`',
            'id_search',
        );
        $primaryToAdd[] = array(
            'pm_advancedsearch_category',
            '`id_search`, `id_category`',
            'id_search',
        );
        $primaryToAdd[] = array(
            'pm_advancedsearch_seo_crosslinks',
            '`id_seo`, `id_seo_linked`',
            'id_seo',
        );
        if (is_array($toAdd) && count($toAdd)) {
            foreach ($toAdd as $infos) {
                $this->columnExists($infos[0], $infos[1], true, $infos[2], (isset($infos[3]) ? $infos[3] : false));
            }
        }
        if (is_array($toChange) && count($toChange)) {
            foreach ($toChange as $infos) {
                $resultset = As4SearchEngineDb::queryNoCache("SHOW COLUMNS FROM `" . bqSQL(_DB_PREFIX_ . $infos[0]) . "` WHERE `Field` = '" . pSQL($infos[1]) . "'");
                foreach ($resultset as $row) {
                    if ($row['Type'] != $infos[2]) {
                        As4SearchEngineDb::execute('ALTER TABLE `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` CHANGE `' . bqSQL($infos[1]) . '` `' . bqSQL($infos[1]) . '` ' . $infos[2] . '');
                    }
                }
            }
        }
        if (is_array($indexToAdd) && count($indexToAdd)) {
            foreach ($indexToAdd as $infos) {
                $result = As4SearchEngineDb::queryNoCache('SHOW INDEX FROM `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` WHERE `Key_name` = "'.pSQL($infos[1]).'"');
                if (!self::_isFilledArray($result)) {
                    As4SearchEngineDb::execute('ALTER TABLE  `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` ADD INDEX `' . bqSQL($infos[1]) . '` ( '.$infos[2].' )');
                }
            }
        }
        if (is_array($primaryToAdd) && count($primaryToAdd)) {
            foreach ($primaryToAdd as $infos) {
                $result = As4SearchEngineDb::queryNoCache('SHOW INDEX FROM `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` WHERE `Key_name` = "PRIMARY"');
                if (!self::_isFilledArray($result)) {
                    if (isset($infos[2])) {
                        $result = As4SearchEngineDb::queryNoCache('SHOW INDEX FROM `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` WHERE `column_name` = "'.pSQL($infos[2]).'"');
                        if (self::_isFilledArray($result)) {
                            As4SearchEngineDb::execute('ALTER TABLE  `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` DROP INDEX `'.bqSQL($infos[2]).'`');
                        }
                    }
                    As4SearchEngineDb::execute('ALTER TABLE  `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` ADD PRIMARY KEY ('.$infos[1].')');
                }
            }
        }
        if (is_array($toRemove) && count($toRemove)) {
            foreach ($toRemove as $infos) {
                if ($this->columnExists($infos[0], $infos[1])) {
                    As4SearchEngineDb::execute('ALTER TABLE `' . bqSQL(_DB_PREFIX_ . $infos[0]) . '` DROP `'.bqSQL($infos[1]).'`');
                }
            }
        }
        $seo = AdvancedSearchSeoClass::getSeoSearchs(false, true);
        $seo_url_updated = false;
        foreach ($seo as $row) {
            if (preg_match('#\{i:#', $row['criteria'])) {
                break;
            }
            $newCriteria = array();
            $criteria = explode(',', $row['criteria']);
            if (count($criteria)) {
                foreach ($criteria as $value) {
                    if (preg_match('/~/', $value)) {
                        $id_criterion_group = AdvancedSearchCriterionGroupClass::getIdCriterionGroupByTypeAndIdLinked($row['id_search'], 'price', 0);
                    } else {
                        $id_criterion_group = AdvancedSearchCriterionClass::getIdCriterionGroupByIdCriterion($row['id_search'], $value);
                    }
                    if (!$id_criterion_group) {
                        continue;
                    }
                    $newCriteria[] = $id_criterion_group.'_'.$value;
                }
            }
            if (count($newCriteria)) {
                $row['criteria'] = serialize($newCriteria);
                Db::getInstance()->update('pm_advancedsearch_seo', $row, 'id_seo = ' . (int)$row['id_seo']);
                if ($seo_url_updated) {
                    As4SearchEngineDb::execute('UPDATE `' . _DB_PREFIX_ . 'pm_advancedsearch_seo_lang` SET `seo_url` = REPLACE(`seo_url`,"/","-")');
                    $seo_url_updated = true;
                }
            }
        }
        if ($updateShopTable) {
            $result = As4SearchEngineDb::query('SELECT * FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_shop` ORDER BY `id_search` , `id_shop`');
            if ($result && self::_isFilledArray($result)) {
                $first_shop = array();
                foreach ($result as $row) {
                    if (!isset($first_shop[$row['id_search']])) {
                        $first_shop[$row['id_search']] = $row['id_shop'];
                    } else {
                        continue;
                    }
                }
                foreach ($first_shop as $id_search => $id_shop) {
                    As4SearchEngineDb::execute('DELETE FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_shop` WHERE `id_search`='.(int)$id_search.' AND `id_shop`!='.(int)$id_shop);
                }
            }
        }
        if ($oldModuleVersion !== false && version_compare($oldModuleVersion, '4.10.0', '<=')) {
            $result = As4SearchEngineDb::query('SELECT * FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_seo`');
            if ($result && self::_isFilledArray($result)) {
                foreach ($result as $row) {
                    $criteria = unserialize($row['criteria']);
                    $newSeoKey = As4SearchEngineSeo::getSeoKeyFromCriteria($row['id_search'], $criteria, $row['id_currency']);
                    if ($newSeoKey != $row['seo_key']) {
                        As4SearchEngineDb::execute('UPDATE `' . _DB_PREFIX_ . 'pm_advancedsearch_seo` SET `seo_key`="' . pSQL($newSeoKey) . '" WHERE id_seo="' . (int)$row['id_seo'] . '"');
                    }
                }
            }
        }
        if ($oldModuleVersion !== false && version_compare($oldModuleVersion, '4.10.1', '<=')) {
            if (is_array($advanced_searchs_id) && sizeof($advanced_searchs_id)) {
                foreach ($advanced_searchs_id as $idSearch) {
                    $result = As4SearchEngineDb::queryNoCache('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '` WHERE `Field` = "id_criterion_linked"');
                    if (self::_isFilledArray($result)) {
                        if (As4SearchEngineDb::execute('INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_link` (`id_criterion`, `id_criterion_linked`) (SELECT `id_criterion`, `id_criterion_linked` FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'`)')) {
                            As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '` DROP `id_criterion_linked`');
                        }
                    }
                    $criterionsCount = (int)As4SearchEngineDb::value('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '`');
                    $criterionsListCount = (int)As4SearchEngineDb::value('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '_list`');
                    if ($criterionsCount > 0 && $criterionsListCount == 0) {
                        As4SearchEngineDb::execute('INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_list` (`id_criterion_parent`, `id_criterion`) (SELECT `id_criterion` AS `id_criterion_parent`, `id_criterion` FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'`)');
                    }
                    $result = As4SearchEngineDb::queryNoCache('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '` WHERE `Field` = "criterions_list"');
                    if (self::_isFilledArray($result)) {
                        $result = As4SearchEngineDb::query('SELECT `id_criterion`, `criterions_list` FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'`');
                        if ($result && self::_isFilledArray($result)) {
                            foreach ($result as $criterionRow) {
                                if ($criterionRow['criterions_list'] != '' && !empty($criterionRow['criterions_list'])) {
                                    $criterionRow['criterions_list'] = array_unique(explode(',', $criterionRow['criterions_list']));
                                    if (self::_isFilledArray($criterionRow['criterions_list'])) {
                                        foreach ($criterionRow['criterions_list'] as $k => $idCriterionToAdd) {
                                            if (empty($idCriterionToAdd) || !is_numeric($idCriterionToAdd)) {
                                                unset($criterionRow['criterions_list'][$k]);
                                            }
                                        }
                                    }
                                }
                                if (self::_isFilledArray($criterionRow['criterions_list'])) {
                                    $idCriterionToAddList = array();
                                    foreach ($criterionRow['criterions_list'] as $idCriterionToAdd) {
                                        $idCriterionToAddList[] = (int)$idCriterionToAdd;
                                        AdvancedSearchCriterionClass::addCriterionToList((int)$idSearch, $criterionRow['id_criterion'], $idCriterionToAdd);
                                    }
                                    AdvancedSearchCriterionClass::populateCriterionsLink((int)$idSearch, $criterionRow['id_criterion'], false, $idCriterionToAddList);
                                } else {
                                    AdvancedSearchCriterionClass::addCriterionToList((int)$idSearch, $criterionRow['id_criterion'], $criterionRow['id_criterion']);
                                }
                            }
                        }
                        As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '` DROP `criterions_list`');
                    }
                }
            }
        }
        if (version_compare($oldModuleVersion, '4.10.6', '<=')) {
            As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_shop` DEFAULT CHARACTER SET=latin1');
            As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_category` DEFAULT CHARACTER SET=latin1');
            As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_cms` DEFAULT CHARACTER SET=latin1');
            As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_products` DEFAULT CHARACTER SET=latin1');
            As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_manufacturers` DEFAULT CHARACTER SET=latin1');
            As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_suppliers` DEFAULT CHARACTER SET=latin1');
            As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_special_pages` DEFAULT CHARACTER SET=latin1');
            As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_seo` DEFAULT CHARACTER SET=latin1');
            As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_seo_crosslinks` DEFAULT CHARACTER SET=latin1');
            if (is_array($advanced_searchs_id) && sizeof($advanced_searchs_id)) {
                foreach ($advanced_searchs_id as $idSearch) {
                    As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '` DEFAULT CHARACTER SET=latin1');
                    As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_group_' . (int)$idSearch . '` DEFAULT CHARACTER SET=latin1');
                    As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_cache_product_criterion_' . (int)$idSearch . '` DEFAULT CHARACTER SET=latin1');
                    As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_cache_product_' . (int)$idSearch . '` DEFAULT CHARACTER SET=latin1');
                    As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_product_price_' . (int)$idSearch . '` DEFAULT CHARACTER SET=latin1');
                    As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '_link` DEFAULT CHARACTER SET=latin1');
                    As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '_list` DEFAULT CHARACTER SET=latin1');
                }
            }
        }
        if (version_compare($oldModuleVersion, '4.11.0', '<')) {
            if (is_array($advanced_searchs_id) && sizeof($advanced_searchs_id)) {
                $result = As4SearchEngineDb::queryNoCache('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '` WHERE `Field` = "single_value"');
                if (self::_isFilledArray($result)) {
                    foreach ($advanced_searchs_id as $idSearch) {
                        foreach (Language::getLanguages(false) as $row_lang) {
                            As4SearchEngineDb::execute('
                                INSERT IGNORE INTO `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '_lang`
                                (
                                    SELECT id_criterion, "' . (int)$row_lang['id_lang'] . '" as id_lang, single_value as value, 0 as decimal_value, "" as url_identifier, "" as url_identifier_original, "" as icon
                                    FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '`
                                    WHERE single_value!=""
                                )
                            ');
                        }
                        As4SearchEngineDb::execute('ALTER TABLE `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '` DROP `single_value`');
                    }
                }
                foreach ($advanced_searchs_id as $idSearch) {
                    As4SearchEngineDb::execute('
                        UPDATE `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '_lang`
                        SET `decimal_value`=CAST(REPLACE(`value`, ",", ".") AS DECIMAL(20,6))
                    ');
                }
                foreach ($advanced_searchs_id as $idSearch) {
                    As4SearchEngineDb::execute('
                        UPDATE `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_group_' . (int)$idSearch . '_lang`
                        SET `url_identifier` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(`name`), "-", "_"), "/", "_"), " ", "_"), "\'", "_"), "\"", "_"), "&", "_"), "%", "_"), ":", "_"),
                        `url_identifier_original` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(`name`), "-", "_"), "/", "_"), " ", "_"), "\'", "_"), "\"", "_"), "&", "_"), "%", "_"), ":", "_")
                    ');
                    As4SearchEngineDb::execute('
                        UPDATE `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_' . (int)$idSearch . '_lang`
                        SET `url_identifier` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(`value`), "-", "_"), "/", "_"), " ", "_"), "\'", "_"), "\"", "_"), "&", "_"), "%", "_"), ":", "_"),
                        `url_identifier_original` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(`value`), "-", "_"), "/", "_"), " ", "_"), "\'", "_"), "\"", "_"), "&", "_"), "%", "_"), ":", "_")
                    ');
                }
            }
        }
    }
    private function columnExists($table, $column, $createIfNotExist = false, $type = false, $insertAfter = false)
    {
        $resultset = As4SearchEngineDb::queryNoCache("SHOW COLUMNS FROM `" . bqSQL(_DB_PREFIX_ . $table) . "`");
        foreach ($resultset as $row) {
            if ($row['Field'] == $column) {
                return true;
            }
        }
        if ($createIfNotExist && As4SearchEngineDb::execute('ALTER TABLE `' . bqSQL(_DB_PREFIX_ . $table) . '` ADD `' . bqSQL($column) . '` ' . $type . ' ' . ($insertAfter ? ' AFTER `' . bqSQL($insertAfter) . '`' : '') . '')) {
            return true;
        }
        return false;
    }
    public function updateAdvancedStyles($css_styles)
    {
        if (Shop::isFeatureActive()) {
            Configuration::updateGlobalValue('PM_'.self::$_module_prefix.'_ADVANCED_STYLES', self::getDataSerialized($css_styles));
        } else {
            Configuration::updateValue('PM_'.self::$_module_prefix.'_ADVANCED_STYLES', self::getDataSerialized($css_styles));
        }
        $this->generateCss();
    }
    public function getAdvancedStylesDb()
    {
        if (Shop::isFeatureActive()) {
            $advanced_css_file_db = Configuration::getGlobalValue('PM_'.self::$_module_prefix.'_ADVANCED_STYLES');
        } else {
            $advanced_css_file_db = Configuration::get('PM_'.self::$_module_prefix.'_ADVANCED_STYLES');
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
                $this->updateAdvancedStyles("/* Advanced Search 4 - Advanced Styles Content */\n");
            }
        }
        $vars = array(
            'advanced_styles' => $this->getAdvancedStylesDb()
        );
        return $this->fetchTemplate('module/tabs/advanced_styles.tpl', $vars);
    }
    public function displayMaintenance()
    {
        $advanced_searchs_id = As4SearchEngine::getSearchsId(false, $this->context->shop->id);
        $criteriaGroupToReindex = array();
        if (self::_isFilledArray($advanced_searchs_id)) {
            $key = 0;
            foreach ($advanced_searchs_id as $idSearch) {
                $criterions_groups_indexed = As4SearchEngineIndexation::getCriterionsGroupsIndexed($idSearch, (int)$this->context->language->id, false);
                if (self::_isFilledArray($criterions_groups_indexed)) {
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
        $advanced_searchs = As4SearchEngine::getAllSearchs((int)$this->context->language->id, false, false);
        $css = array();
        foreach ($advanced_searchs as $advanced_search) {
            $criterions_groups_indexed = As4SearchEngineIndexation::getCriterionsGroupsIndexed($advanced_search['id_search'], (int)$this->context->language->id);
            if (self::_isFilledArray($criterions_groups_indexed)) {
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
                $this->errors[] = $this->_showWarning($this->l('Please set write permision to folder:'). ' '.dirname(__FILE__) . '/views/css/');
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
            'shipping' => $this->l('Product package'),
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
        $categories_level_depth = As4SearchEngineIndexation::getAvailableCategoriesLevelDepth();
        foreach ($categories_level_depth as $category_level_depth) {
            $criterions_groups['associations'][] = array('id' => $category_level_depth['level_depth'], 'name' => $this->l('Categories level') . ' ' . $category_level_depth['level_depth'], 'type' => 'category');
        }
        $criterions_groups['associations'][] = array('id' => 0, 'name' => (version_compare(_PS_VERSION_, '1.7.0.0', '>=') ? $this->l('Brand') : $this->l('Manufacturer')), 'type' => 'manufacturer');
        $criterions_groups['associations'][] = array('id' => 0, 'name' => $this->l('Supplier'), 'type' => 'supplier');
        $attributes_groups = As4SearchEngineIndexation::getAvailableAttributesGroups($id_lang);
        foreach ($attributes_groups as $row) {
            $criterions_groups['attribute'][] = array('id' => $row['id_attribute_group'], 'name' => $row['public_name'], 'internal_name' => (!empty($row['name']) ? $row['name'] : ''), 'type' => 'attribute');
        }
        $features = As4SearchEngineIndexation::getAvailableFeaturesGroups($id_lang);
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
    private function _postProcessSearch()
    {
        $id_search = Tools::getValue('id_search', false);
        $ObjAdvancedSearchClass = new AdvancedSearchClass($id_search);
        $reindexing_categories = false;
        $this->_cleanOutput(true);
        $this->errors = self::_retroValidateController($ObjAdvancedSearchClass);
        if ($id_search && Tools::getValue('recursing_indexing') != $ObjAdvancedSearchClass->recursing_indexing) {
            $reindexing_categories = true;
        }
        if (!sizeof($this->errors)) {
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
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=') && Tools::getValue('id_hook_widget') && $ObjAdvancedSearchClass->id_hook == -2) {
                $ObjAdvancedSearchClass->id_hook = (int)Tools::getValue('id_hook_widget');
            }
            if (!$ObjAdvancedSearchClass->save()) {
                $this->errors[] = $this->l('Error while saving');
            }
            if (!sizeof($this->errors)) {
                if (!$id_search) {
                    if (!$this->installDBCache($ObjAdvancedSearchClass->id)) {
                        $this->errors[] = $this->l('Error while making cache table');
                    }
                    if (!sizeof($this->errors) && !As4SearchEngineIndexation::addCacheProduct($ObjAdvancedSearchClass->id)) {
                        $this->errors[] = $this->l('Error while creating products index');
                    }
                } else {
                    if (!As4SearchEngineIndexation::updateCacheProduct($id_search)) {
                        $this->errors[] = $this->l('Error while creating products index');
                    }
                }
                if (trim($ObjAdvancedSearchClass->internal_name) == '') {
                    $ObjAdvancedSearchClass->internal_name = $this->l('Search engine') . ' ' . $ObjAdvancedSearchClass->id;
                    $ObjAdvancedSearchClass->save();
                }
                As4SearchEngineIndexation::indexFilterByEmplacement($ObjAdvancedSearchClass->id);
                if ($reindexing_categories) {
                    As4SearchEngineIndexation::reindexingCategoriesGroups($ObjAdvancedSearchClass);
                }
                $this->generateCss();
                self::clearSmartyCache((int)$ObjAdvancedSearchClass->id);
                $this->_html .= '<script type="text/javascript">';
                if (!$id_search) {
                    $this->_html .= 'parent.parent.addTabPanel("#wrapAsTab", ' . Tools::jsonEncode($ObjAdvancedSearchClass->id . ' - ' . $ObjAdvancedSearchClass->internal_name) . ',' . $ObjAdvancedSearchClass->id . ', true);';
                    $this->_html .= 'parent.parent.loadTabPanel("#wrapAsTab","li#TabSearchAdminPanel' . $ObjAdvancedSearchClass->id . '");';
                } else {
                    $this->_html .= 'parent.parent.loadTabPanel("#wrapAsTab","li#TabSearchAdminPanel' . $ObjAdvancedSearchClass->id . '");';
                    $this->_html .= 'parent.parent.updateSearchNameIntoTab("li#TabSearchAdminPanel' . $ObjAdvancedSearchClass->id . '", '.Tools::jsonEncode($ObjAdvancedSearchClass->id . ' - ' . $ObjAdvancedSearchClass->internal_name).');';
                }
                $this->_html .= 'parent.parent.show_info("' . $this->l('Search has been updated successfully') . '");parent.parent.closeDialogIframe();';
                $this->_html .= '</script>';
            }
        }
        $this->_displayErrorsJs(true);
        $this->_echoOutput(true);
    }
    private function _postProcessSearchVisibility()
    {
        $id_search = Tools::getValue('id_search', false);
        $ObjAdvancedSearchClass = new AdvancedSearchClass($id_search);
        $this->_cleanOutput(true);
        $this->errors = self::_retroValidateController($ObjAdvancedSearchClass);
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
                $this->_html .= '<script type="text/javascript">';
                $this->_html .= 'parent.parent.loadTabPanel("#wrapAsTab","li#TabSearchAdminPanel' . $ObjAdvancedSearchClass->id . '");';
                $this->_html .= 'parent.parent.updateSearchNameIntoTab("li#TabSearchAdminPanel' . $ObjAdvancedSearchClass->id . '", '.Tools::jsonEncode($ObjAdvancedSearchClass->internal_name).');';
                $this->_html .= 'parent.parent.show_info("' . $this->l('Search has been updated successfully') . '");parent.parent.closeDialogIframe();';
                $this->_html .= '</script>';
            }
        }
        $this->_displayErrorsJs(true);
        $this->_echoOutput(true);
    }
    private function _postProcessCriteria()
    {
        $id_search = Tools::getValue('id_search', false);
        $id_criterion = Tools::getValue('id_criterion', false);
        $key_criterions_group = Tools::getValue('key_criterions_group', false);
        $return = '';
        if (!$id_search || !$id_criterion) {
            $return .= '<script type="text/javascript">parent.parent.show_error("' . $this->l('An error occured') . '");</script>';
        } else {
            $objAdvancedSearchCriterionClass = new AdvancedSearchCriterionClass($id_criterion, $id_search);
            $update = $this->_uploadImageLang($objAdvancedSearchCriterionClass, 'icon', '/modules/pm_advancedsearch4/search_files/criterions/', '-' . $id_search);
            if (is_array($update) && sizeof($update)) {
                foreach ($update as $error) {
                    $return .= '<script type="text/javascript">parent.parent.show_error("' . $error . '");</script>';
                }
            } elseif ($update) {
                $objAdvancedSearchCriterionClass->save();
            }
            $return .= '
            <script type="text/javascript">
                parent.parent.closeDialogIframe();
                parent.parent.getCriterionGroupActions("' . $key_criterions_group . '", true);
                parent.parent.show_info("' . $this->l('Saved') . '");
            </script>
            ';
        }
        self::_cleanBuffer();
        echo $return;
        die();
    }
    private function _postProcessSeoSearch()
    {
        $id_search = Tools::getValue('id_search', false);
        $id_seo = Tools::getValue('id_seo', false);
        $id_currency = Tools::getValue('id_currency', false);
        if (!$id_currency) {
            $id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        }
        self::_cleanBuffer();
        $return = '';
        if (!$id_search) {
            $return .= '<script type="text/javascript">parent.parent.show_error("' . $this->l('An error occured') . '");</script>';
        } else {
            $seo_key = As4SearchEngineSeo::getSeoKeyFromCriteria($id_search, explode(',', Tools::getValue('criteria')), $id_currency);
            if (!$id_seo) {
                $id_seo = AdvancedSearchSeoClass::seoDeletedExists($seo_key);
            }
            if (!$id_seo && AdvancedSearchSeoClass::seoExists($seo_key)) {
                $return .= '<script type="text/javascript">parent.parent.show_error("' . $this->l('This SEO result page already exists') . '");</script>';
            } else {
                $objAdvancedSearchSeoClass = new AdvancedSearchSeoClass($id_seo);
                $this->copyFromPost($objAdvancedSearchSeoClass);
                $objAdvancedSearchSeoClass->seo_key = $seo_key;
                $objAdvancedSearchSeoClass->deleted = 0;
                $error = $objAdvancedSearchSeoClass->validateFields(false, true);
                $errorLang = $objAdvancedSearchSeoClass->validateFieldsLang(false, true);
                if ($error !== true) {
                    $return .= '<script type="text/javascript">parent.parent.show_error("' . addcslashes($error, '"') . '");</script>';
                }
                if ($errorLang !== true) {
                    $return .= '<script type="text/javascript">parent.parent.show_error("' . addcslashes($errorLang, '"') . '");</script>';
                } elseif ($objAdvancedSearchSeoClass->save()) {
                    $return .= '<script type="text/javascript">parent.parent.show_info("' . $this->l('Saved') . '");parent.parent.reloadPanel("seo_search_panel_' . (int)$id_search . '");parent.parent.closeDialogIframe();</script>';
                } else {
                    $return .= '<script type="text/javascript">parent.parent.show_error("' . $this->l('Error while updating seo search') . '");</script>';
                }
            }
        }
        echo $return;
        die();
    }
    public function getSeoStrings($criteria, $id_search, $id_currency, $fields_to_get = false)
    {
        $fieldToFill = array('meta_title', 'meta_description', 'meta_keywords', 'title', 'seo_url' );
        $fieldWithGroupName = array('meta_title', 'meta_description' );
        $groupTypeWithoutGroupName = array('category', 'supplier', 'manufacturer' );
        $offersSelectionStr = $this->translateMultiple('offers_selection');
        $betweenLangStr = $this->translateMultiple('from');
        $andLangStr = $this->translateMultiple('to');
        $moreThanLangStr = $this->translateMultiple('more_than');
        $defaultReturnSeoStr = array();
        $newCriteria = array();
        foreach ($criteria as $value) {
            $info_criterion = explode('_', $value);
            if (isset($info_criterion[2])) {
                $id_criterion_group = $info_criterion[1];
                $id_criterion = $info_criterion[2];
            } else {
                $id_criterion_group = $info_criterion[0];
                $id_criterion = $info_criterion[1];
            }
            if (!isset($newCriteria[$id_criterion_group])) {
                $newCriteria[$id_criterion_group] = array();
            }
            $newCriteria[$id_criterion_group][] = $id_criterion;
        }
        foreach ($this->_languages as $language) {
            foreach ($criteria as $k => $criterion) {
                $endLoop = (($k + 1) == sizeof($criteria));
                $info_criterion = explode('_', $criterion);
                if (isset($info_criterion[2])) {
                    $id_criterion_group = $info_criterion[1];
                    $id_criterion = $info_criterion[2];
                } else {
                    $id_criterion_group = $info_criterion[0];
                    $id_criterion = $info_criterion[1];
                }
                $objAdvancedSearchCriterionGroupClass = new AdvancedSearchCriterionGroupClass($id_criterion_group, $id_search);
                if (preg_match('#~#', $id_criterion)) {
                    $range = explode('~', $id_criterion);
                    $min = $range[0];
                    if ($objAdvancedSearchCriterionGroupClass->criterion_group_type == 'price') {
                        $currency = new Currency($id_currency);
                        if (!empty($range[1])) {
                            $max = Tools::displayPrice($range[1], (Validate::isLoadedObject($currency) ? $currency : null));
                            $criterion_value = $betweenLangStr[$language['id_lang']] . ' ' . $min . ' ' . $andLangStr[$language['id_lang']] . ' ' . $max;
                        } else {
                            $min = Tools::displayPrice($min, (Validate::isLoadedObject($currency) ? $currency : null));
                            $criterion_value = $moreThanLangStr[$language['id_lang']] . ' ' . $min;
                        }
                    } else {
                        if (!empty($range[1])) {
                            $max = (float)$range[1] . (!empty($objAdvancedSearchCriterionGroupClass->range_sign[$language['id_lang']]) ? ' ' . $objAdvancedSearchCriterionGroupClass->range_sign[$language['id_lang']] : '');
                            $criterion_value = $betweenLangStr[$language['id_lang']] . ' ' . $min . ' ' . $andLangStr[$language['id_lang']] . ' ' . $max;
                        } else {
                            $min = (float)$min . (!empty($objAdvancedSearchCriterionGroupClass->range_sign[$language['id_lang']]) ? ' ' . $objAdvancedSearchCriterionGroupClass->range_sign[$language['id_lang']] : '');
                            $criterion_value = $moreThanLangStr[$language['id_lang']] . ' ' . $min;
                        }
                    }
                } else {
                    $objAdvancedSearchCriterionClass = new AdvancedSearchCriterionClass($id_criterion, $id_search, $language['id_lang']);
                    $criterion_value = trim($objAdvancedSearchCriterionClass->value);
                }
                foreach ($fieldToFill as $field) {
                    if ($fields_to_get && ! in_array($field, $fields_to_get)) {
                        continue;
                    }
                    if (!isset($defaultReturnSeoStr[$language['id_lang']])) {
                        $defaultReturnSeoStr[$language['id_lang']] = array();
                    }
                    if (!isset($defaultReturnSeoStr[$language['id_lang']][$field])) {
                        $defaultReturnSeoStr[$language['id_lang']][$field] = '';
                    }
                    if (!$k && $field == 'meta_description') {
                        $defaultReturnSeoStr[$language['id_lang']][$field] .= Configuration::get('PS_SHOP_NAME') . ' ' . $offersSelectionStr[$language['id_lang']] . ' ';
                    }
                    if ($objAdvancedSearchCriterionGroupClass->criterion_group_type != 'price' && in_array($field, $fieldWithGroupName) and ! in_array($objAdvancedSearchCriterionGroupClass->criterion_group_type, $groupTypeWithoutGroupName)) {
                        $defaultReturnSeoStr[$language['id_lang']][$field] .= $objAdvancedSearchCriterionGroupClass->name[$language['id_lang']] . ' ';
                    }
                    if ($field == 'seo_url') {
                        $defaultReturnSeoStr[$language['id_lang']][$field] .= Tools::str2url($criterion_value) . (!$endLoop ? '-' : '');
                    } else {
                        $defaultReturnSeoStr[$language['id_lang']][$field] .= $criterion_value . (!$endLoop && ($field == 'meta_title' || $field == 'meta_description' || $field == 'meta_keywords') ? ', ' : ($endLoop ? '' : ' '));
                    }
                }
            }
        }
        return $defaultReturnSeoStr;
    }
    private function _cartesianReOrder($array)
    {
        $current = array_shift($array);
        if (count($array) > 0) {
            $results = array();
            $temp = $this->_cartesianReOrder($array);
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
    public static function getArrayCriteriaFromSeoArrayCriteria($criteria)
    {
        $newCriteria = array();
        foreach ($criteria as $value) {
            $info_criterion = explode('_', $value);
            if (isset($info_criterion[2])) {
                $id_criterion_group = (int)$info_criterion[1];
                $id_criterion = $info_criterion[2];
            } else {
                $id_criterion_group = (int)$info_criterion[0];
                $id_criterion = $info_criterion[1];
            }
            $id_criterion = preg_replace('/[^0-9.,-~]/', '', $id_criterion);
            if (!isset($newCriteria[$id_criterion_group])) {
                $newCriteria[$id_criterion_group] = array();
            }
            $newCriteria[$id_criterion_group][] = $id_criterion;
        }
        return $newCriteria;
    }
    private function countProductFromSeoCriteria($id_search, $criteria, $id_currency)
    {
        if (self::_isFilledArray($criteria) && $criteria[0]) {
            $selected_criteria_groups_type = array();
            $newCriteria = self::getArrayCriteriaFromSeoArrayCriteria($criteria);
            if (sizeof($newCriteria)) {
                $selected_criteria_groups_type = As4SearchEngine::getCriterionGroupsTypeAndDisplay($id_search, array_keys($newCriteria));
            }
            $search = As4SearchEngine::getSearch($id_search, (int)$this->context->language->id, false);
            $search = $search[0];
            $resultTotalProducts = As4SearchEngineDb::row(As4SearchEngine::getQueryCountResults($search, (int)$this->context->language->id, $newCriteria, $selected_criteria_groups_type, $id_currency));
        }
        $total_product = isset($resultTotalProducts) ? $resultTotalProducts['total'] : 0;
        return $total_product;
    }
    private function _postProcessMassSeoSearch()
    {
        $id_search = Tools::getValue('id_search', false);
        $id_currency = Tools::getValue('id_currency', false);
        if (!$id_currency) {
            $id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        }
        self::_cleanBuffer();
        $return = '';
        if (!$id_search) {
            $return .= '<script type="text/javascript">parent.parent.show_error("' . $this->l('An error occured') . '");</script>';
        } else {
            $criteria_groups = explode(',', Tools::getValue('criteria_groups', ''));
            $criteria = Tools::getValue('criteria', false);
            $seoIds = array();
            if (!sizeof($criteria_groups) || ! sizeof($criteria)) {
                $return .= '<script type="text/javascript">parent.parent.show_error("' . $this->l('Please select at least one criteria') . '");</script>';
            } else {
                $criteria_reorder = array();
                foreach ($criteria_groups as $key_criterion_group) {
                    $id_criterion_group = self::parseInt($key_criterion_group);
                    if (isset($criteria[$id_criterion_group]) && sizeof($criteria[$id_criterion_group])) {
                        $criteria_reorder[] = $criteria[$id_criterion_group];
                    }
                }
                $criteria_cartesian = $this->_cartesianReOrder($criteria_reorder);
                foreach ($criteria_cartesian as $criteria_final_str) {
                    $criteria_final = explode(',', $criteria_final_str);
                    $resultTotalProducts = $this->countProductFromSeoCriteria($id_search, $criteria_final, $id_currency);
                    if (!$resultTotalProducts) {
                        continue;
                    }
                    $seo_key = As4SearchEngineSeo::getSeoKeyFromCriteria($id_search, $criteria_final, $id_currency);
                    $cur_id_seo = AdvancedSearchSeoClass::seoDeletedExists($seo_key);
                    if ($cur_id_seo) {
                        AdvancedSearchSeoClass::undeleteSeoBySeoKey($seo_key);
                    }
                    if (!$cur_id_seo && AdvancedSearchSeoClass::seoExists($seo_key)) {
                        continue;
                    }
                    $defaultReturnSeoStr = $this->getSeoStrings($criteria_final, $id_search, $id_currency);
                    $objAdvancedSearchSeoClass = new AdvancedSearchSeoClass($cur_id_seo);
                    $objAdvancedSearchSeoClass->id_search = $id_search;
                    $objAdvancedSearchSeoClass->criteria = $criteria_final_str;
                    $objAdvancedSearchSeoClass->seo_key = $seo_key;
                    foreach ($defaultReturnSeoStr as $id_lang => $fields) {
                        foreach ($fields as $field => $fieldValue) {
                            $objAdvancedSearchSeoClass->{$field}[$id_lang] = $fieldValue;
                        }
                    }
                    $error = $objAdvancedSearchSeoClass->validateFields(false, true);
                    $errorLang = $objAdvancedSearchSeoClass->validateFieldsLang(false, true);
                    if ($error !== true) {
                        $return .= '<script type="text/javascript">parent.parent.show_error("' . addcslashes($error, '"') . '");</script>';
                    } elseif ($errorLang !== true) {
                        $return .= '<script type="text/javascript">parent.parent.show_error("' . addcslashes($errorLang, '"') . '");</script>';
                    } else {
                        $objAdvancedSearchSeoClass->save();
                        $seoIds[] = $objAdvancedSearchSeoClass->id;
                    }
                }
                if (Tools::getValue('massSeoSearchCrossLinks', true) && sizeof($seoIds)) {
                    foreach ($seoIds as $id_seo) {
                        foreach ($seoIds as $id_seo2) {
                            if ($id_seo == $id_seo2) {
                                continue;
                            }
                            $row = array('id_seo' => (int)$id_seo, 'id_seo_linked' => (int)$id_seo2);
                            Db::getInstance()->insert('pm_advancedsearch_seo_crosslinks', $row);
                        }
                    }
                }
                $return .= '<script type="text/javascript">parent.parent.show_info("' . $this->l('Saved') . '");parent.parent.reloadPanel("seo_search_panel_' . (int)$id_search . '");parent.parent.closeDialogIframe();</script>';
            }
        }
        echo $return;
        die();
    }
    private function _postProcessSeoRegenerate()
    {
        $id_search = Tools::getValue('id_search', false);
        $fields_to_regenerate = Tools::getValue('fields_to_regenerate', false);
        self::_cleanBuffer();
        if (!$id_search) {
            $this->_html .= '<script type="text/javascript">parent.parent.show_error("' . $this->l('An error occured') . '");</script>';
        }
        if (!$fields_to_regenerate || ! sizeof($fields_to_regenerate)) {
            $this->_html .= '<script type="text/javascript">parent.parent.show_error("' . $this->l('You must select at least one field to regenerate') . '");</script>';
        } else {
            $seoSearchs = AdvancedSearchSeoClass::getSeoSearchs((int)$this->context->language->id, false, $id_search);
            foreach ($seoSearchs as $row) {
                $defaultReturnSeoStr = $this->getSeoStrings(unserialize($row['criteria']), $id_search, $row['id_currency'], $fields_to_regenerate);
                if ($defaultReturnSeoStr && is_array($defaultReturnSeoStr) && sizeof($defaultReturnSeoStr)) {
                    $objAdvancedSearchSeoClass = new AdvancedSearchSeoClass($row['id_seo']);
                    $objAdvancedSearchSeoClass->id_search = $id_search;
                    foreach ($defaultReturnSeoStr as $id_lang => $fields) {
                        foreach ($fields as $field => $fieldValue) {
                            $objAdvancedSearchSeoClass->{$field}[$id_lang] = $fieldValue;
                        }
                    }
                    $error = $objAdvancedSearchSeoClass->validateFields(false, true);
                    $errorLang = $objAdvancedSearchSeoClass->validateFieldsLang(false, true);
                    if ($error !== true) {
                        $this->_html .= '<script type="text/javascript">parent.parent.show_error("' . addcslashes($error, '"') . '");</script>';
                    } elseif ($errorLang !== true) {
                        $this->_html .= '<script type="text/javascript">parent.parent.show_error("' . addcslashes($errorLang, '"') . '");</script>';
                    } else {
                        if (!$objAdvancedSearchSeoClass->save()) {
                            $this->_html .= '<script type="text/javascript">parent.parent.show_error("' . $this->l('Error while updating seo search') . '");</script>';
                        }
                    }
                }
            }
            $this->_html .= '<script type="text/javascript">parent.parent.show_info("' . $this->l('Seo data regenerated successfully') . '");parent.parent.reloadPanel("seo_search_panel_' . (int)$id_search . '");parent.parent.closeDialogIframe();</script>';
        }
        echo $this->_html;
        die();
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
        $this->_cleanOutput();
        $this->updateAdvancedStyles(Tools::getValue('advancedConfig'));
        $this->_html .= $this->displayConfirmation($this->l('Styles updated successfully'));
    }
    protected function _postProcess()
    {
        if (Tools::getValue('submitAdvancedConfig')) {
            $this->saveAdvancedConfig();
        } elseif (Tools::getIsset('submitSearch')) {
            $this->_postProcessSearch();
        } elseif (Tools::getIsset('submitSearchVisibility')) {
            $this->_postProcessSearchVisibility();
        } elseif (Tools::getIsset('submitCriteria')) {
            $this->_postProcessCriteria();
        } elseif (Tools::getIsset('submitSeoSearchForm')) {
            $this->_postProcessSeoSearch();
        } elseif (Tools::getIsset('submitMassSeoSearchForm')) {
            $this->_postProcessMassSeoSearch();
        } elseif (Tools::getIsset('submitSeoRegenerate')) {
            $this->_postProcessSeoRegenerate();
        } elseif (Tools::getIsset('action') && Tools::getValue('action') == 'orderSearchEngine') {
            $this->_cleanOutput();
            $order = Tools::getValue('order') ? explode(',', Tools::getValue('order')) : array();
            foreach ($order as $position => $searchIdentifier) {
                $idSearch = self::parseInt($searchIdentifier);
                if (!empty($idSearch)) {
                    Db::getInstance()->update('pm_advancedsearch', array('position' => (int)$position), 'id_search = ' . (int)$idSearch);
                }
            }
            $this->_html .= $this->l('Saved');
            $this->_echoOutput(true);
        } elseif (Tools::getIsset('action') && Tools::getValue('action') == 'orderCriterion') {
            $this->_cleanOutput();
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
            $this->_html .= $this->l('Saved');
            $this->_echoOutput(true);
        } elseif (Tools::getIsset('action') && Tools::getValue('action') == 'orderCriterionGroup') {
            $this->_cleanOutput();
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
            $this->_html .= $this->l('Saved');
            $this->_echoOutput(true);
        } elseif (Tools::getIsset('submitModuleConfiguration') && Tools::isSubmit('submitModuleConfiguration')) {
            $config = $this->_getModuleConfiguration();
            foreach (array('fullTree', 'autoReindex', 'autoSyncActiveStatus', 'moduleCache', 'blurEffect') as $configKey) {
                $config[$configKey] = (bool)Tools::getValue($configKey);
            }
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                $as4SearchProvider = new As4SearchProvider(
                    $this,
                    $this->getTranslator(),
                    new AdvancedSearchClass(),
                    null
                );
                $sortOrders = $as4SearchProvider->getSortOrders(true, false);
                $config['sortOrders'] = array();
                foreach ($sortOrders as $sortOrder) {
                    $config['sortOrders'][$sortOrder->toString()] = (bool)Tools::getValue(str_replace('.', '_', $sortOrder->toString()));
                }
            }
            $this->_setModuleConfiguration($config);
            $this->context->controller->confirmations[] = $this->l('Module configuration successfully saved');
            $this->processClearAllCache(false);
        }
        parent::_postProcess();
    }
    protected function _postSaveProcess($params)
    {
        parent::_postSaveProcess($params);
        if ($params['class'] == 'AdvancedSearchCriterionGroupClass' && Tools::isSubmit('submitCriteriaGroupOptions')) {
            $this->generateCss();
            if (Validate::isLoadedObject($params['obj'])) {
                $this->_html .= '<script type="text/javascript">parent.parent.updateCriterionGroupName("'.(int)$params['obj']->id.'", '.Tools::jsonEncode($params['obj']->name[(int)$this->context->language->id]).');</script>';
            }
            $this->_html .= '<script type="text/javascript">parent.parent.closeDialogIframe();</script>';
        }
    }
    protected function _postDuplicateProcess($params)
    {
        parent::_postDuplicateProcess($params);
        if ($params['class'] == 'AdvancedSearchClass') {
            $this->generateCss();
            $this->_html .= 'addTabPanel("#wrapAsTab","' . $params['obj']->internal_name . '",' . (int)$params['obj']->id . ', true);';
        }
    }
    protected function _postDeleteProcess($params)
    {
        parent::_postDeleteProcess($params);
        if ($params['class'] == 'AdvancedSearchClass') {
            $this->_html .= 'removeTabPanel("#wrapAsTab","li#TabSearchAdminPanel' . Tools::getValue('id_search') . '","ul#asTab");';
            if (!sizeof(As4SearchEngine::getSearchsId(false, $this->context->shop->id))) {
                $this->_html .= 'parent.parent.location.reload();';
            }
        }
    }
    private function _renderConfigurationForm()
    {
        $config = $this->_getModuleConfiguration();
        $sortOrders = array();
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $as4SearchProvider = new As4SearchProvider(
                $this,
                $this->getTranslator(),
                new AdvancedSearchClass(),
                null
            );
            $sortOrders = $as4SearchProvider->getSortOrders(true, false);
        }
        $vars = array(
            'config' => $config,
            'sort_orders' => $sortOrders,
            'default_config' => $this->_defaultConfiguration,
        );
        return $this->fetchTemplate('module/tabs/configuration.tpl', $vars);
    }
    protected function checkSeoCriteriaCombination()
    {
        $criteria = Tools::getValue('criteria', false);
        $id_search = Tools::getValue('id_search', false);
        $id_currency = Tools::getValue('id_currency', false);
        $resultTotalProducts = false;
        if ($criteria && $id_search) {
            $criteria = explode(',', $criteria);
            $resultTotalProducts = $this->countProductFromSeoCriteria($id_search, $criteria, $id_currency);
        }
        if (!$resultTotalProducts) {
            $this->_html .= '$("input[name=submitSeoSearchForm]").hide();$("#errorCombinationSeoSearchForm").show();';
            $this->_html .= '$("#nbProductsCombinationSeoSearchForm").html(\'<p class="ui-state-error ui-corner-all" style="padding:5px;"><b>0 ' . $this->l('result found') . '</b></p>\');';
        } else {
            $this->_html .= '$("input[name=submitSeoSearchForm]").show();$("#errorCombinationSeoSearchForm").hide();';
            $this->_html .= '$("#nbProductsCombinationSeoSearchForm").html(\'<p class="ui-state-highlight ui-corner-all" style="padding:5px;"><b>' . $resultTotalProducts . ' ' . $this->l('result(s) found(s)') . '</b></p>\');';
        }
    }
    public function displaySearchAdminPanel()
    {
        $id_search = (int) Tools::getValue('id_search');
        $advanced_search = As4SearchEngine::getSearch($id_search, (int)$this->context->language->id, false);
        if (!isset($advanced_search[0])) {
            return;
        }
        $advanced_search = $advanced_search[0];
        $criterions_groups = $this->getCriterionsGroupsValue();
        $criterionsUnitGroupsTranslations = $this->getCriterionUnitTranslations();
        $criterions_groups_indexed = As4SearchEngineIndexation::getCriterionsGroupsIndexed($advanced_search['id_search'], (int)$this->context->language->id);
        $keys_criterions_group_indexed = array();
        $criterions_groups_to_reindex = As4SearchEngineIndexation::getCriterionsGroupsIndexed($advanced_search['id_search'], (int)$this->context->language->id, false);
        $criteriaGroupToReindex = array();
        if (self::_isFilledArray($criterions_groups_to_reindex)) {
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
        $this->_html .= $this->fetchTemplate('module/tabs/search_engine.tpl', $vars);
    }
    protected function processIndexCriterionsGroup()
    {
        self::_changeTimeLimit(0);
        $key_criterions_group = Tools::getValue('key_criterions_group', false);
        if (!$key_criterions_group) {
            die;
        }
        $infos_criterions_group = explode('-', $key_criterions_group);
        list($criterions_group_type, $id_criterion_group_linked, $id_search) = $infos_criterions_group;
        if (!$criterions_group_type || !$id_search) {
            die;
        }
        $id_criterion_group = As4SearchEngineIndexation::indexCriterionsGroup($id_search, $criterions_group_type, $id_criterion_group_linked, false, true, false);
        As4SearchEngineIndexation::optimizedSearchTables($id_search);
        self::clearSmartyCache((int)$id_search);
        $new_key_criterions_group_indexed = $criterions_group_type . '-' . (int)$id_criterion_group_linked . '-' . (int)$id_search . '-' . (int)$id_criterion_group;
        $this->_html .= '$("#'.$key_criterions_group.'").attr("id", "'.$new_key_criterions_group_indexed.'");';
        $key_criterions_group = $new_key_criterions_group_indexed;
        $this->_html .= '$("#'.$key_criterions_group.' .loadingOnConnectList").hide().remove();';
        $this->_html .= 'setCriterionGroupActions("'.$key_criterions_group.'");';
        $this->_html .= '$("#'.$key_criterions_group.'").attr("rel",'.(int)$id_criterion_group.');';
        $this->_html .= 'getCriterionGroupActions("'.$key_criterions_group.'");';
        $this->_html .= 'saveCriterionsGroupSorting('. (int)$id_search .');';
        $criterions_groups_to_reindex = As4SearchEngineIndexation::getCriterionsGroupsIndexed((int)$id_search, (int)$this->context->language->id, false);
        $criteriaGroupToReindex = array();
        if (self::_isFilledArray($criterions_groups_to_reindex)) {
            foreach ($criterions_groups_to_reindex as $criterions_group_indexed) {
                $criteriaGroupToReindex[] = array(
                    'id_search' => (int)$id_search,
                    'id_criterion_group' => (int)$criterions_group_indexed['id_criterion_group'],
                );
            }
        }
        $this->_html .= 'var criteriaGroupToReindex'.(int)$id_search.' = '. Tools::jsonEncode($criteriaGroupToReindex) .';';
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
        As4SearchEngineIndexation::desIndexCriterionsGroup($id_search, $criterions_group_type, $id_criterion_group_linked, $id_criterion_group);
        As4SearchEngineIndexation::optimizedSearchTables($id_search);
        self::clearSmartyCache((int)$id_search);
        $this->_html .= '$("#'.$key_criterions_group.' .loadingOnConnectList").hide().remove();';
        $criterions_groups_to_reindex = As4SearchEngineIndexation::getCriterionsGroupsIndexed((int)$id_search, (int)$this->context->language->id, false);
        $criteriaGroupToReindex = array();
        if (self::_isFilledArray($criterions_groups_to_reindex)) {
            foreach ($criterions_groups_to_reindex as $criterions_group_indexed) {
                $criteriaGroupToReindex[] = array(
                    'id_search' => (int)$id_search,
                    'id_criterion_group' => (int)$criterions_group_indexed['id_criterion_group'],
                );
            }
        }
        $this->_html .= 'var criteriaGroupToReindex'.(int)$id_search.' = '. Tools::jsonEncode($criteriaGroupToReindex) .';';
    }
    protected function processRemoveEmptySeo()
    {
        $id_search = Tools::getValue('id_search', false);
        if (!$id_search) {
            die();
        }
        $seoSearchs = AdvancedSearchSeoClass::getSeoSearchs((int)$this->context->language->id, false, $id_search);
        foreach ($seoSearchs as $row) {
            $resultTotalProducts = $this->countProductFromSeoCriteria($id_search, unserialize($row['criteria']), $row['id_currency']);
            if (!$resultTotalProducts) {
                $objAdvancedSearchSeoClass = new AdvancedSearchSeoClass($row['id_seo']);
                if (!$objAdvancedSearchSeoClass->delete()) {
                    $this->_html .= 'show_error("' . $this->l('Error while deleting results page') . ' ' . $row['id_seo'] . '");';
                }
            }
        }
        $this->_html .= 'show_info("' . $this->l('Empty results pages have been deleted') . '");reloadPanel("seo_search_panel_' . (int)$id_search . '");';
    }
    protected function processFillSeoFields()
    {
        $criteria = Tools::getValue('criteria', false);
        $id_search = Tools::getValue('id_search', false);
        $id_currency = Tools::getValue('id_currency', false);
        if (!$criteria || !$id_search) {
            die;
        }
        $criteria = explode(',', $criteria);
        $defaultReturnSeoStr = $this->getSeoStrings($criteria, $id_search, $id_currency);
        foreach ($defaultReturnSeoStr as $id_lang => $fields) {
            foreach ($fields as $field => $fieldValue) {
                $this->_html .= '$("input[name='.$field.'_'.$id_lang.']").val("'.addcslashes($fieldValue, '"').'");';
            }
        }
    }
    protected function processClearAllCache($outputConfirmation = true)
    {
        $advanced_searchs_id = As4SearchEngine::getSearchsId(false);
        foreach ($advanced_searchs_id as $idSearch) {
            self::clearSmartyCache($idSearch);
        }
        As4SearchEngine::setLocalStorageCacheKey();
        if ($outputConfirmation) {
            $this->_html .= 'show_info("'.addcslashes($this->l('Cache has been flushed'), '"').'");';
        }
    }
    protected function processClearAllTables()
    {
        $advanced_searchs_id = As4SearchEngine::getSearchsId(false);
        As4SearchEngine::clearAllTables();
        foreach ($advanced_searchs_id as $idSearch) {
            $this->_html .= 'removeTabPanel("#wrapAsTab","li#TabSearchAdminPanel'.$idSearch.'","ul#asTab");';
        }
        $this->_html .= 'show_info("'.addcslashes($this->l('Clear done'), '"').'"); $("#msgNoResults").slideDown();';
    }
    protected function processReindexSpecificSearch()
    {
        self::_changeTimeLimit(0);
        $id_search = Tools::getValue('id_search');
        As4SearchEngineIndexation::reindexSpecificSearch($id_search);
        $this->_html .= '$( "#progressbarReindexSpecificSearch'.(int)$id_search.'" ).progressbar( "option", "value", 100 );show_info("'.addcslashes($this->l('Indexation done'), '"').'")';
    }
    protected function processDeleteCriterionImg()
    {
        $id_search = Tools::getValue('id_search');
        $id_criterion = Tools::getValue('id_criterion');
        $id_lang = Tools::getValue('id_lang');
        $objCriterion = new AdvancedSearchCriterionClass($id_criterion, $id_search);
        $file_name = $objCriterion->icon[$id_lang];
        $file_name_final_path = _PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/search_files/criterions/'.$file_name;
        $objCriterion->icon[$id_lang] = '';
        if (AdvancedSearchCoreClass::_isRealFile($file_name_final_path)) {
            unlink($file_name_final_path);
        }
        if ($objCriterion->save()) {
            $this->_html .= 'show_info("'.addcslashes($this->l('Criterion image deleted'), '"').'");';
        } else {
            $this->_html .= 'show_info("'.addcslashes($this->l('An error occured'), '"').'");';
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
        if (AdvancedSearchCoreClass::_isRealFile($file_name_temp_path)) {
            rename($file_name_temp_path, $file_name_final_path . $file_name);
            $objCriterion = new AdvancedSearchCriterionClass($id_criterion, $id_search);
            $objCriterion->icon[$id_lang] = $file_name;
            foreach (Language::getLanguages(false) as $lang) {
                if (empty($objCriterion->icon[$lang['id_lang']])) {
                    $new_temp_file_lang = uniqid(self::$_module_prefix . mt_rand()).'.'.self::_getFileExtension($file_name);
                    copy($file_name_final_path . $file_name, $file_name_final_path . $new_temp_file_lang);
                    $objCriterion->icon[$lang['id_lang']] = $new_temp_file_lang;
                }
            }
            if ($objCriterion->save()) {
                $this->_html .= 'ok';
            } else {
                $this->_html .= 'ko';
            }
        } else {
            $this->_html .= 'ko';
        }
    }
    protected function processDeleteCustomCriterion()
    {
        $objCriterion = new AdvancedSearchCriterionClass((int)Tools::getValue('id_criterion'), (int)Tools::getValue('id_search'));
        if (Validate::isLoadedObject($objCriterion)) {
            if ($objCriterion->delete()) {
                $this->_html .= 'parent.show_info("' . $this->l('Successfully deleted') . '");';
                $this->_html .= 'this.location.reload(true);';
            } else {
                $this->_html .= 'parent.show_error("' . $this->l('Error while updating criterion') . '");';
            }
        } else {
            $this->_html .= 'parent.show_error("' . $this->l('Error while deleting criterion') . '");';
        }
    }
    protected function processAddCustomCriterion()
    {
        $objCriterion = new AdvancedSearchCriterionClass(null, (int)Tools::getValue('id_search'));
        $objCriterion->id_criterion_group = (int)Tools::getValue('id_criterion_group');
        $objCriterion->id_criterion_linked = 0;
        $objCriterion->is_custom = 1;
        $this->copyFromPost($objCriterion);
        $validationErrors = AdvancedSearchCoreClass::_retroValidateController($objCriterion);
        if (!self::_isFilledArray($validationErrors)) {
            if ($objCriterion->save()) {
                $this->_html .= 'parent.show_info("' . $this->l('Saved') . '");';
                $this->_html .= 'this.location.reload(true);';
            } else {
                $this->_html .= 'parent.show_error("' . $this->l('Error while adding criterion') . '");';
            }
        } else {
            $this->_html .= 'parent.show_error("' . $this->l('Error while adding criterion') . '");';
            foreach ($validationErrors as $error) {
                $this->_html .= 'parent.show_error("' . $error . '");';
            }
        }
    }
    protected function processUpdateCustomCriterion()
    {
        $objCriterion = new AdvancedSearchCriterionClass((int)Tools::getValue('id_criterion'), (int)Tools::getValue('id_search'));
        $this->copyFromPost($objCriterion);
        if (Validate::isLoadedObject($objCriterion)) {
            if ($objCriterion->save()) {
                $this->_html .= 'parent.show_info("' . $this->l('Saved') . '");';
                $this->_html .= 'this.location.reload(true);';
            } else {
                $this->_html .= 'parent.show_error("' . $this->l('Error while updating criterion') . '");';
            }
        } else {
            $this->_html .= 'parent.show_error("' . $this->l('Error while updating criterion') . '");';
        }
    }
    protected function processAddCustomCriterionToGroup()
    {
        $idSearch = (int)Tools::getValue('id_search');
        $idCriterionGroup = (int)Tools::getValue('id_criterion_group');
        $criterionsGroupList = explode(',', Tools::getValue('criterionsGroupList'));
        $newCriterionsGroupList = array();
        if (self::_isFilledArray($criterionsGroupList)) {
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
        $customCriterionList = AdvancedSearchCriterionClass::getCustomCriterionsLinkIdsByGroup($idSearch, $idCriterionGroup);
        $idCriterionParentToDelete = array();
        foreach ($customCriterionList as $idCriterionParent => $currentCriterionsGroupList) {
            $idCriterionParentToDelete[] = (int)$idCriterionParent;
        }
        foreach ($newCriterionsGroupList as $idCriterionParent => $currentCriterionsGroupList) {
            $idCriterionParentToDelete[] = (int)$idCriterionParent;
        }
        if (sizeof($idCriterionParentToDelete)) {
            As4SearchEngineDb::execute('DELETE FROM `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_list` WHERE `id_criterion_parent` IN (' . implode(',', array_map('intval', $idCriterionParentToDelete)) . ')');
        }
        foreach ($customCriterionList as $idCriterionParent => $currentCriterionsGroupList) {
            AdvancedSearchCriterionClass::populateCriterionsLink($idSearch, $idCriterionParent);
        }
        $sqlInsertMultiple = array();
        $sqlInsertMultipleHeader = 'INSERT IGNORE INTO `'._DB_PREFIX_.'pm_advancedsearch_criterion_'.(int)$idSearch.'_list` (`id_criterion_parent`, `id_criterion`) VALUES ';
        foreach ($newCriterionsGroupList as $idCriterionParent => $currentCriterionsGroupList) {
            if (self::_isFilledArray($currentCriterionsGroupList)) {
                foreach ($currentCriterionsGroupList as $idCriterion) {
                    $sqlInsertMultiple[] = '('. (int)$idCriterionParent. ', '. (int)$idCriterion .')';
                    As4SearchEngineIndexation::sqlBulkInsert('pm_advancedsearch_criterion_'.(int)$idSearch.'_list', $sqlInsertMultipleHeader, $sqlInsertMultiple, 1000);
                }
            }
        }
        As4SearchEngineIndexation::sqlBulkInsert('pm_advancedsearch_criterion_'.(int)$idSearch.'_list', $sqlInsertMultipleHeader, $sqlInsertMultiple, 1);
        foreach ($newCriterionsGroupList as $idCriterionParent => $currentCriterionsGroupList) {
            AdvancedSearchCriterionClass::populateCriterionsLink($idSearch, $idCriterionParent, false, $currentCriterionsGroupList);
        }
        $this->_html .= 'parent.show_info("' . $this->l('Saved') . '");';
    }
    protected function processEnableAllCriterions()
    {
        $objCriterionGoup = new AdvancedSearchCriterionGroupClass((int)Tools::getValue('id_criterion_group'), (int)Tools::getValue('id_search'));
        if (Validate::isLoadedObject($objCriterionGoup)) {
            if (AdvancedSearchCriterionGroupClass::enableAllCriterions((int)Tools::getValue('id_search'), (int)Tools::getValue('id_criterion_group'))) {
                $this->_html .= '$("img[id^=imgActiveCriterion]").attr("src","../img/admin/enabled.gif");';
                $this->_html .= 'parent.show_info("' . $this->l('Saved') . '");';
            } else {
                $this->_html .= 'parent.show_error("' . $this->l('Error while updating criterions status') . '");';
            }
        } else {
            $this->_html .= 'parent.show_error("' . $this->l('Error while updating criterions status') . '");';
        }
    }
    protected function processDisableAllCriterions()
    {
        $objCriterionGoup = new AdvancedSearchCriterionGroupClass((int)Tools::getValue('id_criterion_group'), (int)Tools::getValue('id_search'));
        if (Validate::isLoadedObject($objCriterionGoup)) {
            if (AdvancedSearchCriterionGroupClass::disableAllCriterions((int)Tools::getValue('id_search'), (int)Tools::getValue('id_criterion_group'))) {
                $this->_html .= '$("img[id^=imgActiveCriterion]").attr("src","../img/admin/disabled.gif");';
                $this->_html .= 'parent.show_info("' . $this->l('Saved') . '");';
            } else {
                $this->_html .= 'parent.show_error("' . $this->l('Error while updating criterions status') . '");';
            }
        } else {
            $this->_html .= 'parent.show_error("' . $this->l('Error while updating criterions status') . '");';
        }
    }
    protected function processActiveCriterion()
    {
        $ObjAdvancedSearchCriterionClass = new AdvancedSearchCriterionClass(Tools::getValue('id_criterion'), Tools::getValue('id_search'));
        $ObjAdvancedSearchCriterionClass->visible = ($ObjAdvancedSearchCriterionClass->visible ? 0 : 1);
        if ($ObjAdvancedSearchCriterionClass->save()) {
            $this->_html .= '$("#imgActiveCriterion' . $ObjAdvancedSearchCriterionClass->id . '").attr("src","../img/admin/' . ($ObjAdvancedSearchCriterionClass->visible ? 'enabled' : 'disabled') . '.gif");';
            $this->_html .= 'parent.show_info("' . $this->l('Saved') . '");';
        } else {
            $this->_html .= 'parent.show_error("' . $this->l('Error while updating search') . '");';
        }
    }
    protected function processActiveSearch()
    {
        $ObjAdvancedSearchClass = new AdvancedSearchClass(Tools::getValue('id_search'));
        $ObjAdvancedSearchClass->active = ($ObjAdvancedSearchClass->active ? 0 : 1);
        if ($ObjAdvancedSearchClass->save()) {
            if ($ObjAdvancedSearchClass->active) {
                $this->_html .= '
                    $("#searchStatusLabel' . $ObjAdvancedSearchClass->id . '").html("' . $this->l('enabled') . '");
                    $(".status_search_' . $ObjAdvancedSearchClass->id . ' span").addClass("ui-icon-circle-check").removeClass("ui-icon-circle-close");
                    $(".status_search_' . $ObjAdvancedSearchClass->id . '").toggleClass("enabled_search");
                ';
            } else {
                $this->_html .= '
                    $("#searchStatusLabel' . $ObjAdvancedSearchClass->id . '").html("' . $this->l('disabled') . '");
                    $(".status_search_' . $ObjAdvancedSearchClass->id . ' span").removeClass("ui-icon-circle-check").addClass("ui-icon-circle-close");
                    $(".status_search_' . $ObjAdvancedSearchClass->id . '").toggleClass("enabled_search");
                ';
            }
            $this->_html .= 'show_info("' . $this->l('Saved') . '");';
        } else {
            $this->_html .= 'show_error("' . $this->l('Error while updating search') . '");';
        }
    }
    protected function processDeleteSeoSearch()
    {
        $ObjAdvancedSearchSeoClass = new AdvancedSearchSeoClass(Tools::getValue('id_seo'));
        $ObjAdvancedSearchSeoClass->deleted = 1;
        if ($ObjAdvancedSearchSeoClass->save()) {
            $this->_html .= 'show_info("' . $this->l('The results page has been deleted') . '");reloadPanel("seo_search_panel_' . (int)Tools::getValue('id_search') . '");';
        } else {
            $this->_html .= 'show_error("' . $this->l('Error while deleting the results page') . '");';
        }
    }
    protected function processDeleteMassSeo()
    {
        $id_seos = Tools::getValue('seo_group_action', false);
        $id_search = Tools::getValue('id_search', false);
        if (self::_isFilledArray($id_seos)) {
            foreach ($id_seos as $id_seo) {
                $objAdvancedSearchSeoClass = new AdvancedSearchSeoClass($id_seo);
                $objAdvancedSearchSeoClass->deleted = 1;
                $objAdvancedSearchSeoClass->save();
            }
            $this->_html .= 'show_info("' . $this->l('The results page has been deleted') . '");reloadPanel("seo_search_panel_' . (int)$id_search . '");';
        } else {
            $this->_html .= 'show_error("' . $this->l('Please select at least one results page') . '");';
        }
    }
    protected function displaySeoPriceSlider()
    {
        $id_search = Tools::getValue('id_search', false);
        $id_criterion_group = Tools::getValue('id_criterion_group', false);
        $id_criterion_group_linked = Tools::getValue('id_criterion_group_linked', false);
        $id_currency = Tools::getValue('id_currency', false);
        $currency = new Currency($id_currency);
        if (!$id_search || !$id_currency) {
            die();
        }
        $search = As4SearchEngine::getSearch($id_search, false);
        $price_range = As4SearchEngine::getPriceRangeForSearchBloc($search[0], (int)$id_criterion_group_linked, (int)$id_currency, (int)$this->getCurrentCustomerGroupId(), (int)$this->context->country->id, array(), array());
        $min_price_id_currency = (int)$price_range[0]['min_price_id_currency'];
        $max_price_id_currency = (int)$price_range[0]['max_price_id_currency'];
        if (($min_price_id_currency == 0 && $min_price_id_currency != $id_currency) || ($min_price_id_currency != 0 && $min_price_id_currency != $id_currency)) {
            $price_range[0]['min_price'] = Tools::convertPrice($price_range[0]['min_price'], $id_currency);
        }
        if (($max_price_id_currency == 0 && $max_price_id_currency != $id_currency) || ($max_price_id_currency != 0 && $max_price_id_currency != $id_currency)) {
            $price_range[0]['max_price'] = Tools::convertPrice($price_range[0]['max_price'], $id_currency);
        }
        $price_range[0]['min_price'] = floor($price_range[0]['min_price']);
        $price_range[0]['max_price'] = ceil($price_range[0]['max_price']);
        $vars = array(
            'id_criterion_group' => $id_criterion_group,
            'price_range' => $price_range[0],
            'currency' => $currency,
        );
        $this->_html .= $this->fetchTemplate('module/seo/price_slider.tpl', $vars);
    }
    protected function displaySeoSearchOptions()
    {
        if (Tools::getValue('id_seo_excludes')) {
            $id_seo_excludes = explode(',', Tools::getValue('id_seo_excludes', false));
        } else {
            $id_seo_excludes = array();
        }
        if (Tools::getValue('id_seo_origin')) {
            $id_seo_excludes[] = (int)Tools::getValue('id_seo_origin');
        }
        $query_search = Tools::getValue('q', false);
        $limit = Tools::getValue('limit', 100);
        $start = Tools::getValue('start', 0);
        $nbResults = AdvancedSearchSeoClass::getCrossLinksAvailable((int)$this->context->language->id, $id_seo_excludes, $query_search, true);
        $results = AdvancedSearchSeoClass::getCrossLinksAvailable((int)$this->context->language->id, $id_seo_excludes, $query_search, false, $limit, $start);
        foreach ($results as $key => $value) {
            $this->_html .= $key . '=' . $value . "\n";
        }
        if ($nbResults > ($start + $limit)) {
            $this->_html .= 'DisplayMore' . "\n";
        }
    }
    protected function displayCmsOptions()
    {
        $query = Tools::getValue('q', false);
        if (trim($query)) {
            $limit = Tools::getValue('limit', 100);
            $start = Tools::getValue('start', 0);
            $items = As4SearchEngineDb::query('
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
                    $this->_html .= $row['id_cms']. '=' .$row['meta_title']. "\n";
                }
            }
        }
    }
    protected function displaySeoSearchPanelList()
    {
        $id_search = (int)Tools::getValue('id_search');
        $seoSearchs = AdvancedSearchSeoClass::getSeoSearchs((int)$this->context->language->id, false, $id_search);
        foreach ($seoSearchs as &$row) {
            $row['total_products'] = $this->countProductFromSeoCriteria($id_search, unserialize($row['criteria']), $row['id_currency']);
        }
        $vars = array(
            'id_search' => $id_search,
            'rewrite_settings' => Configuration::get('PS_REWRITING_SETTINGS'),
            'seo_searchs' => $seoSearchs,
            'sitemap_url' => $this->context->link->getModuleLink('pm_advancedsearch4', 'seositemap', array('id_search' => (int)$id_search)),
        );
        $this->_html .= $this->fetchTemplate('module/seo/search_panel.tpl', $vars);
    }
    protected function displaySeoUrl($id_search)
    {
        $id_search = Tools::getValue('id_search', false);
        $id_seo = Tools::getValue('id_seo', false);
        if (!$id_seo || !$id_search) {
            die;
        }
        $ObjAdvancedSearchSeoClass = new AdvancedSearchSeoClass($id_seo, null);
        $seo_url_by_lang = array();
        foreach ($this->_languages as $language) {
            $seo_url_by_lang[(int)$language['id_lang']] = $this->context->link->getModuleLink('pm_advancedsearch4', 'seo', array('id_seo' => (int)$ObjAdvancedSearchSeoClass->id, 'seo_url' => $ObjAdvancedSearchSeoClass->seo_url[$language['id_lang']]), null, (int)$language['id_lang']);
        }
        $vars = array(
            'seo_url_by_lang' => $seo_url_by_lang,
            'pm_flags' => $this->displayPMFlags(),
        );
        $this->_html .= $this->fetchTemplate('module/seo/url.tpl', $vars);
    }
    protected function displaySeoUrlList()
    {
        $id_search = Tools::getValue('id_search');
        $seoSearchs = AdvancedSearchSeoClass::getSeoSearchs((int)$this->context->language->id, false, $id_search);
        if ($seoSearchs && self::_isFilledArray($seoSearchs)) {
            $new_SeoSearch = array();
            foreach ($seoSearchs as $row) {
                $ObjAdvancedSearchSeoClass = new AdvancedSearchSeoClass($row['id_seo'], null);
                foreach ($this->_languages as $language) {
                    $url = $this->context->link->getModuleLink('pm_advancedsearch4', 'seo', array('id_seo' => (int)$ObjAdvancedSearchSeoClass->id, 'seo_url' => $ObjAdvancedSearchSeoClass->seo_url[$language['id_lang']]), null, (int)$language['id_lang']);
                    $title = htmlentities($ObjAdvancedSearchSeoClass->title[$language['id_lang']], ENT_COMPAT, 'UTF-8');
                    $new_SeoSearch[$language['id_lang']][$ObjAdvancedSearchSeoClass->id]['url'] = $url;
                    $new_SeoSearch[$language['id_lang']][$ObjAdvancedSearchSeoClass->id]['title'] = $title;
                }
            }
            $seoSearchs = $new_SeoSearch;
            unset($new_SeoSearch);
        }
        $vars = array(
            'seo_url_list' => $seoSearchs,
            'pm_flags' => $this->displayPMFlags(),
        );
        $this->_html .= $this->fetchTemplate('module/seo/url_list.tpl', $vars);
    }
    protected function displaySeoRegenerateForm()
    {
        $vars = array(
            'id_search' => (int)Tools::getValue('id_search'),
        );
        $this->_html .= $this->fetchTemplate('module/seo/regenerate_form.tpl', $vars);
    }
    protected function displaySeoSearchForm($params)
    {
        $id_search = (int)Tools::getValue('id_search');
        $criterions_groups_indexed = As4SearchEngineSeo::getCriterionsGroupsIndexedForSEO($id_search, (int)$this->context->language->id);
        $search = As4SearchEngine::getSearch($id_search, false, false);
        $criteria = false;
        if ($params['obj'] && $params['obj']->criteria) {
            $criteria = unserialize($params['obj']->criteria);
        }
        if (Validate::isLoadedObject($params['obj']) && !empty($params['obj']->id_currency)) {
            $default_currency = new Currency($params['obj']->id_currency);
        }
        if (!Validate::isLoadedObject($default_currency)) {
            $default_currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
        foreach ($criterions_groups_indexed as &$criterions_group_indexed) {
            if ($criterions_group_indexed['range'] == 1) {
                if ($criterions_group_indexed['criterion_group_type'] == 'price') {
                    $criterions_group_indexed['range_sign'] = $default_currency->sign;
                }
                $ranges = explode(',', $criterions_group_indexed['range_interval']);
                $criterions = array();
                foreach ($ranges as $krange => $range) {
                    $rangeUp = (isset($ranges[$krange+1]) ? $ranges[$krange+1]:'');
                    $range1 = $range.'~'.$rangeUp;
                    $range2 = $this->getTextualRangeValue($range, $rangeUp, $criterions_group_indexed, $default_currency);
                    $criterions[] = array('id_criterion' => $range1, 'value' => $range2);
                }
                $criterions_group_indexed['criterions'] = $criterions;
            } elseif ($criterions_group_indexed['criterion_group_type'] == 'price') {
                $price_range = As4SearchEngine::getPriceRangeForSearchBloc($search[0], (int)$criterions_group_indexed['id_criterion_group_linked'], (int)$default_currency->id, (int)$this->getCurrentCustomerGroupId(), (int)$this->context->country->id, array(), array());
                $criterions_group_indexed['price_range'] = $price_range[0];
            } elseif ($criterions_group_indexed['display_type'] == 5 || $criterions_group_indexed['display_type'] == 8) {
                $range = As4SearchEngine::getCriterionsRange($search[0], (int)$criterions_group_indexed['id_criterion_group'], (int)$this->context->language->id, array(), array(), false, false, $criterions_group_indexed);
                $criterions_group_indexed['range'] = $range[0];
            } else {
                $criterions = As4SearchEngine::getCriterionsFromCriterionGroup((int)$criterions_group_indexed['id_criterion_group'], $criterions_group_indexed['id_search'], $criterions_group_indexed['sort_by'], $criterions_group_indexed['sort_way'], (int)$this->context->language->id);
                $criterions_group_indexed['criterions'] = $criterions;
            }
        }
        $criteria_values = array();
        if (self::_isFilledArray($criteria)) {
            foreach ($criteria as &$criterion) {
                $info_criterion = explode('_', $criterion);
                $id_criterion_group = $info_criterion[0];
                $id_criterion = $info_criterion[1];
                $objAdvancedSearchCriterionGroupClass = new AdvancedSearchCriterionGroupClass($id_criterion_group, $id_search, (int)$this->context->language->id);
                if (preg_match('#~#', $id_criterion)) {
                    $range = explode('~', $id_criterion);
                    $min = $range[0];
                    $max = (!empty($range[1]) ? $range[1] : '');
                    $currency = Context::getContext()->currency;
                    if ($objAdvancedSearchCriterionGroupClass->criterion_group_type == 'price' && !empty($params['obj']->id_currency)) {
                        $currency = new Currency($params['obj']->id_currency);
                        if (!Validate::isLoadedObject($currency)) {
                            $currency = Context::getContext()->currency;
                        }
                    }
                    $criterion_value = $this->getTextualRangeValue($min, $max, $objAdvancedSearchCriterionGroupClass, $currency);
                } else {
                    $objAdvancedSearchCriterionClass = new AdvancedSearchCriterionClass($id_criterion, $id_search, (int)$this->context->language->id);
                    $criterion_value = trim($objAdvancedSearchCriterionClass->value);
                }
                $criteria_values[$criterion] = $criterion_value;
            }
        }
        $crossLinksSelected = false;
        if ($params['obj'] && Validate::isLoadedObject($params['obj'])) {
            $crossLinksSelected = $params['obj']->getCrossLinksOptionsSelected((int)$this->context->language->id);
        }
        $vars = array(
            'params' => $params,
            'cross_links_selected' => $crossLinksSelected,
            'id_search' => $id_search,
            'criterions_groups_indexed' => $criterions_groups_indexed,
            'criteria' => $criteria,
            'criteria_values' => $criteria_values,
            'currentSeo' => $params['obj'],
            'default_currency_id' => $default_currency->id,
            'default_currency_sign_left' => $default_currency->getSign('left'),
            'default_currency_sign_right' => $default_currency->getSign('right'),
            'currencies' => Currency::getCurrencies(),
        );
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $vars['default_currency_sign_left'] = '';
            $vars['default_currency_sign_right'] = $default_currency->getSign();
        }
        $this->_html .= $this->fetchTemplate('module/seo/new_page.tpl', $vars);
    }
    protected function displayMassSeoSearchForm()
    {
        $id_search = (int)Tools::getValue('id_search');
        $search = As4SearchEngine::getSearch($id_search, false, false);
        $criterions_groups_indexed = As4SearchEngineSeo::getCriterionsGroupsIndexedForSEO($id_search, (int)$this->context->language->id);
        $default_currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        foreach ($criterions_groups_indexed as &$criterions_group_indexed) {
            if ($criterions_group_indexed['range'] == 1) {
                if ($criterions_group_indexed['criterion_group_type'] == 'price') {
                    $default_currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                    $criterions_group_indexed['range_sign'] = $default_currency->sign;
                }
                $ranges = explode(',', $criterions_group_indexed['range_interval']);
                $criterions = array();
                foreach ($ranges as $krange => $range) {
                    $rangeUp = (isset($ranges[$krange+1]) ? $ranges[$krange+1] : '');
                    $range1 = $range.'~'.$rangeUp;
                    $range2 = $this->getTextualRangeValue($range, $rangeUp, $criterions_group_indexed, $default_currency);
                    $criterions[] = array('id_criterion' => $range1, 'value' => $range2);
                }
                $criterions_group_indexed['criterions'] = $criterions;
            } elseif ($criterions_group_indexed['criterion_group_type'] == 'price') {
                $price_range = As4SearchEngine::getPriceRangeForSearchBloc($search[0], (int)$criterions_group_indexed['id_criterion_group_linked'], (int)$default_currency->id, (int)$this->getCurrentCustomerGroupId(), (int)$this->context->country->id, array(), array());
                $criterions_group_indexed['price_range'] = $price_range[0];
            } elseif ($criterions_group_indexed['display_type'] == 5 || $criterions_group_indexed['display_type'] == 8) {
                $range = As4SearchEngine::getCriterionsRange($search[0], (int)$criterions_group_indexed['id_criterion_group'], (int)$this->context->language->id, array(), array(), false, false, $criterions_group_indexed);
                $criterions_group_indexed['range'] = $range[0];
            } else {
                $criterions = As4SearchEngine::getCriterionsFromCriterionGroup((int)$criterions_group_indexed['id_criterion_group'], $criterions_group_indexed['id_search'], $criterions_group_indexed['sort_by'], $criterions_group_indexed['sort_way'], (int)$this->context->language->id);
                $criterions_group_indexed['criterions'] = $criterions;
            }
        }
        $vars = array(
            'id_search' => $id_search,
            'criterions_groups_indexed' => $criterions_groups_indexed,
            'default_currency_id' => $default_currency->id,
            'default_currency_sign_left' => $default_currency->getSign('left'),
            'default_currency_sign_right' => $default_currency->getSign('right'),
            'currencies' => Currency::getCurrencies(),
        );
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $vars['default_currency_sign_left'] = '';
            $vars['default_currency_sign_right'] = $default_currency->getSign();
        }
        $this->_html .= $this->fetchTemplate('module/seo/mass_page.tpl', $vars);
    }
    public function getContent()
    {
        $this->_base_config_url = $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name;
        if (Tools::getValue('makeUpdate')) {
            $this->checkIfModuleIsUpdate(true);
        }
        $moduleIsUpToDate = $this->checkIfModuleIsUpdate(false);
        $permissionsErrors = $this->_checkPermissions();
        if (!sizeof($permissionsErrors)) {
            if ($moduleIsUpToDate) {
                if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                    $this->_preProcess();
                    $this->_postProcess();
                }
            }
        }
        $config = $this->_getModuleConfiguration();
        $vars = array(
            'module_configuration' => $config,
            'module_display_name' => $this->displayName,
            'module_is_up_to_date' => $moduleIsUpToDate,
            'permissions_errors' => $permissionsErrors,
            'context_is_shop' => (Shop::getContext() == Shop::CONTEXT_SHOP),
            'css_js_assets' => $this->_loadCssJsLibraries(),
            'from_version_4_9' => (Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false) !== false && version_compare(Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false), '4.8', '>=') && version_compare(Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false), '4.9.1', '<=')),
            'from_version_4_10' => (Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false) !== false && version_compare(Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false), '4.11.0', '<')),
            'block_layered_is_active' => $this->blockLayeredIsEnabled(),
            'block_layered_display_name' => $this->getNativeLayeredModuleDisplayName(),
            'rating_invite' => $this->_showRating(true),
            'parent_content' => parent::getContent(),
            'search_engines' => array(),
            'cache_alert' => false,
        );
        if (!sizeof($permissionsErrors)) {
            if ($moduleIsUpToDate) {
                if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                    $advanced_searchs = As4SearchEngine::getAllSearchs((int)$this->context->language->id, false);
                    $vars['search_engines'] = $advanced_searchs;
                    $vars['cache_alert'] = (self::_isFilledArray($advanced_searchs) && isset($config['moduleCache']) && $config['moduleCache'] == false);
                    $vars['configuration_tab'] = $this->_renderConfigurationForm();
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
        $searchEngines = As4SearchEngine::getAllSearchs((int)$this->context->language->id, false);
        if (AdvancedSearchCoreClass::_isFilledArray($searchEngines)) {
            foreach ($searchEngines as $searchEngine) {
                $cronUrls[] = $this->context->link->getModuleLink('pm_advancedsearch4', 'cron', array('secure_key' => Configuration::getGlobalValue('PM_AS4_SECURE_KEY'), 'id_search' => (int)$searchEngine['id_search']));
            }
        }
        $vars = array(
            'main_cron_url' => $this->context->link->getModuleLink('pm_advancedsearch4', 'cron', array('secure_key' => Configuration::getGlobalValue('PM_AS4_SECURE_KEY'))),
            'cron_urls' => $cronUrls,
        );
        return $this->fetchTemplate('module/tabs/cron.tpl', $vars);
    }
    protected function displaySearchForm($params)
    {
        if (!empty($params['obj']->step_search)) {
            $params['obj']->search_type = 2;
        } else {
            if (!empty($params['obj']->filter_by_emplacement)) {
                $params['obj']->search_type = 0;
            } else {
                $params['obj']->search_type = 1;
            }
        }
        $searchType = array(
            0 => $this->l('Filter'),
            1 => $this->l('Whole catalog'),
            2 => $this->l('Step by step'),
        );
        $publicHookLabel = array(
            'displayhome' => $this->l('Homepage'),
            'displaytop' => $this->l('Top of page'),
            'displaynavfullwidth' => $this->l('Top of page full width'),
            'displayleftcolumn' => $this->l('Left column'),
            'displayrightcolumn' => $this->l('Right column'),
        );
        $hooks = $hooksId = $widgetHooksList = array();
        $valid_hooks = As4SearchEngine::$valid_hooks;
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
        $seo_searchs = AdvancedSearchSeoClass::getSeoSearchs(false, 0, (int)$params['obj']->id);
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
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
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
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $themeLayoutPreferencesLink = $this->context->link->getAdminLink('AdminThemes') . '&display=configureLayouts';
        } else {
            $currentTheme = Theme::getByDirectory($this->context->shop->theme_name);
            $themeLayoutPreferencesLink = $this->context->link->getAdminLink('AdminThemes') . '&updatetheme&id_theme=' . (Validate::isLoadedObject($currentTheme) ? $currentTheme->id : 0);
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
            'spa_module_is_active' => As4SearchEngine::isSPAModuleActive(),
            'products_per_page' => Configuration::get('PS_PRODUCTS_PER_PAGE'),
            'options_order_by' => $this->options_defaut_order_by,
            'options_order_way' => $this->options_defaut_order_way,
            'options_hide_criterion_method' => $this->options_show_hide_crit_method,
            'options_search_method' => $this->options_launch_search_method,
            'default_search_results_selector' => (version_compare(_PS_VERSION_, '1.7.0.0', '>=') ? '#content-wrapper' : '#center_column'),
            'theme_layout_preferences_link' => $themeLayoutPreferencesLink,
        );
        return $this->fetchTemplate('module/search_engine/new.tpl', $vars);
    }
    protected function displayVisibilityForm($params)
    {
        if ($params['obj']->id) {
            $categoriesAssociation = As4SearchEngine::getCategoriesAssociation($params['obj']->id, (int)$this->context->language->id);
            $cmsAssociation = As4SearchEngine::getCMSAssociation($params['obj']->id, (int)$this->context->language->id);
            $manufacturersAssociation = As4SearchEngine::getManufacturersAssociation($params['obj']->id);
            $suppliersAssociation = As4SearchEngine::getSuppliersAssociation($params['obj']->id);
            $productsAssociation = As4SearchEngine::getProductsAssociation($params['obj']->id, (int)$this->context->language->id);
            $productsCategoriesAssociation = As4SearchEngine::getProductsCategoriesAssociation($params['obj']->id, (int)$this->context->language->id);
            $specialPagesAssociation = As4SearchEngine::getSpecialPagesAssociation($params['obj']->id);
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
        $searchEngine = new AdvancedSearchClass($params['obj']->id_search);
        if (in_array($params['obj']->criterion_group_type, $this->criterionGroupIsTemplatisable)) {
            if ($params['obj']->criterion_group_type == 'attribute' && As4SearchEngineIndexation::isColorAttributesGroup($params['obj']->id_criterion_group_linked)) {
                $this->options_criteria_group_type[3] = $this->l('Link with color square');
                $this->options_criteria_group_type[7] = $this->l('Color Square');
            } elseif ($params['obj']->criterion_group_type == 'category' && $params['obj']->id_criterion_group_linked == 0) {
                $this->options_criteria_group_type[9] = $this->l('Level Depth');
            }
        }
        $displayTypeClass = array();
        foreach (array_keys($this->options_criteria_group_type) as $key) {
            $displayTypeClass[$key] = 'display_type-'.$key;
        }
        $newCustomCriterion = new AdvancedSearchCriterionClass(null, $params['obj']->id_search);
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
            'context_type' => $contextType,
            'display_vertical_search_block' => $this->display_vertical_search_block,
            'sortable_criterion_group' => $this->sortableCriterion,
            'new_custom_criterion' => $newCustomCriterion,
            'is_color_group' => As4SearchEngineIndexation::isColorAttributesGroup($params['obj']->id_criterion_group_linked),
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
            $objCrit = new AdvancedSearchCriterionGroupClass(Tools::getValue('id_criterion_group'), Tools::getValue('id_search'));
            if (Tools::getValue('sort_way')) {
                $objCrit->sort_by = Tools::getValue('sort_by');
                $objCrit->sort_way = Tools::getValue('sort_way');
                $objCrit->save();
                if ($objCrit->sort_by == 'o_position') {
                    As4SearchEngineIndexation::indexCriterionsGroup($objCrit->id_search, $objCrit->criterion_group_type, $objCrit->id_criterion_group_linked, $objCrit->id, $objCrit->visible, false, true);
                }
                $msgConfirm = $this->l('Specific sort apply');
                if ($objCrit->sort_by == 'position') {
                    $msgConfirm .= '<br />'.$this->l('Now, you can sort criteria by drag n drop');
                } elseif ($objCrit->sort_by == 'o_position') {
                    $msgConfirm .= '<br />'.$this->l('Criterion will automaticaly inherit position');
                }
                $this->_html .= '<script type="text/javascript">show_info("'.addcslashes($msgConfirm, '"').'");</script>';
            }
        }
        $criterions = As4SearchEngine::getCriterionsFromCriterionGroup($objCrit->id, $objCrit->id_search, $objCrit->sort_by, $objCrit->sort_way, (int)$this->context->language->id);
        $hasCustomCriterions = false;
        foreach ($criterions as &$row) {
            $objCritClass = new AdvancedSearchCriterionClass($row['id_criterion'], $objCrit->id_search);
            $row['obj'] = $objCritClass;
            if ($objCrit->criterion_group_type == 'category') {
                $row['parent_name'] = As4SearchEngine::getCategoryName((int)$row['id_parent'], (int)$this->context->language->id);
            }
            if (!empty($row['is_custom'])) {
                $hasCustomCriterions = true;
            }
            if ((!isset($row['is_custom']) || isset($row['is_custom']) && !$row['is_custom'])) {
                $customCriterionsList = AdvancedSearchCriterionClass::getCustomCriterions($objCrit->id_search, $objCrit->id, (int)$this->context->language->id);
                if (is_array($customCriterionsList) && sizeof($customCriterionsList)) {
                    $customCriterionsList = array(0 => $this->l('None')) + $customCriterionsList;
                }
                $row['custom_criterions_list'] = $customCriterionsList;
                $row['custom_criterions_obj'] = (object)array('custom_group_link_id_'.(int)$row['id_criterion'] => AdvancedSearchCriterionClass::getCustomMasterIdCriterion((int)$objCrit->id_search, $row['id_criterion']));
            }
        }
        $config = $this->_getModuleConfiguration();
        $autoSyncActiveStatus = (!empty($config['autoSyncActiveStatus']) && in_array($objCrit->criterion_group_type, array('category', 'manufacturer', 'supplier')));
        $vars = array(
            'auto_sync_active_status' => $autoSyncActiveStatus,
            'criterion_group' => $objCrit,
            'criterions' => $criterions,
            'is_color_group' => As4SearchEngineIndexation::isColorAttributesGroup($objCrit->id_criterion_group_linked),
            'pm_load_function' => Tools::getValue('pm_load_function'),
            'has_custom_criterions' => $hasCustomCriterions,
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
            'manufacturer' => (version_compare(_PS_VERSION_, '1.7.0.0', '>=') ? 'Brand' : 'Manufacturer'),
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
            'new_products' => 'New products',
            'prices_drop' => 'Prices drop',
            'duplicated_from' => 'Duplicated from %s',
        );
        foreach ($this->_languages as $language) {
            $langObj = new Language((int)$language['id_lang']);
            $return[$language['id_lang']] = self::getCustomModuleTranslation($this->name, $toTranslate[$type], $langObj);
            if ($category_level_depth) {
                $return[$language['id_lang']] .= ' ' . $category_level_depth;
            }
        }
        return $return;
    }
    public function _assignProductSort($search)
    {
        if ($search instanceof AdvancedSearchClass) {
            $orderByDefault = $search->products_order_by;
            $orderWayDefault = $search->products_order_way;
        } else {
            $orderByDefault = $search['products_order_by'];
            $orderWayDefault = $search['products_order_way'];
        }
        $stock_management = (int)(Configuration::get('PS_STOCK_MANAGEMENT')) ? true : false;
        $orderBy = As4SearchEngine::getOrderByValue($search);
        $orderWay = As4SearchEngine::getOrderWayValue($search);
        $this->context->smarty->assign(array(
            'orderby' => $orderBy,
            'orderway' => $orderWay,
            'orderbydefault' => As4SearchEngine::$orderByValues[(int)($orderByDefault)],
            'orderwayposition' => As4SearchEngine::$orderWayValues[(int)($orderWayDefault)],
            'orderwaydefault' => As4SearchEngine::$orderWayValues[(int)($orderWayDefault)],
            'stock_management' => (int)$stock_management
        ));
    }
    public function _assignPagination($products_per_page, $nbProducts = 10)
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
            $textualRange .= Tools::displayPrice($rangeStart, ($rangeEnd ? $fakeCurrency : $currency));
        } else {
            $textualRange .= $rangeStart . ($rangeEnd ? '' : ' ' . $rangeSign);
        }
        if ($rangeEnd) {
            $textualRange .= ' ' . $this->l('to') . ' ';
            if ($isPriceGroup) {
                $textualRange .= Tools::displayPrice($rangeEnd, $currency);
            } else {
                $textualRange .= $rangeEnd . ' ' . $rangeSign;
            }
        }
        return $textualRange;
    }
    public function getCriterionsGroupsAndCriterionsForSearch($result, $id_lang, $selected_criterion = array(), $with_products = false, $id_criterion_group = 0)
    {
        static $return = array();
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset($return[$cacheKey])) {
            return $return[$cacheKey];
        }
        $currency = $this->context->currency;
        $hidden_criteria_state = (isset($this->context->cookie->hidden_criteria_state) ? @unserialize($this->context->cookie->hidden_criteria_state):array());
        $selected_criteria_groups_type = array();
        if (!$selected_criterion || (is_array($selected_criterion) && ! sizeof($selected_criterion))) {
            $reinit_selected_criterion = true;
        }
        if (AdvancedSearchCoreClass::_isFilledArray($result)) {
            foreach ($result as $key => $row) {
                if ($row['filter_by_emplacement'] && Tools::getValue('id_seo') !== false && is_numeric(Tools::getValue('id_seo')) && Tools::getValue('id_seo') > 0) {
                    if (!isset($result[$key]['seo_criterion_groups'])) {
                        $result[$key]['seo_criterion_groups'] = array();
                    }
                    if ($row['filter_by_emplacement'] && Tools::getValue('id_seo') !== false && is_numeric(Tools::getValue('id_seo')) && Tools::getValue('id_seo') > 0) {
                        $seo_search = new AdvancedSearchSeoClass((int)Tools::getValue('id_seo'));
                        if (Validate::isLoadedObject($seo_search) && isset($seo_search->criteria) && !empty($seo_search->criteria)) {
                            $result[$key]['id_seo'] = (int)$seo_search->id;
                            $criteres_seo = @unserialize($seo_search->criteria);
                            if (AdvancedSearchCoreClass::_isFilledArray($criteres_seo)) {
                                foreach ($criteres_seo as $critere_seo) {
                                    $critere_seo = explode('_', $critere_seo);
                                    $id_criterion_group_seo = (int)$critere_seo[0];
                                    $result[$key]['seo_criterion_groups'][] = $id_criterion_group_seo;
                                }
                            }
                        }
                    }
                    $result[$key]['seo_criterion_groups'] = array_unique($result[$key]['seo_criterion_groups']);
                } else {
                    $result[$key]['seo_criterion_groups'] = array();
                }
                if (isset($reinit_selected_criterion)) {
                    $selected_criterion = array();
                }
                if ($this->context->controller instanceof pm_advancedsearch4seoModuleFrontController && $this->context->controller->getIdSeo()) {
                    $selected_criterion = $this->context->controller->getSelectedCriterions();
                }
                if ($this->context->controller instanceof pm_advancedsearch4searchresultsModuleFrontController && $this->context->controller->getSearchEngine()->id != $row['id_search']) {
                    $selected_criterion = array();
                }
                if (Tools::getValue('seo_url')) {
                    $result[$key]['keep_category_information'] = 0;
                    $row['keep_category_information'] = 0;
                }
                $result[$key]['selected_criterion_from_emplacement'] = array();
                if ($row['filter_by_emplacement']) {
                    $result[$key]['selected_criterion_from_emplacement'] = As4SearchEngine::getCriteriaFromEmplacement($row['id_search'], $row['id_category_root']);
                }
                if ($row['filter_by_emplacement'] && (!$selected_criterion || (is_array($selected_criterion) && ! sizeof($selected_criterion)))) {
                    $selected_criterion = $result[$key]['selected_criterion_from_emplacement'];
                }
                if ($row['filter_by_emplacement'] && Tools::getValue('id_seo') !== false && is_numeric(Tools::getValue('id_seo')) && Tools::getValue('id_seo') > 0) {
                    As4SearchEngineLogger::log('SEO page, looking for pre-selected criterions');
                    $seo_search = new AdvancedSearchSeoClass((int)Tools::getValue('id_seo'));
                    if (Validate::isLoadedObject($seo_search) && isset($seo_search->criteria) && !empty($seo_search->criteria)) {
                        $result[$key]['id_seo'] = (int)$seo_search->id;
                        $criteres_seo = @unserialize($seo_search->criteria);
                        if (self::_isFilledArray($criteres_seo)) {
                            foreach ($criteres_seo as $critere_seo) {
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
                        $row['id_seo'] = (int)Tools::getValue('id_seo');
                        $row['seo_url'] = (string)trim(Tools::getValue('seo_url'));
                    }
                }
                if (is_array($selected_criterion) && sizeof($selected_criterion)) {
                    $selected_criterion = As4SearchEngine::cleanArrayCriterion($selected_criterion);
                    $selected_criteria_groups_type = As4SearchEngine::getCriterionGroupsTypeAndDisplay($row['id_search'], array_keys($selected_criterion));
                }
                if ($row['step_search'] && is_array($selected_criterion) && sizeof($selected_criterion)) {
                    $selected_criterion_groups = array_keys($selected_criterion);
                }
                $current_selected_criterion = $selected_criterion;
                if (!$id_criterion_group) {
                    $result[$key]['criterions_groups'] = AdvancedSearchCriterionGroupClass::getCriterionsGroupsFromIdSearch($row['id_search'], $id_lang, false);
                } else {
                    $result[$key]['criterions_groups'] = AdvancedSearchCriterionGroupClass::getCriterionsGroup($row['id_search'], $id_criterion_group, $id_lang);
                }
                $selectedCriterionsForSeo = array();
                if (self::_isFilledArray($current_selected_criterion)) {
                    $currentSelectedCriterionTmp = $current_selected_criterion;
                    if (self::_isFilledArray($result[$key]['selected_criterion_from_emplacement'])) {
                        foreach ($result[$key]['selected_criterion_from_emplacement'] as $idCriterionGroupTmp => $selectedCriterionsTmp) {
                            foreach ($result[$key]['criterions_groups'] as $criterionGroupTmp) {
                                if ($idCriterionGroupTmp == (int)$criterionGroupTmp['id_criterion_group'] && !empty($criterionGroupTmp['visible'])) {
                                    if (isset($currentSelectedCriterionTmp[$idCriterionGroupTmp])) {
                                        foreach ($selectedCriterionsTmp as $idCriterionTmp) {
                                            $criterionIndex = array_search($idCriterionTmp, $currentSelectedCriterionTmp[$idCriterionGroupTmp]);
                                            if ($criterionIndex !== false) {
                                                unset($currentSelectedCriterionTmp[$idCriterionGroupTmp][$criterionIndex]);
                                                if (!sizeof($currentSelectedCriterionTmp[$idCriterionGroupTmp])) {
                                                    unset($currentSelectedCriterionTmp[$idCriterionGroupTmp]);
                                                }
                                            }
                                        }
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
                            $tmpCriterionGroupRow['is_color_attribute'] = As4SearchEngineIndexation::isColorAttributesGroup((int)$tmpCriterionGroupRow['id_criterion_group_linked']);
                        }
                    } else {
                        $tmpCriterionGroupRow['is_color_attribute'] = false;
                    }
                }
                if (is_array($current_selected_criterion) && sizeof($current_selected_criterion)) {
                    $result[$key]['criterions_groups_selected'] = AdvancedSearchCriterionGroupClass::getCriterionsGroup($row['id_search'], array_keys($current_selected_criterion), $id_lang);
                    foreach ($current_selected_criterion as $id_criterion_group_selected => $selected_criteria) {
                        foreach ($selected_criteria as $criterion_value) {
                            if (!isset($result[$key]['criterions_selected'][$id_criterion_group_selected])) {
                                $result[$key]['criterions_selected'][$id_criterion_group_selected] = array();
                            }
                            if (preg_match('#~#', $criterion_value)) {
                                $range = explode('~', $criterion_value);
                                $rangeUp = (isset($range[1]) ? $range[1] : '');
                                $groupInfo = AdvancedSearchCriterionGroupClass::getCriterionGroupTypeAndRangeSign($row['id_search'], $id_criterion_group_selected, $id_lang);
                                $result[$key]['criterions_selected'][$id_criterion_group_selected][] = array(
                                    'value' => $this->getTextualRangeValue($range[0], $rangeUp, $groupInfo, $currency),
                                    'id_criterion' => $criterion_value,
                                    'visible' => 1,
                                );
                            } else {
                                $result[$key]['criterions_selected'][$id_criterion_group_selected][] = AdvancedSearchCriterionClass::getCriterionValueById($row['id_search'], $id_lang, $criterion_value);
                            }
                        }
                    }
                }
                if (!Tools::getValue('only_products')) {
                    if (isset($hidden_criteria_state[$row['id_search']])) {
                        $result[$key]['advanced_search_open'] = $hidden_criteria_state[$row['id_search']];
                    } else {
                        $result[$key]['advanced_search_open'] = 0;
                    }
                    As4SearchEngineLogger::log("Retrieve criterions groups");
                    if (sizeof($result[$key]['criterions_groups'])) {
                        $prev_id_criterion_group = false;
                        foreach ($result[$key]['criterions_groups'] as $key2 => $row2) {
                            if ($row2['visible'] == 0) {
                                continue;
                            }
                            if ($row2['criterion_group_type'] == 'subcategory') {
                                if (!($this->context->controller instanceof pm_advancedsearch4advancedsearch4ModuleFrontController) && !($this->context->controller instanceof pm_advancedsearch4searchresultsModuleFrontController) && $this->context->controller->php_self != 'index' && $this->context->controller->php_self != 'category' && $this->context->controller->php_self != 'product') {
                                    continue;
                                }
                            }
                            if (!$row2['visible'] && ! isset($selected_criterion[$row2['id_criterion_group']]) && (($row2['criterion_group_type'] == 'manufacturer' && ! Tools::getValue('id_manufacturer')) || ($row2['criterion_group_type'] == 'supplier' && ! Tools::getValue('id_supplier')) || ($row2['criterion_group_type'] == 'category' && ! Tools::getValue('id_category')))) {
                                continue;
                            }
                            if (!$row['step_search'] || ($row['step_search'] && $row['step_search_next_in_disabled']) || (($row['step_search'] && ($key2 == 0 || (isset($selected_criterion_groups) && (in_array($row2['id_criterion_group'], $selected_criterion_groups) || ($prev_id_criterion_group && in_array($prev_id_criterion_group, $selected_criterion_groups)) || ! sizeof($result[$key]['criterions'][$prev_id_criterion_group]))))))) {
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
                                        $selected_criteria_groups_type2 = As4SearchEngine::getCriterionGroupsTypeAndDisplay($row['id_search'], array_keys($citeria4count));
                                        $nb_products = As4SearchEngine::getProductsSearched((int)$row['id_search'], As4SearchEngine::cleanArrayCriterion($citeria4count), $selected_criteria_groups_type2, null, null, true);
                                        if (!$row['display_empty_criteria'] && !$nb_products) {
                                            continue;
                                        }
                                        $criteria_formated[$range1] = array('id_criterion' => $range1, 'value' => $range2, 'nb_product'=> $nb_products);
                                    }
                                    $result[$key]['criterions'][$row2['id_criterion_group']] = $criteria_formated;
                                } elseif ($row2['criterion_group_type'] == 'price') {
                                    $range_selected_criterion = As4SearchEngine::cleanArrayCriterion($selected_criterion);
                                    unset($range_selected_criterion[$row2['id_criterion_group']]);
                                    $result[$key]['criterions'][$row2['id_criterion_group']] = As4SearchEngine::getPriceRangeForSearchBloc($row, (int)$row2['id_criterion_group'], (int)$this->context->currency->id, (int)$this->context->country->id, (int)$this->getCurrentCustomerGroupId(), ($row['step_search'] && !$id_criterion_group && $key2 == 0 ? array() : $range_selected_criterion), ($row['step_search'] && !$id_criterion_group && $key2 == 0 ? array() : $selected_criteria_groups_type));
                                    As4SearchEngineLogger::log("Retrieve price range group " . $row2['id_criterion_group']);
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
                                    if ($row['step_search'] && $result[$key]['criterions'][$row2['id_criterion_group']][0]['min'] == 0 && $result[$key]['criterions'][$row2['id_criterion_group']][0]['max'] == 0) {
                                        unset($result[$key]['criterions_groups'][$key2]);
                                        continue;
                                    }
                                    if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                                        if (version_compare(_PS_VERSION_, '1.7.6.0', '>=')) {
                                            $locale = $this->context->getCurrentLocale();
                                            if (is_int($currency)) {
                                                $currency = Currency::getCurrencyInstance($this->context->currency);
                                            }
                                            $isoCode = is_array($currency) ? $currency['iso_code'] : $currency->iso_code;
                                            $priceSpecification = $locale->getPriceSpecification($isoCode);
                                            $currencySymbol = $priceSpecification->getCurrencySymbol();
                                            $currencyFormat = $priceSpecification->getPositivePattern();
                                        } else {
                                            $cldr = Tools::getCldr($this->context);
                                            $cldrCurrency = new \ICanBoogie\CLDR\Currency($cldr->getRepository(), $this->context->currency->iso_code);
                                            $localizedCurrency = $cldrCurrency->localize($cldr->getCulture());
                                            $currencySymbol = $localizedCurrency->locale['currencies'][$localizedCurrency->target->code]['symbol'];
                                            $currencyFormat = $currency->format;
                                        }
                                        $result[$key]['criterions_groups'][$key2]['currency_format'] = str_replace('¤', $currencySymbol, $currencyFormat);
                                        $result[$key]['criterions_groups'][$key2]['currency_symbol'] = $currencySymbol;
                                    } else {
                                        $result[$key]['criterions_groups'][$key2]['left_range_sign'] = $currency->getSign('left');
                                        $result[$key]['criterions_groups'][$key2]['right_range_sign'] = $currency->getSign('right');
                                    }
                                    $result[$key]['criterions'][$row2['id_criterion_group']][0]['step'] = ((float)$row2['range_nb'] <= 0 ? 1 : $row2['range_nb']);
                                    As4SearchEngine::setupMinMaxUsingStep($result[$key]['criterions'][$row2['id_criterion_group']][0]['step'], $result[$key]['criterions'][$row2['id_criterion_group']][0]['min'], $result[$key]['criterions'][$row2['id_criterion_group']][0]['max']);
                                    if (AdvancedSearchCoreClass::_isFilledArray($selected_criterion) && isset($selected_criterion[$row2['id_criterion_group']]) && sizeof($selected_criterion[$row2['id_criterion_group']])) {
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
                                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_min_currency_formated']= Tools::displayPrice($result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_min']);
                                    }
                                    if (isset($result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max'])) {
                                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max_currency_formated']= Tools::displayPrice($result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max']);
                                    }
                                    if (isset($result[$key]['criterions'][$row2['id_criterion_group']][0]['min'])) {
                                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['min_currency_formated']= Tools::displayPrice($result[$key]['criterions'][$row2['id_criterion_group']][0]['min']);
                                    }
                                    if (isset($result[$key]['criterions'][$row2['id_criterion_group']][0]['max'])) {
                                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['max_currency_formated']= Tools::displayPrice($result[$key]['criterions'][$row2['id_criterion_group']][0]['max']);
                                    }
                                } elseif ($row2['criterion_group_type'] == 'subcategory' || $row2['display_type'] == 9) {
                                    $idCategoryStart = false;
                                    $currentIdCategory = As4SearchEngine::getCurrentCategory();
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
                                    $criteriaList = As4SearchEngine::getCriterionsForSearchBloc($row, $row2['id_criterion_group'], As4SearchEngine::cleanArrayCriterion($selected_criterion), $selected_criteria_groups_type, true, $row2, $result[$key]['criterions_groups']);
                                    if (empty($idCategoryStart) && isset($selected_criterion[$row2['id_criterion_group']]) && self::_isFilledArray($selected_criterion[$row2['id_criterion_group']]) && sizeof($selected_criterion[$row2['id_criterion_group']]) == 1) {
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
                                    $range_selected_criterion = As4SearchEngine::cleanArrayCriterion($selected_criterion);
                                    unset($range_selected_criterion[$row2['id_criterion_group']]);
                                    $result[$key]['criterions'][$row2['id_criterion_group']] = As4SearchEngine::getCriterionsRange($row, (int)$row2['id_criterion_group'], (int)$id_lang, $range_selected_criterion, $selected_criteria_groups_type, (int)$this->context->cookie->id_currency, (int)$this->getCurrentCustomerGroupId(), $row2);
                                    if ($row['step_search'] && $result[$key]['criterions'][$row2['id_criterion_group']][0]['min'] == 0 && $result[$key]['criterions'][$row2['id_criterion_group']][0]['max'] == 0) {
                                        unset($result[$key]['criterions_groups'][$key2]);
                                        continue;
                                    }
                                    $result[$key]['criterions'][$row2['id_criterion_group']][0]['step'] = ((float)$row2['range_nb'] <= 0 ? 1 : $row2['range_nb']);
                                    $result[$key]['criterions_groups'][$key2]['left_range_sign'] = '';
                                    $result[$key]['criterions_groups'][$key2]['right_range_sign'] = (isset($row2['range_sign']) && Tools::strlen($row2['range_sign']) > 0 ? ' '.$row2['range_sign'] : '');
                                    As4SearchEngine::setupMinMaxUsingStep($result[$key]['criterions'][$row2['id_criterion_group']][0]['step'], $result[$key]['criterions'][$row2['id_criterion_group']][0]['min'], $result[$key]['criterions'][$row2['id_criterion_group']][0]['max']);
                                    if (AdvancedSearchCoreClass::_isFilledArray($selected_criterion) && isset($selected_criterion[$row2['id_criterion_group']]) && sizeof($selected_criterion[$row2['id_criterion_group']])) {
                                        $range = explode('~', $selected_criterion[$row2['id_criterion_group']][0]);
                                        $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_min'] = $range[0];
                                        if (isset($range[1])) {
                                            $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max'] = $range[1];
                                        } else {
                                            $result[$key]['criterions'][$row2['id_criterion_group']][0]['cur_max'] = $result[$key]['criterions'][$row2['id_criterion_group']][0]['max'];
                                        }
                                    }
                                } else {
                                    $criteria = As4SearchEngine::getCriterionsForSearchBloc($row, $row2['id_criterion_group'], As4SearchEngine::cleanArrayCriterion($selected_criterion), $selected_criteria_groups_type, true, $row2, $result[$key]['criterions_groups']);
                                    if ($row2['display_type'] == 3 || $row2['display_type'] == 4 || $row2['display_type'] == 7) {
                                        As4SearchEngineSeo::addSeoPageUrlToCriterions($row['id_search'], $criteria, $selectedCriterionsForSeo);
                                    }
                                    $criteria_formated = array();
                                    if ($criteria && sizeof($criteria)) {
                                        $criteria_formated = array();
                                        foreach ($criteria as $rowCriteria) {
                                            $criteria_formated[$rowCriteria['id_criterion']] = $rowCriteria;
                                        }
                                    }
                                    $result[$key]['criterions'][$row2['id_criterion_group']] = $criteria_formated;
                                    if ($row['filter_by_emplacement'] && Tools::getValue('id_seo') == false && (As4SearchEngine::getCurrentManufacturer() || As4SearchEngine::getCurrentSupplier())) {
                                        $preSelectedCriterionEmplacement = As4SearchEngine::getCriteriaFromEmplacement($row['id_search']);
                                        if (is_array($preSelectedCriterionEmplacement) && isset($preSelectedCriterionEmplacement[$row2['id_criterion_group']]) && AdvancedSearchCoreClass::_isFilledArray($preSelectedCriterionEmplacement[$row2['id_criterion_group']])) {
                                            $result[$key]['criterions_groups'][$key2]['is_preselected_by_emplacement'] = true;
                                        }
                                    }
                                    As4SearchEngineLogger::log("Retrieve criterions for group " . $row2['id_criterion_group']);
                                }
                                if (!$row['step_search'] || $key2 == 0 || (isset($selected_criterion_groups) && (in_array($row2['id_criterion_group'], $selected_criterion_groups) || ($prev_id_criterion_group && in_array($prev_id_criterion_group, $selected_criterion_groups)) || ! sizeof($result[$key]['criterions'][$prev_id_criterion_group])))) {
                                    $result[$key]['selected_criterions'][$row2['id_criterion_group']]['is_selected'] = true;
                                } else {
                                    $result[$key]['selected_criterions'][$row2['id_criterion_group']]['is_selected'] = false;
                                }
                                $prev_id_criterion_group = $row2['id_criterion_group'];
                            }
                        }
                    }
                }
                if ($with_products) {
                    $result[$key]['products'] = As4SearchEngine::getProductsSearched((int)$row['id_search'], As4SearchEngine::cleanArrayCriterion($selected_criterion), $selected_criteria_groups_type, (int)Tools::getValue('p', 1), (int)Tools::getValue('n', $row['products_per_page']), false);
                    As4SearchEngineLogger::log("Retrieve results");
                }
                if ($with_products || (isset($row['display_nb_result_on_blc']) && $row['display_nb_result_on_blc']) || (isset($row['hide_criterions_group_with_no_effect']) && $row['hide_criterions_group_with_no_effect'])) {
                    $result[$key]['total_products'] = As4SearchEngine::getProductsSearched((int)$row['id_search'], As4SearchEngine::cleanArrayCriterion($selected_criterion), $selected_criteria_groups_type, null, null, true);
                    As4SearchEngineLogger::log("Retrieve results count");
                }
                $result[$key]['selected_criterion'] = $selected_criterion;
            }
            if (isset($row['hide_criterions_group_with_no_effect']) && $row['hide_criterions_group_with_no_effect']) {
                foreach ($result[$key]['criterions_groups'] as $criterions_group_key => $criterions_group) {
                    if ($criterions_group['criterion_group_type'] != 'attribute') {
                        if ($criterions_group['criterion_group_type'] == 'category' && $criterions_group['display_type'] == 9) {
                            continue;
                        }
                        if (isset($result[$key]['selected_criterion'][$criterions_group['id_criterion_group']]) && self::_isFilledArray($result[$key]['selected_criterion'][$criterions_group['id_criterion_group']])) {
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
                                        $nb_product = As4SearchEngine::getProductsSearched((int)$row['id_search'], As4SearchEngine::cleanArrayCriterion($selected_criterion), $selected_criteria_groups_type, null, null, true);
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
            if (empty($result[$key]['id_seo']) && !empty($result[$key]['hide_empty_crit_group']) && empty($result[$key]['display_empty_criteria']) && (isset($result[$key]['total_products']) && $result[$key]['total_products'] == 0) && (($result[$key]['filter_by_emplacement'] && $result[$key]['selected_criterion'] == $result[$key]['selected_criterion_from_emplacement']) || (!$result[$key]['filter_by_emplacement'] && !self::_isFilledArray($result[$key]['selected_criterion'])))) {
                unset($result[$key]);
            }
        }
        $return[$cacheKey] = $result;
        return $result;
    }
    private function recursiveGetParents(&$selected_criterions = array(), $linkedList = array(), $id_parent = 0, $current = array())
    {
        if (isset($linkedList[(int)$current]) && (int)$linkedList[(int)$current][0] != (int)$id_parent) {
            $selected_criterions[] = (int)$linkedList[(int)$current][0];
            $this->recursiveGetParents($selected_criterions, $linkedList, (int)$id_parent, (int)$linkedList[(int)$current][0]);
        }
    }
    private function _assignForProductsResults()
    {
        $this->context->smarty->assign(array('comparator_max_item' => (int)(Configuration::get('PS_COMPARATOR_MAX_ITEM'))));
        if (Tools::getIsset('id_seo') && (int)Tools::getValue('id_seo') > 0) {
            $resultSeoUrl = AdvancedSearchSeoClass::getSeoSearchByIdSeo((int)Tools::getValue('id_seo'), (int)$this->context->language->id);
            if (self::_isFilledArray($resultSeoUrl)) {
                $this->context->smarty->assign(array(
                    'as_seo_title'       => $resultSeoUrl[0]['title'],
                    'as_seo_description' => $resultSeoUrl[0]['description'],
                ));
            }
        }
        $this->context->smarty->assign(array('categorySize' => $this->_getImageSize('category'), 'mediumSize' => $this->_getImageSize('medium'), 'thumbSceneSize' => $this->_getImageSize('thumb_scene'), 'homeSize' => $this->_getImageSize('home'), 'static_token' => Tools::getToken(false) ));
    }
    private function _getImageSize($imageType)
    {
        $img_size = Image::getSize($imageType);
        if (!self::_isFilledArray($img_size)) {
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                $img_size = Image::getSize(ImageType::getFormattedName($imageType));
            } else {
                $img_size = Image::getSize(ImageType::getFormatedName($imageType));
            }
        } else {
            return $img_size;
        }
        if (!self::_isFilledArray($img_size)) {
            $img_size = Image::getSize($imageType.'_default');
        }
        return $img_size;
    }
    private function getLocationName($id_lang)
    {
        $location_name = false;
        $idCategory = As4SearchEngine::getCurrentCategory();
        if ($idCategory) {
            $location_name = As4SearchEngine::getCategoryName($idCategory, $id_lang);
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
    private function _fixPaginationLinks($pageContent)
    {
        $pageContent = str_replace(array('?controller=advancedsearch4?fc=module', '?controller=advancedsearch4?'), array('?controller=advancedsearch4&fc=module', '?controller=advancedsearch4&'), $pageContent);
        return $pageContent;
    }
    protected function getCacheId($name = null)
    {
        $cacheId = parent::getCacheId($name);
        if (As4SearchEngine::$productFilterListQuery) {
            $cacheId .= '|' . sha1(As4SearchEngine::$productFilterListQuery);
        }
        return $cacheId;
    }
    public function putInSmartyCache($cacheId, $data)
    {
        if (!is_object($this->context->smarty)) {
            return;
        }
        $config = $this->_getModuleConfiguration();
        if (!empty($config['moduleCache'])) {
            $cacheId = $this->getCacheId($cacheId);
            $this->context->smarty->assign('serialized_data', self::getDataSerialized(serialize($data)));
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                $templatePath = 'module:pm_advancedsearch4/views/templates/hook/'.Tools::substr(_PS_VERSION_, 0, 3).'/cache.tpl';
            } else {
                $templatePath = $this->templatePrefix . 'cache.tpl';
            }
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                $this->fetch($templatePath, $cacheId);
            } else {
                $this->display(__FILE__, $templatePath, $cacheId);
            }
        }
    }
    public function getFromSmartyCache($cacheId)
    {
        $config = $this->_getModuleConfiguration();
        if (!empty($config['moduleCache'])) {
            $cacheId = $this->getCacheId($cacheId);
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                $templatePath = 'module:pm_advancedsearch4/views/templates/hook/'.Tools::substr(_PS_VERSION_, 0, 3).'/cache.tpl';
            } else {
                $templatePath = $this->templatePrefix . 'cache.tpl';
            }
            if ($this->isCached($templatePath, $cacheId)) {
                if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                    $cacheData = unserialize(self::getDataUnserialized(trim(strip_tags($this->fetch($templatePath, $cacheId)))));
                } else {
                    $cacheData = unserialize(self::getDataUnserialized($this->display(__FILE__, $templatePath, $cacheId)));
                }
                if ($cacheData !== false) {
                    return $cacheData;
                }
            }
        }
        return null;
    }
    public function setSmartyVarsForTpl(AdvancedSearchClass $searchEngine, $selectedCriterions = array())
    {
        $with_product = false;
        $searchs = As4SearchEngine::getSearch($searchEngine->id, (int)$this->context->language->id);
        $searchs = $this->getCriterionsGroupsAndCriterionsForSearch($searchs, (int)$this->context->language->id, $selectedCriterions, $with_product, false);
        $hookName = As4SearchEngine::getHookName($searchs[0]['id_hook']);
        if (preg_match('/displayHome/i', $hookName)) {
            $hookName = 'home';
        }
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
    public function displayNextStepSearch($id_search, $id_criterion_group, $with_product, $selected_criterion = array(), $selected_criterion_hidden = array())
    {
        $this->_cleanOutput();
        $ajaxMode = Tools::getValue('ajaxMode', false);
        $this->includeAssets();
        $searchs = As4SearchEngine::getSearch($id_search, (int)$this->context->language->id);
        $json_return = array();
        $hookName = As4SearchEngine::getHookName($searchs[0]['id_hook']);
        $json_return['next_id_criterion_group'] = $this->getNextIdCriterionGroup($id_search);
        As4SearchEngineLogger::log("Retrieve steps");
        $searchs = $this->getCriterionsGroupsAndCriterionsForSearch($searchs, (int)$this->context->language->id, $selected_criterion, $with_product, $id_criterion_group);
        $next_id_criterion_group = AdvancedSearchCriterionGroupClass::getNextIdCriterionGroup($id_search, $id_criterion_group);
        As4SearchEngineLogger::log("Retrieve criterons and results 1");
        $this->context->smarty->assign(array(
            'as_searchs' => $searchs,
            'as_search' => $searchs[0],
            'hookName' => $hookName,
            'criterions_group' => $searchs[0]['criterions_groups'][0],
            'as_selected_criterion' => $selected_criterion,
            'next_id_criterion_group' => $next_id_criterion_group,
        ));
        $json_return['html_criteria_block'] = $this->display(__FILE__, $this->templatePrefix . 'pm_advancedsearch_criterions.tpl');
        if ($searchs[0]['remind_selection'] == 3 || $searchs[0]['remind_selection'] == 2) {
            $json_return['html_selection_block'] = $this->display(__FILE__, $this->templatePrefix . 'pm_advancedsearch_selection_block.tpl');
        }
        if ($with_product) {
            $this->_assignForProductsResults();
            $this->_assignProductSort($searchs[0]);
            $this->_assignPagination($searchs[0]['products_per_page'], $searchs[0]['total_products']);
            $json_return['html_products'] = $this->display(__FILE__, $this->templatePrefix . 'pm_advancedsearch_results.tpl');
            $json_return['html_products'] = $this->_fixPaginationLinks($json_return['html_products']);
            $json_return['total_products'] = $searchs[0]['total_products'];
        }
        self::_cleanBuffer();
        if ($ajaxMode) {
            $return = Tools::jsonEncode($json_return);
            if (function_exists('json_last_error') && json_last_error() == 5 && function_exists('mb_convert_encoding')) {
                foreach (array_keys($json_return) as $k) {
                    $json_return[$k] = mb_convert_encoding($json_return[$k], 'UTF-8', 'UTF-8');
                }
                $return = Tools::jsonEncode($json_return);
            }
            $this->context->smarty->assign('return', $return);
            unset($return);
        } else {
            $return = '';
            foreach ($json_return as $value) {
                $return .= $value;
            }
            $this->context->smarty->assign('return', $return);
        }
        if ($ajaxMode) {
            header('Content-Type: application/json');
            echo $this->display(__FILE__, $this->templatePrefix . 'pm_advancedsearch-json.tpl');
        } else {
            return $this->display(__FILE__, $this->templatePrefix . 'pm_advancedsearch-json.tpl');
        }
    }
    public function displayAjaxSearchBlocks($id_search, $tplName, $with_product, $selected_criterion = array(), $selected_criterion_hidden = array(), $only_product = false)
    {
        $this->_cleanOutput();
        $this->includeAssets();
        $ajaxMode = Tools::getValue('ajaxMode', false);
        $idSeo = Tools::getValue('id_seo', false);
        $searchs = As4SearchEngine::getSearch($id_search, (int)$this->context->language->id);
        As4SearchEngineLogger::log("Retrieve searchs");
        if ($with_product) {
            if (empty($idSeo) && $ajaxMode && empty($searchs[0]['filter_by_emplacement']) && !self::_isFilledArray($selected_criterion)) {
                self::_cleanBuffer();
                die(Tools::jsonEncode(array(
                    'html_products' => '',
                    'html_blocks' => '',
                    'redirect_to_url' => As4SearchEngine::generateURLFromCriterions((int)$searchs[0]['id_search'], $selected_criterion)
                )));
            }
        }
        $searchs = $this->getCriterionsGroupsAndCriterionsForSearch($searchs, (int)$this->context->language->id, $selected_criterion, $with_product, false);
        $json_return = array();
        $hookName = As4SearchEngine::getHookName($searchs[0]['id_hook']);
        As4SearchEngineLogger::log("Retrieve criterons and results 2");
        if ($ajaxMode) {
            $json_return['next_id_criterion_group'] = $this->getNextIdCriterionGroup($id_search);
        }
        if (preg_match('/displayHome/i', $hookName)) {
            $hookName = 'home';
        }
        if (Tools::getValue('id_seo')) {
            $needHiddenForm = false;
            if ($only_product && ((preg_match('/leftcolumn/i', $hookName) && !$this->context->controller->display_column_left)
            || (preg_match('/rightcolumn/i', $hookName) && !$this->context->controller->display_column_right))) {
                $needHiddenForm = true;
                $hookName = '';
            } elseif (isset($searchs[0]) && isset($searchs[0]['id_hook']) && $searchs[0]['id_hook'] == -1) {
                $needHiddenForm = false;
                $hookName = '';
            }
            if ((empty($hookName) && isset($searchs[0]) && isset($searchs[0]['id_hook']) && $searchs[0]['id_hook'] != -1) || $hookName == 'home') {
                $needHiddenForm = true;
            }
            $this->context->smarty->assign(array(
                'hideAS4Form' => $needHiddenForm,
                'ajaxMode' => $ajaxMode,
                'as_searchs' => $searchs,
                'hookName' => $hookName,
                'as_selected_criterion' => $selected_criterion,
            ));
            if (!$only_product || ($only_product && $needHiddenForm)) {
                $json_return['html_block'] = $this->display(__FILE__, $this->templatePrefix . $tplName);
            }
        } else {
            $this->context->smarty->assign(array(
                'hideAS4Form' => (((empty($hookName) && isset($searchs[0]) && isset($searchs[0]['id_hook']) && $searchs[0]['id_hook'] != -1) || $hookName == 'home') ? false : false),
                'ajaxMode' => $ajaxMode,
                'as_searchs' => $searchs,
                'hookName' => $hookName,
                'as_selected_criterion' => $selected_criterion,
            ));
            if (!$only_product || ($only_product && ((empty($hookName) && isset($searchs[0]) && isset($searchs[0]['id_hook']) && $searchs[0]['id_hook'] != -1) || $hookName == 'home'))) {
                $json_return['html_block'] = $this->display(__FILE__, $this->templatePrefix . $tplName);
            }
        }
        if ($with_product) {
            $this->_assignForProductsResults();
            $this->_assignProductSort($searchs[0]);
            $this->_assignPagination($searchs[0]['products_per_page'], $searchs[0]['total_products']);
            if (empty($idSeo) && $ajaxMode && isset($searchs[0]['redirect_one_product']) && $searchs[0]['redirect_one_product'] && isset($searchs[0]['search_method']) && $searchs[0]['search_method'] == 2 && self::_isFilledArray($searchs[0]['products']) && sizeof($searchs[0]['products']) == 1) {
                self::_cleanBuffer();
                echo Tools::jsonEncode(array('html_products' => '', 'html_blocks' => '', 'redirect_to_url' => $searchs[0]['products'][0]['link']));
                die;
            }
            $json_return['html_products'] = $this->display(__FILE__, $this->templatePrefix . 'pm_advancedsearch_results.tpl');
            $json_return['html_products'] = $this->_fixPaginationLinks($json_return['html_products']);
            $json_return['total_products'] = $searchs[0]['total_products'];
        }
        $json_return['url'] = As4SearchEngine::generateURLFromCriterions((int)$searchs[0]['id_search'], $selected_criterion);
        self::_cleanBuffer();
        if ($ajaxMode) {
            $return = Tools::jsonEncode($json_return);
            if (function_exists('json_last_error') && json_last_error() == 5 && function_exists('mb_convert_encoding')) {
                foreach (array_keys($json_return) as $k) {
                    $json_return[$k] = mb_convert_encoding($json_return[$k], 'UTF-8', 'UTF-8');
                }
                $return = Tools::jsonEncode($json_return);
            }
            $this->context->smarty->assign('return', $return);
            unset($return);
        } else {
            $return = '';
            foreach ($json_return as $value) {
                $return .= $value;
            }
            $this->context->smarty->assign('return', $return);
        }
        if ($ajaxMode) {
            header('Content-Type: application/json');
            echo $this->display(__FILE__, $this->templatePrefix . 'pm_advancedsearch-json.tpl');
        } else {
            return $this->display(__FILE__, $this->templatePrefix . 'pm_advancedsearch-json.tpl');
        }
    }
    public function displaySearchBlock($hookName, $tplName, $selected_criterion = array(), $specific_id_search = false, $fromWidget = false)
    {
        if ($this->context->controller instanceof pm_advancedsearch4searchresultsModuleFrontController) {
            $selected_criterion = $this->context->controller->getSelectedCriterions();
        }
        $inlineJsTplName = false;
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $inlineJsTplName = Tools::substr(_PS_VERSION_, 0, 3) . '/pm_advancedsearch_js.tpl';
        }
        $newHookName = Hook::getRetroHookName($hookName);
        if ($newHookName == false) {
            $newHookName = $hookName;
        }
        $searchs = As4SearchEngine::getSearchsFromHook($newHookName, (int)$this->context->language->id, $fromWidget);
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
        As4SearchEngineLogger::log("Retrieve searchs for hook ".$hookName);
        if (!empty($searchs)) {
            $this->includeAssets();
            $searchs = $this->getCriterionsGroupsAndCriterionsForSearch($searchs, (int)$this->context->language->id, $selected_criterion, false);
            $this->context->smarty->assign(array(
                'as_searchs' => $searchs,
                'hookName' => $hookName,
                'as_selected_criterion' => $selected_criterion,
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
        As4SearchEngineLogger::log("Retrieve id_search by smarty variable");
        $searchs = As4SearchEngine::getSearchsFromHook(-1, (int)$this->context->language->id);
        if (self::_isFilledArray($searchs)) {
            $this->includeAssets();
            foreach ($searchs as $search) {
                if ($this->context->controller instanceof pm_advancedsearch4searchresultsModuleFrontController && $this->context->controller->getSearchEngine()->id == (int)$search['id_search']) {
                    $selectedCriterions = $this->context->controller->getCriterionsList();
                } else {
                    $selectedCriterions = array();
                }
                $search['next_id_criterion_group'] = $this->getNextIdCriterionGroup((int)$search['id_search']);
                As4SearchEngineLogger::log("Retrieve criterions by smarty variable");
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
        if (isset($this->context->controller) && is_object($this->context->controller) && isset($this->context->controller->php_self)) {
            if (in_array($this->context->controller->php_self, As4SearchEngine::$validPageName)) {
                As4SearchEngine::$productFilterListSource = $this->context->controller->php_self;
            }
        }
        if (As4SearchEngine::$productFilterListSource == 'best-sales') {
            As4SearchEngine::getBestSellersProductsIds();
        } elseif (As4SearchEngine::$productFilterListSource == 'new-products') {
            As4SearchEngine::getNewProductsIds();
        } elseif (As4SearchEngine::$productFilterListSource == 'prices-drop') {
            As4SearchEngine::getPricesDropProductsIds();
        } elseif (As4SearchEngine::$productFilterListSource == 'search' || As4SearchEngine::$productFilterListSource == 'jolisearch' || As4SearchEngine::$productFilterListSource == 'module-ambjolisearch-jolisearch') {
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                if (empty(As4SearchEngine::$productFilterListData) && Tools::getIsset('s') && Tools::getValue('s')) {
                    As4SearchEngine::$productFilterListData = Tools::getValue('s');
                } elseif (empty(As4SearchEngine::$productFilterListData) && Tools::getIsset('search_query') && Tools::getValue('search_query')) {
                    As4SearchEngine::$productFilterListData = Tools::getValue('search_query');
                }
            } else {
                if (empty(As4SearchEngine::$productFilterListData) && Tools::getIsset('search_query') && Tools::getValue('search_query')) {
                    As4SearchEngine::$productFilterListData = Tools::getValue('search_query');
                }
            }
            if (As4SearchEngine::$productFilterListSource == 'search' && As4SearchEngine::$productFilterListData) {
                As4SearchEngine::getProductsByNativeSearch(As4SearchEngine::$productFilterListData);
            } elseif (As4SearchEngine::$productFilterListSource == 'jolisearch') {
                if (empty(As4SearchEngine::$productFilterListQuery)) {
                    $ambJoliSearch = Module::getInstanceByName('ambjolisearch');
                    if (Validate::isLoadedObject($ambJoliSearch) && $ambJoliSearch->active) {
                        if (!class_exists('AmbjolisearchjolisearchModuleFrontController')) {
                            require_once _PS_ROOT_DIR_ . '/modules/ambjolisearch/controllers/front/jolisearch.php';
                        }
                        As4SearchEngine::$productFilterListQuery = implode(',', AmbjolisearchjolisearchModuleFrontController::find(
                            $this->context->language->id,
                            As4SearchEngine::$productFilterListData,
                            1,
                            -1,
                            'position',
                            'desc',
                            false,
                            true,
                            null,
                            true
                        ));
                        if (empty(As4SearchEngine::$productFilterListQuery)) {
                            As4SearchEngine::$productFilterListQuery = '-1';
                        }
                    } else {
                        As4SearchEngine::$productFilterListSource = false;
                        As4SearchEngine::$productFilterListData = false;
                        As4SearchEngine::$productFilterListQuery = false;
                    }
                }
            } elseif (As4SearchEngine::$productFilterListSource == 'module-ambjolisearch-jolisearch') {
                if (empty(As4SearchEngine::$productFilterListQuery)) {
                    $ambJoliSearch = Module::getInstanceByName('ambjolisearch');
                    if (Validate::isLoadedObject($ambJoliSearch) && $ambJoliSearch->active) {
                        $ambJoliSearch->setAdvancedSearch4Results(As4SearchEngine::$productFilterListData);
                        if (empty(As4SearchEngine::$productFilterListQuery)) {
                            As4SearchEngine::$productFilterListQuery = '-1';
                        }
                        As4SearchEngine::$productFilterListSource = 'module-ambjolisearch-jolisearch';
                    } else {
                        As4SearchEngine::$productFilterListSource = false;
                        As4SearchEngine::$productFilterListData = false;
                        As4SearchEngine::$productFilterListQuery = false;
                    }
                }
            }
        }
    }
    public function hookHeader()
    {
        if ($this->_isInMaintenance()) {
            return;
        }
        if (!Tools::getValue('ajaxMode')) {
            $this->assignSearchVar();
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $this->includeAssets();
        }
    }
    public function includeAssets()
    {
        $this->registerFrontSmartyObjects();
        if (!Tools::getValue('ajaxMode')) {
            if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                Media::addJsDef(array(
                    'ASPath' => __PS_BASE_URI__ . 'modules/pm_advancedsearch4/',
                    'ASSearchUrl' => $this->context->link->getModuleLink('pm_advancedsearch4', 'advancedsearch4'),
                    'as4_orderBySalesAsc' => $this->l('Sales: Lower first'),
                    'as4_orderBySalesDesc' => $this->l('Sales: Highest first'),
                ));
                $this->setProductFilterContext();
                $this->context->controller->addCSS(_THEME_CSS_DIR_.'scenes.css', 'all', false);
                $this->context->controller->addCSS(_THEME_CSS_DIR_.'category.css', 'all', false);
                $this->context->controller->addCSS(_THEME_CSS_DIR_.'product_list.css', 'all', false);
                if (Configuration::get('PS_COMPARATOR_MAX_ITEM') > 0) {
                    $this->context->controller->addJS(_THEME_JS_DIR_ . 'products-comparison.js');
                }
                $this->context->controller->addJquery();
                $ui_slider_path = Media::getJqueryUIPath('ui.slider', 'base', true);
                $this->context->controller->addCSS($ui_slider_path['css'], 'all', false);
                $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/css/' . $this->name . '.css', 'all');
                $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/' . $this->name . '/' . self::DYN_CSS_FILE, 'all');
                $this->context->controller->addJqueryUI(array('ui.slider', 'ui.core'));
                $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/selectize/selectize.min.js');
                $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/css/selectize/selectize.css', 'all');
                $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/jquery.ui.touch-punch.min.js');
                $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/jquery.actual.min.js');
                $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/jquery.form.js');
                $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/as4_plugin.js');
                $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/pm_advancedsearch.js');
            } elseif (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                Media::addJsDef(array(
                    'ASPath' => __PS_BASE_URI__ . 'modules/pm_advancedsearch4/',
                    'ASSearchUrl' => $this->context->link->getModuleLink('pm_advancedsearch4', 'advancedsearch4'),
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
//                $this->context->controller->addJqueryUI(array('ui.slider', 'ui.core'));
                $this->context->controller->registerJavascript('selectize/selectize.min.js', 'modules/'.$this->name.'/views/js/selectize/selectize.min.js', array('position' => 'bottom', 'priority' => 150));
                $this->context->controller->registerStylesheet('selectize/selectize.css', 'modules/'.$this->name.'/views/css/selectize/selectize.css', array('media' => 'all', 'priority' => 900));
                $this->context->controller->registerJavascript('modules-'.$this->name.'-js-2', 'modules/'.$this->name.'/views/js/jquery.ui.touch-punch.min.js', array('position' => 'bottom', 'priority' => 150));
                $this->context->controller->registerJavascript('modules-'.$this->name.'-js-3', 'modules/'.$this->name.'/views/js/jquery.actual.min.js', array('position' => 'bottom', 'priority' => 150));
                $this->context->controller->registerJavascript('modules-'.$this->name.'-js-4', 'modules/'.$this->name.'/views/js/jquery.form.js', array('position' => 'bottom', 'priority' => 150));
                $this->context->controller->registerJavascript('modules-'.$this->name.'-js-5', 'modules/'.$this->name.'/views/js/as4_plugin-17.js', array('position' => 'bottom', 'priority' => 150));
                $this->context->controller->registerJavascript('modules-'.$this->name.'-js-6', 'modules/'.$this->name.'/views/js/pm_advancedsearch.js', array('position' => 'bottom', 'priority' => 150));
            }
        }
        $this->criteria_group_type_interal_name = array(1 => 'select', 2 => 'image', 3 => 'link', 4 => 'checkbox', 5 => 'slider', 7 => 'colorsquare', 8 => 'range', 9 => 'level_depth');
        $config = $this->_getModuleConfiguration();
        if (!empty($this->context->cookie->nb_item_per_page)) {
            $as4_localCacheKey = sha1(As4SearchEngine::getLocalStorageCacheKey() . $this->context->cookie->nb_item_per_page);
        } else {
            $as4_localCacheKey = As4SearchEngine::getLocalStorageCacheKey();
        }
        $this->context->smarty->assign(array(
            'ASSearchUrlForm' => $this->context->link->getModuleLink('pm_advancedsearch4', 'advancedsearch4'),
            'as4_productFilterListSource' => As4SearchEngine::$productFilterListSource,
            'as4_productFilterListData' => (isset(As4SearchEngine::$productFilterListSource) && (As4SearchEngine::$productFilterListSource == 'search' || As4SearchEngine::$productFilterListSource == 'jolisearch' || As4SearchEngine::$productFilterListSource == 'module-ambjolisearch-jolisearch') && !empty(As4SearchEngine::$productFilterListData) ? self::getDataSerialized(As4SearchEngine::$productFilterListData) : ''),
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
        if ($this->_isInMaintenance()) {
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
        if ($this->_isInMaintenance()) {
            return;
        }
        return $this->displaySearchBlock('home', 'pm_advancedsearch.tpl');
    }
    public function hookDisplayTop($params)
    {
        if ($this->_isInMaintenance()) {
            return;
        }
        return $this->displaySearchBlock('top', 'pm_advancedsearch.tpl');
    }
    public function hookDisplayNavFullWidth($params)
    {
        if ($this->_isInMaintenance()) {
            return;
        }
        return $this->displaySearchBlock('displayNavFullWidth', 'pm_advancedsearch.tpl');
    }
    public function hookDisplayLeftColumn($params)
    {
        if ($this->_isInMaintenance()) {
            return;
        }
        return $this->displaySearchBlock('leftcolumn', 'pm_advancedsearch.tpl');
    }
    public function hookDisplayRightColumn($params)
    {
        if ($this->_isInMaintenance()) {
            return;
        }
        return $this->displaySearchBlock('rightcolumn', 'pm_advancedsearch.tpl');
    }
    public function hookDisplayAdvancedSearch4($params)
    {
        if ($this->_isInMaintenance() || !isset($params['id_search_engine'])) {
            return;
        }
        return $this->displaySearchBlock('-1', 'pm_advancedsearch.tpl', array(), (int)$params['id_search_engine']);
    }
    public function hookActionObjectAddAfter($params)
    {
        $conf = $this->_getModuleConfiguration();
        if (!empty($conf['autoReindex']) && isset($params['object']) && Validate::isLoadedObject($params['object'])) {
            As4SearchEngineIndexation::$processingAutoReindex = true;
            As4SearchEngineIndexation::indexCriterionsGroupFromObject($params['object']);
            As4SearchEngineIndexation::$processingAutoReindex = false;
        }
    }
    public function hookActionObjectUpdateAfter($params)
    {
        $conf = $this->_getModuleConfiguration();
        if (isset($params['object']) && is_object($params['object']) && $params['object'] instanceof Shop) {
            if (isset($this->context->controller) && is_object($this->context->controller) && $this->context->controller instanceof AdminThemesController) {
                if (Tools::getValue('action') == 'resetToDefaults' || Tools::getValue('action') == 'enableTheme' || Tools::getValue('action') == 'ThemeInstall') {
                    Configuration::updateValue('PM_' . self::$_module_prefix . '_UPDATE_THEME', 1);
                }
            }
        }
        if (!empty($conf['autoReindex']) && isset($params['object']) && Validate::isLoadedObject($params['object'])) {
            As4SearchEngineIndexation::$processingAutoReindex = true;
            As4SearchEngineIndexation::indexCriterionsGroupFromObject($params['object']);
            As4SearchEngineIndexation::$processingAutoReindex = false;
        }
    }
    public function hookActionObjectDeleteAfter($params)
    {
        $conf = $this->_getModuleConfiguration();
        if ($params['object'] instanceof Language) {
            $res = Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_lang` WHERE `id_lang`='.(int)$params['object']->id);
            $res &= Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_seo_lang` WHERE `id_lang`='.(int)$params['object']->id);
            $advanced_searchs_id = As4SearchEngine::getSearchsId(false);
            foreach ($advanced_searchs_id as $idSearch) {
                $res &= Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_group_'. (int)$idSearch .'_lang` WHERE `id_lang`='.(int)$params['object']->id);
                $res &= Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_'. (int)$idSearch .'_lang` WHERE `id_lang`='.(int)$params['object']->id);
            }
            return;
        }
        if (!empty($conf['autoReindex']) && isset($params['object']) && Validate::isLoadedObject($params['object'])) {
            As4SearchEngineIndexation::$processingAutoReindex = true;
            if ($params['object'] instanceof SpecificPrice && !empty($params['object']->id_product)) {
                As4SearchEngineIndexation::indexCriterionsGroupFromObject($params['object']);
            } else {
                As4SearchEngineIndexation::indexCriterionsGroupFromObject($params['object'], true);
            }
            As4SearchEngineIndexation::$processingAutoReindex = false;
        }
    }
    public function hookActionObjectLanguageAddAfter($params)
    {
        $lang = $params['object'];
        if (Validate::isLoadedObject($lang)) {
            $advanced_searchs_id = As4SearchEngine::getSearchsId(false);
            $res = Db::getInstance()->Execute('
                INSERT IGNORE INTO `' . _DB_PREFIX_ . 'pm_advancedsearch_lang`
                (
                    SELECT `id_search`, "'. (int)$lang->id .'" AS `id_lang`, `title`, `description`
                    FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_lang`
                    WHERE `id_lang` = '. (int)$this->_default_language .'
                )
            ');
            $res &= Db::getInstance()->Execute('
                INSERT IGNORE INTO `' . _DB_PREFIX_ . 'pm_advancedsearch_seo_lang`
                (
                    SELECT `id_seo`, "'. (int)$lang->id .'" AS `id_lang`, `meta_title`, `meta_description`, `meta_keywords`, `title`, `description`, `seo_url`
                    FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_seo_lang`
                    WHERE `id_lang` = '. (int)$this->_default_language .'
                )
            ');
            foreach ($advanced_searchs_id as $idSearch) {
                $res &= Db::getInstance()->Execute('
                    INSERT IGNORE INTO `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_group_'. (int)$idSearch .'_lang`
                    (
                        SELECT `id_criterion_group`, "'. (int)$lang->id .'" AS `id_lang`, `name`, `url_identifier`, `url_identifier_original`, `icon`, `range_sign`, `range_interval`, `all_label`
                        FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_group_'. (int)$idSearch .'_lang`
                        WHERE `id_lang` = '. (int)$this->_default_language .'
                    )
                ');
                $res &= Db::getInstance()->Execute('
                    INSERT IGNORE INTO `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_'. (int)$idSearch .'_lang`
                    (
                        SELECT `id_criterion`, "'. (int)$lang->id .'" AS `id_lang`, `value`, `decimal_value`, `url_identifier`, `url_identifier_original`, `icon`
                        FROM `' . _DB_PREFIX_ . 'pm_advancedsearch_criterion_'. (int)$idSearch .'_lang`
                        WHERE `id_lang` = '. (int)$this->_default_language .'
                    )
                ');
            }
        }
    }
    public function hookUpdateProduct($params)
    {
        $conf = $this->_getModuleConfiguration();
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
        $conf = $this->_getModuleConfiguration();
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
        $conf = $this->_getModuleConfiguration();
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
        $conf = $this->_getModuleConfiguration();
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
    public function hookActionProductListOverride($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return;
        }
        if ($this->isFullTreeModeEnabled()) {
            $continue = true;
            if (As4SearchEngine::isSPAModuleActive()) {
                $pm_productsbyattributes = Module::getInstanceByName('pm_productsbyattributes');
                if (version_compare($pm_productsbyattributes->version, '1.0.4', '>=')) {
                    $continue = false;
                    $params['hookExecuted'] = true;
                    $params['nbProducts'] = $pm_productsbyattributes->getCategoryProducts((int)$this->context->controller->getCategory()->id_category, null, null, null, $this->context->controller->orderBy, $this->context->controller->orderWay, true, true);
                    $this->context->controller->pagination((int)$params['nbProducts']);
                    $params['catProducts'] = $pm_productsbyattributes->getCategoryProducts((int)$this->context->controller->getCategory()->id_category, (int)$this->context->language->id, (int)$this->context->controller->p, (int)$this->context->controller->n, $this->context->controller->orderBy, $this->context->controller->orderWay, false, true);
                    $pm_productsbyattributes->splitProductsList($params['catProducts']);
                }
            }
            if ($continue) {
                $params['hookExecuted'] = true;
                $params['nbProducts'] = $this->getCategoryProducts(null, null, null, $this->context->controller->orderBy, $this->context->controller->orderWay, true);
                $this->context->controller->pagination((int)$params['nbProducts']);
                $params['catProducts'] = $this->getCategoryProducts((int)$this->context->language->id, (int)$this->context->controller->p, (int)$this->context->controller->n, $this->context->controller->orderBy, $this->context->controller->orderWay);
            }
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
        if ($query->getIdCategory() && $this->isFullTreeModeEnabled() && !As4SearchEngine::isSPAModuleActive()) {
            $searchProvider = new As4FullTreeSearchProvider($this, $this->getTranslator());
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
                        $ObjAdvancedSearchClass = new AdvancedSearchClass((int)$search_engine['id_search']);
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
                $ObjAdvancedSearchClass = new AdvancedSearchClass((int)$search_engine['id_search']);
                $ObjAdvancedSearchClass->delete();
            }
        }
    }
    public function getCategoryProducts($id_lang, $p, $n, $order_by = null, $order_way = null, $get_total = false, $active = true, $random = false, $random_number_products = 1, $check_access = true, Context $context = null, $schemas_pr = null)
    {
        if (!$context) {
            $context = $this->context;
        }
        if ($check_access && !$context->controller->getCategory()->checkAccess((int)$context->customer->id)) {
            return false;
        }
        $front = in_array($context->controller->controller_type, array('front', 'modulefront'));
        $id_supplier = (int)Tools::getValue('id_supplier');

        $schemas = false;
        if (isset($context->cookie) AND isset($context->cookie->schemas) and $context->cookie->schemas) {
            $schemas = $context->cookie->schemas;
        }
        if (isset($schemas_pr) AND is_bool($schemas_pr)) {
            $schemas = $schemas_pr;
        }

        if ($get_total) {
            $sql = 'SELECT COUNT(DISTINCT cp.`id_product`) AS total
                    FROM `'._DB_PREFIX_.'product` p
                    '.Shop::addSqlAssociation('product', 'p').'
                    LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON p.`id_product` = cp.`id_product`
                    LEFT JOIN `'._DB_PREFIX_.'category` c ON (c.`id_category` = cp.`id_category`
                    AND c.nleft >= '.(int)$context->controller->getCategory()->nleft.' AND c.nright <= '.(int)$context->controller->getCategory()->nright.')
                    WHERE c.id_category > 0 '.
                (($front and !$schemas) ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').
                ($active ? ' AND product_shop.`active` = 1' : '') .
                ($id_supplier ? 'AND p.id_supplier = '.(int)$id_supplier : '') .
                (($front AND $schemas) ? ' AND p.`reference` LIKE "%SPL%"' : ' AND p.`reference` NOT LIKE "%SPL%"');
            return (int)As4SearchEngineDb::value($sql);
        }
        if ($p < 1) {
            $p = 1;
        }
        $order_by  = Validate::isOrderBy($order_by)   ? Tools::strtolower($order_by)  : 'position';
        $order_way = Validate::isOrderWay($order_way) ? Tools::strtoupper($order_way) : 'ASC';
        $order_by_prefix = false;
        if ($order_by == 'id_product' || $order_by == 'date_add' || $order_by == 'date_upd') {
            $order_by_prefix = 'p';
        } elseif ($order_by == 'name') {
            $order_by_prefix = 'pl';
        } elseif ($order_by == 'manufacturer' || $order_by == 'manufacturer_name') {
            $order_by_prefix = 'm';
            $order_by = 'name';
        } elseif ($order_by == 'position') {
            $order_by_prefix = 'cp';
        }
        if ($order_by == 'price') {
            $order_by = 'orderprice';
        }
        $nb_days_new_product = Configuration::get('PS_NB_DAYS_NEW_PRODUCT');
        if (!Validate::isUnsignedInt($nb_days_new_product)) {
            $nb_days_new_product = 20;
        }
        if (version_compare(_PS_VERSION_, '1.6.1.0', '>=')) {
            $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) AS quantity'.(Combination::isFeatureActive() ? ', IFNULL(product_attribute_shop.id_product_attribute, 0) AS id_product_attribute,
                    product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity' : '').', pl.`description`, pl.`description_short`, pl.`available_now`,
                    pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, image_shop.`id_image` id_image,
                    il.`legend` as legend, m.`name` AS manufacturer_name, cl.`name` AS category_default,
                    DATEDIFF(product_shop.`date_add`, DATE_SUB("'.date('Y-m-d').' 00:00:00",
                    INTERVAL '.(int)$nb_days_new_product.' DAY)) > 0 AS new, product_shop.price AS orderprice
                FROM `'._DB_PREFIX_.'category_product` cp
                LEFT JOIN `'._DB_PREFIX_.'product` p
                    ON p.`id_product` = cp.`id_product`
                '.Shop::addSqlAssociation('product', 'p').
                (Combination::isFeatureActive() ? ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
                ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int)$context->shop->id.')':'').'
                '.Product::sqlStock('p', 0).'
                LEFT JOIN `'._DB_PREFIX_.'category` c ON (c.`id_category` = cp.`id_category`
                    AND c.nleft >= '.(int)$context->controller->getCategory()->nleft.' AND c.nright <= '.(int)$context->controller->getCategory()->nright.')
                LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
                    ON (product_shop.`id_category_default` = cl.`id_category`
                    AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').')
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
                    ON (p.`id_product` = pl.`id_product`
                    AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').')
                LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
                    ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$context->shop->id.')
                LEFT JOIN `'._DB_PREFIX_.'image_lang` il
                    ON (image_shop.`id_image` = il.`id_image`
                    AND il.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN `'._DB_PREFIX_.'manufacturer` m
                    ON m.`id_manufacturer` = p.`id_manufacturer`
                WHERE c.id_category > 0
                    AND product_shop.`id_shop` = '.(int)$context->shop->id
                    .($active ? ' AND product_shop.`active` = 1' : '')
                    .(($front and !$schemas) ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '')
                    .($id_supplier ? ' AND p.id_supplier = '.(int)$id_supplier : '')
                    . (($front AND $schemas) ? ' AND p.`reference` LIKE "%SPL%"' : ' AND p.`reference` NOT LIKE "%SPL%"'); // MOD
            $sql .= ' GROUP BY cp.id_product';
        } else {
            $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity'.(Combination::isFeatureActive() ? ', MAX(product_attribute_shop.id_product_attribute) id_product_attribute, MAX(product_attribute_shop.minimal_quantity) AS product_attribute_minimal_quantity' : '').', pl.`description`, pl.`description_short`, pl.`available_now`,
                    pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, MAX(image_shop.`id_image`) id_image,
                    MAX(il.`legend`) as legend, m.`name` AS manufacturer_name, cl.`name` AS category_default,
                    DATEDIFF(product_shop.`date_add`, DATE_SUB(NOW(),
                    INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).'
                        DAY)) > 0 AS new, product_shop.price AS orderprice
                FROM `'._DB_PREFIX_.'category_product` cp
                LEFT JOIN `'._DB_PREFIX_.'product` p
                    ON p.`id_product` = cp.`id_product`
                '.Shop::addSqlAssociation('product', 'p').
                (Combination::isFeatureActive() ? 'LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa
                ON (p.`id_product` = pa.`id_product`)
                '.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1').'
                '.Product::sqlStock('p', 'product_attribute_shop', false, $context->shop) :  Product::sqlStock('p', 'product', false, $this->context->shop)).'
                LEFT JOIN `'._DB_PREFIX_.'category` c ON (c.`id_category` = cp.`id_category`
                    AND c.nleft >= '.(int)$context->controller->getCategory()->nleft.' AND c.nright <= '.(int)$context->controller->getCategory()->nright.')
                LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
                    ON (product_shop.`id_category_default` = cl.`id_category`
                    AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').')
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
                    ON (p.`id_product` = pl.`id_product`
                    AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').')
                LEFT JOIN `'._DB_PREFIX_.'image` i
                    ON (i.`id_product` = p.`id_product`)'.
                Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
                LEFT JOIN `'._DB_PREFIX_.'image_lang` il
                    ON (image_shop.`id_image` = il.`id_image`
                    AND il.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN `'._DB_PREFIX_.'manufacturer` m
                    ON m.`id_manufacturer` = p.`id_manufacturer`
                WHERE c.id_category > 0
                    AND product_shop.`id_shop` = '.(int)$context->shop->id
                    .($active ? ' AND product_shop.`active` = 1' : '')
                    .($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '')
                    .($id_supplier ? ' AND p.id_supplier = '.(int)$id_supplier : '')
                    .' GROUP BY product_shop.id_product';
        }
        if ($random === true) {
            $sql .= ' ORDER BY RAND() LIMIT '.(int)$random_number_products;
        } else {
            $sql .= ' ORDER BY '.(!empty($order_by_prefix) ? $order_by_prefix.'.' : '').'`'.bqSQL($order_by).'` '.pSQL($order_way).'
            LIMIT '.(((int)$p - 1) * (int)$n).','.(int)$n;
        }
        $result = As4SearchEngineDb::query($sql);
        if (!$result) {
            return array();
        }
        if ($order_by == 'orderprice') {
            Tools::orderbyPrice($result, $order_way);
        }
        return Product::getProductsProperties($id_lang, $result);
    }
    public function reindexCriteriaGroup()
    {
        $id_search = Tools::getValue('id_search');
        $id_criterion_group = Tools::getValue('id_criterion_group');
        As4SearchEngineIndexation::reindexSpecificCriterionGroup($id_search, $id_criterion_group);
    }
    public function cronTask($idSearch = false)
    {
        AdvancedSearchCoreClass::_changeTimeLimit(0);
        $start_memory = memory_get_usage();
        $time_start = microtime(true);
        if (!empty($idSearch)) {
            As4SearchEngineIndexation::reindexSpecificSearch($idSearch);
        } else {
            As4SearchEngineIndexation::reindexAllSearchs(true);
        }
        return array(
            'result' => true,
            'source' => (Tools::isPHPCLI() ? 'cli' : 'web'),
            'id_search' => (!empty($idSearch) ? $idSearch : false),
            'elasped_time' => round((microtime(true) - $time_start)*1000, 2),
            'memory_usage' => round((memory_get_usage() - $start_memory)/1024/1024, 2),
            'indexation_stats' => array(
                'criterions' => array(
                    'total' => As4SearchEngineIndexation::$indexationStats['total_criterions'],
                    'new' => As4SearchEngineIndexation::$indexationStats['new_criterions'],
                    'updated' => As4SearchEngineIndexation::$indexationStats['updated_criterions'],
                    'unchanged' => As4SearchEngineIndexation::$indexationStats['unchanged_criterions'],
                ),
            ),
        );
    }
    public static function clearSmartyCache($idSearch, $idCriterionGroup = null)
    {
        $smarty = Context::getContext()->smarty;
        $module = Module::getInstanceByName('pm_advancedsearch4');
        $templatePath = null;
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $templatePath = $module->getTemplatePath($module->templatePrefix . 'cache.tpl');
        }
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
            $module = Module::getInstanceByName('pm_advancedsearch4');
        }
        return $module;
    }
    public function isFullTreeModeEnabled()
    {
        $conf = $this->_getModuleConfiguration();
        return (isset($conf['fullTree']) && $conf['fullTree'] && $this->context->controller instanceof CategoryController && !$this->blockLayeredIsEnabled());
    }
    private function blockLayeredIsEnabled()
    {
        $blocklayered_module = Module::getInstanceByName('blocklayered');
        $ps_facetedsearch_module = Module::getInstanceByName('ps_facetedsearch');
        return (is_object($blocklayered_module) && isset($blocklayered_module->active) && $blocklayered_module->active == true) || (is_object($ps_facetedsearch_module) && isset($ps_facetedsearch_module->active) && $ps_facetedsearch_module->active == true);
    }
    private function getNativeLayeredModuleDisplayName()
    {
        $blocklayered_module = Module::getInstanceByName('blocklayered');
        $ps_facetedsearch_module = Module::getInstanceByName('ps_facetedsearch');
        if (!empty($blocklayered_module->displayName)) {
            return $blocklayered_module->displayName;
        } elseif (!empty($ps_facetedsearch_module->displayName)) {
            return $ps_facetedsearch_module->displayName;
        } else {
            return null;
        }
    }
}
