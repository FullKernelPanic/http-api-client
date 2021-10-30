<?php

declare(strict_types=1);

namespace HttpApiClient;

use ApiClientBase\ApiException;
use ApiClientBase\RequestInterface;
use ApiClientBase\ResponseInterface;
use ApiClientBase\UnsupportedRequestType;
use ApiClientBase\UnsupportedResponseType;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class Client implements \ApiClientBase\Client
{
    private HttpClient $client;
    private LoggerInterface $logger;
    private ResponseMapperSelectorInterface $responseFactory;

    public function __construct(
        HttpClient $client,
        LoggerInterface $logger,
        ResponseMapperSelectorInterface $responseFactory
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @throws UnsupportedRequestType|ApiException|UnsupportedResponseType
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $this->assureSupportedRequestType($request);

        try {
            /** @var Request $request */
            $response = $this->client->send($request);

            $this->logger->info(
                'Successfull api call ' . $request->getUri(),
                [
                    'request' => $request,
                    'response' => $response,
                ]
            );

            return $this->responseFactory->mapResponse($request, $response);
        } catch (GuzzleException $exception) {
            $this->logger->error($exception->getMessage(), ['request' => $request]);

            throw new ApiException('Error during the API call', 0, $exception);
        }
    }

    /**
     * @param RequestInterface $request
     *
     * @throws UnsupportedRequestType
     */
    private function assureSupportedRequestType(RequestInterface $request): void
    {
        if (!($request instanceof Request)) {
            throw new UnsupportedRequestType(sprintf('Request must extend %s class', Request::class));
        }
    }
}
