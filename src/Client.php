<?php

namespace DazzaDev\DgtCrSender;

use DazzaDev\DgtCrSender\Exceptions\DGTException;
use DazzaDev\DgtCrSender\Exceptions\SenderException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

abstract class Client
{
    /**
     * HTTP client instance
     */
    protected GuzzleClient $httpClient;

    /**
     * Whether to use test environment
     */
    protected bool $isTest = false;

    /**
     * Callback URL for DTE events
     */
    protected ?string $callbackUrl = null;

    /**
     * Issuer information
     */
    protected array $issuer = [];

    /**
     * Receiver information
     */
    protected array $receiver = [];

    /**
     * API URL for production environment
     */
    private const API_URL_PROD = 'https://api.comprobanteselectronicos.go.cr/recepcion/v1';

    /**
     * API URL for sandbox environment
     */
    private const API_URL_SANDBOX = 'https://api-sandbox.comprobanteselectronicos.go.cr/recepcion/v1';

    /**
     * Bearer token for Authorization header (full value, e.g. "Bearer <token>")
     */
    protected ?string $bearerToken = null;

    /**
     * Refresh token for token renewal
     */
    protected ?string $refreshToken = null;

    /**
     * Constructor to initialize the HTTP client
     */
    public function __construct()
    {
        $this->httpClient = new GuzzleClient;
    }

    /**
     * Set the environment to test mode
     */
    public function setTestMode(bool $isTest = true): self
    {
        $this->isTest = $isTest;

        return $this;
    }

    /**
     * Set bearer token (expects full header value, e.g. "Bearer <token>")
     */
    public function setBearerToken(?string $bearerToken): self
    {
        $this->bearerToken = $bearerToken;

        return $this;
    }

    /**
     * Get bearer token (full header value)
     */
    public function getBearerToken(): ?string
    {
        return $this->bearerToken;
    }

    /**
     * Set refresh token
     */
    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Get refresh token
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Get whether test mode is enabled
     */
    public function isTestMode(): bool
    {
        return $this->isTest;
    }

    /**
     * Get the API URL based on environment
     */
    public function getApiUrl(): string
    {
        return $this->isTest ? self::API_URL_SANDBOX : self::API_URL_PROD;
    }

    /**
     * Set callback URL
     */
    public function setCallbackUrl(?string $callbackUrl): self
    {
        $this->callbackUrl = $callbackUrl;

        return $this;
    }

    /**
     * Get callback URL
     */
    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }

    /**
     * Set issuer information
     */
    public function setIssuer(array $issuer): self
    {
        $this->issuer = $issuer;

        return $this;
    }

    /**
     * Get issuer information
     */
    public function getIssuer(): array
    {
        return $this->issuer;
    }

    /**
     * Get issuer identification
     */
    public function getIssuerIdentification(): string
    {
        return $this->issuer['identification_type'].$this->issuer['identification_number'];
    }

    /**
     * Set receiver information
     */
    public function setReceiver(array $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver information
     */
    public function getReceiver(): array
    {
        return $this->receiver;
    }

    /**
     * Get receiver identification
     */
    public function getReceiverIdentification(): string
    {
        return $this->receiver['identification_type'].$this->receiver['identification_number'];
    }

    /**
     * Ensure bearer token is present
     */
    protected function ensureBearerToken(): void
    {
        if (empty($this->bearerToken)) {
            throw new SenderException('Missing bearer token: autentica primero con Client::auth');
        }
    }

    /**
     * Wrap a request with consistent RequestException handling
     */
    protected function handleRequest(callable $request, string $errorContext = 'Error'): array
    {
        try {
            return $request();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $errorBody = (string) $response->getBody();
                $errorCause = $response->getHeaderLine('X-Error-Cause');
                $validationException = $response->getHeaderLine('validation-exception');

                // Handle 400 errors with X-Error-Cause
                $message = $errorContext.': '.$statusCode.' - ';
                if ($statusCode === 400 && $errorCause !== '') {
                    $message .= $errorCause;
                } else {
                    $message .= $errorBody;
                }

                // Handle validation errors with validation-exception
                if ($validationException !== '') {
                    $message .= ' | '.$validationException;
                }

                throw new DGTException($message);
            }

            throw new DGTException($e->getMessage());
        }
    }

    /**
     * POST Method
     */
    public function post(string $url, array $payload, array $options = []): array
    {
        $options = array_merge([
            'json' => $payload,
            'headers' => [
                'Authorization' => 'Bearer '.$this->bearerToken,
                'Content-Type' => 'application/json',
                'charset' => 'utf-8',
            ],
        ], $options);

        $response = $this->httpClient->post($url, $options);

        $responseBody = json_decode($response->getBody()->getContents(), true);

        return $responseBody ?? [];
    }

    /**
     * GET Method
     */
    public function get(string $url, array $options = []): array
    {
        $options = array_merge([
            'headers' => [
                'Authorization' => 'Bearer '.$this->bearerToken,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ], $options);

        $response = $this->httpClient->get($url, $options);

        $responseBody = json_decode($response->getBody()->getContents(), true);

        return $responseBody ?? [];
    }
}
