<?php

namespace PHPNomad\Guzzle\FetchIntegration\Tests\Integration;

use PHPNomad\Fetch\Models\FetchPayload;
use PHPNomad\Guzzle\FetchIntegration\Strategies\FetchStrategy;
use PHPNomad\Rest\Exceptions\RestException;
use PHPUnit\Framework\TestCase;

final class ClosedLoopbackFailureContractTest extends TestCase
{
    public function testClosedLoopbackConnectionBecomesServerError(): void
    {
        self::markTestIncomplete('Remove this marker when implementing the accepted exception contract.');

        $server = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
        self::assertIsResource(
            $server,
            sprintf('Could not reserve a loopback port: [%d] %s', $errorCode, $errorMessage)
        );

        $address = stream_socket_get_name($server, false);
        fclose($server);

        self::assertIsString($address);

        try {
            (new FetchStrategy())->fetch(new FetchPayload('http://' . $address . '/closed', null));
            self::fail('The closed loopback connection did not throw a RestException.');
        } catch (RestException $exception) {
            self::assertSame(500, $exception->getCode());
            self::assertNotSame('', $exception->getMessage());
        }
    }
}
