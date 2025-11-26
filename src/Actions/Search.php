<?php

namespace DazzaDev\DgtCrSender\Actions;

use DazzaDev\DgtCrSender\Client;

class Search extends Client
{
    /**
     * Obtiene un resumen de todos los comprobantes electrónicos
     * que ha enviado el obligado tributario
     * ordenado de forma descendente por la fecha.
     */
    public function getDocuments($offset = 0, $limit = 50): array
    {
        $this->ensureBearerToken();

        return $this->handleRequest(function () use ($offset, $limit) {
            return $this->get($this->getApiUrl().'/comprobantes', [
                'query' => [
                    'offset' => $offset,
                    'limit' => $limit,
                    'emisor' => $this->getIssuerIdentification(),
                    'receptor' => $this->getReceiverIdentification(),
                ],
            ]);
        });
    }

    /**
     * Obtiene el comprobante indicado por la clave.
     */
    public function getDocument(string $documentKey): array
    {
        $this->ensureBearerToken();

        return $this->handleRequest(function () use ($documentKey) {
            return $this->get($this->getApiUrl().'/comprobantes/'.$documentKey);
        });
    }

    /**
     * Obtiene el estado del comprobante indicado por la clave.
     */
    public function checkStatus(string $documentKey): array
    {
        $this->ensureBearerToken();

        return $this->handleRequest(function () use ($documentKey) {
            return $this->get($this->getApiUrl().'/recepcion/'.$documentKey);
        });
    }
}
