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

namespace AdvancedSearch;
if (!defined('_PS_VERSION_')) {
    exit;
}
use Db;
abstract class SearchEngineDb
{
    public static $as4SqlLog = false;
    public static $as4SqlQueryThresholdTime = 0;
    public static function query($query, $type = 1, $useArray = true, $useCache = true)
    {
        static $groupConcatLimitDone = false;
        if (!$groupConcatLimitDone) {
            self::setGroupConcatMaxLength();
            $groupConcatLimitDone = true;
        }
        static $logFileName = null;
        if (self::$as4SqlLog && $logFileName == null) {
            $logFileName = sprintf(
                dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
                'sql' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR .
                'sql-%s-%s.log',
                date('Ymd-His'),
                uniqid()
            );
        }
        $time = null;
        $log = null;
        $finalOrigin = array();
        $result = null;
        $instanceMaster = _PS_USE_SQL_SLAVE_;
        if (!$useCache) {
            $instanceMaster = true;
        }
        if (self::$as4SqlLog) {
            $log = preg_replace('/^\s+$/m', ' ', trim($query));
            $log = preg_replace('/^\s+/m', '', trim($log));
            $finalOrigin = array(
                'line' => 'UNKNOWN',
                'class' => 'UNKNOWN',
                'function' => 'UNKNOWN',
            );
            $origin = debug_backtrace();
            foreach ($origin as $originRow) {
                if (!empty($originRow['class']) && $originRow['class'] != 'AdvancedSearch\SearchEngineDb') {
                    $finalOrigin = $originRow;
                    break;
                }
            }
            $time = microtime(true);
        }
        if ($type == 1) {
            $result = Db::getInstance($instanceMaster)->ExecuteS($query, $useArray, $useCache);
        } elseif ($type == 2) {
            $result = Db::getInstance($instanceMaster)->getRow($query, $useCache);
        } elseif ($type == 3) {
            $result = Db::getInstance($instanceMaster)->getValue($query, $useCache);
        } elseif ($type == 4) {
            $result = Db::getInstance()->Execute($query);
        }
        if (self::$as4SqlLog) {
            $elaspedTime = (microtime(true) - $time) * 1000;
            if ($elaspedTime >= self::$as4SqlQueryThresholdTime) {
                $log .= sprintf(
                    "\n\n%.2fms - L%d - %s::%s\n\n",
                    $elaspedTime,
                    $finalOrigin['line'],
                    $finalOrigin['class'],
                    $finalOrigin['function']
                );
                file_put_contents($logFileName, $log, FILE_APPEND);
            }
        }
        return $result;
    }
    public static function queryNoCache($query, $type = 1, $useArray = true)
    {
        return self::query($query, $type, $useArray, false);
    }
    public static function row($query, $useCache = true)
    {
        return self::query($query, 2, true, $useCache);
    }
    public static function value($query, $useCache = true)
    {
        return self::query($query, 3, true, $useCache);
    }
    public static function valueList($query, $castFunction = false, $useCache = true)
    {
        $list = array();
        foreach (self::query($query, 1, true, $useCache) as $row) {
            $list[] = current($row);
        }
        if ($castFunction !== false) {
            $list = array_map($castFunction, $list);
        }
        return $list;
    }
    public static function execute($query)
    {
        return self::query($query, 4);
    }
    public static function setGroupConcatMaxLength()
    {
        return Db::getInstance()->Execute('SET group_concat_max_len = 33554432');
    }
}
