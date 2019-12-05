<?php
declare(strict_types=1);
/**
 * 发送策略接口类
 *
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Contract;


interface StrategyInterface
{
    /**
     * 应用策略并返回结果
     *
     * @param array $gateways
     *
     * @return array
     */
    public function apply(array $gateways);
}