<?php

namespace Centrifugo;

use Centrifugo\Exceptions\CentrifugoException;
use Centrifugo\Exceptions\CentrifugoResponseException;

/**
 * Class Response
 * @package Centrifugo
 */
class Response
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var array
     */
    protected $body;
    /**
     * @var string
     */
    protected $error;
    /**
     * @var string
     */
    protected $method;
    /**
     * @var array
     */
    protected $decodedBody = [];
    /**
     * @var CentrifugoException
     */
    protected $thrownException;

    /**
     * Response constructor.
     *
     * @param Request $request
     * @param string|array $body
     * @param null $error
     * @param null $method
     */
    public function __construct(Request $request, $body, $error = null, $method = null)
    {
        $this->request = $request;
        $this->body = $body;
        $this->error = $error;
        $this->method = $method;
        $this->decodeBody();
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getDecodedBody()
    {
        return $this->decodedBody;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return boolean
     */
    public function isError()
    {
        return isset($this->error);
    }

    /**
     * Throws the exception.
     *
     * @throws CentrifugoException
     */
    public function throwException()
    {
        throw $this->thrownException;
    }

    /**
     * Instantiates an exception to be thrown later.
     */
    public function makeException()
    {
        $this->thrownException = CentrifugoResponseException::create($this);
    }

    /**
     * @return CentrifugoException
     */
    public function getThrownException()
    {
        return $this->thrownException;
    }

    /**
     * Decode body
     */
    public function decodeBody()
    {
        $this->decodedBody = is_array($this->body) ? $this->body : json_decode($this->body, JSON_OBJECT_AS_ARRAY);
        if ($this->isError()) {
            $this->makeException();
        }
    }
}
