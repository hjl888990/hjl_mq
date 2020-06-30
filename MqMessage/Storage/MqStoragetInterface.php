<?php

namespace MqMessage\Storage;
/**
 * Created by PhpStorm.
 * User: 11956
 * Date: 2020/6/29
 * Time: 15:59
 */
interface MqStoragetInterface
{

    /**
     * 初始化队列信息
     * MqStoragetInterface constructor.
     * @param $mqConfig
     */
    public function __construct($mqConfig);

    /**
     * 获取一条消息
     * @param $queueName
     * @param $waitTime
     * @return mixed
     */
    public function receiveMessage($queueName, $waitTime);

    /**
     * 发送一条消息
     * @param $queueName
     * @param $messageBody
     * @return mixed
     */
    public function sendMessage($queueName, $messageBody);

    /**
     * 发送一批消息
     * @param $queueName
     * @param $messageBody
     * @return mixed
     */
    public function batchSendMessage($queueName, $messageBody);

    /**
     * 删除一条消息
     * @param $queueName
     * @param $receiptHandle
     * @return mixed
     */
    public function deleteMessage($queueName, $receiptHandle);

    /**
     * 改变一条消息的隐藏时间
     * @param $queueName
     * @param $receiptHandle
     * @param $visibilityTimeout
     * @return mixed
     */
    public function changeMessageVisibility($queueName, $receiptHandle, $visibilityTimeout);


}