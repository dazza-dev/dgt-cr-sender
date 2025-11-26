<?php

namespace DazzaDev\DgtCrSender\Actions;

use DazzaDev\DgtCrSender\Client;

class Send extends Client
{
    /**
     * Recibe el comprobante electrónico o respuesta del receptor.
     */
    public function send(string $documentType, string $documentKey, string $date, string $signedXml): array
    {
        $this->ensureBearerToken();

        return $this->handleRequest(function () use ($documentType, $documentKey, $date, $signedXml) {
            return $this->post(
                url: $this->getApiUrl().'/recepcion',
                payload: $this->buildPayload(
                    $documentType,
                    $documentKey,
                    $date,
                    $signedXml
                )
            );
        });
    }

    /**
     * Build the payload for the API.
     */
    private function buildPayload(string $documentType, string $documentKey, string $date, string $signedXml): array
    {
        $issuer = $this->getIssuer();
        $receiver = $this->getReceiver();

        $payload = [
            'clave' => $documentKey,
            'fecha' => $date,
            'emisor' => [
                'tipoIdentificacion' => $issuer['identification_type'],
                'numeroIdentificacion' => $issuer['identification_number'],
            ],
            'receptor' => [
                'tipoIdentificacion' => $receiver['identification_type'],
                'numeroIdentificacion' => $receiver['identification_number'],
            ],
            'comprobanteXml' => $signedXml,
        ];

        // Add receiver sequence number if it's set
        if ($documentType == 'receiver-message' && isset($receiver['sequential_number'])) {
            $payload['consecutivoReceptor'] = $receiver['sequential_number'];
        }

        // Add callback URL if it's set
        if ($this->getCallbackUrl()) {
            $payload['callbackUrl'] = $this->getCallbackUrl().'/'.$documentType;
        }

        return $payload;
    }
}
