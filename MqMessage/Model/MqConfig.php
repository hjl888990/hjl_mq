<?php


namespace MqMessage\Model;


class MqConfig
{
    /**
     * 默认一个进程一次最多消费消息数量
     * @var int
     */
    public $maxConsumeCount = 1000;
    /**
     * 默认空消息最大监听事时间
     * @var int
     */
    public $maxWaitTime = 10;
    /**
     * 默认一个消息最大消费次数
     * @var int
     */
    public $maxConsumeTimes = 5;
    /**
     * 保存消费失败的消息
     * @var bool
     */
    public $saveConsumeFailMessage = true;
    /**
     * 保存消费成功的消息
     * @var bool
     */
    public $saveConsumeSuccessMessage = false;
    /**
     * 消息隐藏时间
     * @var int
     */
    public $messageVisibilityTime = 30;


    /**
     * 设置类属性
     * @param $params
     */
    public function setProperty($params) {
        if (empty($params)) {
            return;
        }
        foreach ($params as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }
            $this->$key = $value;
        }

    }
}