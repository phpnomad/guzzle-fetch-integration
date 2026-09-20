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
    /** @var array<string, string> */
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
        $body = json_encode($data, JSON_THROW_ON_ERROR);
        $this->body = $body;
        $this->setHeader('Content-Type', 'application/json');
        return $this;
    }

    /**
     * Get the response body content as a JSON-decoded array.
     *
     * @return array<mixed> The JSON-decoded body content.
     */
    public function getJson(): array
    {
        if ($this->body === null || $this->body === '') {
            return [];
        }

        $decoded = json_decode($this->body, true);
        if (json_last_error() !== JSON_ERROR_NONE || $decoded === null) {
            return [];
        }

        return is_array($decoded) ? $decoded : [$decoded];
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

    /** @return array<string, string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
