<?php

declare(strict_types=1);
/**
 * 短信测试用例类
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */
namespace HyperfTest\Cases;
use Hyperf\Config\Config;
use Psr\Container\ContainerInterface;
use Hyperf\Utils\ApplicationContext;
use PHPUnit\Framework\TestCase;
use HyperfLibraries\Sms\SmsFactory;
use HyperfLibraries\Sms\Sms;
use Mockery;
use HyperfLibraries\Sms\Exception\NoGatewayAvailableException;
use Hyperf\Guzzle\ClientFactory;

class SmsTest extends TestCase
{
    /**
     * 腾讯短信测试用例
     */
    public function testQcloud()
    {
        try{
            $client = $this->getClient();
            $result = $client->send('18888888888', [ //手机号码
                                          'content'  => '您正在申请手机注册，验证码为：${code}，5分钟内有效！', //短信内容
                                          'template' => 'SMS_180342928', //模板id
                                          'data' => [
                                              'code' => 6379 //验证码
                                          ]
            ]);var_dump($result);
            $this->assertEquals($result['aliyun']['status'], 'success');
        }catch (NoGatewayAvailableException $exception) {
            echo 'Error:' . $exception->getException('aliyun')->getMessage().' '.$exception->getException('aliyun')->getFile().' '.$exception->getException('aliyun')->getLine();
        }


    }

    protected function getClient(){
        $container = Mockery::mock(ContainerInterface::class);


        $config = new Config([
            'sms' => [
                // HTTP 请求的超时时间（秒）
                'timeout' => 5.0,

                // 默认发送配置
                'default' => [
                    // 网关调用策略，默认：顺序调用
                    'strategy' => \HyperfLibraries\Sms\Strategy\OrderStrategy::class,

                    // 默认可用的发送网关
                    'gateways' => [
                        'aliyun',
                    ],
                ],
                // 可用的网关配置
                'gateways' => [
                        'aliyun' => [
                            'access_key_id' => '',
                            'access_key_secret' => '',
                            'sign_name' => '',
                        ],
                ],
            ],
        ]);

        $container->shouldReceive('get')->with(ClientFactory::class)->andReturn(new ClientFactory($container));
        $container->shouldReceive('get')->with(Sms::class)->andReturn(new Sms($config));

        ApplicationContext::setContainer($container);

        $factory = new SmsFactory();

        return $factory($container);
    }

    protected function tearDown()
    {
        Mockery::close();
    }
}