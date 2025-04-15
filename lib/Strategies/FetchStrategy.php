<?php

namespace PHPNomad\Guzzle\FetchIntegration\Strategies;



use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use PHPNomad\Guzzle\FetchIntegration\Models\Response;
use PHPNomad\Fetch\Interfaces\FetchStrategy as FetchStrategyInterface;
use PHPNomad\Fetch\Models\FetchPayload;
use PHPNomad\Rest\Exceptions\RestException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class FetchStrategy
 * Implements the FetchStrategy interface using Guzzle.
 */
class FetchStrategy implements FetchStrategyInterface
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Perform an HTTP request using Guzzle based on the given payload.
     *
     * @param FetchPayload $payload Contains the request data.
     * @return Response The response object containing status, headers, and body.
     * @throws RestException In case of a request error.
     */
    public function fetch(FetchPayload $payload): Response
    {
        $url = $payload->getUrl();
        $method = $payload->getMethod();
        $headers = $payload->getHeaders();
        $body = $payload->getBody();
        $params = $payload->getParams();


        // Perform the request through a separate method to handle exceptions
        $guzzleResponse = $this->performRequest($method, $url, [
            'headers' => $headers,
            'body' => $body,
            'query' => $params,
        ]);

        // Initialize the response object
        $response = new Response();
        $response->setStatus($guzzleResponse->getStatusCode())
            ->setBody($guzzleResponse->getBody()->getContents());

        // Set headers
        foreach ($guzzleResponse->getHeaders() as $name => $values) {
            $response->setHeader($name, implode(', ', $values));
        }

        return $response;
    }

    /**
     * Perform the actual Guzzle request and handle exceptions.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $url The URL to send the request to.
     * @param array $options Guzzle options array.
     * @return ResponseInterface The Guzzle response.
     * @throws RestException
     */
    protected function performRequest(string $method, string $url, array $options): ResponseInterface
    {
        try {
            return $this->client->request($method, $url, $options);
        } catch (RequestException|GuzzleException $e) {
            // Convert Guzzle exception to a generic REST exception
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
            throw new RestException($e->getMessage(), $e->getHandlerContext(), $statusCode);
        }
    }
}
