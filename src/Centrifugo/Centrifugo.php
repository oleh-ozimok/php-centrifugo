<?php

namespace Centrifugo;

/**
 * Class Centrifugo
 * @package Centrifugo
 */
class Centrifugo
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
     * @var CentrifugoClient
     */
    protected $client;
    /**
     * @var Response
     */
    protected $lastResponse;
    
    /**
     * Centrifugo constructor.
     * @param $endpoint
     * @param $secret
     * @param array $transportConfig
     */
    public function __construct($endpoint, $secret, array $transportConfig)
    {
        $this->endpoint = $endpoint;
        $this->secret   = $secret;
        $this->client   = new CentrifugoClient(TransportFactory::createChain($transportConfig));
    }

    /**
     * @param string $method
     * @param array $params
     * @return Request
     */
    public function request($method, $params = [])
    {
        return new Request($this->endpoint, $this->secret, $method, $params);
    }

    /**
     * Send message into channel.
     * @param string $channel
     * @param array $data
     * @return Response
     */
    public function publish($channel, array $data)
    {
        return $this->sendRequest('publish', ['channel' => $channel, 'data' => $data]);
    }

    /**
     * Very similar to publish but allows to send the same data into many channels.
     * @param array $channels
     * @param array $data
     * @return Response
     */
    public function broadcast(array $channels, array $data)
    {
        return $this->sendRequest('broadcast', ['channels' => $channels, 'data' => $data]);
    }

    /**
     * Unsubscribe user from channel.
     * @param $channel
     * @param $userId
     * @return Response
     */
    public function unsubscribe($channel, $userId)
    {
        return $this->sendRequest('unsubscribe', ['channel' => $channel, 'user' => (string)$userId]);
    }

    /**
     * disconnect user by user ID.
     * @param $userId
     * @return Response
     */
    public function disconnect($userId)
    {
        return $this->sendRequest('disconnect', ['user' => (string)$userId]);
    }

    /**
     * Get channel presence information (all clients currently subscribed on this channel).
     * @param $channel
     * @return Response
     */
    public function presence($channel)
    {
        return $this->sendRequest('presence', ['channel' => $channel]);
    }

    /**
     * Get channel history information (list of last messages sent into channel).
     * @param $channel
     * @return Response
     */
    public function history($channel)
    {
        return $this->sendRequest('history', ['channel' => $channel]);
    }

    /**
     * Get channels information (list of currently active channels).
     * @return Response
     */
    public function channels()
    {
        return $this->sendRequest('channels');
    }

    /**
     * Get stats information about running server nodes.
     * @return Response
     */
    public function stats()
    {
        return $this->sendRequest('stats');
    }

    /**
     * Get information about single Centrifugo node.
     * @param string $endpoint
     * @return Response
     */
    public function node($endpoint)
    {
        $request = new Request($endpoint, $this->secret, 'node', []);

        return $this->sendBatchRequest([$request]);
    }

    /**
     * @return Response
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Generate client connection token.
     * @param string $user
     * @param string $timestamp
     * @param string $info
     * @return string
     */
    public function generateClientToken($user, $timestamp, $info = '')
    {
        $ctx = hash_init('sha256', HASH_HMAC, $this->secret);
        hash_update($ctx, $user);
        hash_update($ctx, $timestamp);
        hash_update($ctx, $info);

        return hash_final($ctx);
    }

    /**
     * Generate channel sign
     * @param string $client
     * @param string $channel
     * @param string $info
     * @return string
     */
    public function generateChannelSign($client, $channel, $info = '')
    {
        $ctx = hash_init('sha256', HASH_HMAC, $this->secret);
        hash_update($ctx, $client);
        hash_update($ctx, $channel);
        hash_update($ctx, $info);

        return hash_final($ctx);
    }

    /**
     * Send request.
     * @param string $method
     * @param array $params
     * @return Response
     * @throws Exceptions\CentrifugoException
     */
    public function sendRequest($method, $params = [])
    {
        $request = $this->request($method, $params);
        $this->lastResponse = $this->sendBatchRequest([$request])->shiftResponses();
        if($this->lastResponse->isError()){
            $this->lastResponse->throwException();
        }

        return $this->lastResponse;
    }

    /**
     * Send batch request.
     * @param Request[] $requests
     * @return BatchResponse
     */
    public function sendBatchRequest(array $requests)
    {
        $batchRequest = new BatchRequest($this->endpoint, $this->secret, $requests);

        return $this->lastResponse = $this->client->sendBatchRequest($batchRequest);
    }
}