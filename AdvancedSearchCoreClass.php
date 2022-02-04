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
class AdvancedSearchCoreClass extends Module
{
    // Begin AttributesDeclaration
    protected $_html;
    protected $_html_at_end;
    protected $_base_config_url;
    protected $_default_language;
    protected $_fields_options;
    protected $_iso_lang;
    protected $_languages;
    protected $_css_files;
    protected $_js_files;
    protected $_coreClassName;
    protected $_registerOnHooks;
    public static $_module_prefix = 'as4';
    protected $_debug_mode = false;
    protected $_copyright_link = false;
    protected $_support_link = false;
    protected $_getting_started = false;
    protected $_initTinyMceAtEnd = false;
    protected $_initColorPickerAtEnd = false;
    protected $_initBindFillSizeAtEnd = false;
    protected static $_gradient_separator = '-';
    protected static $_border_separator = ' ';
    protected static $_shadow_separator = ' ';
    protected $_temp_upload_dir = '/uploads/temp/';
    public function __construct()
    {
        $this->_coreClassName = Tools::strtolower(get_class());
        parent::__construct();
        $this->_initClassVar();
    }
    public function install()
    {
        if (parent::install() == false or $this->_registerHooks() == false) {
            return false;
        }
        return true;
    }
    protected function _registerHooks()
    {
        if (!isset($this->_registerOnHooks) || !self::_isFilledArray($this->_registerOnHooks)) {
            return true;
        }
        foreach ($this->_registerOnHooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }
        return true;
    }
    public static function Db_ExecuteS($q)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($q);
    }
    private function getProductsOnLive($q, $limit, $start)
    {
        $result = self::Db_ExecuteS('
        SELECT p.`id_product`, CONCAT(p.`id_product`, \' - \', IFNULL(CONCAT(NULLIF(TRIM(p.reference), \'\'), \' - \'), \'\'), pl.`name`) AS name
        FROM `' . _DB_PREFIX_ . 'product` p, `' . _DB_PREFIX_ . 'product_lang` pl, `' . _DB_PREFIX_ . 'product_shop` ps
        WHERE p.`id_product`=pl.`id_product`
        AND p.`id_product`=ps.`id_product`
        '.Shop::addSqlRestriction(false, 'ps').'
        AND pl.`id_lang`=' . (int)$this->_default_language . '
        AND ps.`active` = 1
        AND ((p.`id_product` LIKE \'%' . pSQL($q) . '%\') OR (pl.`name` LIKE \'%' . pSQL($q) . '%\') OR (p.`reference` LIKE \'%' . pSQL($q) . '%\') OR (pl.`description` LIKE \'%' . pSQL($q) . '%\') OR (pl.`description_short` LIKE \'%' . pSQL($q) . '%\'))
        GROUP BY p.`id_product`
        ORDER BY pl.`name` ASC ' . ($limit ? 'LIMIT ' . (int)$start . ', ' . (int)$limit : ''));
        return $result;
    }
    private function getSuppliersOnLive($q, $limit, $start)
    {
        $result = self::Db_ExecuteS('
        SELECT s.`id_supplier`, s.`name`
        FROM `' . _DB_PREFIX_ . 'supplier` s
        WHERE (s.name LIKE \'%' . pSQL($q) . '%\')
        AND s.`active` = 1
        ORDER BY s.`name` ' . ($limit ? 'LIMIT ' . (int)$start . ', ' . (int)$limit : ''));
        return $result;
    }
    private function getManufacturersOnLive($q, $limit, $start)
    {
        $result = self::Db_ExecuteS('
        SELECT m.`id_manufacturer`, m.`name`
        FROM `' . _DB_PREFIX_ . 'manufacturer` m
        WHERE (m.name LIKE \'%' . pSQL($q) . '%\')
        AND m.`active` = 1
        ORDER BY m.`name` ' . ($limit ? 'LIMIT ' . (int)$start . ', ' . (int)$limit : ''));
        return $result;
    }
    private function getCMSPagesOnLive($q, $limit, $start)
    {
        $result = self::Db_ExecuteS('
        SELECT c.`id_cms`, cl.`meta_title`
        FROM `' . _DB_PREFIX_ . 'cms` c
        LEFT JOIN `'._DB_PREFIX_.'cms_lang` cl ON c.id_cms=cl.id_cms
        WHERE (cl.meta_title LIKE \'%' . pSQL($q) . '%\')
        AND cl.`id_lang`=' . (int)$this->_default_language . '
        AND c.`active` = 1
        ORDER BY cl.`meta_title` ' . ($limit ? 'LIMIT ' . (int)$start . ', ' . (int)$limit : ''));
        return $result;
    }
    public static function getCustomMetasByIdLang()
    {
        $finalList = array();
        $metas = Meta::getMetas();
        foreach ($metas as $meta) {
            $finalList[$meta['page']] = $meta['page'];
        }
        $pages_names = Meta::getMetasByIdLang((int)Context::getContext()->language->id);
        foreach ($pages_names as $pageName) {
            if (!empty($pageName['title'])) {
                $pageName['title'] .= ' (' . $pageName['page'] . ')';
            }
            $finalList[$pageName['page']] = $pageName;
        }
        unset($pages_names);
        $moduleInstance = Module::getInstanceByName('pm_advancedsearch4');
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $finalList['checkout'] = array(
                'page' => 'checkout',
                'title' => $moduleInstance->l('Checkout', $moduleInstance->_coreClassName) . ' (checkout)',
            );
        }
        $finalList['product'] = array(
            'page' => 'product',
            'title' => $moduleInstance->l('Product', $moduleInstance->_coreClassName) . ' (product)',
        );
        $finalList['category'] = array(
            'page' => 'category',
            'title' => $moduleInstance->l('Category', $moduleInstance->_coreClassName) . ' (category)',
        );
        $finalList['cms'] = array(
            'page' => 'cms',
            'title' => $moduleInstance->l('CMS', $moduleInstance->_coreClassName) . ' (cms)',
        );
        $finalList['index'] = array(
            'page' => 'index',
            'title' => $moduleInstance->l('Homepage', $moduleInstance->_coreClassName) . ' (index)',
        );
        return $finalList;
    }
    private function getControllerNameOnLive($q)
    {
        $pages = Meta::getPages();
        $pages['product'] = 'product';
        $pages['category'] = 'category';
        $pages['cms'] = 'cms';
        $pages['index'] = 'index';
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $pages['checkout'] = 'checkout';
        }
        $pages_names = self::getCustomMetasByIdLang();
        $ignoreList = array('pm_advancedsearch4-advancedsearch4', 'pm_advancedsearch4-seositemap', 'pm_modalcart3');
        $controllers_list = array();
        foreach ($pages_names as $page_name) {
            if (isset($page_name['page']) && ((isset($pages[$page_name['page']]) || in_array($page_name['page'], $pages)) || (isset($pages[str_replace('-', '', $page_name['page'])]) || in_array(str_replace('-', '', $page_name['page']), $pages)))) {
                $ignore = false;
                foreach ($ignoreList as $pageToIgnore) {
                    if (stripos($page_name['page'], $pageToIgnore) !== false) {
                        $ignore = true;
                        continue;
                    }
                }
                if (!$ignore && (stripos($page_name['page'], $q) !== false || stripos($page_name['title'], $q) !== false)) {
                    $controllers_list[] = $page_name;
                }
            }
        }
        return $controllers_list;
    }
    protected function pmClearCache()
    {
        $this->clearCompiledTpl();
        if (Configuration::get('PS_FORCE_SMARTY_2')) {
            return $this->context->smarty->clear_cache(null, self::$_module_prefix);
        } else {
            return $this->context->smarty->clearCache(null, self::$_module_prefix);
        }
        return true;
    }
    protected static function clearCompiledTplAlternative($tplFileName, $compileDir)
    {
        $result = false;
        $compileDir = rtrim($compileDir, '/');
        $files = scandir($compileDir);
        if ($files && sizeof($files)) {
            foreach ($files as $filename) {
                if ($filename != '.' && $filename != '..' && is_dir($compileDir.'/'.$filename)) {
                    self::clearCompiledTplAlternative($tplFileName, $compileDir.'/'.$filename);
                } else {
                    $ext = self::_getFileExtension($filename);
                    if ($filename == '.' && $filename == '..' || is_dir($compileDir.'/'.$filename) || $filename == 'index.php' || $ext != 'php' || !preg_match('/file\.'.preg_quote($tplFileName).'\.php/', $filename)) {
                        continue;
                    }
                    if (Tools::file_exists_cache($compileDir.'/'.$filename) && @unlink($compileDir.'/'.$filename)) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }
    protected function clearCompiledTpl()
    {
        $files = scandir(dirname(__FILE__));
        if ($files && sizeof($files)) {
            foreach ($files as $filename) {
                $ext = self::_getFileExtension($filename);
                if ($ext != 'tpl') {
                    continue;
                }
                if (Configuration::get('PS_FORCE_SMARTY_2')) {
                    $this->context->smarty->clear_compiled_tpl($filename);
                } else {
                    if (!$this->context->smarty->clearCompiledTemplate($filename)) {
                        self::clearCompiledTplAlternative($filename, $this->context->smarty->getCompileDir());
                    }
                }
            }
        }
    }
    protected function _checkPermissions()
    {
        $errors = array();
        if (isset($this->_file_to_check) && is_array($this->_file_to_check) && count($this->_file_to_check)) {
            foreach ($this->_file_to_check as $fileOrDir) {
                if (!is_writable(dirname(__FILE__) . '/' . $fileOrDir)) {
                    $errors[] = dirname(__FILE__) . '/' . $fileOrDir;
                }
            }
        }
        return $errors;
    }
    protected function getContent()
    {
        $return = '';
        if ($this->_require_maintenance) {
            $return .= $this->_maintenanceWarning();
            $return .= $this->_maintenanceButton();
            $return .= '<hr class="pm_hr" />';
        }
        return $return;
    }
    public static function _getFileExtension($filename)
    {
        $split = explode('.', $filename);
        $extension = end($split);
        return Tools::strtolower($extension);
    }
    public function _showWarning($text)
    {
        $vars = array(
            'text' => $text
        );
        return $this->fetchTemplate('core/warning.tpl', $vars);
    }
    protected function _showRating($show = false)
    {
        $dismiss = (int)Configuration::getGlobalValue('PM_'.self::$_module_prefix.'_DISMISS_RATING');
        if ($show && $dismiss != 1 && self::_getNbDaysModuleUsage() >= 3) {
            return $this->fetchTemplate('core/rating.tpl');
        }
        return '';
    }
    public function _showInfo($text)
    {
        $vars = array(
            'text' => $text
        );
        return $this->fetchTemplate('core/info.tpl', $vars);
    }
    public function _displayTitle($title)
    {
        $vars = array(
            'text' => $title
        );
        return $this->fetchTemplate('core/title.tpl', $vars);
    }
    public function _displaySubTitle($title)
    {
        $vars = array(
            'text' => $title
        );
        return $this->fetchTemplate('core/sub_title.tpl', $vars);
    }
    public function _displayErrorsJs($include_script_tag = false)
    {
        $vars = array(
            'include_script_tag' => $include_script_tag,
            'js_errors' => $this->errors,
        );
        $this->_html .= $this->fetchTemplate('core/js_errors.tpl', $vars);
    }
    
    private function _getPMdata()
    {
        $param = array();
        $param[] = 'ver-'._PS_VERSION_;
        $param[] = 'current-'.$this->name;
        
        $result = $this->getPMAddons();
        if ($result && self::_isFilledArray($result)) {
            foreach ($result as $moduleName => $moduleVersion) {
                $param[] = $moduleName . '-' . $moduleVersion;
            }
        }
        return self::getDataSerialized(implode('|', $param));
    }
    private function getPMAddons()
    {
        $pmAddons = array();
        $result = self::Db_ExecuteS('SELECT DISTINCT name FROM '._DB_PREFIX_.'module WHERE name LIKE "pm_%"');
        if ($result && self::_isFilledArray($result)) {
            foreach ($result as $module) {
                $instance = Module::getInstanceByName($module['name']);
                if ($instance && isset($instance->version)) {
                    $pmAddons[$module['name']] = $instance->version;
                }
            }
        }
        return $pmAddons;
    }
    private function doHttpRequest($data = array(), $c = 'prestashop', $s = 'api.addons')
    {
        $data = array_merge(array(
            'version' => _PS_VERSION_,
            'iso_lang' => Tools::strtolower($this->_iso_lang),
            'iso_code' => Tools::strtolower(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'))),
            'module_key' => $this->module_key,
            'method' => 'contributor',
            'action' => 'all_products',
        ), $data);
        $postData = http_build_query($data);
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'content' => $postData,
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 15,
            )
        ));
        $response = Tools::file_get_contents('https://' . $s . '.' . $c . '.com', false, $context);
        if (empty($response)) {
            return false;
        }
        $responseToJson = Tools::jsonDecode($response);
        if (empty($responseToJson)) {
            return false;
        }
        return $responseToJson;
    }
    private function getAddonsModulesFromApi()
    {
        $modules = Configuration::get('PM_' . self::$_module_prefix . '_AM');
        $modules_date = Configuration::get('PM_' . self::$_module_prefix . '_AMD');
        if ($modules && strtotime('+2 day', $modules_date) > time()) {
            return Tools::jsonDecode($modules, true);
        }
        $jsonResponse = $this->doHttpRequest();
        if (empty($jsonResponse->products)) {
            return array();
        }
        $dataToStore = array();
        foreach ($jsonResponse->products as $addonsEntry) {
            $dataToStore[(int)$addonsEntry->id] = array(
                'name' => $addonsEntry->name,
                'displayName' => $addonsEntry->displayName,
                'url' => $addonsEntry->url,
                'compatibility' => $addonsEntry->compatibility,
                'version' => $addonsEntry->version,
                'description' => $addonsEntry->description,
            );
        }
        Configuration::updateValue('PM_' . self::$_module_prefix . '_AM', Tools::jsonEncode($dataToStore));
        Configuration::updateValue('PM_' . self::$_module_prefix . '_AMD', time());
        return Tools::jsonDecode(Configuration::get('PM_' . self::$_module_prefix . '_AM'), true);
    }
    private function getPMModulesFromApi()
    {
        $modules = Configuration::get('PM_' . self::$_module_prefix . '_PMM');
        $modules_date = Configuration::get('PM_' . self::$_module_prefix . '_PMMD');
        if ($modules && strtotime('+2 day', $modules_date) > time()) {
            return Tools::jsonDecode($modules, true);
        }
        $jsonResponse = $this->doHttpRequest(array('list' => $this->getPMAddons()), 'presta-module', 'api-addons');
        if (empty($jsonResponse)) {
            return array();
        }
        Configuration::updateValue('PM_' . self::$_module_prefix . '_PMM', Tools::jsonEncode($jsonResponse));
        Configuration::updateValue('PM_' . self::$_module_prefix . '_PMMD', time());
        return Tools::jsonDecode(Configuration::get('PM_' . self::$_module_prefix . '_PMM'), true);
    }
    public function _displaySupport()
    {
        $get_started_image_list = array();
        if (isset($this->_getting_started) && self::_isFilledArray($this->_getting_started)) {
            foreach ($this->_getting_started as $get_started_image) {
                $get_started_image_list[] = "{ 'href': '".$get_started_image['href']."', 'title': '".htmlentities($get_started_image['title'], ENT_QUOTES, 'UTF-8')."' }";
            }
        }
        $pm_addons_products = $this->getAddonsModulesFromApi();
        $pm_products = $this->getPMModulesFromApi();
        if (!is_array($pm_addons_products)) {
            $pm_addons_products = array();
        }
        if (!is_array($pm_products)) {
            $pm_products = array();
        }
        self::shuffleArray($pm_addons_products);
        if (self::_isFilledArray($pm_addons_products)) {
            if (!empty($pm_products['ignoreList']) && self::_isFilledArray($pm_products['ignoreList'])) {
                foreach ($pm_products['ignoreList'] as $ignoreId) {
                    if (isset($pm_addons_products[$ignoreId])) {
                        unset($pm_addons_products[$ignoreId]);
                    }
                }
            }
            $addonsList = $this->getPMAddons();
            if ($addonsList && self::_isFilledArray($addonsList)) {
                foreach (array_keys($addonsList) as $moduleName) {
                    foreach ($pm_addons_products as $k => $pm_addons_product) {
                        if ($pm_addons_product['name'] == $moduleName) {
                            unset($pm_addons_products[$k]);
                            break;
                        }
                    }
                }
            }
        }
        $vars = array(
            'support_links' => (self::_isFilledArray($this->_support_link) ? $this->_support_link : array()),
            'copyright_link' => (self::_isFilledArray($this->_copyright_link) ? $this->_copyright_link : false),
            'get_started_image_list' => (isset($this->_getting_started) && self::_isFilledArray($this->_getting_started) ? $this->_getting_started : array()),
            'pm_module_version' => $this->version,
            'pm_data' => $this->_getPMdata(),
            'pm_products' => $pm_products,
            'pm_addons_products' => $pm_addons_products,
            'html_at_end' =>  $this->_includeHTMLAtEnd(),
        );
        return $this->fetchTemplate('core/support.tpl', $vars);
    }
    protected function _preProcess()
    {
        if (Tools::getIsset('dismissRating')) {
            $this->_cleanOutput();
            Configuration::updateGlobalValue('PM_'.self::$_module_prefix.'_DISMISS_RATING', 1);
            die;
        } elseif (Tools::getIsset('pm_load_function')) {
            if (method_exists($this, Tools::getValue('pm_load_function'))) {
                $this->_cleanOutput();
                if (Tools::getValue('class')) {
                    if (class_exists(Tools::getValue('class'))) {
                        $class = Tools::getValue('class');
                        $obj = new $class();
                        if (Tools::getValue($obj->identifier)) {
                            $obj = new $class(Tools::getValue($obj->identifier));
                        }
                        $pmLoadFunction = Tools::getValue('pm_load_function');
                        $params = array('obj'=>$obj,'class'=>$class, 'method'=> $pmLoadFunction,'reload_after'=>Tools::getValue('pm_reload_after'),'js_callback'=>Tools::getValue('pm_js_callback'));
                        $this->_preLoadFunctionProcess($params);
                        $this->_html .= $this->$pmLoadFunction($params);
                    } else {
                        $this->_cleanOutput();
                        $this->_html .= $this->_showWarning($this->l('Class', $this->_coreClassName).' '.Tools::getValue('class').' '.$this->l('does not exists', $this->_coreClassName));
                        $this->_echoOutput(true);
                    }
                } else {
                    $pmLoadFunction = Tools::getValue('pm_load_function');
                    $params = array('method' => $pmLoadFunction,'reload_after'=>Tools::getValue('pm_reload_after'),'js_callback'=>Tools::getValue('pm_js_callback'));
                    $this->_preLoadFunctionProcess($params);
                    $this->_html .= $this->$pmLoadFunction($params);
                }
                $this->_echoOutput(true);
            } else {
                $this->_cleanOutput();
                $this->_html .= $this->_showWarning($this->l('Method unvailable', $this->_coreClassName));
                $this->_echoOutput(true);
            }
        } elseif (Tools::getIsset('pm_delete_obj')) {
            if (Tools::getValue('class')) {
                if (class_exists(Tools::getValue('class'))) {
                    $class = Tools::getValue('class');
                    $obj = new $class();
                    $obj = new $class(Tools::getValue($obj->identifier));
                    $this->_preDeleteProcess(array('obj'=>$obj, 'class'=>$class));
                    if ($obj->delete()) {
                        $this->_cleanOutput();
                        $this->_postDeleteProcess(array('class'=>$class));
                        $this->_echoOutput(true);
                    } else {
                        $this->_cleanOutput();
                        $this->_html .= $this->_showWarning($this->l('Error while deleting object', $this->_coreClassName));
                        $this->_echoOutput(true);
                    }
                } else {
                    $this->_cleanOutput();
                    $this->_html .= $this->_showWarning($this->l('Class', $this->_coreClassName).' '.Tools::getValue('class').' '.$this->l('does not exists', $this->_coreClassName));
                    $this->_echoOutput(true);
                }
            } else {
                $this->_cleanOutput();
                $this->_html .= $this->_showWarning($this->l('Please send class name into “class“ var', $this->_coreClassName));
                $this->_echoOutput(true);
            }
        } elseif (Tools::getIsset('pm_save_order')) {
            if (!Tools::getValue('order')) {
                $this->_cleanOutput();
                $this->_html .= $this->_showWarning($this->l('Not receive IDS', $this->_coreClassName));
                $this->_echoOutput(true);
            } elseif (!Tools::getValue('destination_table')) {
                $this->_cleanOutput();
                $this->_html .= $this->_showWarning($this->l('Please send destination table', $this->_coreClassName));
                $this->_echoOutput(true);
            } elseif (!Tools::getValue('field_to_update')) {
                $this->_cleanOutput();
                $this->_html .= $this->_showWarning($this->l('Please send name of position field', $this->_coreClassName));
                $this->_echoOutput(true);
            } elseif (!Tools::getValue('identifier')) {
                $this->_cleanOutput();
                $this->_html .= $this->_showWarning($this->l('Please send identifier', $this->_coreClassName));
                $this->_echoOutput(true);
            } else {
                $order = Tools::getValue('order');
                $identifier = Tools::getValue('identifier');
                $field_to_update = Tools::getValue('field_to_update');
                $destination_table = Tools::getValue('destination_table');
                foreach ($order as $position => $id) {
                    $id = preg_replace("/^\w+_/", "", $id);
                    $data = array($field_to_update=>$position);
                    Db::getInstance()->update($destination_table, $data, $identifier . ' = ' . (int) $id);
                }
                $this->_cleanOutput();
                $this->_echoOutput(true);
            }
        } elseif (Tools::getIsset('getPanel') && Tools::getValue('getPanel')) {
            self::_cleanBuffer();
            switch (Tools::getValue('getPanel')) {
                case 'getChildrenCategories':
                    if (Tools::getValue('id_category_parent')) {
                        $children_categories = self::getChildrenWithNbSelectedSubCat(Tools::getValue('id_category_parent'), Tools::getValue('selectedCat'), $this->_default_language);
                        die(Tools::jsonEncode($children_categories));
                    }
                    break;
            }
        } elseif (Tools::getIsset('pm_duplicate_obj')) {
            if (Tools::getValue('class')) {
                if (class_exists(Tools::getValue('class'))) {
                    $class = Tools::getValue('class');
                    $obj = new $class();
                    $obj = new $class((int)Tools::getValue($obj->identifier));
                    $this->_preDuplicateProcess(array('obj' =>$obj, 'class' => $class));
                    $duplicated_obj = $obj->duplicate();
                    if ($duplicated_obj instanceof $class) {
                        $this->_cleanOutput();
                        $this->_postDuplicateProcess(array('obj' => $duplicated_obj, 'class' => $class));
                        $this->_echoOutput(true);
                    } else {
                        $this->_cleanOutput();
                        $this->_html .= $this->_showWarning($this->l('Error while duplicating object', $this->_coreClassName));
                        $this->_echoOutput(true);
                    }
                } else {
                    $this->_cleanOutput();
                    $this->_html .= $this->_showWarning($this->l('Class', $this->_coreClassName).' '.Tools::getValue('class').' '.$this->l('does not exists', $this->_coreClassName));
                    $this->_echoOutput(true);
                }
            } else {
                $this->_cleanOutput();
                $this->_html .= $this->_showWarning($this->l('Please send class name into “class“ var', $this->_coreClassName));
                $this->_echoOutput(true);
            }
        }
    }
    protected function _maintenanceButton()
    {
        $vars = array(
            'maintenance_enabled' => Configuration::get('PM_' . self::$_module_prefix . '_MAINTENANCE'),
        );
        return $this->fetchTemplate('core/components/maintenance_button.tpl', $vars);
    }
    protected function _maintenanceWarning()
    {
        $ip_maintenance = Configuration::get('PS_MAINTENANCE_IP');
        $addIpWarning = '';
        $return = '<div id="maintenanceWarning" ' . ((Configuration::get('PM_' . self::$_module_prefix . '_MAINTENANCE')) ? '' : 'style="display:none"') . '">';
        if (!$ip_maintenance || empty($ip_maintenance)) {
            $addIpWarning = '<br /><br />' . $this->l('You must define a maintenance IP in your', $this->_coreClassName) . '
                <a href="' . $this->context->link->getAdminLink('AdminMaintenance') . '" style="text-decoration:underline;">
                ' . $this->l('Preferences Panel.', $this->_coreClassName) . '
                </a>';
        }
        $return .= $this->_showWarning(
            sprintf($this->l('%s is currently running in maintenance mode.', $this->_coreClassName), $this->displayName) .
            $addIpWarning
        );
        $return .= '</div>';
        return $return;
    }
    protected function _postProcessMaintenance()
    {
        $return = '';
        $maintenance = Configuration::get('PM_' . self::$_module_prefix . '_MAINTENANCE');
        $maintenance = ($maintenance ? 0 : 1);
        Configuration::updateValue('PM_' . self::$_module_prefix . '_MAINTENANCE', (int)$maintenance);
        $return .= '$("a#buttonMaintenance").after("'.addcslashes($this->_maintenanceButton(), '"').'").remove();';
        if ($maintenance) {
            $return .= '$("#pmImgMaintenance").attr("class", "ui-icon ui-icon-locked");';
            $return .= '$("#maintenanceWarning").slideDown();';
            $return .= 'show_info("' . sprintf($this->l('%s is now in maintenance mode.', $this->_coreClassName), $this->displayName) . '");';
        } else {
            $return .= '$("#pmImgMaintenance").attr("class", "ui-icon ui-icon-unlocked");';
            $return .= '$("#maintenanceWarning").slideUp();';
            $return .= 'show_info("' . sprintf($this->l('%s is now running in normal mode.', $this->_coreClassName), $this->displayName) . '");';
        }
        $this->pmClearCache();
        self::_cleanBuffer();
        return $return;
    }
    protected function _isInMaintenance()
    {
        if (isset($this->_cacheIsInMaintenance)) {
            return $this->_cacheIsInMaintenance;
        }
        if (Configuration::get('PM_'.self::$_module_prefix.'_MAINTENANCE')) {
            $ips = explode(',', Configuration::get('PS_MAINTENANCE_IP'));
            if (in_array($_SERVER['REMOTE_ADDR'], $ips)) {
                $this->_cacheIsInMaintenance = false;
                return false;
            }
            $this->_cacheIsInMaintenance = true;
            return true;
        }
        $this->_cacheIsInMaintenance = false;
        return false;
    }
    protected function _preCopyFromPost()
    {
    }
    protected function _postCopyFromPost($params)
    {
    }
    protected function _preDuplicateProcess($params)
    {
    }
    protected function _preDeleteProcess($params)
    {
    }
    protected function _preLoadFunctionProcess(&$params)
    {
    }
    protected function _postDuplicateProcess($params)
    {
        if (isset($params['include_script_tag']) && $params['include_script_tag']) {
            $this->_html .= '<script type="text/javascript">';
        }
        if (Tools::getIsset('pm_reload_after') && Tools::getValue('pm_reload_after')) {
            $this->_reloadPanels(Tools::getValue('pm_reload_after'));
        }
        if (Tools::getIsset('pm_js_callback') && Tools::getValue('pm_js_callback')) {
            $this->_getJsCallback(Tools::getValue('pm_js_callback'));
        }
        $this->_html .= 'parent.parent.show_info("'.$this->l('Successfully duplicated', $this->_coreClassName).'");';
        if (isset($params['include_script_tag']) && $params['include_script_tag']) {
            $this->_html .= '</script>';
        }
    }
    protected function _postDeleteProcess($params)
    {
        if (isset($params['include_script_tag']) && $params['include_script_tag']) {
            $this->_html .= '<script type="text/javascript">';
        }
        if (Tools::getIsset('pm_reload_after') && Tools::getValue('pm_reload_after')) {
            $this->_reloadPanels(Tools::getValue('pm_reload_after'));
        }
        if (Tools::getIsset('pm_js_callback') && Tools::getValue('pm_js_callback')) {
            $this->_getJsCallback(Tools::getValue('pm_js_callback'));
        }
        $this->_html .= 'parent.parent.show_info("'.$this->l('Successfully deleted', $this->_coreClassName).'");';
        if (isset($params['include_script_tag']) && $params['include_script_tag']) {
            $this->_html .= '</script>';
        }
    }
    protected function _getJsCallback($js_callback)
    {
        $js_callbacks = explode('|', $js_callback);
        foreach ($js_callbacks as $js_callback) {
            $this->_html .= 'parent.parent.'.$js_callback.'();';
        }
    }
    protected function _reloadPanels($reload_after)
    {
        $reload_after = explode('|', $reload_after);
        foreach ($reload_after as $panel) {
            $this->_html .= 'parent.parent.reloadPanel("'.$panel.'");';
        }
    }
    protected function _postSaveProcess($params)
    {
        if (isset($params['include_script_tag']) && $params['include_script_tag']) {
            $this->_html .= '<script type="text/javascript">';
        }
        if (isset($params['reload_after']) && $params['reload_after']) {
            $this->_reloadPanels($params['reload_after']);
        }
        if (isset($params['js_callback']) && $params['js_callback']) {
            $this->_getJsCallback($params['js_callback']);
        }
        $this->_html .= 'parent.parent.show_info("'.$this->l('Successfully saved', $this->_coreClassName).'");';
        if (isset($params['include_script_tag']) && $params['include_script_tag']) {
            $this->_html .= '</script>';
        }
    }
    protected function _postProcess()
    {
        if (Tools::getValue('pm_save_obj')) {
            if (class_exists(Tools::getValue('pm_save_obj'))) {
                $class = Tools::getValue('pm_save_obj');
                $obj = new $class();
                if (Tools::getValue($obj->identifier)) {
                    $obj = new $class(Tools::getValue($obj->identifier));
                }
                $this->errors = self::_retroValidateController($obj);
                if (!self::_isFilledArray($this->errors)) {
                    $this->copyFromPost($obj);
                    if ($obj->save()) {
                        $this->_cleanOutput();
                        $this->_postSaveProcess(array('class'=>$class, 'obj'=>$obj, 'include_script_tag'=>true, 'reload_after'=>Tools::getValue('pm_reload_after'), 'js_callback'=>Tools::getValue('pm_js_callback')));
                        $this->_echoOutput(true);
                    } else {
                        $this->_cleanOutput();
                        $this->_html .= $this->_showWarning($this->l('Error while saving object', $this->_coreClassName));
                        $this->_echoOutput(true);
                    }
                } else {
                    $this->_cleanOutput();
                    $this->_displayErrorsJs(true);
                    $this->_echoOutput(true);
                }
            } else {
                $this->_cleanOutput();
                $this->_html .= $this->_showWarning($this->l('Class', $this->_coreClassName).' '.Tools::getValue('class').' '.$this->l('does not exists', $this->_coreClassName));
                $this->_echoOutput(true);
            }
        } elseif (Tools::getValue('activeMaintenance')) {
            echo $this->_postProcessMaintenance(self::$_module_prefix);
            die();
        } elseif (Tools::getValue('uploadTempFile')) {
            $this->_postProcessUploadTempFile();
        } elseif (Tools::getValue('getItem')) {
            $this->_cleanOutput();
            $item = Tools::getValue('itemType');
            $query = Tools::getValue('q', false);
            if (!$query || Tools::strlen($query) < 1) {
                self::_cleanBuffer();
                die();
            }
            $limit = Tools::getValue('limit', 100);
            $start = Tools::getValue('start', 0);
            switch ($item) {
                case 'product':
                    $items = $this->getProductsOnLive($query, $limit, $start);
                    $item_id_column = 'id_product';
                    $item_name_column = 'name';
                    break;
                case 'supplier':
                    $items = $this->getSuppliersOnLive($query, $limit, $start);
                    $item_id_column = 'id_supplier';
                    $item_name_column = 'name';
                    break;
                case 'manufacturer':
                    $items = $this->getManufacturersOnLive($query, $limit, $start);
                    $item_id_column = 'id_manufacturer';
                    $item_name_column = 'name';
                    break;
                case 'cms':
                    $items = $this->getCMSPagesOnLive($query, $limit, $start);
                    $item_id_column = 'id_cms';
                    $item_name_column = 'meta_title';
                    break;
                case 'controller':
                    $items = $this->getControllerNameOnLive($query);
                    $item_id_column = 'page';
                    $item_name_column = 'title';
                    break;
            }
            if ($items) {
                foreach ($items as $row) {
                    $this->_html .= $row[$item_id_column] . '=' . $row[$item_name_column] . "\n";
                }
            }
            $this->_echoOutput(true);
            die();
        }
    }
    protected function _postProcessUploadTempFile()
    {
        $this->_cleanOutput();
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["fileUpload"]["name"];
        } else {
            $fileName = uniqid("file_" . self::$_module_prefix . mt_rand());
        }
        $extension = self::_getFileExtension($fileName);
        $filePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . uniqid(self::$_module_prefix . mt_rand()) . '.' . $extension;
        if (!$out = @fopen("{$filePath}.part", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }
        if (!empty($_FILES)) {
            if ($_FILES["fileUpload"]["error"] || !is_uploaded_file($_FILES["fileUpload"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }
            if (!$in = @fopen($_FILES["fileUpload"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        @fclose($out);
        @fclose($in);
        rename("{$filePath}.part", $filePath);
        die('{"jsonrpc" : "2.0", "filename" : "'. basename($filePath) .'"}');
    }
    protected function _initClassVar()
    {
        $this->_default_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $this->_iso_lang = Language::getIsoById((int)$this->context->language->id);
        $this->_languages = Language::getLanguages(false);
    }
    public function _startForm($configOptions)
    {
        $defaultOptions = array(
            'action' => false,
            'target' => 'dialogIframePostForm',
            'iframetarget' => true
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $return = '';
        if ($configOptions['iframetarget']) {
            $return .= $this->_headerIframe();
        }
        $vars = array(
            'form_action' => ($configOptions['action'] ? $configOptions['action'] : $this->_base_config_url),
            'form_id' => $configOptions['id'],
            'form_target' => $configOptions['target'],
            'obj_id' => (isset($configOptions['obj']) && is_object($configOptions['obj']) && !empty($configOptions['obj']->id) ? $configOptions['obj']->id : false),
            'obj_identifier' => (isset($configOptions['obj']) && is_object($configOptions['obj']) && !empty($configOptions['obj']->id) ? $configOptions['obj']->identifier : false),
            'obj_class' => (isset($configOptions['obj']) && is_object($configOptions['obj']) ? get_class($configOptions['obj']) : false),
            'pm_reload_after' => (!empty($configOptions['params']['reload_after']) ? $configOptions['params']['reload_after'] : false),
            'pm_js_callback' => (!empty($configOptions['params']['js_callback']) ? $configOptions['params']['js_callback'] : false),
        );
        $return .= $this->fetchTemplate('core/components/form/start_form.tpl', $vars, $configOptions);
        return $return;
    }
    public function _endForm($configOptions)
    {
        $defaultOptions = array(
            'id' => null,
            'iframetarget' => true,
            'jquerytoolsvalidatorfunction' => false
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $vars = array(
            'form_id' => $configOptions['id'],
            'has_jquerytools' => ($configOptions['id'] != null && in_array('jquerytools', $this->_css_js_to_load)),
            'jquerytools_validator_function' => $configOptions['jquerytoolsvalidatorfunction'],
        );
        $return = $this->fetchTemplate('core/components/form/end_form.tpl', $vars, $configOptions);
        if ($configOptions['iframetarget']) {
            $return .= $this->_footerIframe();
        }
        return $return;
    }
    public function _retrieveFormValue($type, $fieldName, $fieldDbName = false, $obj, $defaultValue = '', $compareValue = false, $key = false)
    {
        if (!$fieldDbName) {
            $fieldDbName = $fieldName;
        }
        switch ($type) {
            case 'text':
                if (is_array($obj)) {
                    if ($key) {
                        return htmlentities(Tools::stripslashes(Tools::getValue($fieldName, (self::_isFilledArray($obj) && isset($obj[$fieldDbName] [$key]) ? $obj[$fieldDbName] [$key] : $defaultValue))), ENT_COMPAT, 'UTF-8');
                    } else {
                        return htmlentities(Tools::stripslashes(Tools::getValue($fieldName, (self::_isFilledArray($obj) && isset($obj[$fieldDbName]) ? $obj[$fieldDbName] : $defaultValue))), ENT_COMPAT, 'UTF-8');
                    }
                } else {
                    if ($key) {
                        return htmlentities(Tools::stripslashes(Tools::getValue($fieldName, ($obj && isset($obj->{$fieldDbName}[$key]) ? $obj->{$fieldDbName}[$key] : $defaultValue))), ENT_COMPAT, 'UTF-8');
                    } else {
                        return htmlentities(Tools::stripslashes(Tools::getValue($fieldName, ($obj && isset($obj->{$fieldDbName}) ? $obj->{$fieldDbName} : $defaultValue))), ENT_COMPAT, 'UTF-8');
                    }
                }
                break;
            case 'textpx':
                if (is_array($obj)) {
                    if ($key) {
                        return (int)preg_replace('#px#', '', Tools::getValue($fieldName, (self::_isFilledArray($obj) && isset($obj[$fieldDbName]) ? $obj[$fieldDbName] [$key] : $defaultValue)));
                    } else {
                        return (int)preg_replace('#px#', '', Tools::getValue($fieldName, (self::_isFilledArray($obj) && isset($obj[$fieldDbName]) ? $obj[$fieldDbName] : $defaultValue)));
                    }
                } else {
                    if ($key) {
                        return (int)preg_replace('#px#', '', Tools::getValue($fieldName, ($obj && isset($obj->{$fieldDbName}) ? $obj->{$fieldDbName}[$key] : $defaultValue)));
                    } else {
                        return (int)preg_replace('#px#', '', Tools::getValue($fieldName, ($obj && isset($obj->{$fieldDbName}) ? $obj->{$fieldDbName} : $defaultValue)));
                    }
                }
                break;
            case 'select':
                if (is_array($obj)) {
                    return ((Tools::getValue($fieldName, (self::_isFilledArray($obj) && isset($obj[$fieldDbName]) ? $obj[$fieldDbName] : $defaultValue)) == $compareValue) ? ' selected="selected"' : '');
                } else {
                    return ((Tools::getValue($fieldName, ($obj && isset($obj->{$fieldDbName}) ? $obj->{$fieldDbName} : $defaultValue)) == $compareValue) ? ' selected="selected"' : '');
                }
                break;
            case 'radio':
            case 'checkbox':
                if (is_array($obj)) {
                    if (isset($obj[$fieldName]) && is_array($obj[$fieldName]) && sizeof($obj[$fieldName]) && isset($obj[$fieldDbName])) {
                        return ((in_array($compareValue, $obj[$fieldName])) ? ' checked="checked"' : '');
                    }
                    return ((Tools::getValue($fieldName, (self::_isFilledArray($obj) && isset($obj[$fieldDbName]) ? $obj[$fieldDbName] : $defaultValue)) == $compareValue) ? ' checked="checked"' : '');
                } else {
                    if (isset($obj->$fieldName) && is_array($obj->$fieldName) && sizeof($obj->$fieldName) && isset($obj->{$fieldDbName})) {
                        return ((in_array($compareValue, $obj->$fieldName)) ? ' checked="checked"' : '');
                    }
                    return ((Tools::getValue($fieldName, ($obj && isset($obj->{$fieldDbName}) ? $obj->{$fieldDbName} : $defaultValue)) == $compareValue) ? ' checked="checked"' : '');
                }
                break;
        }
    }
    public function _startFieldset($configOptions)
    {
        $defaultOptions = array(
            'title' => false,
            'icon' => false,
            'hide' => true,
            'onclick' => false
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        return $this->fetchTemplate('core/components/fieldset/start_fieldset.tpl', array(), $configOptions);
    }
    public function _endFieldset()
    {
        return $this->fetchTemplate('core/components/fieldset/end_fieldset.tpl');
    }
    public function _displayAjaxSelectMultiple($configOptions)
    {
        $defaultOptions = array(
            'remoteurl' => false,
            'limit' => 50,
            'limitincrement' => 20,
            'remoteparams' => false,
            'tips' => false,
            'triggeronliclick' => true,
            'displaymore' => true,
            'idcolumn' => '',
            'namecolumn' => ''
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $vars = array(
            'index_column' => (isset($configOptions['namecolumn']) && isset($configOptions['idcolumn']) && !empty($configOptions['namecolumn']) && !empty($configOptions['idcolumn'])),
        );
        return $this->fetchTemplate('core/components/ajax_select_multiple.tpl', $vars, $configOptions);
    }
    public function _displayInputActive($configOptions)
    {
        $defaultOptions = array(
            'defaultvalue' => false,
            'tips' => false,
            'onclick' => false,
            'on_label' => false,
            'off_label' => false,
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $vars = array(
            'selected_on' => $this->_retrieveFormValue('radio', $configOptions['key_active'], $configOptions['key_db'], $configOptions['obj'], $configOptions['defaultvalue'], 1),
            'selected_off' => $this->_retrieveFormValue('radio', $configOptions['key_active'], $configOptions['key_db'], $configOptions['obj'], $configOptions['defaultvalue'], 0),
        );
        return $this->fetchTemplate('core/components/input_active.tpl', $vars, $configOptions);
    }
    public function _displayInputColor($configOptions)
    {
        $defaultOptions = array(
            'size' => '60px',
            'defaultvalue' => false,
            'tips' => false
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $vars = array(
            'current_value' => $this->_retrieveFormValue('text', $configOptions['key'], false, $configOptions['obj'], $configOptions['defaultvalue']),
        );
        $this->_initColorPickerAtEnd = true;
        return $this->fetchTemplate('core/components/input_color.tpl', $vars, $configOptions);
    }
    public function _displayInputFileLang($configOptions)
    {
        $defaultOptions = array(
            'plupload' => true,
            'filetype' => 'gif,jpg,png,jpeg',
            'tips' => false,
            'extend' => false,
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $flag_key = $this->getKeyForLanguageFlags();
        $flags = $this->displayPMFlags($flag_key);
        $vars = array(
            'is_image' => preg_match('/jpg|jpeg|gif|bmp|png/i', $configOptions['filetype']),
            'pm_flags' => $flags,
            'flag_key' => $flag_key,
            'file_location_dir' => dirname(__FILE__) . $configOptions['destination'],
        );
        return $this->fetchTemplate('core/components/input_file_lang.tpl', $vars, $configOptions);
    }
    public function _displayInlineUploadFile($configOptions)
    {
        $defaultOptions = array(
            'plupload' => true,
            'filetype' => 'gif,jpg,png,jpeg',
            'tips' => false,
            'extend' => false,
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $flags = $this->displayPMFlags();
        $vars = array(
            'is_image' => preg_match('/jpg|jpeg|gif|bmp|png/i', $configOptions['filetype']),
            'pm_flags' => $flags,
            'file_location_dir' => dirname(__FILE__) . $configOptions['destination'],
        );
        return $this->fetchTemplate('core/components/input_inline_file_lang.tpl', $vars, $configOptions);
    }
    protected function _displayInputSlider($configOptions)
    {
        $defaultOptions = array(
            'minvalue' => 0,
            'maxvalue' => 100,
            'suffix' => '%',
            'size' => '250px',
            'defaultvalue' => 0,
            'tips' => false
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $vars = array(
            'current_value' => $this->_retrieveFormValue('text', $configOptions['key'], false, $configOptions['obj'], $configOptions['defaultvalue']),
        );
        $this->_html .= $this->fetchTemplate('core/components/slider.tpl', $vars, $configOptions);
    }
    private function _parseOptions($defaultOptions = array(), $options = array())
    {
        if (self::_isFilledArray($options)) {
            $options = array_change_key_case($options, CASE_LOWER);
        }
        if (isset($options['tips']) && !empty($options['tips'])) {
            $options['tips'] = htmlentities($options['tips'], ENT_QUOTES, 'UTF-8');
        }
        if (self::_isFilledArray($defaultOptions)) {
            $defaultOptions = array_change_key_case($defaultOptions, CASE_LOWER);
            foreach (array_keys($defaultOptions) as $option_name) {
                if (!isset($options[$option_name])) {
                    $options[$option_name] = $defaultOptions[$option_name];
                }
            }
        }
        return $options;
    }
    public function _displayInputText($configOptions)
    {
        $defaultOptions = array(
            'type' => 'text',
            'size' => '150px',
            'defaultvalue' => false,
            'min' => false,
            'max' => false,
            'maxlength' => false,
            'onkeyup' => false,
            'onchange' => false,
            'required' => false,
            'tips' => false,
            'placeholder' => false,
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $vars = array(
            'current_value' => $this->_retrieveFormValue('text', $configOptions['key'], false, $configOptions['obj'], $configOptions['defaultvalue']),
        );
        return $this->fetchTemplate('core/components/input_text.tpl', $vars, $configOptions);
    }
    public function _displayInputTextLang($configOptions)
    {
        $defaultOptions = array(
            'size' => '150px',
            'type' => 'text',
            'min' => false,
            'max' => false,
            'maxlength' => false,
            'onkeyup' => false,
            'onchange' => false,
            'required' => false,
            'tips' => false,
            'placeholder' => false,
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $current_value = array();
        foreach ($this->_languages as $language) {
            $current_value[$language['id_lang']] = $this->_retrieveFormValue('text', $configOptions['key'] . '_' . $language['id_lang'], $configOptions['key'], $configOptions['obj'], false, false, $language['id_lang']);
        }
        $vars = array(
            'current_value' => $current_value,
            'pm_flags' => $this->displayPMFlags(),
        );
        return $this->fetchTemplate('core/components/input_text_lang.tpl', $vars, $configOptions);
    }
    public function _displayRichTextareaLang($configOptions)
    {
        $defaultOptions = array(
            'size' => '100%',
            'min' => false,
            'max' => false,
            'maxlength' => false,
            'onkeyup' => false,
            'onchange' => false,
            'required' => false,
            'tips' => false
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $current_value = array();
        foreach ($this->_languages as $language) {
            $current_value[$language['id_lang']] = $this->_retrieveFormValue('text', $configOptions['key'] . '_' . $language['id_lang'], $configOptions['key'], $configOptions['obj'], false, false, $language['id_lang']);
        }
        $vars = array(
            'current_value' => $current_value,
            'pm_flags' => $this->displayPMFlags(false, 'tinyMceFlags'),
        );
        $this->_initTinyMceAtEnd = true;
        return $this->fetchTemplate('core/components/rich_textarea_lang.tpl', $vars, $configOptions);
    }
    public function _displaySelect($configOptions)
    {
        $defaultOptions = array(
            'size' => '200px',
            'defaultvalue' => false,
            'options' => array(),
            'onchange' => false,
            'tips' => false
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $selected_attr = array();
        foreach (array_keys($configOptions['options']) as $value) {
            $selected_attr[$value] = $this->_retrieveFormValue('select', $configOptions['key'], false, $configOptions['obj'], '0', $value);
        }
        $vars = array(
            'selected_attr' => $selected_attr,
        );
        return $this->fetchTemplate('core/components/select.tpl', $vars, $configOptions);
    }
    public function _displayCategoryTree($configOptions)
    {
        $defaultOptions = array(
            'input_name' => 'categoryBox',
            'selected_cat' => array(0),
            'use_radio' => false,
            'category_root_id' => Category::getRootCategory()->id
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        $selectedCat = $this->getCategoryInformations(Tools::getValue('categoryBox', $configOptions['selected_cat']), $this->_default_language, $configOptions['input_name'], $configOptions['use_radio']);
        $vars = array(
            'category_tree' => $this->_renderAdminCategorieTree($selectedCat, $configOptions['input_name'], $configOptions['use_radio'], $configOptions['category_root_id']),
        );
        return $this->fetchTemplate('core/components/category_tree/global.tpl', $vars, $configOptions);
    }
    private static function getCategoryInformations($ids_category, $id_lang = null)
    {
        if ($id_lang === null) {
            $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        }
        if (!self::_isFilledArray($ids_category)) {
            return array();
        }
        $categories = array();
        if (isset($ids_category[0]['id_category'])) {
            $ids_category_tmp = array();
            foreach ($ids_category as $cat) {
                $ids_category_tmp[] = $cat['id_category'];
            }
            $ids_category = $ids_category_tmp;
        } elseif (is_object($ids_category[0]) && isset($ids_category[0]->id_category)) {
            $ids_category_tmp = array();
            foreach ($ids_category as $cat) {
                $ids_category_tmp[] = $cat->id_category;
            }
            $ids_category = $ids_category_tmp;
        }
        foreach ($ids_category as $idCat) {
            if (empty($idCat)) {
                unset($ids_category);
            }
        }
        if (self::_isFilledArray($ids_category)) {
            $results = Db::getInstance()->ExecuteS('
                SELECT c.`id_category`, cl.`name`, cl.`link_rewrite`, cl.`id_lang`
                FROM `'._DB_PREFIX_.'category` c
                LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl').')
                ' . Shop::addSqlAssociation('category', 'cl') . '
                WHERE cl.`id_lang` = '.(int)$id_lang.'
                AND c.`id_category` IN ('.implode(',', array_map('intval', $ids_category)).')');
            foreach ($results as $category) {
                $categories[$category['id_category']] = $category;
            }
        }
        return $categories;
    }
    protected function getCategoryTreeForSelect()
    {
        $categoryList = Category::getCategories((int)$this->context->language->id);
        $categorySelect = $categoryParentSelect = $alreadyAdd = array();
        $rootCategoryId = Configuration::get('PS_ROOT_CATEGORY');
        foreach ($categoryList as $shopCategory) {
            foreach ($shopCategory as $idCategory => $categoryInformations) {
                if ($rootCategoryId == $idCategory) {
                    continue;
                }
                $categoryParentSelect[$categoryInformations['infos']['id_parent']][$idCategory] = str_repeat('&#150 ', $categoryInformations['infos']['level_depth'] - 1) . $categoryInformations['infos']['name'];
            }
        }
        foreach ($categoryList as $shopCategory) {
            foreach ($shopCategory as $idCategory => $categoryInformations) {
                if ($rootCategoryId == $idCategory || in_array($idCategory, $alreadyAdd)) {
                    continue;
                }
                $categorySelect[$idCategory] = str_repeat('&#150 ', $categoryInformations['infos']['level_depth'] - 1) . $categoryInformations['infos']['name'];
                if (isset($categoryParentSelect[$idCategory])) {
                    foreach ($categoryParentSelect[$idCategory] as $idCategoryChild => $categoryLabel) {
                        $categorySelect[$idCategoryChild] = $categoryLabel;
                        $alreadyAdd[] = $idCategoryChild;
                    }
                }
            }
        }
        return $categorySelect;
    }
    private function _renderAdminCategorieTree($selected_cat = array(), $input_name = 'categoryBox', $use_radio = false, $category_root_id = 1)
    {
        if (!$use_radio) {
            $input_name = $input_name.'[]';
        }
        $hidden_selected_categories = array();
        $root_is_selected = false;
        foreach ($selected_cat as $cat) {
            if (self::_isFilledArray($cat)) {
                if ($cat['id_category'] != $category_root_id) {
                    $hidden_selected_categories[] = $cat['id_category'];
                } elseif ($cat['id_category'] == $category_root_id) {
                    $root_is_selected = true;
                }
            } else {
                if ($cat != $category_root_id) {
                    $hidden_selected_categories[] = $cat;
                } else {
                    $root_is_selected = true;
                }
            }
        }
        $root_category = new Category($category_root_id, $this->_default_language);
        $root_category_name = $root_category->name;
        if (self::_isFilledArray($selected_cat)) {
            if (isset($selected_cat[0])) {
                $selected_cat_js = implode(',', $selected_cat);
            } else {
                $selected_cat_js = implode(',', array_keys($selected_cat));
            }
        } else {
            $selected_cat_js = '';
        }
        $input_selector_value = str_replace(']', '', str_replace('[', '', $input_name));
        $vars = array(
            'input_name' => $input_name,
            'hidden_selected_categories' => $hidden_selected_categories,
            'selected_cat_js' => $selected_cat_js,
            'category_root_id' => (int)$category_root_id,
            'root_category_name' => $root_category_name,
            'input_selector_value' => $input_selector_value,
            'use_radio' => $use_radio,
            'root_is_selected' => $root_is_selected,
        );
        return $this->fetchTemplate('core/components/category_tree/tree.tpl', $vars);
    }
    protected function _uploadImageLang(&$obj, $key, $path, $add_to_filename = false)
    {
        $ext = false;
        $update = false;
        $errors = array();
        foreach ($this->_languages as $language) {
            $file = false;
            if (Tools::getIsset('unlink_' . $key . '_' . $language['id_lang']) and Tools::getValue('unlink_' . $key . '_' . $language['id_lang']) and isset($obj->{$key}[$language['id_lang']]) and $obj->{$key}[$language['id_lang']]) {
                @unlink(_PS_ROOT_DIR_ . $path . $obj->{$key}[$language['id_lang']]);
                $obj->{$key}[$language['id_lang']] = '';
                $update = true;
            } else {
                if (isset($_FILES[$key . '_' . $language['id_lang']]['tmp_name']) and $_FILES[$key . '_' . $language['id_lang']]['tmp_name'] != null) {
                    $file = $_FILES[$key . '_' . $language['id_lang']];
                } elseif ((! isset($obj->{$key}[$language['id_lang']]) || (isset($obj->{$key}[$language['id_lang']]) && !$obj->{$key}[$language['id_lang']])) && isset($_FILES[$key . '_' . $this->_default_language]['tmp_name']) and $_FILES[$key . '_' . $this->_default_language]['tmp_name'] != null) {
                    $file = $_FILES[$key . '_' . $this->_default_language];
                }
                if ($file) {
                    if (!is_dir(_PS_ROOT_DIR_ . $path)) {
                        mkdir(_PS_ROOT_DIR_ . $path, 0777, true);
                    }
                    if (!is_dir(_PS_ROOT_DIR_ . $path . $language['iso_code'] . '/')) {
                        mkdir(_PS_ROOT_DIR_ . $path . $language['iso_code'] . '/', 0777, true);
                    }
                    $ext = $this->getFileExtension($file['name']);
                    if (isset($obj->{$key}[$language['id_lang']]) && $obj->{$key}[$language['id_lang']]) {
                        @unlink(_PS_ROOT_DIR_ . $path . $obj->{$key}[$language['id_lang']]);
                    }
                    if (!in_array($ext, $this->allowFileExtension) || ! getimagesize($file['tmp_name']) || ! copy($file['tmp_name'], _PS_ROOT_DIR_ . $path . $language['iso_code'] . '/' . $obj->id . ($add_to_filename ? $add_to_filename : '') . '.' . $ext)) {
                        $errors[] = Tools::displayError('An error occured during the image upload');
                    }
                    if (!sizeof($errors)) {
                        $obj->{$key}[$language['id_lang']] = $language['iso_code'] . '/' . $obj->id . ($add_to_filename ? $add_to_filename : '') . '.' . $ext;
                        $update = true;
                    }
                }
            }
        }
        if (sizeof($errors)) {
            return $errors;
        }
        return $update;
    }
    protected function _getGradientFromArray($key)
    {
        $value = Tools::getValue($key);
        if (is_array($value)) {
            return $value[0] . (Tools::getValue($key . '_gradient') && isset($value[1]) && $value[1] ? self::$_gradient_separator . $value[1] : '');
        } else {
            return $value;
        }
    }
    private static function getAllSubCategories($id_cat, $id_lang, $all_sub_categories = array())
    {
        $category = new Category((int)$id_cat);
        $sub_cats = $category->getSubcategories($id_lang);
        if (count($sub_cats) > 0) {
            foreach ($sub_cats as $sub_cat) {
                $all_sub_categories[] = $sub_cat['id_category'];
                self::getAllSubCategories($sub_cat['id_category'], $id_lang, $all_sub_categories);
            }
        }
        return $all_sub_categories;
    }
    public static function getChildrenWithNbSelectedSubCat($id_parent, $selectedCat, $id_lang)
    {
        $selectedCat = explode(',', str_replace(' ', '', $selectedCat));
        if (!is_array($selectedCat)) {
            $selectedCat = array();
        }
        return Db::getInstance()->ExecuteS('
                SELECT c.`id_category`, c.`level_depth`, CONCAT(cl.`name`, " (ID: ", c.`id_category`, ")") as `name`, IF((
                SELECT COUNT(*)
                FROM `'._DB_PREFIX_.'category` c2
                WHERE c2.`id_parent` = c.`id_category`
        ) > 0, 1, 0) AS has_children, '.($selectedCat ? '(
                SELECT count(c3.`id_category`)
                FROM `'._DB_PREFIX_.'category` c3
                WHERE c3.`nleft` > c.`nleft`
                AND c3.`nright` < c.`nright`
                AND c3.`id_category`  IN ('.implode(',', array_map('intval', $selectedCat)).')
        )' : '0').' AS nbSelectedSubCat
                FROM `'._DB_PREFIX_.'category` c
                LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl').')
                ' . Shop::addSqlAssociation('category', 'cl') . '
                WHERE `id_lang` = '.(int)($id_lang).'
                AND c.`id_parent` = '.(int)($id_parent).'
                ORDER BY category_shop.`position` ASC');
    }
    protected function _loadCssJsLibrary($library)
    {
        $return = '';
        switch ($library) {
            case 'core':
                $return .= '<script type="text/javascript">
                    var _base_config_url = "' . $this->_base_config_url . '";
                    var PS_ALLOW_ACCENTED_CHARS_URL = '.(int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL').';
                    var _modulePath = "' . $this->_path . '";
                    var _id_employee = ' . ( int ) $this->context->cookie->id_employee . ';
                    var id_language = Number(' . $this->_default_language . ');
                    var baseAdminDir = "'. __PS_BASE_URI__.basename(_PS_ADMIN_DIR_).'/";
                    var iso_user = "'.$this->context->language->iso_code.'";
                    var lang_is_rtl = "'.(int)$this->context->language->is_rtl.'";
                </script>';
                $this->context->controller->addJqueryUI(array('ui.draggable', 'ui.droppable', 'ui.sortable', 'ui.widget', 'ui.dialog', 'ui.tabs', 'ui.progressbar'), '../../../../modules/'.$this->name.'/views/css/jquery-ui-theme');
                $this->context->controller->addJS($this->_path . 'views/js/adminCore.js');
                $this->context->controller->addJS($this->_path . 'views/js/admin.js');
                $this->context->controller->addCSS($this->_path . 'views/css/adminCore.css');
                $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
                break;
            case 'jquerytiptip':
                $this->context->controller->addJS($this->_path . 'views/js/jquery.tipTip.js');
                break;
            case 'jgrowl':
                $this->context->controller->addJS($this->_path . 'views/js/jGrowl/jquery.jgrowl_minimized.js');
                $this->context->controller->addCSS($this->_path . 'views/css/jGrowl/jquery.jgrowl.css');
                break;
            case 'multiselect':
                $this->context->controller->addJS($this->_path . 'views/js/multiselect/jquery.tmpl.1.1.1.js');
                $this->context->controller->addJS($this->_path . 'views/js/multiselect/jquery.blockUI.js');
                $this->context->controller->addJS($this->_path . 'views/js/multiselect/ui.multiselect.js');
                $this->context->controller->addCSS($this->_path . 'views/css/multiselect/ui.multiselect.css');
                break;
            case 'colorpicker':
                $this->context->controller->addJS($this->_path . 'views/js/colorpicker/colorpicker.js');
                $this->context->controller->addCSS($this->_path . 'views/css/colorpicker/colorpicker.css');
                break;
            case 'codemirrorcore':
                $this->context->controller->addJS($this->_path . 'views/js/codemirror/codemirror.js');
                $this->context->controller->addCSS($this->_path . 'views/css/codemirror/codemirror.css');
                $this->context->controller->addCSS($this->_path . 'views/css/codemirror/default.css');
                break;
            case 'codemirrorcss':
                $this->context->controller->addJS($this->_path . 'views/js/codemirror/css.js');
                break;
            case 'datatables':
                $this->context->controller->addJS($this->_path . 'views/js/datatables/jquery.dataTables.min.js');
                $this->context->controller->addCSS($this->_path . 'views/css/datatables/demo_table_jui.css');
                break;
            case 'tiny_mce':
                if (version_compare(_PS_VERSION_, '1.6.0.12', '>=')) {
                    $this->context->controller->addJS(__PS_BASE_URI__ . 'js/admin/tinymce.inc.js');
                } else {
                    $this->context->controller->addJS(__PS_BASE_URI__ . 'js/tinymce.inc.js');
                }
                $this->context->controller->addJS(__PS_BASE_URI__ . 'js/admin/tinymce.inc.js');
                $this->context->controller->addJS(__PS_BASE_URI__ . 'js/tiny_mce/tiny_mce.js');
                break;
            case 'chosen':
                $this->context->controller->addJqueryPlugin('chosen');
                break;
            case 'plupload':
                $this->context->controller->addJS($this->_path . 'views/js/plupload.full.min.js');
                break;
            case 'form':
                $this->context->controller->addJS($this->_path . 'views/js/jquery.form.js');
                break;
        }
        return $return;
    }
    protected function _loadCssJsLibraries()
    {
        $return = '';
        if (self::_isFilledArray($this->_css_js_to_load)) {
            foreach ($this->_css_js_to_load as $library) {
                $return .= $this->_loadCssJsLibrary($library);
            }
        }
        return $return;
    }
    private function _includeHTMLAtEnd()
    {
        $return = '';
        if ($this->_initTinyMceAtEnd) {
            $return .= $this->_initTinyMce();
        }
        if ($this->_initColorPickerAtEnd) {
            $return .= $this->_initColorPicker();
        }
        if ($this->_initBindFillSizeAtEnd) {
            $return .= $this->_initBindFillSize();
        }
        $return .= '<script type="text/javascript">$(\'.hideAfterLoad\').hide();</script>';
        $return .= $this->_html_at_end;
        return $return;
    }
    public function _addButton($configOptions)
    {
        $defaultOptions = array(
            'text' => '',
            'href' => '',
            'title' => '',
            'onclick' => false,
            'icon_class' => false,
            'class' => false,
            'rel' => false,
            'target' => false,
            'id' => false
        );
        $configOptions = $this->_parseOptions($defaultOptions, $configOptions);
        if (!$configOptions['id']) {
            $curId = 'button_' . uniqid(self::$_module_prefix . mt_rand());
        } else {
            $curId = $configOptions['id'];
        }
        $vars = array(
            'currentId' => $curId,
        );
        return $this->fetchTemplate('core/components/button.tpl', $vars, $configOptions);
    }
    public function _displaySubmit($value, $name)
    {
        $vars = array(
            'value' => $value,
            'name' => $name,
        );
        return $this->fetchTemplate('core/components/submit_button.tpl', $vars);
    }
    protected function _headerIframe()
    {
        $return = '';
        $assets = array();
        $backupHtml = $this->_html;
        $inline = $this->_loadCssJsLibraries();
        foreach ($this->context->controller->css_files as $cssUri => $media) {
            if (!preg_match('/gamification/i', $cssUri)) {
                $assets[] = '<link href="'.$cssUri.'" rel="stylesheet" type="text/css" media="'.$media.'" />';
            }
        }
        foreach ($this->context->controller->js_files as $jsUri) {
            if (!preg_match('#gamification|notifications\.js|help\.js#i', $jsUri)) {
                $assets[] = '<script type="text/javascript" src="'.$jsUri.'"></script>';
            }
        }
        $return = $backupHtml;
        $vars = array(
            'ps_version' => Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2),
            'ps_version_full' => str_replace('.', '', _PS_VERSION_),
            'assets' => $assets,
            'inline' => $inline,
        );
        $return .= $this->fetchTemplate('core/iframe/header.tpl', $vars);
        $return .= $inline;
        return $return;
    }
    protected function _footerIframe()
    {
        $vars = array(
            'debug_mode' => $this->_debug_mode,
            'html_at_end' => $this->_includeHTMLAtEnd(),
        );
        return $this->fetchTemplate('core/iframe/footer.tpl', $vars);
    }
    protected function _initDataTable($id_table, $returnHTML = false, $returnAsScript = false)
    {
        $vars = array(
            'idDataTable' => $id_table,
            'returnAsScript' => $returnAsScript,
        );
        $return = $this->fetchTemplate('core/components/datatable.tpl', $vars);
        if ($returnHTML) {
            return $return;
        }
        $this->_html .= $return;
    }
    protected function _initTinyMce()
    {
        $vars = array(
            'init_tinymce_iso' => (Tools::file_exists_cache(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $this->_iso_lang . '.js') ? $this->_iso_lang : 'en'),
            'init_tinymce_ad' => dirname($_SERVER["PHP_SELF"]),
            'init_tinymce_css_dir' => _THEME_CSS_DIR_,
        );
        return $this->fetchTemplate('core/init_tinymce.tpl', $vars);
    }
    protected function _initBindFillSize()
    {
        return $this->fetchTemplate('core/init_bind_fill_size.tpl');
    }
    protected function _initColorPicker()
    {
        return $this->fetchTemplate('core/init_color_picker.tpl');
    }
    protected function copyFromPost(&$destination, $destination_type = 'object', $data = false)
    {
        $this->_preCopyFromPost();
        $clearTempDirectory = false;
        if (!$data) {
            if (method_exists('Tools', 'getAllValues')) {
                $data = Tools::getAllValues();
            } else {
                $data = $_POST;
            }
        }
        foreach ($data as $key => $value) {
            if (preg_match('/_temp_file$/', $key) && $value) {
                $final_destination = dirname(__FILE__) . Tools::getValue($key . '_destination');
                $final_file = $final_destination . $value;
                $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . $value;
                if (self::_isRealFile($temp_file)) {
                    rename($temp_file, $final_file);
                }
                $key = preg_replace('/_temp_file$/', '', $key);
                if ($old_file = Tools::getValue($key . '_old_file')) {
                    if (self::_isRealFile($final_destination . Tools::getValue($key . '_old_file'))) {
                        @unlink($final_destination . Tools::getValue($key . '_old_file'));
                    }
                }
                $clearTempDirectory = true;
            } elseif (preg_match('/_unlink$/', $key)) {
                $key = preg_replace('/_unlink$/', '', $key);
                $final_file = dirname(__FILE__) . Tools::getValue($key . '_temp_file_destination') . Tools::getValue($key . '_temp_file');
                $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . Tools::getValue($key . '_temp_file');
                if (self::_isRealFile($final_file)) {
                    @unlink($final_file);
                }
                if (self::_isRealFile($temp_file)) {
                    @unlink($temp_file);
                }
                $value = '';
                $clearTempDirectory = true;
            } elseif (preg_match('/activestatus/', $key)) {
                $key = 'active';
            } elseif (preg_match('/height$|width$/i', $key)) {
                $value=trim($value);
                if (!Validate::isInt($value)) {
                    $value = '' ;
                    continue;
                }
                $unit = (Tools::getValue($key . '_unit') == 1?'px':'%');
                $value = $value.$unit ;
            } elseif (preg_match('/color/', $key)) {
                $value = $this->_getGradientFromArray($key);
            }
            if (key_exists($key, $destination)) {
                if ($destination_type == 'object') {
                    $destination->{$key} = $value;
                } else {
                    $destination[$key] = $value;
                }
            }
        }
        if ($destination_type == 'object') {
            $rules = call_user_func(array(get_class($destination), 'getValidationRules' ), get_class($destination));
            if (sizeof($rules['validateLang'])) {
                $languages = Language::getLanguages(false);
                foreach ($languages as $language) {
                    foreach (array_keys($rules['validateLang']) as $field) {
                        if ((isset($data[$field . '_' . (int)$language['id_lang'] . '_temp_file_lang'])
                        && $data[$field . '_' . (int)$language['id_lang'] . '_temp_file_lang'])
                        || (isset($data[$field . '_all_lang']) && !$destination->{$field}[(int)$language['id_lang']]
                        && $data[$field . '_all_lang']
                        && isset($data[$field . '_' . (int)$this->_default_language . '_temp_file_lang'])
                        && $data[$field . '_' . (int)$this->_default_language . '_temp_file_lang'])) {
                            if (isset($data[$field . '_all_lang'])
                            && $data[$field . '_all_lang']
                            && $language['id_lang'] != $this->_default_language) {
                                $key_default_language = $field . '_' . (int)$this->_default_language . '_temp_file_lang';
                                $old_file = $data[$key_default_language];
                                $new_temp_file_lang = uniqid(self::$_module_prefix . mt_rand()).'.'.self::_getFileExtension($data[$key_default_language]);
                            }
                            $key = $field . '_' . (int)$language['id_lang'] . '_temp_file_lang';
                            $final_destination = dirname(__FILE__) . Tools::getValue($key . '_destination_lang');
                            if (isset($data[$field . '_all_lang']) && $data[$field . '_all_lang'] && $language['id_lang'] != $this->_default_language) {
                                $final_file = $final_destination . $new_temp_file_lang;
                                $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . $old_file;
                            } else {
                                $final_file = $final_destination . Tools::getValue($key);
                                $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . Tools::getValue($key);
                            }
                            if (self::_isRealFile($temp_file)) {
                                copy($temp_file, $final_file);
                            }
                            $key = preg_replace('/_temp_file_lang$/', '', $key);
                            if ($old_file = Tools::getValue($key . '_old_file_lang')) {
                                if (self::_isRealFile($final_destination . Tools::getValue($key . '_old_file_lang'))) {
                                    @unlink($final_destination . Tools::getValue($key . '_old_file_lang'));
                                }
                            }
                            if (isset($data[$field . '_all_lang'])
                            && $data[$field . '_all_lang']
                            && $language['id_lang'] != $this->_default_language) {
                                $destination->{$field}[(int)$language['id_lang']] = $new_temp_file_lang;
                            } else {
                                $destination->{$field}[(int)$language['id_lang']] = Tools::getValue($field . '_' . (int)$language['id_lang'] . '_temp_file_lang');
                            }
                            $clearTempDirectory = true;
                        }
                        if (Tools::getIsset($field . '_' . (int)$language['id_lang'] . '_unlink_lang') && Tools::getValue($field . '_' . (int)$language['id_lang'] . '_unlink_lang')) {
                            $key = $field . '_' . (int)$language['id_lang'] . '_unlink_lang';
                            $key = preg_replace('/_unlink_lang$/', '', $key);
                            $final_file = dirname(__FILE__) . Tools::getValue($key . '_temp_file_lang_destination_lang') . Tools::getValue($key . '_old_file_lang');
                            $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . Tools::getValue($key . '_old_file_lang');
                            if (self::_isRealFile($final_file)) {
                                @unlink($final_file);
                            }
                            if (self::_isRealFile($temp_file)) {
                                @unlink($temp_file);
                            }
                            $destination->{$field}[(int)$language['id_lang']] = '';
                            $clearTempDirectory = true;
                        }
                        if (Tools::getIsset($field . '_' . (int)$language['id_lang'])) {
                            $destination->{$field}[(int)$language['id_lang']] = Tools::getValue($field . '_' . (int)$language['id_lang']);
                        }
                    }
                }
            }
        } else {
            $rules = call_user_func(array($destination['class_name'], 'getValidationRules'), $destination['class_name']);
            if (sizeof($rules['validateLang'])) {
                $languages = Language::getLanguages();
                foreach ($languages as $language) {
                    foreach (array_keys($rules['validateLang']) as $field) {
                        if (isset($data[$field . '_' . (int)$language['id_lang'] . '_temp_file_lang']) && Tools::getValue($field . '_' . (int)$language['id_lang'] . '_temp_file_lang')) {
                            $key = $field . '_' . (int)$language['id_lang'] . '_temp_file_lang';
                            $final_destination = dirname(__FILE__) . Tools::getValue($key . '_destination_lang');
                            $final_file = $final_destination . Tools::getValue($key);
                            $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . Tools::getValue($key);
                            if (self::_isRealFile($temp_file)) {
                                rename($temp_file, $final_file);
                            }
                            $key = preg_replace('/_temp_file_lang$/', '', $key);
                            if ($old_file = Tools::getValue($key . '_old_file_lang')) {
                                if (self::_isRealFile($final_destination . Tools::getValue($key . '_old_file_lang'))) {
                                    @unlink($final_destination . Tools::getValue($key . '_old_file_lang'));
                                }
                            }
                            $destination[$field][(int)$language['id_lang']] = Tools::getValue($field . '_' . (int)$language['id_lang'] . '_temp_file_lang');
                            $clearTempDirectory = true;
                        }
                        if (isset($destination[$field . '_' . (int)$language['id_lang'] . '_unlink_lang']) && Tools::getValue($field . '_' . (int)$language['id_lang'] . '_unlink_lang')) {
                            $key = $field . '_' . (int)$language['id_lang'] . '_unlink_lang';
                            $key = preg_replace('/_unlink_lang$/', '', $key);
                            $final_file = dirname(__FILE__) . Tools::getValue($key . '_temp_file_lang_destination_lang') . Tools::getValue($key . '_old_file_lang');
                            $temp_file = dirname(__FILE__) . $this->_temp_upload_dir . Tools::getValue($key . '_old_file_lang');
                            if (self::_isRealFile($final_file)) {
                                @unlink($final_file);
                            }
                            if (self::_isRealFile($temp_file)) {
                                @unlink($temp_file);
                            }
                            $destination[$field][(int)$language['id_lang']] = '';
                            $clearTempDirectory = true;
                        }
                        if (isset($destination[$field . '_' . (int)$language['id_lang']])) {
                            $destination[$field][(int)$language['id_lang']] = $destination[$field . '_' . (int)$language['id_lang']];
                        }
                    }
                }
            }
        }
        if ($clearTempDirectory) {
            $this->_clearDirectory(dirname(__FILE__) . $this->_temp_upload_dir);
        }
        $this->_postCopyFromPost(array('destination'=>$destination));
    }
    public static function _isFilledArray($array)
    {
        return ($array && is_array($array) && sizeof($array));
    }
    public static function shuffleArray(&$a)
    {
        if (is_array($a) && sizeof($a)) {
            $ks = array_keys($a);
            shuffle($ks);
            $new = array();
            foreach ($ks as $k) {
                $new[$k] = $a[$k];
            }
            $a = $new;
            return true;
        }
        return false;
    }
    public static function getDataSerialized($data, $type = 'base64')
    {
        if (is_array($data)) {
            return array_map($type . '_encode', array($data));
        } else {
            return current(array_map($type . '_encode', array($data)));
        }
    }
    public static function getDataUnserialized($data, $type = 'base64')
    {
        if (is_array($data)) {
            return array_map($type . '_decode', array($data));
        } else {
            return current(array_map($type . '_decode', array($data)));
        }
    }
    protected function _cleanOutput()
    {
        $this->_html = '';
        self::_cleanBuffer();
    }
    public static function _cleanBuffer()
    {
        if (ob_get_length() > 0) {
            ob_clean();
        }
    }
    protected function _echoOutput($die = false)
    {
        echo $this->_html;
        if ($die) {
            die();
        }
    }
    protected function _clearDirectory($dir)
    {
        if (!$dh = @opendir($dir)) {
            return;
        }
        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..' || $obj == 'index.php') {
                continue;
            }
            if (!@unlink($dir . '/' . $obj)) {
                $this->_clearDirectory($dir . '/' . $obj);
            }
        }
        closedir($dh);
    }
    public static function _isRealFile($filename)
    {
        return (Tools::file_exists_cache($filename) && ! is_dir($filename));
    }
    public function _getTplPath($tpl_name, $view = 'hook')
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return $this->getTemplatePath('views/templates/' . $view . '/1.7/' . $tpl_name);
        }
        return $this->getTemplatePath('views/templates/' . $view . '/' . $tpl_name);
    }
    protected static function hex2rgb($hexstr)
    {
        if (Tools::strlen($hexstr) < 7) {
            $hexstr = $hexstr.str_repeat(Tools::substr($hexstr, -1), 7-Tools::strlen($hexstr));
        }
        $int = hexdec($hexstr);
        return array(
            0 => 0xFF & ($int >> 0x10),
            1 => 0xFF & ($int >> 0x8),
            2 => 0xFF & $int
        );
    }
    protected static function tls2rgb($t, $l, $s)
    {
        if ($t<0) {
            $t = 360+$t;
        }
        if ($l<0) {
            $l = 0;
        }
        if ($s<0) {
            $s = 0;
        }
        if ($t>360) {
            $t = $t-360;
        }
        if ($l>255) {
            $l = 255;
        }
        if ($s>250) {
            $s = 250;
        }
        $l /= 255;
        $s /= 255;
        if ($l < 1/2) {
            $q = $l * (1 + $s);
        } elseif ($l >= 1/2) {
            $q = $l + $s - ($l * $s);
        }
        $p = 2 * $l - $q;
        $hk = $t / 360;
        $a = array(
            0 => $hk + 1/3,
            1 => $hk,
            2 => $hk - 1/3,
        );
        $z = array();
        foreach ($a as $k => &$tc) {
            if ($tc < 0) {
                $tc++;
            } elseif ($tc > 1) {
                $tc--;
            }
            if ($tc < 1/6) {
                $z[$k] = $p + (($q - $p) * 6 * $tc);
            } elseif ($tc >= 1/6 && $tc < 1/2) {
                $z[$k] = $q;
            } elseif ($tc >= 1/2 && $tc < 2/3) {
                $z[$k] = $p + (($q - $p) * 6 * (2/3 - $tc));
            } else {
                $z[$k] = $p;
            }
        }
        $z[0] = (int)round($z[0] * 255);
        $z[1] = (int)round($z[1] * 255);
        $z[2] = (int)round($z[2] * 255);
        return $z;
    }
    protected static function rgb2tls($r, $v, $b)
    {
        $max = max($r, $v, $b);
        $min = min($r, $v, $b);
        if ($max == $min) {
            $t = 0;
        }
        if ($max == $r) {
            @$t = 60 * (($v - $b) / ($max - $min));
        } elseif ($max == $v) {
            @$t = 60 * (($b - $r) / ($max - $min)) + 120;
        } elseif ($max == $b) {
            @$t = 60 * (($r - $v) / ($max - $min)) + 240;
        }
        $t = (int)round($t);
        $l = 1/2 * ($max + $min);
        $l2 = $l / 255;
        $l = (int)round($l);
        if ($max == $min) {
            $s = 0;
        } elseif ($l2 <= 1/2) {
            $s = ($max - $min) / (2*$l2);
        } elseif ($l2 > 1/2) {
            $s = ($max - $min) / (2 - 2*$l2);
        }
        $s = (int)round($s);
        if ($t<0) {
            $t = 360+$t;
        }
        if ($l<0) {
            $l = 0;
        }
        if ($s<0) {
            $s = 0;
        }
        if ($t>360) {
            $t = $t-360;
        }
        if ($l>255) {
            $l = 255;
        }
        if ($s>250) {
            $s = 250;
        }
        return array($t, $l , $s);
    }
    protected static function rgb2hex($r, $g, $b)
    {
        if (is_array($r) && sizeof($r) == 3) {
            list($r, $g, $b) = $r;
        }
        $r = (int)$r;
        $g = (int)$g;
        $b = (int)$b;
        $r = dechex($r<0?0:($r>255?255:$r));
        $g = dechex($g<0?0:($g>255?255:$g));
        $b = dechex($b<0?0:($b>255?255:$b));
        $color = (Tools::strlen($r) < 2?'0':'').$r;
        $color .= (Tools::strlen($g) < 2?'0':'').$g;
        $color .= (Tools::strlen($b) < 2?'0':'').$b;
        return '#'.$color;
    }
    public static function _getCssRule($selector, $rule, $value, $is_important = false, $params = false, &$css_rules = array())
    {
        $css_rule = '';
        switch ($rule) {
            case 'width':
                $value ? $value : 0;
                $css_rule .= ' width:' . $value . ($params && isset($params['suffix']) ? $params['suffix'] : 'px') . ($is_important ? '!important' : '') . ';';
                break;
            case 'height':
                $value ? $value : 0;
                $css_rule .= ' height:' . $value . ($params && isset($params['suffix']) ? $params['suffix'] : 'px') . ($is_important ? '!important' : '') . ';';
                break;
            case 'bg_gradient':
                $val = explode(self::$_gradient_separator, $value);
                if (isset($val[1]) && $val[1]) {
                    $color1 = htmlentities($val[0], ENT_COMPAT, 'UTF-8');
                    $color2 = htmlentities($val[1], ENT_COMPAT, 'UTF-8');
                } elseif (isset($val[0]) && $val[0]) {
                    $color1 = htmlentities($val[0], ENT_COMPAT, 'UTF-8');
                }
                if (!isset($color1)) {
                    return '';
                }
                $css_rule .= 'background:' . $color1 . ($is_important ? '!important' : '') . ';';
                if (isset($color2)) {
                    $css_rule .= 'background: -webkit-gradient(linear, 0 0, 0 bottom, from(' . $color1 . '), to(' . $color2 . '))' . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'background: -webkit-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'background: -moz-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'background: -ms-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'background: -o-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'background: linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                    $css_rule .= '-pie-background: linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                }
                break;
            case 'css3button':
                if (!trim($value)) {
                    return '';
                }
                $base_color_hex = $value;
                $base_color_rgb = self::hex2rgb($base_color_hex);
                $base_color_tls = self::rgb2tls($base_color_rgb[0], $base_color_rgb[1], $base_color_rgb[2]);
                $border_color_rgb = self::tls2rgb((int)$base_color_tls[0], (int)$base_color_tls[1]-49, (int)$base_color_tls[2]-16);
                $top0_color_rgb = self::tls2rgb((int)$base_color_tls[0], (int)$base_color_tls[1]+42, (int)$base_color_tls[2]-1);
                $bottom50_color_rgb = self::tls2rgb((int)$base_color_tls[0], (int)$base_color_tls[1]-13, (int)$base_color_tls[2]+18);
                $bottom100_color_rgb = self::tls2rgb((int)$base_color_tls[0], (int)$base_color_tls[1]-10, (int)$base_color_tls[2]+15);
                $boxshadow_color_rgb = self::tls2rgb((int)$base_color_tls[0], (int)$base_color_tls[1]+19, (int)$base_color_tls[2]-29);
                $border_color_hex = self::rgb2hex($border_color_rgb[0], $border_color_rgb[1], $border_color_rgb[2]);
                $top0_color_hex = self::rgb2hex($top0_color_rgb[0], $top0_color_rgb[1], $top0_color_rgb[2]);
                $bottom50_color_hex = self::rgb2hex($bottom50_color_rgb[0], $bottom50_color_rgb[1], $bottom50_color_rgb[2]);
                $bottom100_color_hex = self::rgb2hex($bottom100_color_rgb[0], $bottom100_color_rgb[1], $bottom100_color_rgb[2]);
                $boxshadow_color_hex = self::rgb2hex($boxshadow_color_rgb[0], $boxshadow_color_rgb[1], $boxshadow_color_rgb[2]);
                $css_rule .= 'border: 1px '.$border_color_hex.' solid;'."\n";
                $css_rule .= '-webkit-box-shadow: 0px 0px 0px #aaa, inset 0 5px 10px '.$boxshadow_color_hex.';'."\n";
                $css_rule .= '-moz-box-shadow: 0px 0px 0px #aaa, inset 0 5px 10px '.$boxshadow_color_hex.';'."\n";
                $css_rule .= 'box-shadow: 0px 0px 0px #aaa, inset 0 5px 10px '.$boxshadow_color_hex.';'."\n";
                $css_rule .= 'background-color: '.$base_color_hex.'; /* Old browsers */'."\n";
                $css_rule .= 'background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0%, '.$top0_color_hex.'), color-stop(50%, '.$base_color_hex.'), color-stop(50%, '.$bottom50_color_hex.'), color-stop(100%, '.$bottom100_color_hex.')); /* Chrome,Safari4+ */'."\n";
                $css_rule .= 'background-image: -webkit-linear-gradient(top, '.$top0_color_hex.' 0%, '.$base_color_hex.' 50%, '.$bottom50_color_hex.' 50%, '.$bottom100_color_hex.' 100%); /* Chrome10+,Safari5.1+ */'."\n";
                $css_rule .= 'background-image: -moz-linear-gradient(top, '.$top0_color_hex.' 0%, '.$base_color_hex.' 50%, '.$bottom50_color_hex.' 50%, '.$bottom100_color_hex.' 100%); /* FF3.6+ */'."\n";
                $css_rule .= 'background-image: -ms-linear-gradient(top, '.$top0_color_hex.' 0%, '.$base_color_hex.' 50%, '.$bottom50_color_hex.' 50%, '.$bottom100_color_hex.' 100%); /* IE10+ */'."\n";
                $css_rule .= 'background-image: -o-linear-gradient(top, '.$top0_color_hex.' 0%, '.$base_color_hex.' 50%, '.$bottom50_color_hex.' 50%, '.$bottom100_color_hex.' 100%); /* Opera 11.10+ */'."\n";
                $css_rule .= 'background-image: linear-gradient(top, '.$top0_color_hex.' 0%, '.$base_color_hex.' 50%, '.$bottom50_color_hex.' 50%, '.$bottom100_color_hex.' 100%); /* W3C */'."\n";
                $css_rule .= 'filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=\''.$top0_color_hex.'\', endColorstr=\''.$base_color_hex.'\'); /* IE7,8,9 */';
                break;
            case 'bg_image':
                $css_rule .= 'background-image: url(' . $value . ')' . ($is_important ? '!important' : '') . ';';
                break;
            case 'float':
                $css_rule .= 'float:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'position':
                $css_rule .= 'position:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'color':
                $css_rule .= 'color:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'font_size':
                $value ? $value : 0;
                $css_rule .= 'font-size:' . $value . ($params && isset($params['suffix']) ? $params['suffix'] : 'px') . ($is_important ? '!important' : '') . ';';
                break;
            case 'font_style':
                $value ? $value : 'none';
                $css_rule .= 'font-style:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'font_weight':
                $value ? $value : 'none';
                $css_rule .= 'font-weight:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'text_decoration':
                $value ? $value : 'none';
                $css_rule .= 'text-decoration:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'line_height':
                $value ? $value : 0;
                $css_rule .= 'line-height:' . $value . ($params && isset($params['suffix']) ? $params['suffix'] : 'px') . ($is_important ? '!important' : '') . ';';
                break;
            case 'text_align':
                $value ? $value : 'none';
                $css_rule .= 'text-align:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'border':
                if ($value == 'none') {
                    $css_rule .= 'border:none!important;';
                } else {
                    $val = explode(self::$_border_separator, $value);
                    if (isset($val[5]) && $val[5]) {
                        $top    = htmlentities(str_replace('px', '', $val[0]), ENT_COMPAT, 'UTF-8');
                        $right    = htmlentities(str_replace('px', '', $val[1]), ENT_COMPAT, 'UTF-8');
                        $bottom    = htmlentities(str_replace('px', '', $val[2]), ENT_COMPAT, 'UTF-8');
                        $left    = htmlentities(str_replace('px', '', $val[3]), ENT_COMPAT, 'UTF-8');
                        $style    = htmlentities(str_replace('px', '', $val[4]), ENT_COMPAT, 'UTF-8');
                        $color    = htmlentities(str_replace('px', '', $val[5]), ENT_COMPAT, 'UTF-8');
                    } else {
                        return '';
                    }
                    $css_rule .= 'border-top:'   . $top . ($top ? ($params && isset($params['suffix']) ? $params['suffix'] : 'px'):'') . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'border-right:'  . $right . ($right ? ($params && isset($params['suffix']) ? $params['suffix'] : 'px'):'') . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'border-bottom:' . $bottom . ($bottom ? ($params && isset($params['suffix']) ? $params['suffix'] : 'px'):'') . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'border-left:'  . $left .  ($left ? ($params && isset($params['suffix']) ? $params['suffix'] : 'px'):'') . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'border-style:' . $style . ($is_important ? '!important' : '') . ';';
                    $css_rule .= 'border-color:' . $color . ($is_important ? '!important' : '') . ';';
                }
                break;
            case 'text_transform':
                $css_rule .= 'text-transform:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'border_size':
                $css_rule .= 'border-size:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'border_radius':
                $css_rule .= '-webkit-border-radius:' . $value . ($is_important ? '!important' : '') . ';';
                $css_rule .= '-moz-border-radius:' . $value . ($is_important ? '!important' : '') . ';';
                $css_rule .= 'border-radius:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'shadow':
                if ($value == 'none') {
                    $css_rule .= '-webkit-box-shadow:none!important;';
                    $css_rule .= '-moz-box-shadow:none!important;';
                    $css_rule .= 'box-shadow:none!important;';
                } else {
                    $val = explode(self::$_shadow_separator, $value);
                    $css_rule .= '-webkit-box-shadow:' . $val[0] .' '. $val[1] .' '. $val[2] .' '. $val[3].($is_important ? '!important' : '') . ';';
                    $css_rule .= '-moz-box-shadow:' . $val[0] .' '. $val[1] .' '. $val[2] .' '. $val[3].($is_important ? '!important' : '') . ';';
                    $css_rule .= 'box-shadow:' . $val[0] .' '. $val[1] .' '. $val[2] .' '. $val[3].($is_important ? '!important' : '') . ';';
                }
                break;
            case 'margin':
                $css_rule .= 'margin:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'padding':
                $css_rule .= 'padding:' . $value . ($is_important ? '!important' : '') . ';';
                break;
            case 'opacity':
                $css_rule .= '-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=' . $value . ')"';
                $css_rule .= 'filter: alpha(opacity=' . $value . ')' . ($is_important ? '!important' : '') . ';';
                $css_rule .= '-khtml-opacity:' . ($value / 100) . ($is_important ? '!important' : '') . ';';
                $css_rule .= '-moz-opacity:' . ($value / 100) . ($is_important ? '!important' : '') . ';';
                $css_rule .= 'opacity:' . ($value / 100) . ($is_important ? '!important' : '') . ';';
                break;
            case 'custom':
                $css_rule .= $value;
                break;
        }
        if (!isset($css_rules[$selector])) {
            $css_rules[$selector] = array();
        }
        $css_rules[$selector][] = $css_rule;
        return $css_rules;
    }
    protected function getKeyForLanguageFlags()
    {
        return uniqid(self::$_module_prefix . mt_rand());
    }
    protected function displayPMFlags($key = false, $class = false)
    {
        if (!$key) {
            $key = $this->getKeyForLanguageFlags();
        }
        $vars = array(
            'flag_key' => $key,
            'class' => $class,
        );
        $return = $this->fetchTemplate('core/flags.tpl', $vars);
        return $return;
    }
    public function _displayTabsPanel($params)
    {
        $this->_html .= '<div id="'.$params['id_panel'].'">';
        $this->_html .= '<ul style="height: 30px;">';
        foreach ($params['tabs'] as $id_tab => $tab) {
            $label = '';
            if (isset($tab['img']) && $tab['img']) {
                $label .= '<img src="'.$tab['img'].'" alt="'.$tab['label'].'" title="'.$tab['label'].'" /> ';
            }
            $label .= $tab['label'];
            if (isset($tab['url']) && $tab['url']) {
                $href = $tab['url'];
            } elseif (isset($tab['funcs']) && $tab['funcs']) {
                $href = '#tab-'.$params['id_panel'].'-'.$id_tab;
            } else {
                continue;
            }
            $this->_html .= '<li><a href="'.$href.'"><span>'.$label.'</span></a></li>';
        }
        $this->_html .= '</ul>';
        foreach ($params['tabs'] as $id_tab => $tab) {
            if (isset($tab['funcs']) && $tab['funcs']) {
                $this->_html .= '<div id="tab-'.$params['id_panel'].'-'.$id_tab.'">';
                if (self::_isFilledArray($tab['funcs'])) {
                    foreach ($tab['funcs'] as $func) {
                        call_user_func(array($this, $func));
                    }
                } elseif (!is_array($tab['funcs'])) {
                    call_user_func(array($this, $tab['funcs']));
                }
                $this->_html .= '</div>';
            }
        }
        $this->_html .= '</div>';
        $this->_html .= '<script type="text/javascript">
            $(document).ready(function() {
                $("#'.$params['id_panel'].'").tabs({cache:true});
            });
        </script>';
    }
    public static function _retroValidateController($obj)
    {
        $error_field = '';
        $error_field_lang = '';
        try {
            $error_field = $obj->validateFields(false, true);
        } catch (Exception $e) {
        }
        if ($error_field !== true) {
            return array($error_field);
        }
        try {
            $error_field_lang = $obj->validateFieldsLang(false, true);
        } catch (Exception $e) {
        }
        if ($error_field_lang !== true) {
            return array($error_field_lang);
        }
        return array();
    }
    public static function pregQuoteSql($str)
    {
        return preg_replace('#([.\+*?^$()\[\]{}=!<>|:-])#', '\\\\\\\\\\\${1}', $str);
    }
    public static function _changeTimeLimit($time)
    {
        if (!ini_get('safe_mode')) {
            if (function_exists('set_time_limit') && (ini_get('max_execution_time') < $time || $time === 0)) {
                set_time_limit($time);
            }
        }
    }
    protected static function _getNbDaysModuleUsage()
    {
        $sql = 'SELECT DATEDIFF(NOW(),date_add)
                FROM '._DB_PREFIX_.'configuration
                WHERE name = \''.pSQL('PM_'.self::$_module_prefix.'_LAST_VERSION').'\'
                ORDER BY date_add ASC';
        return (int)Db::getInstance()->getValue($sql);
    }
    protected function _getModuleConfiguration()
    {
        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $allShopConfig = Configuration::getMultiShopValues('PM_' . self::$_module_prefix . '_CONF');
            if (!isset($allShopConfig[(int)$this->context->shop->id])) {
                $oldConf = Configuration::get('PM_' . self::$_module_prefix . '_CONF');
                if (!empty($oldConf)) {
                    $oldConf = Tools::jsonDecode($oldConf, true);
                    if ($oldConf != false) {
                        Configuration::updateValue('PM_' . self::$_module_prefix . '_CONF', Tools::jsonEncode($oldConf));
                    }
                } else {
                    Configuration::updateValue('PM_' . self::$_module_prefix . '_CONF', Tools::jsonEncode($this->_defaultConfiguration));
                }
            }
            $conf = Configuration::get('PM_' . self::$_module_prefix . '_CONF');
            return Tools::jsonDecode($conf, true);
        } else {
            return $this->_defaultConfiguration;
        }
    }
    public static function getModuleConfigurationStatic($idShop = null)
    {
        $conf = Configuration::get('PM_' . self::$_module_prefix . '_CONF', null, null, $idShop);
        if (!empty($conf)) {
            return Tools::jsonDecode($conf, true);
        } else {
            return array();
        }
    }
    protected function _setModuleConfiguration($newConf)
    {
        Configuration::updateValue('PM_' . self::$_module_prefix . '_CONF', Tools::jsonEncode($newConf));
    }
    protected function _setDefaultConfiguration()
    {
        if (!is_array($this->_getModuleConfiguration()) || !sizeof($this->_getModuleConfiguration())) {
            Configuration::updateValue('PM_' . self::$_module_prefix . '_CONF', Tools::jsonEncode($this->_defaultConfiguration));
        }
        return true;
    }
    public function getCurrentCustomerGroupId()
    {
        $id_group = (int)Configuration::get('PS_UNIDENTIFIED_GROUP');
        if (Validate::isLoadedObject($this->context->customer)) {
            $id_group = (int)$this->context->customer->id_default_group;
        }
        return $id_group;
    }
    public static function _getSmartyVarValue($varName)
    {
        $smarty = Context::getContext()->smarty;
        if (method_exists($smarty, 'getTemplateVars')) {
            return $smarty->getTemplateVars($varName);
        } elseif (method_exists($smarty, 'get_template_vars')) {
            return $smarty->get_template_vars($varName);
        }
        return false;
    }
    protected function _onBackOffice()
    {
        if (isset($this->context->cookie->id_employee) && Validate::isUnsignedId($this->context->cookie->id_employee)) {
            return true;
        }
        return false;
    }
    public static function arrayMapRecursive($fn, $arr)
    {
        if (!is_array($arr)) {
            return array();
        }
        $rarr = array();
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $rarr[$k] = self::arrayMapRecursive($fn, $v);
                if (!count($rarr[$k])) {
                    unset($rarr[$k]);
                }
            } else {
                if (preg_match('#~#', $v)) {
                    $interval = explode('~', $v);
                } else {
                    $interval = null;
                }
                if ($interval != null && is_array($interval) && count($interval) == 2) {
                    $isValidInterval = true;
                    foreach ($interval as $kInterval => $intervalValue) {
                        if ($kInterval == 1 && $intervalValue == '' && is_numeric($interval[0])) {
                            continue;
                        } elseif (!is_numeric($intervalValue)) {
                            $isValidInterval = false;
                            break;
                        }
                    }
                    if ($isValidInterval) {
                        $rarr[$k] = $v;
                    }
                } else {
                    $rarr[$k] = call_user_func($fn, $v);
                    if ($rarr[$k] == 0) {
                        unset($rarr[$k]);
                    }
                }
            }
        }
        return $rarr;
    }
    protected static function getCustomModuleTranslation($name, $string, $language)
    {
        static $translationCache = array();
        $cacheKey = md5($name . $string . $language->id);
        if (isset($translationCache[$cacheKey])) {
            return $translationCache[$cacheKey];
        }
        $translationsStrings = array();
        $files_by_priority = array(
            _PS_THEME_DIR_.'modules/'.$name.'/translations/'.$language->iso_code.'.php',
            _PS_THEME_DIR_.'modules/'.$name.'/'.$language->iso_code.'.php',
            _PS_MODULE_DIR_.$name.'/translations/'.$language->iso_code.'.php',
            _PS_MODULE_DIR_.$name.'/'.$language->iso_code.'.php'
        );
        foreach ($files_by_priority as $file) {
            if (Tools::file_exists_cache($file)) {
                include($file);
                if (!empty($translationsStrings)) {
                    $translationsStrings = $translationsStrings;
                    if (isset($_MODULE)) {
                        $translationsStrings += $_MODULE;
                    }
                } else {
                    if (isset($_MODULE)) {
                        $translationsStrings = $_MODULE;
                    }
                }
            }
        }
        $string = preg_replace("/\\\*'/", "\'", $string);
        $key = md5($string);
        $currentKey = Tools::strtolower('<{' . $name . '}' . _THEME_NAME_ . '>' . $name . '_' . $key);
        $defaultKey = Tools::strtolower('<{' . $name . '}prestashop>' . $name . '_' . $key);
        if (isset($translationsStrings[$currentKey])) {
            $ret = Tools::stripslashes($translationsStrings[$currentKey]);
        } elseif (isset($translationsStrings[$defaultKey])) {
            $ret = Tools::stripslashes($translationsStrings[$defaultKey]);
        } else {
            $ret = $string;
        }
        $translationCache[$cacheKey] = htmlspecialchars($ret, ENT_COMPAT, 'UTF-8');
        return $translationCache[$cacheKey];
    }
    public function smartyNoFilterModifier($s)
    {
        return $s;
    }
    protected function registerFrontSmartyObjects()
    {
        static $registeredFO = false;
        if (!$registeredFO && !empty($this->context->smarty)) {
            $this->context->smarty->unregisterPlugin('modifier', self::$_module_prefix . '_nofilter');
            $this->context->smarty->registerPlugin('modifier', self::$_module_prefix . '_nofilter', array($this, 'smartyNoFilterModifier'));
            $registeredFO = true;
        }
    }
    protected function registerSmartyObjects()
    {
        static $registered = false;
        if (!$registered && !empty($this->context->smarty)) {
            $this->registerFrontSmartyObjects();
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_startForm', array($this, '_startForm'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_endForm', array($this, '_endForm'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_startFieldset', array($this, '_startFieldset'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_endFieldset', array($this, '_endFieldset'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_button', array($this, '_addButton'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_ajaxSelectMultiple', array($this, '_displayAjaxSelectMultiple'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_categoryTree', array($this, '_displayCategoryTree'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_inputActive', array($this, '_displayInputActive'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_inputColor', array($this, '_displayInputColor'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_inputText', array($this, '_displayInputText'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_inputTextLang', array($this, '_displayInputTextLang'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_inputFileLang', array($this, '_displayInputFileLang'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_inlineUploadFile', array($this, '_displayInlineUploadFile'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_richTextareaLang', array($this, '_displayRichTextareaLang'));
            $this->context->smarty->registerPlugin('function', self::$_module_prefix . '_select', array($this, '_displaySelect'));
            $this->context->smarty->registerObject('module', $this, array(
                '_showWarning',
                '_showInfo',
                '_displayTitle',
                '_displaySubTitle',
                '_displaySubmit',
                '_displaySupport',
            ), false);
            $registered = true;
        }
    }
    protected function fetchTemplate($tpl, $customVars = array(), $configOptions = array())
    {
        $this->registerSmartyObjects();
        $this->context->smarty->assign(array(
            'ps_version_full' => str_replace('.', '', _PS_VERSION_),
            'ps_major_version' => Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2),
            'module_name' => $this->name,
            'module_path' => $this->_path,
            'base_config_url' => $this->_base_config_url,
            'current_iso_lang' => $this->_iso_lang,
            'current_id_lang' => (int)$this->context->language->id,
            'default_language' => $this->_default_language,
            'languages' => $this->_languages,
            'options' => $configOptions,
        ));
        if (sizeof($customVars)) {
            $this->context->smarty->assign($customVars);
        }
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/' . $tpl);
    }
}
