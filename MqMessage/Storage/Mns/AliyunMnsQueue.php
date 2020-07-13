<?php

namespace MqMessage\Storage\Mns;

use AliyunMNS\Client;
use AliyunMNS\Requests\PublishMessageRequest;
use AliyunMNS\Requests\SendMessageRequest;
use AliyunMNS\Requests\BatchSendMessageRequest;
use AliyunMNS\Model\SendMessageRequestItem;
use AliyunMNS\Exception\MnsException;
use MqMessage\Storage\MqStoragetInterface;

class AliyunMnsQueue implements MqStoragetInterface
{

    private $client;

    /**
     * 初始化
     * @param $mqConfig
     */
    public function __construct($mqConfig) {
        if (empty($mqConfig['endPoint']) || empty($mqConfig['accessId']) || empty($mqConfig['accessKey'])) {
            return;
        }
        $this->client = new Client($mqConfig['endPoint'], $mqConfig['accessId'], $mqConfig['accessKey']);
    }


    /**
     * 发送单条信息至队列
     * @param $queueName
     * @param $messageBody
     * @return mixed
     */
    public function sendMessage($queueName, $messageBody) {
        try {
            if (empty($this->client)) {
                throw new \Exception('client is null');
            }
            if (empty($queueName)) {
                throw new \Exception('queueName is null');
            }
            if (empty($messageBody)) {
                throw new \Exception('messageBody is null');
            }
            $queue   = $this->client->getQueueRef($queueName);
            $request = new SendMessageRequest($messageBody);
            $queue->sendMessage($request);
            $ret['success'] = true;
        } catch (\Exception $e) {
            $ret['success'] = false;
            $ret ['code']   = $e->getCode();
            $ret ['errMsg'] = $e->getMessage();
        }
        return $ret;
    }

    /**
     * 发送主题消息
     * @param $queueName
     * @param $messageBody
     * @return mixed
     */
    public function sendTopicMessage($topicName, $messageBody) {
        try {
            if (empty($this->client)) {
                throw new \Exception('client is null');
            }
            if (empty($topicName)) {
                throw new \Exception('topicName is null');
            }
            if (empty($messageBody)) {
                throw new \Exception('messageBody is null');
            }
            $topic   = $this->client->getTopicRef($topicName);
            $request = new PublishMessageRequest($messageBody);
            $topic->publishMessage($request);
            $ret['success'] = true;
        } catch (\Exception $e) {
            $ret['success'] = false;
            $ret ['code']   = $e->getCode();
            $ret ['errMsg'] = $e->getMessage();
        }
        return $ret;
    }

    /**
     * 发送多条条信息至队列
     * @param $queueName
     * @param $messageBody
     * @return mixed
     * @throws \Exception
     */
    public function batchSendMessage($queueName, $messageBody) {
        try {
            if (empty($this->client)) {
                throw new \Exception('client is null');
            }
            if (empty($queueName)) {
                throw new \Exception('queueName is null');
            }
            if (empty($messageBody)) {
                throw new \Exception('messageBody is null');
            }
            $queue = $this->client->getQueueRef($queueName);
            $items = [];
            foreach ($messageBody as $one) {
                $one     = is_array($one) ? json_encode($one) : $one;
                $items[] = new SendMessageRequestItem($one);
                if (count($items) % 15 == 0) {
                    $request = new BatchSendMessageRequest($items);
                    $queue->batchSendMessage($request);
                    $items = [];
                }
            }
            if (!empty($items)) {
                $request = new BatchSendMessageRequest($items);
                $queue->batchSendMessage($request);
            }
            $ret['success'] = true;
        } catch (\Exception $e) {
            $ret['success'] = false;
            $ret ['code']   = $e->getCode();
            $ret ['errMsg'] = $e->getMessage();
        }
        return $ret;
    }


    /**
     * 消费单条队列信息
     * @param $queueName
     * @param int $waitTime
     * @return mixed
     * @throws \Exception
     */
    public function receiveMessage($queueName, $waitTime = 30) {
        try {
            if (empty($this->client)) {
                throw new \Exception('client is null');
            }
            if (empty($queueName)) {
                throw new \Exception('queueName is null');
            }
            $queue = $this->client->getQueueRef($queueName);
            $res   = $queue->receiveMessage($waitTime);

            $ret ['success']               = true;
            $ret ['data'] ['body']         = $res->getMessageBody();
            $ret ['data'] ['handle']       = $res->getReceiptHandle();
            $ret ['data'] ['dequeueCount'] = $res->getDequeueCount();//被消费次数

        } catch (\Exception $e) {
            $ret['success'] = false;
            $ret ['code']   = $e->getCode();
            $ret ['errMsg'] = $e->getMessage();
        }
        return $ret;
    }


    /**
     * 删除单条队列信息
     * @param $queueName
     * @param $receiptHandle
     * @return mixed
     * @throws \Exception
     */
    public function deleteMessage($queueName, $receiptHandle) {
        try {
            if (empty($this->client)) {
                throw new \Exception('client is null');
            }
            if (empty($queueName)) {
                throw new \Exception('queueName is null');
            }
            if (empty($receiptHandle)) {
                throw new \Exception('receiptHandle is null');
            }
            $queue = $this->client->getQueueRef($queueName);
            $queue->deleteMessage($receiptHandle);
            $ret ['success'] = true;
        } catch (\Exception $e) {
            $ret['success'] = false;
            $ret ['code']   = $e->getCode();
            $ret ['errMsg'] = $e->getMessage();
        }
        return $ret;
    }

    /**
     * 隐藏消息多少秒后可以再次被消费
     * @param $queueName
     * @param $receiptHandle
     * @param int $visibilityTimeout
     * @return mixed
     * @throws \Exception
     */
    public function changeMessageVisibility($queueName, $receiptHandle, $visibilityTimeout = 60) {
        try {
            if (empty($this->client)) {
                throw new \Exception('client is null');
            }
            if (empty($queueName)) {
                throw new \Exception('queueName is null');
            }
            if (empty($receiptHandle)) {
                throw new \Exception('receiptHandle is null');
            }
            $queue = $this->client->getQueueRef($queueName);

            $queue->changeMessageVisibility($receiptHandle, $visibilityTimeout);
            $ret ['success'] = true;
        } catch (\Exception $e) {
            $ret['success'] = false;
            $ret ['code']   = $e->getCode();
            $ret ['errMsg'] = $e->getMessage();
        }
        return $ret;
    }

    /**
     * 队列无消息判断
     * @param $response
     * @return bool
     */
    public function checkNoMessage($response) {
        $result = false;
        if (!empty($response['success'])) {
            return $result;
        }
        if ($response['code'] == 404) {//无消息
            $result = true;
        }
        return $result;
    }


}