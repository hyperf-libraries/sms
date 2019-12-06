<?php

declare(strict_types=1);
/**
 * 短信服务主要实现类
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms;

use HyperfLibraries\Sms\Contract\GatewayInterface;
use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\Contract\StrategyInterface;
use HyperfLibraries\Sms\Strategy\OrderStrategy;
use HyperfLibraries\Sms\Exception\InvalidArgumentException;
use Closure;
use Hyperf\Contract\ConfigInterface;
use HyperfLibraries\Sms\Contract\SmsInterface;
use HyperfLibraries\Sms\Exception\NoGatewayAvailableException;

class Sms implements SmsInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var array
     */
    protected $customCreators = [];
    /**
     * @var array
     */
    protected $gateways = [];
    /**
     * @var Sender
     */
    protected $sender;
    /**
     * @var array
     */
    protected $strategies = [];
    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }
    /**
     * Send a message.
     *
     * @param string|array                                       $to
     * @param MessageInterface|array $message
     * @param array                                              $gateways
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws NoGatewayAvailableException
     */
    public function send($to, $message, array $gateways = [])
    {
        $to = $this->formatPhoneNumber($to);
        $message = $this->formatMessage($message);
        $gateways = empty($gateways) ? $message->getGateways() : $gateways;
        if (empty($gateways)) {
            $gateways = $this->config->get('sms.default.gateways', []);
        }
        return $this->getSender()->send($to, $message, $this->formatGateways($gateways));
    }
    /**
     * Create a gateway.
     *
     * @param string $name
     *
     * @return GatewayInterface
     *
     * @throws InvalidArgumentException
     */
    public function gateway($name)
    {
        if (!isset($this->gateways[$name])) {
            $this->gateways[$name] = $this->createGateway($name);
        }
        return $this->gateways[$name];
    }
    /**
     * Get a strategy instance.
     *
     * @param string|null $strategy
     *
     * @return StrategyInterface
     *
     * @throws InvalidArgumentException
     */
    public function strategy($strategy = null)
    {
        if (\is_null($strategy)) {
            $strategy = $this->config->get('sms.default.strategy', OrderStrategy::class);
        }
        if (!\class_exists($strategy)) {
            $strategy = __NAMESPACE__.'\Strategy\\'.\ucfirst($strategy);
        }
        if (!\class_exists($strategy)) {
            throw new InvalidArgumentException("Unsupported strategy \"{$strategy}\"");
        }
        if (empty($this->strategies[$strategy]) || !($this->strategies[$strategy] instanceof StrategyInterface)) {
            $this->strategies[$strategy] = new $strategy($this);
        }
        return $this->strategies[$strategy];
    }
    /**
     * Register a custom driver creator Closure.
     *
     * @param string   $name
     * @param \Closure $callback
     *
     * @return $this
     */
    public function extend($name, Closure $callback)
    {
        $this->customCreators[$name] = $callback;
        return $this;
    }

    /**
     * @return Sender
     */
    public function getSender()
    {
        return $this->sender ?: $this->sender = new Sender($this);
    }
    /**
     * Create a new driver instance.
     *
     * @param string $name
     *
     * @return GatewayInterface
     *
     * @throws InvalidArgumentException
     */
    protected function createGateway($name)
    {
        if (isset($this->customCreators[$name])) {
            $gateway = $this->callCustomCreator($name);
        } else {
            $className = $this->formatGatewayClassName($name);
            $gateway = $this->makeGateway($className, $this->config);
        }
        if (!($gateway instanceof GatewayInterface)) {
            throw new InvalidArgumentException(\sprintf('Gateway "%s" must implement interface %s.', $name, GatewayInterface::class));
        }
        return $gateway;
    }
    /**
     * Make gateway instance.
     *
     * @param string $gateway
     * @param ConfigInterface  $config
     *
     * @return GatewayInterface
     *
     * @throws InvalidArgumentException
     */
    protected function makeGateway($gateway, $config)
    {
        if (!\class_exists($gateway) || !\in_array(GatewayInterface::class, \class_implements($gateway))) {
            throw new InvalidArgumentException(\sprintf('Class "%s" is a invalid sms gateway.', $gateway));
        }
        return new $gateway($config);
    }
    /**
     * Format gateway name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function formatGatewayClassName($name)
    {
        if (\class_exists($name) && \in_array(GatewayInterface::class, \class_implements($name))) {
            return $name;
        }
        $name = \ucfirst(\str_replace(['-', '_', ''], '', $name));
        return __NAMESPACE__."\\Gateway\\{$name}Gateway";
    }

    /**
     * Call a custom gateway creator.
     *
     * @param string $gateway
     *
     * @return mixed
     */
    protected function callCustomCreator($gateway)
    {
        return \call_user_func($this->customCreators[$gateway], $this->config->get("gateways.{$gateway}", []));
    }
    /**
     * @param string|\HyperfLibraries\Sms\Contract\PhoneNumberInterface $number
     *
     * @return \HyperfLibraries\Sms\PhoneNumber
     */
    protected function formatPhoneNumber($number)
    {
        if ($number instanceof PhoneNumberInterface) {
            return $number;
        }

        return new PhoneNumber(\trim( (string) $number));
    }
    /**
     * @param array|string|MessageInterface $message
     *
     * @return MessageInterface
     */
    protected function formatMessage($message)
    {
        if (!($message instanceof MessageInterface)) {
            if (!\is_array($message)) {
                $message = [
                    'content' => $message,
                    'template' => $message,
                ];
            }
            $message = new Message($message);
        }
        return $message;
    }
    /**
     * @param array $gateways
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    protected function formatGateways(array $gateways)
    {
        return $this->strategy()->apply($gateways);
    }


}