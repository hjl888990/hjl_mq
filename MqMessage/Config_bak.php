<?php
/**
 * Created by PhpStorm.
 * User: 11956
 * Date: 2020/6/29
 * Time: 15:48
 */

return [
    //默认队列类型
    'defaultMqType' => 'mns',
    //消息日志
    'logType'       => 'file',//db、file、aliyun-sls
    //mns配置
    'mns'           => [
        'accessId'  => '*******',
        'accessKey' => '*******',
        'endPoint'  => '*******',
    ],

];