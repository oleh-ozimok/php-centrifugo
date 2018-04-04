<?php

namespace Centrifugo\Clients;

use Centrifugo\Request;
use Centrifugo\BatchRequest;
use Centrifugo\Clients\Redis\TransportInterface;
use Centrifugo\Exceptions\CentrifugoTransportException;

/**
 * Class RedisClient
 * @package Centrifugo\Clients
 */
class RedisClient extends BaseClient
{
    const QUEUE_NAME = 'centrifugo.api';
    const QUEUE_SHARD_PATTERN = 'centrifugo.api.%u';

    /**
     * @var TransportInterface
     */
    protected $transport;
    /**
     * @var int
     */
    protected $shardsNumber = 0;
    /**
     * @var array
     */
    protected $supportedMethods = ['publish', 'broadcast', 'unsubscribe', 'disconnect'];

    /**
     * RedisClient constructor.
     *
     * @param TransportInterface $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param int $shardsNumber
     */
    public function setShardsNumber($shardsNumber)
    {
        $this->shardsNumber = (int) $shardsNumber;
    }

    /**
     * @inheritdoc
     */
    protected function processRequest(Request $request)
    {
        if (!$this->canProcessRequest($request)) {
            throw new CentrifugoTransportException('RedisTransport can\'t process request.');
        }

        $queue = $this->getQueue();
        $message = $this->makeMassage($request);

        if (false === $this->transport->rPush($queue, $message)) {
            throw new CentrifugoTransportException('RedisTransport can\'t push to: ' . $queue);
        }

        return $request instanceof BatchRequest
            ? $this->emulateBatchResponse($request)
            : $this->emulateResponse($request);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function canProcessRequest(Request $request)
    {
        if ($request instanceof BatchRequest) {
            foreach ($request as $req) {
                if (!$this->isMethodSupported($req->getMethod())) {
                    return false;
                }
            }

            return true;
        }

        return $this->isMethodSupported($request->getMethod());
    }

    /**
     * @param string $method
     *
     * @return mixed
     */
    protected function isMethodSupported($method)
    {
        return in_array($method, $this->supportedMethods);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    protected function makeMassage(Request $request)
    {
        return json_encode([
            'data' => $request instanceof BatchRequest ? $request->toArray() : [$request->toArray()],
        ]);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function emulateResponse(Request $request)
    {
        return [
            'body' => $request->getParams(),
            'method' => $request->getMethod(),
            'error' => null,
        ];
    }

    /**
     * @param BatchRequest $request
     *
     * @return array
     */
    protected function emulateBatchResponse(BatchRequest $request)
    {
        $response = [];
        foreach ($request as $req) {
            $response[] = $this->emulateResponse($req);
        }

        return $response;
    }

    /**
     * @return string
     */
    protected function getQueue()
    {
        if (!$this->shardsNumber) {
            return self::QUEUE_NAME;
        }

        return sprintf(self::QUEUE_SHARD_PATTERN, rand(0, $this->shardsNumber - 1));
    }
}
