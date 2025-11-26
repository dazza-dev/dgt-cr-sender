<?php

namespace DazzaDev\DgtCrSender\Actions;

use DazzaDev\DgtCrSender\Client;
use DazzaDev\DgtCrSender\Exceptions\AuthException;
use GuzzleHttp\Exception\GuzzleException;

class Auth extends Client
{
    /**
     * Base AUTH URL
     */
    private const AUTH_URL_BASE = 'https://idp.comprobanteselectronicos.go.cr/auth/realms';

    /**
     * Production AUTH URL
     */
    private const AUTH_URL_PROD = '/rut/protocol/openid-connect';

    /**
     * Test AUTH URL
     */
    private const AUTH_URL_TEST = '/rut-stag/protocol/openid-connect';

    /**
     * Authenticate
     */
    public function auth(string $username, string $password): array
    {
        try {
            $response = $this->httpClient->post($this->getAuthUrl().'/token', [
                'form_params' => [
                    'client_id' => $this->getClientId(),
                    'username' => $username,
                    'password' => $password,
                    'grant_type' => 'password',
                ],
            ]);

            $responseBody = $response->getBody()->getContents();

            return json_decode($responseBody, true);
        } catch (GuzzleException $e) {
            throw new AuthException($e->getMessage());
        }
    }

    /**
     * Renew the token
     */
    public function renewToken(string $refreshToken): array
    {
        try {
            $response = $this->httpClient->post($this->getAuthUrl().'/token', [
                'form_params' => [
                    'client_id' => $this->getClientId(),
                    'refresh_token' => $refreshToken,
                    'grant_type' => 'refresh_token',
                ],
            ]);

            $responseBody = $response->getBody()->getContents();

            return json_decode($responseBody, true);
        } catch (GuzzleException $e) {
            throw new AuthException($e->getMessage());
        }
    }

    /**
     * Logout
     */
    public function logout(string $refreshToken): bool
    {
        try {
            $response = $this->httpClient->post($this->getAuthUrl().'/logout', [
                'form_params' => [
                    'client_id' => $this->getClientId(),
                    'refresh_token' => $refreshToken,
                ],
            ]);

            if ($response->getStatusCode() === 204) {
                return true;
            }

            return false;
        } catch (GuzzleException $e) {
            throw new AuthException($e->getMessage());
        }
    }

    /**
     * Get the API URL based on the test mode
     */
    private function getAuthUrl(): string
    {
        $authUrl = $this->isTest ? self::AUTH_URL_TEST : self::AUTH_URL_PROD;

        return self::AUTH_URL_BASE.$authUrl;
    }

    /**
     * Get the Client ID based on environment
     */
    public function getClientId(): string
    {
        return $this->isTest ? 'api-stag' : 'api-prod';
    }
}
