<?php

namespace PHPNomad\Guzzle\FetchIntegration\Tests\Acceptance;

use JsonException;
use PHPNomad\Guzzle\FetchIntegration\Models\Response;
use PHPUnit\Framework\TestCase;

final class ResponseJsonContractTest extends TestCase
{
    public function testSetJsonPreservesSuccessfulEncodingAndResponseState(): void
    {
        $response = new Response();
        $response->setHeader('X-Sentinel', 'preserved');
        $data = ['message' => 'accepted', 'nested' => ['count' => 1]];

        $result = $response->setJson($data);

        self::assertSame($response, $result);
        self::assertSame(json_encode($data), $response->getBody());
        self::assertSame('application/json', $response->getHeader('Content-Type'));
        self::assertSame('preserved', $response->getHeader('X-Sentinel'));
    }

    public function testSetJsonRejectsEncodingFailureWithoutMutatingResponse(): void
    {
        $response = new Response();
        $response->setBody('previous body');
        $response->setHeader('Content-Type', 'text/plain');

        try {
            $response->setJson(['invalidUtf8' => "\xB1\x31"]);
            self::fail('Invalid JSON input did not throw JsonException.');
        } catch (JsonException $exception) {
            self::assertNotSame('', $exception->getMessage());
        }

        self::assertSame('previous body', $response->getBody());
        self::assertSame('text/plain', $response->getHeader('Content-Type'));
    }

    /**
     * @dataProvider successfulScalarEncodingProvider
     * @param bool|float|int|string|null $data
     */
    public function testSetJsonPreservesSuccessfulScalarAndNullEncoding($data, string $body): void
    {
        $response = new Response();

        $result = $response->setJson($data);

        self::assertSame($response, $result);
        self::assertSame($body, $response->getBody());
        self::assertSame('application/json', $response->getHeader('Content-Type'));
    }

    /** @return array<string, array{bool|float|int|string|null, string}> */
    public function successfulScalarEncodingProvider(): array
    {
        return [
            'false' => [false, 'false'],
            'true' => [true, 'true'],
            'zero' => [0, '0'],
            'float' => [1.25, '1.25'],
            'string' => ['accepted', '"accepted"'],
            'null' => [null, 'null'],
        ];
    }

    public function testGetJsonPreservesArraysAndDecodesObjectsAsAssociativeArrays(): void
    {
        $response = new Response();
        $response->setBody('{"message":"accepted","nested":{"count":1}}');

        self::assertSame(
            ['message' => 'accepted', 'nested' => ['count' => 1]],
            $response->getJson()
        );

        $response->setBody('["first","second"]');
        self::assertSame(['first', 'second'], $response->getJson());

        $response->setBody('[]');
        self::assertSame([], $response->getJson());
    }

    public function testGetJsonTreatsUntouchedNullableBodyAsEmpty(): void
    {
        self::assertSame([], (new Response())->getJson());
    }

    /**
     * @dataProvider scalarJsonProvider
     * @param array<int, bool|float|int|string> $expected
     */
    public function testGetJsonWrapsSuccessfulNonNullScalars(string $body, array $expected): void
    {
        $response = new Response();
        $response->setBody($body);

        self::assertSame($expected, $response->getJson());
    }

    /** @return array<string, array{string, array<int, bool|float|int|string>}> */
    public function scalarJsonProvider(): array
    {
        return [
            'false' => ['false', [false]],
            'true' => ['true', [true]],
            'zero' => ['0', [0]],
            'float' => ['1.25', [1.25]],
            'empty string' => ['""', ['']],
            'string' => ['"accepted"', ['accepted']],
        ];
    }

    /**
     * @dataProvider emptyJsonProvider
     */
    public function testGetJsonKeepsEmptyMalformedAndNullBodiesEmpty(string $body): void
    {
        $response = new Response();
        $response->setBody($body);

        self::assertSame([], $response->getJson());
    }

    /** @return array<string, array{string}> */
    public function emptyJsonProvider(): array
    {
        return [
            'empty body' => [''],
            'malformed body' => ['{"message":'],
            'JSON null' => ['null'],
        ];
    }
}
