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
abstract class As4SearchEngineDb
{
    // Enable SQL log ?
    public static $as4SqlLog = false;
    // Only log if elasped time >= X ms
    public static $as4SqlQueryThresholdTime = 0;
    public static function query($query, $type = 1, $useArray = true, $useCache = true)
    {
        if (self::$as4SqlLog) {
            $time = microtime(true);
            $log = trim($query);
            $finalOrigin = array(
                'line' => 'UNKNWON',
                'class' => 'UNKNWON',
                'function' => 'UNKNWON',
            );
            $origin = debug_backtrace();
            foreach ($origin as $originRow) {
                if (!empty($originRow['class']) && $originRow['class'] != 'As4SearchEngineDb') {
                    $finalOrigin = $originRow;
                    break;
                }
            }
        }
        $instanceMaster = _PS_USE_SQL_SLAVE_;
        if (!$useCache) {
            $instanceMaster = true;
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
            $elaspedTime = round((microtime(true) - $time)*1000, 2);
            if ($elaspedTime >= self::$as4SqlQueryThresholdTime) {
                $log .= "\n\n";
                $log .= 'L' . $finalOrigin['line'] . ' - ' . $finalOrigin['class'] . '::' . $finalOrigin['function'] . ' => ';
                $log .= $elaspedTime . 'ms';
                $log .= "\n\n";
                file_put_contents(dirname(__FILE__) . '/sql_log.txt', $log, FILE_APPEND);
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
        return self::execute('SET group_concat_max_len := @@max_allowed_packet');
    }
}
