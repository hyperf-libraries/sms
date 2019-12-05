<?php

declare(strict_types=1);
/**
 * 短信服务商网关基类
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use HyperfLibraries\Sms\Contract\GatewayInterface;
use Hyperf\Contract\ConfigInterface;

Abstract class GatewayAbstract implements GatewayInterface
{

    const DEFAULT_TIMEOUT = 5.0;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var float
     */
    protected $timeout;

    /**
     * @param array $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }
    /**
     * Return timeout.
     *
     * @return int|mixed
     */
    public function getTimeout()
    {
        return $this->timeout ?: $this->config->get('sms.timeout', self::DEFAULT_TIMEOUT);
    }
    /**
     * Set timeout.
     *
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = floatval($timeout);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return strtolower(str_replace([__NAMESPACE__.'\\', 'Gateway'], '', get_class($this)));
    }

}