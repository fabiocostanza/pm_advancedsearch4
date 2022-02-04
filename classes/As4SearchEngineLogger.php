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
abstract class As4SearchEngineLogger
{
    const DEBUG = false;
    private static $bench_start;
    private static $bench_step;
    public static function log($nom_etape)
    {
        if (self::DEBUG) {
            if (!self::$bench_start) {
                self::$bench_start = microtime(true);
            }
            if (self::$bench_step) {
                $time_elapsed_step = microtime(true) - self::$bench_step;
            }
            self::$bench_step = microtime(true);
            $time_elapsed_start = self::$bench_step - self::$bench_start;
            if (!class_exists('FireLogger')) {
                include_once(_PS_ROOT_DIR_ . '/modules/pm_advancedsearch4/lib/firelogger/FireLogger.php');
            }
            flog($nom_etape);
            flog('=> Elasped time since start ' . $time_elapsed_start . ' s');
            if (isset($time_elapsed_step)) {
                flog('=> Elasped time since last step ' . $time_elapsed_step . ' s');
            }
        }
    }
}
