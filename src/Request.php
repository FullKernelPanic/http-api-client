<?php

declare(strict_types=1);

namespace HttpApiClient;

use ApiClientBase\RequestInterface;

abstract class Request extends \GuzzleHttp\Psr7\Request implements RequestInterface
{
}
