<?php

namespace DazzaDev\DgtCrSender;

use DazzaDev\DgtCrSender\Actions\Auth;
use DazzaDev\DgtCrSender\Actions\Search;
use DazzaDev\DgtCrSender\Actions\Send;

class Sender extends Client
{
    /**
     * Authenticate using the Auth class
     */
    public function auth(string $username, string $password): array
    {
        $auth = new Auth;
        $auth->setTestMode($this->isTest);

        $token = $auth->auth($username, $password);
        $this->setBearerToken($token['access_token']);
        $this->setRefreshToken($token['refresh_token']);

        return $token;
    }

    /**
     * Renew the token using the Auth class
     */
    public function renewToken(?string $refreshToken = null): array
    {
        $auth = new Auth;
        $auth->setTestMode($this->isTest);
        $refreshToken = $refreshToken ?? $this->getRefreshToken();

        $token = $auth->renewToken($refreshToken);
        $this->setBearerToken($token['access_token']);
        $this->setRefreshToken($token['refresh_token']);

        return $token;
    }

    /**
     * Logout using the Auth class
     */
    public function logout(?string $refreshToken = null): bool
    {
        $auth = new Auth;
        $auth->setTestMode($this->isTest);
        $refreshToken = $refreshToken ?? $this->getRefreshToken();

        return $auth->logout($refreshToken);
    }

    /**
     * Send DTE.
     */
    public function send(string $documentType, string $documentKey, string $date, string $signedXml)
    {
        $send = new Send;
        $send->setTestMode($this->isTest);
        $send->setIssuer($this->issuer);
        $send->setReceiver($this->receiver);
        $send->setCallbackUrl($this->callbackUrl);
        $send->setBearerToken($this->bearerToken);

        return $send->send($documentType, $documentKey, $date, $signedXml);
    }

    /**
     * get Documents.
     */
    public function getDocuments(int $offset = 0, int $limit = 50): array
    {
        $search = new Search;
        $search->setTestMode($this->isTest);
        $search->setIssuer($this->issuer);
        $search->setReceiver($this->receiver);
        $search->setBearerToken($this->bearerToken);

        // Limit must be between 1 and 50
        $limit = ($limit > 50) ? 50 : $limit;

        return $search->getDocuments($offset, $limit);
    }

    /**
     * Search DTE.
     */
    public function getDocument(string $documentKey): array
    {
        $search = new Search;
        $search->setTestMode($this->isTest);
        $search->setIssuer($this->issuer);
        $search->setReceiver($this->receiver);
        $search->setBearerToken($this->bearerToken);

        return $search->getDocument($documentKey);
    }

    /**
     * Check Status of a DTE.
     */
    public function checkStatus(string $documentKey): array
    {
        $search = new Search;
        $search->setTestMode($this->isTest);
        $search->setIssuer($this->issuer);
        $search->setReceiver($this->receiver);
        $search->setBearerToken($this->bearerToken);

        return $search->checkStatus($documentKey);
    }
}
