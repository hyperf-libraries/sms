<?php

declare(strict_types=1);
/**
 * 消息实现类
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms;

use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\Contract\GatewayInterface;

class Message implements MessageInterface
{
    /**
     * @var array
     */
    protected $gateways = [];
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $content;
    /**
     * @var string
     */
    protected $template;
    /**
     * @var array
     */
    protected $data = [];
    /**
     * Message constructor.
     *
     * @param array  $attributes
     * @param string $type
     */
    public function __construct(array $attributes = [], $type = MessageInterface::TEXT_MESSAGE)
    {
        $this->type = $type;
        foreach ($attributes as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }
    /**
     * Return the message type.
     *
     * @return string
     */
    public function getMessageType()
    {
        return $this->type;
    }
    /**
     * Return message content.
     *
     * @param GatewayInterface|null $gateway
     *
     * @return string
     */
    public function getContent(GatewayInterface $gateway = null)
    {
        return is_callable($this->content) ? call_user_func($this->content, $gateway) : $this->content;
    }
    /**
     * Return the template id of message.
     *
     * @param GatewayInterface|null $gateway
     *
     * @return string
     */
    public function getTemplate(GatewayInterface $gateway = null)
    {
        return is_callable($this->template) ? call_user_func($this->template, $gateway) : $this->template;
    }
    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }
    /**
     * @param mixed $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    /**
     * @param mixed $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
    /**
     * @param GatewayInterface|null $gateway
     *
     * @return array
     */
    public function getData(GatewayInterface $gateway = null)
    {
        return is_callable($this->data) ? call_user_func($this->data, $gateway) : $this->data;
    }
    /**
     * @param array|callable $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    /**
     * @return array
     */
    public function getGateways()
    {
        return $this->gateways;
    }
    /**
     * @param array $gateways
     *
     * @return $this
     */
    public function setGateways(array $gateways)
    {
        $this->gateways = $gateways;
        return $this;
    }
    /**
     * @param $property
     *
     * @return string
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}