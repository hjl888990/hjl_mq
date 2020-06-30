<?php
/**
 * Created by PhpStorm.
 * User: 11956
 * Date: 2020/6/29
 * Time: 16:38
 */

namespace MqMessage\Storage;


use MqMessage\Storage\Mns\AliyunMnsQueue;

class MqStorageFactory
{

    private static $mqInstance = [];

    //队列存储类型
    const MQ_STORAGE_TYPE_OF_MNS = 'mns';

    /**
     * 获取队列存储实例
     * @param $mqType
     * @param $mqConfig
     * @return string
     */
    public static function getMqStorageInstance($mqType, $mqConfig) {
        if (empty($mqType) || empty($mqConfig)) {
            return null;
        }
        $hashKey = md5(json_encode(['mqType' => $mqType, 'mqConfig' => $mqConfig]));
        if (self::$mqInstance[$hashKey]) {
            return self::$mqInstance[$hashKey];
        }

        switch ($mqType) {
            case self::MQ_STORAGE_TYPE_OF_MNS:
                self::$mqInstance[$hashKey] = new AliyunMnsQueue($mqConfig);
                break;
            default:
                self::$mqInstance[$hashKey] = new AliyunMnsQueue($mqConfig);
                break;
        }
        return self::$mqInstance[$hashKey];
    }
}