<?php

/*
 * @author: 布尔
 * @name: mqtt客户端
 * @desc: 介绍
 * @LastEditTime: 2024-05-29 16:47:51
 */

declare(strict_types=1);

namespace Eykj\MqttClient;

use PhpMqtt\Client\MqttClient as PMqttClient;
use PhpMqtt\Client\ConnectionSettings;
use function Hyperf\Support\env;

class MqttClient extends  PMqttClient
{
    protected ConnectionSettings $ConnectionSettings;
    /**
     * @author: 布尔
     * @name: 初始化配置
     * @return {*}
     */
    public function __construct(ConnectionSettings $ConnectionSettings)
    {
        parent::__construct((string)env("MQTT_HOST", ''), (int)env("MQTT_PORT", 1883), env("APP_NAME", NULL) . "_" . rand(1000, 9999) . "_send");
        $this->ConnectionSettings = $ConnectionSettings;
    }

    /**
     * @author: 布尔
     * @name: 发送消息
     * @param {string} $topic 推送目标话题 
     * @param {string} $data 推送数据 
     * @param {int} $qos 0最多一次 1至少一次 2仅一次
     * @param {bool} $retain 保持
     * @return {*}
     */
    public function send(string $topic, string $data, int $qos = 2, bool $retain = false)
    {
        /* data参数内增加时间戳 */
        $data['timestamp'] = sprintf('%03d', round(microtime(true) * 1000));
        /* 设置配置 */
        $connectionSettings  = ($this->ConnectionSettings)
            ->setUsername(env('MQTT_USER', ''))
            ->setPassword(env('MQTT_PASS', ''))
            ->setKeepAliveInterval(60);
        /* 标记会话状态  0 (false)：客户端在断开连接后希望服务器保留其会话状态。当客户端重新连接时，它将继续使用相同的会话 ID，并且可以访问之前订阅的主题。1 (true)：客户端希望服务器在断开连接后清除其会话状态。当客户端重新连接时，它将创建一个新的会话 ID，并且必须重新订阅所有主题。*/
        $clean_session = true;
        /*链接*/
        $this->connect($connectionSettings, $clean_session);
        /*发送*/
        $this->publish($topic, $data, $qos, $retain);
        /*断开*/
        $this->disconnect();
        /* 记录日志 */
        alog(['topic' => $topic, 'data' => $data], 6);
    }

    /**
     * @author: 布尔
     * @name: 设备回调mqtt推送
     * @param {array} $param
     * @return {*}
     */
    public function post_device_send(array $param)
    {
        $topic = 'YY2099_' . $param['deviceSn'];
        $data = ['func' => $param['func'], 'data' => $param['data'], 'errmsg' => $param['errmsg'], 'errcode' => $param['errcode']];
        return $this->send($topic, json_encode($data, 320));
    }
}
