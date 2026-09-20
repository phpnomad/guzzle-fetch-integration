<?php

namespace PHPNomad\Guzzle\FetchIntegration\Tests\Acceptance;

use JsonException;
use PHPNomad\Guzzle\FetchIntegration\Models\Response;
use PHPUnit\Framework\TestCase;

final class ResponseJsonContractTest extends TestCase
{
    public function testSetJsonPreservesSuccessfulEncodingAndResponseState(): void
    {
        self::markTestIncomplete('Remove this marker when implementing the accepted response JSON contract.');

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
        self::markTestIncomplete('Remove this marker when implementing the accepted response JSON contract.');

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

    public function testGetJsonPreservesArraysAndDecodesObjectsAsAssociativeArrays(): void
    {
        self::markTestIncomplete('Remove this marker when implementing the accepted response JSON contract.');

        $response = new Response();
        $response->setBody('{"message":"accepted","nested":{"count":1}}');

        self::assertSame(
            ['message' => 'accepted', 'nested' => ['count' => 1]],
            $response->getJson()
        );
    }

    /**
     * @dataProvider scalarJsonProvider
     * @param array<int, bool|int|string> $expected
     */
    public function testGetJsonWrapsSuccessfulNonNullScalars(string $body, array $expected): void
    {
        self::markTestIncomplete('Remove this marker when implementing the accepted response JSON contract.');

        $response = new Response();
        $response->setBody($body);

        self::assertSame($expected, $response->getJson());
    }

    /** @return array<string, array{string, array<int, bool|int|string>}> */
    public function scalarJsonProvider(): array
    {
        return [
            'false' => ['false', [false]],
            'zero' => ['0', [0]],
            'empty string' => ['""', ['']],
            'string' => ['"accepted"', ['accepted']],
        ];
    }

    /**
     * @dataProvider emptyJsonProvider
     */
    public function testGetJsonKeepsEmptyMalformedAndNullBodiesEmpty(string $body): void
    {
        self::markTestIncomplete('Remove this marker when implementing the accepted response JSON contract.');

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
