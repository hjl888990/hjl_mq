<?php

namespace MqMessage\Common;

/**
 * Created by PhpStorm.
 * User: 11956
 * Date: 2020/6/29
 * Time: 18:34
 */
class Tools
{

    /**
     * 获取配置项
     * @param $key 参数名
     * @return mixed|null
     */
    public static function getConfig($key) {
        $mqConfig = require(__DIR__ . '/../Config.php');
        $key      = explode('.', $key);
        $value    = $mqConfig;
        foreach ($key as $k) {
            if (!isset($value[$k])) {
                $value = null;
                break;
            }
            $value = $value[$k];
        }
        return $value;
    }

    /**
     * 获取队列配置
     * @param $mqType 队列类型
     * @return mixed|null
     */
    public static function getMqStorageConfig($mqType) {
        if (empty($mqType)) {
            return null;
        }
        $mqConfig = require(__DIR__ . '/../Config.php');
        switch ($mqType) {
            case 'mns':
                $mqStorageConfig = isset($mqConfig['mns']) ? $mqConfig['mns'] : null;
                return $mqStorageConfig;
            default:
                return null;

        }

    }

}