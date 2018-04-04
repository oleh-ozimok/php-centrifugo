<?php

namespace Centrifugo\Clients\Redis;

/**
 * Interface TransportInterface
 * @package Centrifugo\Clients\Redis
 */
interface TransportInterface
{
    /**
     * @param string $key
     * @param string $value
     * @return int|false
     */
    public function rPush($key, $value);
}