<?php

namespace PHPNomad\Guzzle\FetchIntegration\Models;

use PHPNomad\Http\Interfaces\Response as ResponseInterface;

/**
 * Class Response
 * Implements the Response interface to manage HTTP response data.
 */
class Response implements ResponseInterface
{
    protected int $status;
    protected array $headers = [];
    protected ?string $body = null;
    protected ?string $errorMessage = null;

    /**
     * Set the HTTP status code for the response.
     *
     * @param int $code The HTTP status code.
     * @return $this
     */
    public function setStatus(int $code)
    {
        $this->status = $code;
        return $this;
    }

    /**
     * Get the HTTP status code for the response.
     *
     * @return int The HTTP status code.
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Set an HTTP header for the response.
     *
     * @param string $name Header name.
     * @param string $value Header value.
     * @return $this
     */
    public function setHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Get the value of an HTTP header.
     *
     * @param string $name Header name.
     * @return string|null The header value, or null if not set.
     */
    public function getHeader(string $name)
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Set the body content of the response.
     *
     * @param string $body The body content.
     * @return $this
     */
    public function setBody(string $body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get the body content of the response as a string.
     *
     * @return string The body content.
     */
    public function getBody(): string
    {
        return $this->body ?? '';
    }

    /**
     * Set the response body as JSON.
     *
     * @param mixed $data The data to be encoded as JSON.
     * @return $this
     */
    public function setJson($data)
    {
        $this->body = json_encode($data);
        $this->setHeader('Content-Type', 'application/json');
        return $this;
    }

    /**
     * Get the response body content as a JSON-decoded array.
     *
     * @return array The JSON-decoded body content.
     */
    public function getJson(): array
    {
        return json_decode($this->body, true) ?? [];
    }

    /**
     * Set an error message for the response and set the HTTP status code.
     *
     * @param string $message The error message.
     * @param int $code The HTTP status code (default: 400).
     * @return $this
     */
    public function setError(string $message, int $code = 400)
    {
        $this->errorMessage = $message;
        $this->setStatus($code);
        $this->setJson(['error' => $message]);
        return $this;
    }

    /**
     * Get the error message from the response, if any.
     *
     * @return string|null The error message, or null if no error.
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Get the response data as an object.
     *
     * @return object The response data object containing status, headers, and body.
     */
    public function getResponse(): object
    {
        return (object)[
            'status' => $this->status,
            'headers' => $this->headers,
            'body' => $this->body,
            'error' => $this->errorMessage,
        ];
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}