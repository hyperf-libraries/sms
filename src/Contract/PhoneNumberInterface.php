<?php

declare(strict_types=1);
/**
 * 手机号码接口类
 *
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */
namespace HyperfLibraries\Sms\Contract;

use JsonSerializable;
/**
 * Interface PhoneNumberInterface.
 *
 * @author overtrue <i@overtrue.me>
 */
interface PhoneNumberInterface extends JsonSerializable
{
    /**
     * 86.
     *
     * @return int
     */
    public function getIDDCode();
    /**
     * 18888888888.
     *
     * @return int
     */
    public function getNumber();
    /**
     * +8618888888888.
     *
     * @return string
     */
    public function getUniversalNumber();
    /**
     * 008618888888888.
     *
     * @return string
     */
    public function getZeroPrefixedNumber();
    /**
     * @return string
     */
    public function __toString();
}