<?php

declare(strict_types=1);

namespace Idiosyncratic\Http\Server;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

class DefaultRequestHandlerTest extends TestCase
{
    public function testHandlesRequest() : void
    {
        $psrFactory = new Psr17Factory();

        $request = $this->createMock(ServerRequestInterface::class);

        $handler = new DefaultRequestHandler($psrFactory);

        $response = $handler->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertEquals(404, $response->getStatusCode());

        $this->assertEquals('Not Found', $response->getReasonPhrase());
    }
}
