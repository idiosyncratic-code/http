<?php

declare(strict_types=1);

namespace Idiosyncratic\Http\Server\Stub;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BarMiddlewareHandler implements MiddlewareInterface
{
    private $psrFactory;

    public function __construct(Psr17Factory $psrFactory)
    {
        $this->psrFactory = $psrFactory;
    }

    /**
     * @inheritdoc
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $this->psrFactory->createResponse(200, '0K')
                         ->withBody($this->psrFactory->createStream());

        $response->getBody()->write('bar');

        return $response;
    }
}
