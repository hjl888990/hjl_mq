<?php

namespace MqMessage\Model;

use MqMessage\Common\MqConfig;
use MqMessage\Storage\MqStorageFactory;

/**
 * Created by PhpStorm.
 * User: 11956
 * Date: 2020/6/29
 * Time: 18:09
 */
class MqMessageModel
{
    private $client;

    const QUEUE_CONSUME_LIMIT = 1000;

    const MNS_MESSAGE_MAX_CONSUME_TIMES = 60;

    public function __construct($mqType, $mqConfig) {
        $this->client = MqStorageFactory::getMqStorageInstance($mqType, $mqConfig);

    }

    /**
     * 消费MNS消息
     * @param $queueKey
     * @param $callUserFunc
     * @throws CIException
     * @throws \Exception
     */
    public function consumeMnsMessage($queueName, $callUserFunc) {

        $count = 0;
        while ($count < self::QUEUE_CONSUME_LIMIT) {//每个进程最大消费次数判断，默认1000次
            $result = $this->client->receiveMessage($queueName, self::QUEUE_WAIT_TIME);
            if (self::noMessageLongTime($result)) {//长时间无消息主动退出
                break;
            }
            if (empty($result['success'])) {
                throw new \Exception(json_encode($result));
            }
            $count++;//消费次数基数
            if (empty($result['data'])) {
                $this->client->deleteMessage($queueName, $result['data']['handle']);
                continue;
            }
            $msgBody      = empty($result['data']['body']) ? '' : $result['data']['body'];//消息正文
            $dequeueCount = empty($result['data']['dequeueCount']) ? 1 : $result['data']['dequeueCount'];//被消费次数
            //写入变量内,方便定位问题
            $message_post_value    = is_array($msgBody) ? json_encode($msgBody) : $msgBody;
            $_POST['message_body'] = $message_post_value;
            //消费消息
            $dealResult = call_user_func($callUserFunc, $msgBody);
            //成功消费删除消息
            if ($dealResult) {
                $this->client->deleteMessage($result['data']['handle']);
                continue;
            }
            //消费失败未达到最大消费次数的延迟继续消费
            if ($dequeueCount < self::MNS_MESSAGE_MAX_CONSUME_TIMES) {
                $this->client->changeMessageVisibility($result['data']['handle'], self::QUEUE_CONSUME_FAIL_HIDDEN_TIME);
                continue;
            }
            //消费失败达到最大消费次数
            $saveResult = $this->saveMnsDeadMessage($queueName, $result['data']);
            if ($saveResult) {
                $this->client->deleteMessage($result['data']['handle']);
            } else {
                $this->client->changeMessageVisibility($result['data']['handle'], self::QUEUE_CONSUME_FAIL_HIDDEN_TIME);
            }
            continue;
        };
    }

}