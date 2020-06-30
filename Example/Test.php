<?php

use app\models\dao\MnsDeadMessage;
use MqMessage\MqMessageClient;

class Test
{
    /**
     * 监听消费；单条
     */
    public function consumeMessage() {
        $queueName           = 'HJLtest';
        $messageModel        = new MqMessageClient();
        $mnsDeadMessageModel = new MnsDeadMessage();
        try {
            $consumeMessageUserFunc = [$this, 'consumeMessageFunc'];//消息消费回调方法
            $saveMessageUserFunc    = [$mnsDeadMessageModel, 'saveMessage'];//消息保存回调方法
            $config                 = [
                'saveConsumeFailMessage'    => true,//保存消费失败的消息
                'saveConsumeSuccessMessage' => true,//保存消费成功的消息
                'maxConsumeTimes'           => 2,//一个消息最大消息次数
                'maxConsumeCount'           => 100,//一个进程一次最多消费消息数量
                'maxWaitTime'               => 10,//空消息最大监听事时间(秒)
                'messageVisibilityTime'     => 30,//消息隐藏时间
            ];
            $messageModel->consumeMessage($queueName, $consumeMessageUserFunc, $saveMessageUserFunc, $config);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }

    /**
     * 消息消费回调方法
     * @param $msgBody
     * @return bool
     */
    public function consumeMessageFunc($msgBody) {
        /****消费逻辑**/
        return true;
        //成功 return true;
        //失败 return false;
        //跳过 return -1;
    }

    /**
     * 推送单条消息
     */
    public function sendMessage() {
        $queueName    = 'HJLtest';
        $messageModel = new MqMessageClient();
        try {
            //$messageBody = '11111';
            $messageBody = ['a' => 1];
            $result      = $messageModel->sendMessage($queueName, $messageBody, $config = []);
            var_dump($result);
            exit;
            //成功 $result['success'] = true;
            //失败 $result['success'] = false;

        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }

    /**
     * 推送多条消息
     */
    public function batchSendMessage() {
        $queueName    = 'HJLtest';
        $messageModel = new MqMessageClient();
        try {
            $messageBody = '11111';
            //$messageBody = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17];
            //$messageBody = [[1,2],['a','b']];
            $result = $messageModel->batchSendMessage($queueName, $messageBody, $config = []);
            var_dump($result);
            exit;
            //成功 $result['success'] = true;
            //失败 $result['success'] = false;

        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}