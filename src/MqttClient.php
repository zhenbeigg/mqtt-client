<?php

/*
 * @author: 布尔
 * @name: mqtt客户端
 * @desc: 介绍
 * @LastEditTime: 2023-11-28 18:56:30
 */
declare (strict_types=1);
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
    public function __construct(ConnectionSettings $ConnectionSettings){
        parent::__construct((string)env("MQTT_HOST",''), (int)env("MQTT_PORT", 1883), env("APP_NAME",NULL)."_".rand(1000,9999));
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
    public function send(string $topic,string $data,int $qos=2,bool $retain=false)
    {
        /* 设置配置 */
        $connectionSettings  = ($this->ConnectionSettings)
        ->setUsername(env('MQTT_USER', ''))
        ->setPassword(env('MQTT_PASS', ''))
        ->setKeepAliveInterval(60);
        /*链接*/
        $this->connect($connectionSettings);
        /*发送*/
        $this->publish($topic,$data,$qos,$retain);
        /*断开*/
        $this->disconnect();
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
        return $this->send($topic,json_encode($data,320));
    }
}