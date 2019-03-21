<?php

declare(strict_types=1);

namespace Idiosyncratic\Http\Server\Stub;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

final class DebugRequestHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    private $response;

    /** @var StreamFactoryInterface */
    private $stream;

    private $htmlDumper;

    private $cloner;

    public function __construct(
        ResponseFactoryInterface $response,
        StreamFactoryInterface $stream
    ) {
        $this->response = $response;

        $this->stream = $stream;

        $this->htmlDumper = new HtmlDumper();

        $this->cloner = new VarCloner();
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->response->createResponse(200, '0K')
                         ->withBody($this->stream->createStream());

        $response->getBody()->write(sprintf(
            '<html><body><pre>%s</pre></body></html>',
            $this->htmlDumper->dump($this->cloner->cloneVar($request), true)
        ));

        return $response;
    }
}
