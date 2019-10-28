<?php

namespace Centrifugo;

/**
 * Class Request
 * @package Centrifugo
 */
class Request
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * @var string
     */
    protected $secret;
    /**
     * @var string
     */
    protected $method;
    /**
     * @var array
     */
    protected $params;

    /**
     * Request constructor.
     *
     * @param string $endpoint
     * @param string $secret
     * @param string $method
     * @param array $params
     */
    public function __construct($endpoint, $secret, $method, array $params)
    {
        $this->endpoint = $endpoint;
        $this->secret = $secret;
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @return string
     */
    public function getEncodedParams()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return [
            'Content-Type: application/json',
            'Authorization: apikey' . $this->secret,
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return ['method' => $this->method, 'params' => $this->params];
    }
}
