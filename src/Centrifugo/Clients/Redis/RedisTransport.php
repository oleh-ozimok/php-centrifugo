<?php

namespace Centrifugo\Clients\Redis;

use \Redis;

/**
 * Class RedisTransport
 * @package Centrifugo\Clients\Redis
 */
class RedisTransport implements TransportInterface
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * RedisTransport constructor.
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @inheritdoc
     */
    public function rPush($key, $value)
    {
        return $this->redis->rpush($key, $value);
    }
}