<?php

namespace Centrifugo;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * Class BatchResponse
 * @package Centrifugo
 */
class BatchResponse extends Response implements IteratorAggregate, ArrayAccess
{
    /**
     * @var BatchRequest
     */
    protected $batchRequest;
    /**
     * @var Response[]
     */
    protected $responses = [];

    /**
     * BatchResponse constructor.
     * @param BatchRequest $batchRequest
     * @param Response $response
     */
    public function __construct(BatchRequest $batchRequest, Response $response)
    {
        $this->batchRequest = $batchRequest;
        $request = $response->getRequest();
        $body = $response->getBody();

        parent::__construct($request, $body);

        $responses = $response->getDecodedBody();

        $this->setResponses($responses);
    }

    /**
     * @param array $responses
     */
    public function setResponses(array $responses)
    {
        $this->responses = [];
        foreach ($responses as $key => $response) {
            $this->addResponse($key, $response);
        }
    }

    /**
     * Add a response to the list.
     *
     * @param int        $key
     * @param array|null $response
     */
    public function addResponse($key, array $response)
    {
        $originalRequest = isset($this->batchRequest[$key]) ? $this->batchRequest[$key] : null;
        $responseBody    = isset($response['body']) ? $response['body'] : null;
        $responseError   = isset($response['error']) ? $response['error'] : null;
        $responseMethod  = isset($response['method']) ? $response['method'] : null;

        $this->responses[$key] = new Response($originalRequest, $responseBody, $responseError, $responseMethod);
    }

    /**
     * @return Response[]
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * @return Response
     */
    public function shiftResponses()
    {
        return array_shift($this->responses);
    }

    /***
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->responses);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->addResponse($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->responses[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->responses[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return isset($this->responses[$offset]) ? $this->responses[$offset] : null;
    }
}