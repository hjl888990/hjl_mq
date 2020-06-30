<?php
/**
 * Created by PhpStorm.
 * User: 11956
 * Date: 2020/6/29
 * Time: 15:49
 */

namespace MqMessage;

use MqMessage\Common\Tools;
use MqMessage\Model\MqConfig;
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
            $mqType = Tools::getConfig('defaultMqType');
        }
        $this->mqType   = $mqType;
        $this->mqConfig = Tools::getMqStorageConfig($mqType);

    }

    /**
     * 监听消费消息：单条
     * @param $queueName 队列名称
     * @param $consumeMessageUserFunc 消费回调方法
     * @param array $saveMessageUserFunc 保存消息回调方法
     * @param array $config 配置
     * @throws \Exception
     */
    public function consumeMnsMessage($queueName, $consumeMessageUserFunc, $saveMessageUserFunc = [], $config = []) {
        $this->isValid([$queueName, $consumeMessageUserFunc]);
        $mqConfig = new MqConfig();
        if (!empty($config)) {
            $mqConfig->setProperty($config);
        }
        $mqModel = new MqMessageModel($this->mqType, $this->mqConfig);
        $mqModel->consumeMessage($queueName, $consumeMessageUserFunc, $saveMessageUserFunc, $mqConfig);
    }


    /**
     * 推送单条消息到队列
     * @param $queueName 队列名称
     * @param $messageBody 内容
     * @param array $config 配置
     * @return bool
     * @throws \Exception
     */
    public function sendMessage($queueName, $messageBody, $config = []) {
        $this->isValid([$queueName, $messageBody]);
        $mqConfig = new MqConfig();
        if (!empty($config)) {
            $mqConfig->setProperty($config);
        }
        $mqModel = new MqMessageModel($this->mqType, $this->mqConfig);
        $result  = $mqModel->sendMessage($queueName, $messageBody, $mqConfig);
        return $result;
    }

    /**
     * 推送多条消息到队列
     * @param $queueName 队列名称
     * @param $messageBody 内容
     * @param array $config 配置
     * @return bool
     * @throws \Exception
     */
    public function batchSendMessage($queueName, $messageBody, $config = []) {
        $this->isValid([$queueName, $messageBody]);
        $mqConfig = new MqConfig();
        if (!empty($config)) {
            $mqConfig->setProperty($config);
        }
        $mqModel = new MqMessageModel($this->mqType, $this->mqConfig);
        $result  = $mqModel->batchSendMessage($queueName, $messageBody, $mqConfig);
        return $result;
    }


    /**
     * 参数校验
     * @param array $params
     * @throws \Exception
     */
    protected function isValid($params = []) {
        if (empty($this->mqType)) {
            throw new \Exception('mqType is null');
        }
        if (empty($this->mqConfig)) {
            throw new \Exception('mqConfig is null');
        }
        if (empty($params)) {
            return;
        }
        foreach ($params as $param) {
            if (empty($param)) {
                throw new \Exception('param is null');
            }
        }
    }

}