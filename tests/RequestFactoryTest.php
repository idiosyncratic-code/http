<?php

declare(strict_types=1);

namespace Idiosyncratic\Http\Server;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

class RequestFactoryTest extends TestCase
{
    public function testCreatesGetRequest() : void
    {
        $data = "GET /?foo=bar&bar=baz HTTP/1.1\r\nX-Custom-Header: Foo\r\nX-Custom-Header: Bar\r\nHost: identity.docker\r\nConnection: close\r\nUser-Agent: PHPUnit\r\n\r\n";

        $endOfHeaders = strpos($data, "\r\n\r\n");

        $psrFactory = new Psr17Factory();

        $requestFactory = new RequestFactory($psrFactory, $psrFactory, $psrFactory);

        $request = $requestFactory->createRequest(
            $data,
            $endOfHeaders,
            'http://127.0.0.1:80',
            'tcp://127.0.0.2:81'
        );

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
    }
}
