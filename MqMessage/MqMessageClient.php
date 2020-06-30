<?php
/**
 * Created by PhpStorm.
 * User: 11956
 * Date: 2020/6/29
 * Time: 15:49
 */

namespace MqMessage;


use MqMessage\Common\MqConfig;
use MqMessage\Model\MqMessageModel;

class MqMessageClient
{
    private $mqType;

    private $mqConfig;

    /**
     * 构造方法
     * MqMessageClient constructor.
     * @param string $mqType 队列类型
     */
    public function __construct($mqType = '') {
        if (empty($mqType)) {
            $mqType = MqConfig::getConfig('defaultMqType');
        }
        $this->mqType   = $mqType;
        $this->mqConfig = MqConfig::getMqStorageConfig($mqType);

    }

    /**
     * 监听消费消息：单条
     * @param $queueName
     * @param $callUserFunc
     * @throws \Exception
     */
    public function consumeMnsMessage($queueName, $callUserFunc) {
        $mqModel = new MqMessageModel($this->mqType, $this->mqConfig);
        $mqModel->consumeMnsMessage($queueName, $callUserFunc);
    }


}