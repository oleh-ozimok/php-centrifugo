<?php

namespace Centrifugo;

use Centrifugo\Transport\HttpTransport;
use Centrifugo\Transport\RedisTransport;
use Centrifugo\Exceptions\CentrifugoTransportException;

/**
 * Class TransportFactory
 * @package Centrifugo
 */
abstract class TransportFactory
{
    const HTTP_TRANSPORT  = 'http';
    const REDIS_TRANSPORT = 'redis';

    /**
     * @param array $config
     * @return HttpTransport|RedisTransport|null
     * @throws CentrifugoTransportException
     */
    public static function createChain(array $config)
    {
        $chain = null;
        foreach ($config as $transportType => $params){
            $transport = static::create($transportType, $params);
            if($chain){
                $chain->appendHandler($transport);
            } else {
                $chain = $transport;
            }
        }

        return $chain;
    }

    /**
     * @param string $type
     * @param array $params
     * @return HttpTransport|RedisTransport
     * @throws CentrifugoTransportException
     */
    public static function create($type, array $params)
    {
        switch ($type){
            case self::HTTP_TRANSPORT:
                return new HttpTransport($params);
            case self::REDIS_TRANSPORT:
                if (empty($params['host'])) {
                    throw new CentrifugoTransportException('You should specified a host.');
                }
                if(isset($params['timeout']) && !isset($params['port'])){
                    throw new CentrifugoTransportException('You should specified a port if you specified a timeout.');
                }
                $redisClient = isset($params['port'], $params['timeout'])
                    ? new RedisTransport($params['host'], $params['port'], $params['timeout'])
                    : new RedisTransport($params['host'], $params['port']);
                if(isset($config['db'])){
                    $redisClient->setDb($params['db']);
                }
                if(isset($config['shards'])){
                    $redisClient->setShardsNumber($params['shardsNumber']);
                }

                return $redisClient;
            default:
                throw new CentrifugoTransportException('Unknown transport type: ' . $type);
        }
    }
}