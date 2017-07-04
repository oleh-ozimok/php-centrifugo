<?php

namespace Centrifugo;

use ArrayAccess;
use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;

/**
 * Class BatchRequest
 *
 * @package Centrifugo
 */
class BatchRequest extends Request implements IteratorAggregate, ArrayAccess
{
    /**
     * @var Request[]
     */
    protected $requests;

    /**
     * BatchRequest constructor.
     *
     * @param string $endpoint
     * @param string $secret
     * @param array $requests
     */
    public function __construct($endpoint, $secret, array $requests)
    {
        parent::__construct($endpoint, $secret, null, []);

        $this->add($requests);
    }

    /**
     * @param $request
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function add($request)
    {
        if (is_array($request)) {
            array_walk($request, [$this, __METHOD__]);

            return $this;
        }

        if (!$request instanceof Request) {
            throw new InvalidArgumentException('Argument for add() must be of type array or Request.');
        }

        $this->requests[] = $request;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->requests as $request) {
            $array[] = $request->toArray();
        }

        return $array;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->requests);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->add($value);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->requests[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->requests[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return isset($this->requests[$offset]) ? $this->requests[$offset] : null;
    }
}
