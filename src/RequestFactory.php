<?php

declare(strict_types=1);

namespace Idiosyncratic\Http\Server;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use React\Socket\ConnectionInterface;
use Throwable;

final class RequestFactory
{
    /** @var ServerRequestFactoryInterface */
    private $request;

    /** @var UriFactoryInterface */
    private $uri;

    /** @var StreamFactoryInterface */
    private $stream;

    public function __construct(
        ServerRequestFactoryInterface $request,
        UriFactoryInterface $uri,
        StreamFactoryInterface $stream
    ) {
        $this->request = $request;

        $this->uri = $uri;

        $this->stream = $stream;
    }

    public function createRequest(
        $data,
        int $endOfHeaders,
        string $localAddress,
        string $remoteAddress
    ) : ServerRequestInterface {
        $requestData = [];

        $requestData['server'] = $this->createServerParams($localAddress, $remoteAddress);

        $messageLines = array_filter(preg_split(
            '/(\\r?\\n)/',
            (string) substr($data, 0, $endOfHeaders),
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        ), static function ($messageLine) {
            return ! empty(trim($messageLine));
        });

        $startLine = array_shift($messageLines);

        $requestData = array_merge($requestData, $this->parseStartLine($startLine));

        $requestData['headers'] = $this->parseHeaders($messageLines);

        $requestData['uri'] = $this->uri->createUri(sprintf('%s%s', $localAddress, $requestData['path']));

        $queryParams = [];

        parse_str($requestData['uri']->getQuery(), $queryParams);

        $request = $this->request->createServerRequest(
            $requestData['method'],
            $requestData['uri'],
            $requestData['server']
        )->withQueryParams($queryParams)
         ->withRequestTarget($requestData['requestTarget'])
         ->withProtocolVersion($requestData['version']);

        $headers = $requestData['headers'];

        $request = array_reduce(array_keys($headers), static function ($request, $header) use ($headers) {
            return $request->withHeader($header, $headers[$header]);
        }, $request);

        return $request;
    }

    private function parseHeaders(array $headers) : array
    {
        $result = [];

        array_walk($headers, static function ($header, $key) use (&$result) : void {
            if (strpos($header, ':') === false) {
                return;
            }

            $parts           = explode(':', $header, 2);
            $name            = trim($parts[0]);
            $value           = isset($parts[1]) ? trim($parts[1]) : '';
            $result[$name][] = $value;
        });

        return $result;
    }

    private function parseStartLine(string $startLine) : array
    {
        $requestTarget = null;

        if (strncmp($startLine, 'OPTIONS * ', 10) === 0) {
            $startLine     = 'OPTIONS / ' . substr($startLine, 10);
            $requestTarget = '*';
        }

        if (strncmp($startLine, 'CONNECT ', 8) === 0) {
            $parts = explode(' ', $startLine, 3);
            $uri   = parse_url('tcp://' . $parts[1]);

            // check this is a valid authority-form request-target (host:port)
            if (isset($uri['scheme'], $uri['host'], $uri['port']) === false || count($uri) !== 3) {
                throw new InvalidArgumentException('CONNECT method MUST use authority-form request target');
            }

            $requestTarget = $parts[1];

            $parts[1] = 'http://' . $parts[1] . '/';

            $startLine = implode(' ', $parts);
        }

        $startLineParts    = explode(' ', $startLine, 3);
        $startLineSubParts = isset($startLineParts[2]) ?  explode('/', $startLineParts[2]) : [];

        return [
            'startLine' => $startLine,
            'requestTarget' => $requestTarget ?? $startLineParts[1],
            'method' => $startLineParts[0],
            'path' => $startLineParts[1],
            'version' => isset($startLineParts[2]) ? $startLineSubParts[1] : '1.1',
        ];
    }

    private function createServerParams(string $localAddress, string $remoteAddress) : array
    {
        $serverParams = array_merge(
            $_SERVER,
            [
                'REQUEST_TIME' => time(),
                'REQUEST_TIME_FLOAT' => microtime(true),
            ]
        );

        if ($remoteAddress !== null) {
            $remoteAddress               = parse_url($remoteAddress);
            $serverParams['REMOTE_ADDR'] = $remoteAddress['host'];
            $serverParams['REMOTE_PORT'] = $remoteAddress['port'];
        }

        if ($localAddress !== null) {
            $localAddress = parse_url($localAddress);

            if (isset($localAddress['host'], $localAddress['port'])) {
                $serverParams['SERVER_ADDR'] = $localAddress['host'];
                $serverParams['SERVER_PORT'] = $localAddress['port'];
            }

            if (isset($localAddress['scheme']) && $localAddress['scheme'] === 'https') {
                $serverParams['HTTPS'] = true;
            } else {
                $serverParams['HTTPS'] = false;
            }
        }

        return $serverParams;
    }
}
