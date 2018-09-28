<?php

namespace Centrifugo\Clients;

use Centrifugo\Exceptions\CentrifugoTransportException;
use Centrifugo\Request;

/**
 * Class HttpClient
 * @package Centrifugo\Clients
 */
class HttpClient extends BaseClient
{
    const HTTP_CODE_OK = 200;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * HttpClient constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    public function processRequest(Request $request)
    {
        $connection = curl_init();

        try {
            curl_setopt_array($connection, $this->options);
            curl_setopt_array($connection, [
                CURLOPT_URL            => $request->getEndpoint(),
                CURLOPT_HTTPHEADER     => $request->getHeaders(),
                CURLOPT_POSTFIELDS     => $request->getEncodedParams(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
            ]);

            $rawResponse = curl_exec($connection);

            if (curl_errno($connection)) {
                throw new CentrifugoTransportException('HttpClient CURL error: ' . curl_error($connection));
            }

            $httpCode = curl_getinfo($connection, CURLINFO_HTTP_CODE);

            if ($httpCode != self::HTTP_CODE_OK) {
                throw new CentrifugoTransportException('HttpClient return invalid response code: ' . $httpCode);
            }
        } finally {
            curl_close($connection);
        }

        return $rawResponse;
    }
}
