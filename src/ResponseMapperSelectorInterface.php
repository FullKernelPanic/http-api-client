<?php

declare(strict_types=1);

namespace HttpApiClient;

use ApiClientBase\RequestInterface;
use ApiClientBase\ResponseInterface;
use ApiClientBase\UnsupportedResponseType;

interface ResponseMapperSelectorInterface
{
    /**
     * @throws UnsupportedResponseType
     */
    public function mapResponse(
        RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response
    ): ResponseInterface;
}
