<?php

declare(strict_types=1);
/**
 * 消息接口类
 *
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */
namespace HyperfLibraries\Sms\Contract;

use HyperfLibraries\Sms\Contract\GatewayInterface;

interface MessageInterface
{
    const TEXT_MESSAGE = 'text';
    const VOICE_MESSAGE = 'voice';
    /**
     * 返回消息类型
     *
     * @return string
     */
    public function getMessageType();
    /**
     * 返回消息内容
     *
     * @param GatewayInterface|null $gateway
     *
     * @return string
     */
    public function getContent(GatewayInterface $gateway = null);
    /**
     * 返回消息的模板id
     *
     * @param GatewayInterface|null $gateway
     *
     * @return string
     */
    public function getTemplate(GatewayInterface $gateway = null);
    /**
     * 返回消息的模板数据
     *
     * @param GatewayInterface|null $gateway
     *
     * @return array
     */
    public function getData(GatewayInterface $gateway = null);
    /**
     * 返回支持服务商
     *
     * @return array
     */
    public function getGateways();
}