<?php

declare(strict_types=1);

namespace HttpApiClient\Test\Unit;

use ApiClientBase\ApiException;
use ApiClientBase\RequestInterface;
use ApiClientBase\ResponseInterface;
use ApiClientBase\UnsupportedRequestType;
use ApiClientBase\UnsupportedResponseType;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use HttpApiClient\Client;
use HttpApiClient\Request;
use HttpApiClient\ResponseMapperSelectorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as PSRResponseInterface;
use Psr\Log\LoggerInterface;

class ClientTest extends TestCase
{
    private GuzzleClient|MockObject $guzzleClient;
    private MockObject|LoggerInterface $logger;
    private MockObject|ResponseFactoryInterface $responseFactory;

    protected function setUp(): void
    {
        $this->guzzleClient = $this->createMock(GuzzleClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->responseFactory = $this->createMock(ResponseMapperSelectorInterface::class);

        $this->client = new Client($this->guzzleClient, $this->logger, $this->responseFactory);
    }

    public function testSendSuccesfulCall(): void
    {
        $request = $this->createMock(Request::class);
        $response = $this->createMock(ResponseInterface::class);
        $psrResponse = $this->createMock(PSRResponseInterface::class);

        $this->guzzleClient
            ->expects($this->once())
            ->method('send')
            ->with($request)
            ->willReturn($psrResponse);

        $this->responseFactory
            ->expects($this->once())
            ->method('mapResponse')
            ->with($request, $psrResponse)
            ->willReturn($response);

        $this->assertSame($response, $this->client->send($request));
    }

    public function testSendUnsupportedRequestType(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $this->guzzleClient
            ->expects($this->never())
            ->method('send');

        $this->responseFactory
            ->expects($this->never())
            ->method('mapResponse');

        $this->expectException(UnsupportedRequestType::class);

        $this->client->send($request);
    }

    public function testSendHttpClientError(): void
    {
        $request = $this->createMock(Request::class);

        $this->guzzleClient
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new ClientException('foo', $request, $this->createMock(PSRResponseInterface::class)));

        $this->responseFactory
            ->expects($this->never())
            ->method('mapResponse');

        $this->expectException(ApiException::class);

        $this->client->send($request);
    }

    public function testSendResponseMapperError(): void
    {
        $request = $this->createMock(Request::class);
        $psrResponse = $this->createMock(PSRResponseInterface::class);

        $this->guzzleClient
            ->expects($this->once())
            ->method('send')
            ->with($request)
            ->willReturn($psrResponse);

        $this->responseFactory
            ->expects($this->once())
            ->method('mapResponse')
            ->with($request, $psrResponse)
            ->willThrowException(new UnsupportedResponseType());

        $this->expectException(UnsupportedResponseType::class);

        $this->client->send($request);
    }
}
