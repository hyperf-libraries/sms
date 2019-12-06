<?php

declare(strict_types=1);
/**
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Exception;

use Throwable;

class NoGatewayAvailableException extends Exception
{
    /**
     * @var array
     */
    public $results = [];
    /**
     * @var array
     */
    public $exceptions = [];
    /**
     * NoGatewayAvailableException constructor.
     *
     * @param array           $results
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct(array $results = [], $code = 0, Throwable $previous = null)
    {//var_dump($results['qcloud']['exception']->getMessage());exit;
        $this->results = $results;
        $this->exceptions = \array_column($results, 'exception', 'gateway');
        parent::__construct('All the gateways have failed. You can get error details by `$exception->getExceptions()`', $code, $previous);
    }
    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }
    /**
     * @param string $gateway
     *
     * @return mixed|null
     */
    public function getException($gateway)
    {
        return isset($this->exceptions[$gateway]) ? $this->exceptions[$gateway] : null;
    }
    /**
     * @return array
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }
    /**
     * @return mixed
     */
    public function getLastException()
    {
        return end($this->exceptions);
    }
}