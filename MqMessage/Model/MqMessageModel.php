<?php

namespace MqMessage\Model;

use MqMessage\Storage\MqStorageFactory;

/**
 * Created by PhpStorm.
 * User: 11956
 * Date: 2020/6/29
 * Time: 18:09
 */
class MqMessageModel
{
    private $mqType;
    private $client;

    public function __construct($mqType, $mqConfig) {
        $this->mqType = $mqType;
        $this->client = MqStorageFactory::getMqStorageInstance($mqType, $mqConfig);
    }

    /**
     * 监听消费消息：单条
     * @param $queueName 队列名称
     * @param $consumeMessageUserFunc 消费回调方法
     * @param $saveMessageUserFunc 保存消息回调方法
     * @param MqConfig $mqConfig 配置
     * @throws \Exception
     */
    public function consumeMessage($queueName, $consumeMessageUserFunc, $saveMessageUserFunc, MqConfig $mqConfig) {
        if (empty($this->client)) {
            throw new \Exception('MQ client is null');
        }
        $count = 0;
        while ($count < $mqConfig->maxConsumeCount) {
            $result = $this->client->receiveMessage($queueName, $mqConfig->maxWaitTime);
            if ($this->client->checkNoMessage($result)) {//长时间无消息主动退出
                break;
            }
            if (empty($result['success'])) {
                throw new \Exception(json_encode($result));
            }
            $count++;
            if (empty($result['data'])) {
                $this->client->deleteMessage($queueName, $result['data']['handle']);
                continue;
            }
            $msgBody      = empty($result['data']['body']) ? '' : $result['data']['body'];//消息正文
            $dequeueCount = empty($result['data']['dequeueCount']) ? 1 : $result['data']['dequeueCount'];//被消费次数
            //消费消息
            $dealResult = call_user_func($consumeMessageUserFunc, $msgBody);
            //成功标识：不返回||返回true||返回大于0的数字
            if (is_null($dealResult) || $dealResult == true || $dealResult > 0) {
                if (!empty($mqConfig->saveConsumeSuccessMessage) && !empty($saveMessageUserFunc)) {
                    call_user_func($saveMessageUserFunc, [
                        'success'      => true,
                        'queueType'    => $this->mqType,
                        'queueName'    => $queueName,
                        'msgBody'      => $msgBody,
                        'dequeueCount' => $dequeueCount,
                    ]);
                }
                $this->client->deleteMessage($queueName, $result['data']['handle']);
                continue;
            }
            //失败标识：返回false||0||'0'
            if (empty($dealResult) && !is_null($dealResult)) {
                if ($dequeueCount >= $mqConfig->maxConsumeTimes) {//达到最大消费次数
                    if (!empty($mqConfig->saveConsumeFailMessage) && !empty($saveMessageUserFunc)) {
                        call_user_func($saveMessageUserFunc, [
                            'success'      => false,
                            'queueType'    => $this->mqType,
                            'queueName'    => $queueName,
                            'msgBody'      => $msgBody,
                            'dequeueCount' => $dequeueCount,
                        ]);
                    }
                    $this->client->deleteMessage($queueName, $result['data']['handle']);
                    continue;
                } else {
                    $this->client->changeMessageVisibility($queueName, $result['data']['handle'], $mqConfig->messageVisibilityTime);
                    continue;
                }
            }
            //跳过：比如业务端判断到消费重叠
            continue;
        };
    }

    /**
     * 推送单条消息到队列
     * @param $queueName 队列名称
     * @param $messageBody 消息体
     * @param MqConfig $mqConfig 配置
     * @return
     * @throws \Exception
     */
    public function sendMessage($queueName, $messageBody, MqConfig $mqConfig) {
        if (empty($this->client)) {
            throw new \Exception('MQ client is null');
        }
        $messageBody = is_array($messageBody) ? json_encode($messageBody) : $messageBody;
        return $this->client->sendMessage($queueName, $messageBody);
    }

    /**
     * 推送多条消息到队列
     * @param $queueName 队列名称
     * @param $messageBody 消息体
     * @param MqConfig $mqConfig 配置
     * @return
     * @throws \Exception
     */
    public function batchSendMessage($queueName, $messageBody, MqConfig $mqConfig) {
        if (empty($this->client)) {
            throw new \Exception('MQ client is null');
        }
        $messageBody = is_array($messageBody) ? $messageBody : [$messageBody];
        return $this->client->batchSendMessage($queueName, $messageBody);
    }


}