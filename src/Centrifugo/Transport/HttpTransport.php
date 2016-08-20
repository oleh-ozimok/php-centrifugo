<?php

namespace Centrifugo\Transport;

use Exception;
use Centrifugo\Request;
use Centrifugo\RequestHandler;
use Centrifugo\Exceptions\CentrifugoTransportException;

/**
 * Class HttpTransport
 * @package Centrifugo\Transport
 */
class HttpTransport extends RequestHandler
{
    const HTTP_CODE_OK = 200;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * HttpClient constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    public function processing(Request $request)
    {
        $connection = curl_init();
        try {
            curl_setopt_array($connection, $this->options);
            curl_setopt_array($connection, [
                CURLOPT_URL            => $request->getEndpoint(),
                CURLOPT_HTTPHEADER     => $request->getHeaders(),
                CURLOPT_POSTFIELDS     => $request->getEncodedParams(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true
            ]);
            $rawResponse = curl_exec($connection);
            if (curl_errno($connection)) {
                throw new CentrifugoTransportException('HttpClient CURL error: ' . curl_error($connection));
            }
            if (($httpCode = curl_getinfo($connection, CURLINFO_HTTP_CODE)) != self::HTTP_CODE_OK) {
                throw new CentrifugoTransportException('HttpClient return invalid response code: ' . $httpCode);
            }
            curl_close($connection);
        } catch (Exception $exception){
            curl_close($connection);
            throw $exception;
        }

        return $rawResponse;
    }
}