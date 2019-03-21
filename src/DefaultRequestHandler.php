<?php

declare(strict_types=1);

namespace Idiosyncratic\Http\Server;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DefaultRequestHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    private $response;

    public function __construct(
        ResponseFactoryInterface $response
    ) {
        $this->response = $response;
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->response->createResponse(404, 'Not Found');
    }
}
