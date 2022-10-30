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

// PHP Cli only
if ('cli' == php_sapi_name()) {
    include(dirname(__FILE__).'/../../../../config/config.inc.php');
    include(dirname(__FILE__).'/../../../../init.php');
    $module = Module::getInstanceByName(_PM_AS_MODULE_NAME_);
    $idSearch = false;
    // Retrieve id_search into args
    if (isset($argv) && is_array($argv) && isset($argv[1]) && is_numeric($argv[1]) && !empty($argv[1])) {
        $idSearch = (int)$argv[1];
        $searchInstance = new AdvancedSearch\Models\Search((int)$idSearch);
        if (!Validate::isLoadedObject($searchInstance)) {
            die(json_encode(array('result' => false)));
        }
    }
    die(json_encode($module->cronTask($idSearch)));
} else {
    header('HTTP/1.0 403 Forbidden', true, 403);
    die;
}
