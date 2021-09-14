<?php

namespace HttpApiClient;

use ApiClientBase\RequestInterface;

abstract class Request extends \GuzzleHttp\Psr7\Request implements RequestInterface
{

}