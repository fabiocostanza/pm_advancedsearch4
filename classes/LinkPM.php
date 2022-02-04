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
class LinkPM extends Link
{
    public function getPaginationLink($type, $id_object, $nb = false, $sort = false, $pagination = false, $array = false)
    {
        $controllerName = Dispatcher::getInstance()->getController();
        if (in_array($controllerName, array('seo', 'searchresults', 'advancedsearch4'))) {
            if ($controllerName == 'seo') {
                $params = array(
                    'id_seo' => (int)Tools::getValue('id_seo'),
                    'seo_url' => Tools::getValue('seo_url')
                );
                if (isset($array['p'])) {
                    $params['p'] = (int)$array['p'];
                }
            } elseif ($controllerName == 'searchresults') {
                $params = array(
                    'id_search' => (int)Tools::getValue('id_search'),
                    'as4_sq' => Tools::getValue('as4_sq')
                );
                if (isset($array['p'])) {
                    $params['p'] = (int)$array['p'];
                }
                if (As4SearchEngine::getCurrentCategory()) {
                    $idCategorySearch = As4SearchEngine::getCurrentCategory();
                    $category = new Category($idCategorySearch, Context::getContext()->language->id);
                    if (Validate::isLoadedObject($category)) {
                        $params['id'] = $idCategorySearch;
                        $params['rewrite'] = $category->link_rewrite;
                        $controllerName .= '-categories';
                    }
                }
            } elseif ($controllerName == 'advancedsearch4') {
                $selectedCriterions = Context::getContext()->controller->getCriterionsList();
                return As4SearchEngine::generateURLFromCriterions((int)Tools::getValue('id_search'), $selectedCriterions, null, $array, $pagination);
            }
            if (Tools::getIsset('n') && (int)Tools::getValue('n') > 0) {
                $params['n'] = (int)Tools::getValue('n');
            }
            if (Tools::getIsset('orderby') && Tools::getValue('orderby')) {
                $params['orderby'] = Tools::getValue('orderby');
            }
            if (Tools::getIsset('orderway') && Tools::getValue('orderway')) {
                $params['orderway'] = Tools::getValue('orderway');
            }
            $url = $this->getModuleLink('pm_advancedsearch4', $controllerName, $params);
            if (!$array) {
                return $url;
            } else {
                unset($params['id_seo']);
                unset($params['seo_url']);
                unset($params['id_search']);
                unset($params['as4_sq']);
                unset($params['id']);
                unset($params['rewrite']);
                $params['requestUrl'] = $url;
                return $params;
            }
        } else {
            return parent::getPaginationLink($type, $id_object, $nb, $sort, $pagination, $array);
        }
    }
}
