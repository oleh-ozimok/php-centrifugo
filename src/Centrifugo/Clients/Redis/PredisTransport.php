<?php

namespace Centrifugo\Clients\Redis;

use Predis\Client;

/**
 * Class PredisTransport
 * @package Centrifugo\Clients\Redis
 */
class PredisTransport implements TransportInterface
{
    /**
     * @var Client
     */
    private $predisClient;

    /**
     * PredisTransport constructor.
     * @param Client $predisClient
     */
    public function __construct(Client $predisClient)
    {
        $this->predisClient = $predisClient;
    }

    /**
     * @inheritdoc
     */
    public function rPush($key, $value)
    {
        return $this->predisClient->rpush($key, [$value]);
    }
}