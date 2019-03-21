<?php

declare(strict_types=1);

namespace Idiosyncratic\Http\Server;

use Idiosyncratic\Http\Server\Stub\BarMiddlewareHandler;
use Idiosyncratic\Http\Server\Stub\DebugRequestHandler;
use Idiosyncratic\Http\Server\Stub\FooMiddlewareHandler;
use Idiosyncratic\Http\Server\Stub\PassThroughMiddlewareHandler;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareRequestHandlerTest extends TestCase
{
    public function testHandlesRequestWithMiddleware()
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $psrFactory = new Psr17Factory();

        $handler = new MiddlewareRequestHandler(
            new DebugRequestHandler($psrFactory, $psrFactory),
            new PassThroughMiddlewareHandler($psrFactory),
            new FooMiddlewareHandler($psrFactory),
            new BarMiddlewareHandler($psrFactory)
        );

        $response = $handler->handle($request);

        $this->assertEquals('foo', (string) $response->getBody());
    }

    public function testHandlesRequestWithDefaultHandler()
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $psrFactory = new Psr17Factory();

        $handler = new MiddlewareRequestHandler(
            new DebugRequestHandler($psrFactory, $psrFactory),
            new PassThroughMiddlewareHandler($psrFactory)
        );

        $response = $handler->handle($request);

        $this->assertStringStartsWith('<html><body><pre><script>', (string) $response->getBody());
    }
}
