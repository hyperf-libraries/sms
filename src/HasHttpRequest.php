<?php

declare(strict_types=1);
/**
 * http 基础请求
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms;

use Hyperf\Guzzle\ClientFactory;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Utils\ApplicationContext;

trait HasHttpRequest
{
    /**
     * Make a get request.
     *
     * @param string $endpoint
     * @param array  $query
     * @param array  $headers
     *
     * @return array
     */
    protected function get($endpoint, $query = [], $headers = [])
    {
        return $this->request('get', $endpoint, [
            'headers' => $headers,
            'query' => $query,
        ]);
    }
    /**
     * Make a post request.
     *
     * @param string $endpoint
     * @param array  $params
     * @param array  $headers
     *
     * @return array
     */
    protected function post($endpoint, $params = [], $headers = [])
    {
        return $this->request('post', $endpoint, [
            'headers' => $headers,
            'form_params' => $params,
        ]);
    }
    /**
     * Make a post request with json params.
     *
     * @param       $endpoint
     * @param array $params
     * @param array $headers
     *
     * @return array
     */
    protected function postJson($endpoint, $params = [], $headers = [])
    {
        return $this->request('post', $endpoint, [
            'headers' => $headers,
            'json' => $params,
        ]);
    }
    /**
     * Make a http request.
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $options  http://docs.guzzlephp.org/en/latest/request-options.html
     *
     * @return array
     */
    protected function request($method, $endpoint, $options = [])
    {
        return $this->unwrapResponse($this->getHttpClient($this->getBaseOptions())->{$method}($endpoint, $options));
    }
    /**
     * Return base Guzzle options.
     *
     * @return array
     */
    protected function getBaseOptions()
    {
        $options = [
            'base_uri' => \method_exists($this, 'getBaseUri') ? $this->getBaseUri() : '',
            'timeout' => \method_exists($this, 'getTimeout') ? $this->getTimeout() : 5.0,
        ];
        return $options;
    }
    /**
     * Return http ClientFactory.
     *
     * @param array $options
     *
     * @return ClientFactory
     *
     * @codeCoverageIgnore
     */
    protected function getHttpClient(array $options = [])
    {

        $container = ApplicationContext::getContainer();
        return $container->get(ClientFactory::class)->create($options);
    }
    /**
     * Convert response contents to json.
     *
     * @param ResponseInterface $response
     *
     * @return ResponseInterface|array|string
     */
    protected function unwrapResponse(ResponseInterface $response)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        $contents = $response->getBody()->getContents();
        if (false !== stripos($contentType, 'json') || stripos($contentType, 'javascript')) {
            return \json_decode($contents, true);
        } elseif (false !== stripos($contentType, 'xml')) {
            return \json_decode(\json_encode(\simplexml_load_string($contents)), true);
        }
        return $contents;
    }
}