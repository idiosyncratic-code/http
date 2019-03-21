<?php

declare(strict_types=1);

namespace Idiosyncratic\Http\Server\Stub;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PassThroughMiddlewareHandler implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return $handler->handle($request);
    }
}
