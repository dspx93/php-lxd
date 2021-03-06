<?php

namespace Dspx93\Lxd\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Http\Client\Exception\HttpException;
use Dspx93\Lxd\Exception\OperationException;
use Dspx93\Lxd\Exception\AuthenticationFailedException;
use Dspx93\Lxd\Exception\NotFoundException;
use Dspx93\Lxd\Exception\ConflictException;

/**
 * Handle LXD errors
 *
 */
class LxdExceptionThower implements Plugin
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first) : \Http\Promise\Promise
    {
        return $next($request)->then(function (ResponseInterface $response) use ($request) {
            return $response;
        }, function (\Http\Client\Exception $e) use ($request) {
            if (get_class($e) === HttpException::class) {
                $response = $e->getResponse();

                if (401 === $response->getStatusCode()) {
                    throw new OperationException($request, $response, $e);
                }

                if (403 === $response->getStatusCode()) {
                    throw new AuthenticationFailedException($request, $response, $e);
                }

                if (404 === $response->getStatusCode()) {
                    throw new NotFoundException($request, $response, $e);
                }

                if (409 === $response->getStatusCode()) {
                    throw new ConflictException($request, $response, $e);
                }
            }

            throw $e;
        });
    }
}
