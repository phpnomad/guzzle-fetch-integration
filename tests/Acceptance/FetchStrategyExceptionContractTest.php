<?php

namespace PHPNomad\Guzzle\FetchIntegration\Tests\Acceptance;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPNomad\Fetch\Models\FetchPayload;
use PHPNomad\Guzzle\FetchIntegration\Strategies\FetchStrategy;
use PHPNomad\Rest\Exceptions\RestException;
use PHPUnit\Framework\TestCase;

final class FetchStrategyExceptionContractTest extends TestCase
{
    public function testRequestExceptionWithResponsePreservesDetails(): void
    {
        $context = ['handler' => 'response'];
        $request = new Request('GET', 'https://example.test/resource');
        $exception = new RequestException(
            'The upstream service refused the request.',
            $request,
            new Response(429),
            null,
            $context
        );

        $result = $this->captureRestException($this->strategyFor($exception));

        self::assertSame(429, $result->getCode());
        self::assertSame('The upstream service refused the request.', $result->getMessage());
        self::assertSame($context, $result->getContext());
    }

    public function testRequestExceptionWithoutResponseBecomesServerError(): void
    {
        $context = ['handler' => 'request'];
        $request = new Request('GET', 'https://example.test/resource');
        $exception = new RequestException(
            'The request failed before a response arrived.',
            $request,
            null,
            null,
            $context
        );

        $result = $this->captureRestException($this->strategyFor($exception));

        self::assertSame(500, $result->getCode());
        self::assertSame('The request failed before a response arrived.', $result->getMessage());
        self::assertSame($context, $result->getContext());
    }

    public function testConnectExceptionBecomesServerError(): void
    {
        $context = ['errno' => 7, 'error' => 'connection refused'];
        $request = new Request('GET', 'https://example.test/resource');
        $exception = new ConnectException(
            'The connection failed.',
            $request,
            null,
            $context
        );

        $result = $this->captureRestException($this->strategyFor($exception));

        self::assertSame(500, $result->getCode());
        self::assertSame('The connection failed.', $result->getMessage());
        self::assertSame($context, $result->getContext());
    }

    public function testGenericGuzzleExceptionBecomesServerErrorWithoutContext(): void
    {
        $exception = new TransferException('The transport failed.');

        $result = $this->captureRestException($this->strategyFor($exception));

        self::assertSame(500, $result->getCode());
        self::assertSame('The transport failed.', $result->getMessage());
        self::assertSame([], $result->getContext());
    }

    private function strategyFor(TransferException $exception): FetchStrategy
    {
        $handler = HandlerStack::create(new MockHandler([$exception]));

        return new ClientBackedFetchStrategy(new Client(['handler' => $handler]));
    }

    private function captureRestException(FetchStrategy $strategy): RestException
    {
        try {
            $strategy->fetch(new FetchPayload('https://example.test/resource', null));
            self::fail('The failed Guzzle request did not throw a RestException.');
        } catch (RestException $exception) {
            return $exception;
        }
    }
}

final class ClientBackedFetchStrategy extends FetchStrategy
{
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}
