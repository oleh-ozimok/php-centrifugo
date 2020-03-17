<?php

namespace Centrifugo\Transport;

use Centrifugo\BatchRequest;
use Centrifugo\Exceptions\CentrifugoTransportException;
use Centrifugo\Request;
use Centrifugo\RequestHandler;
use Redis;

/**
 * Class RedisTransport
 * @package Centrifugo\Transport
 */
class RedisTransport extends RequestHandler
{
    const QUEUE_NAME = 'centrifugo.api';
    const QUEUE_SHARD_PATTERN = 'centrifugo.api.%u';

    /**
     * @var Redis
     */
    protected $connection;
    /**
     * @var string
     */
    protected $host;
    /**
     * @var int
     */
    protected $port;
    /**
     * @var float
     */
    protected $timeout;
    /**
     * @var int
     */
    protected $db = 0;
    /**
     * @var int
     */
    protected $shardsNumber = 0;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var array
     */
    protected $supportedMethods = ['publish', 'broadcast', 'unsubscribe', 'disconnect'];

    /**
     * RedisClient constructor.
     *
     * @param string $host
     * @param int $port
     * @param float $timeout
     */
    public function __construct($host, $port = 6379, $timeout = 15.0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    /**
     * @param int $db
     */
    public function setDb($db)
    {
        $this->db = (int) $db;
    }

    /**
     * @param int $shardsNumber
     */
    public function setShardsNumber($shardsNumber)
    {
        $this->shardsNumber = (int) $shardsNumber;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = (string) $password;
    }

    /**
     * Open Redis connection
     *
     * @throws CentrifugoTransportException
     */
    protected function openConnection()
    {
        $this->connection = new Redis();

        if (!$this->connection->connect($this->host, $this->port, $this->timeout)) {
            throw new CentrifugoTransportException('Failed to open redis DB connection.');
        }

        if ($this->password && !$this->connection->auth($this->password)) {
            throw new CentrifugoTransportException('Failed to authenticate redis connection.');
        }

        if ($this->db && !$this->connection->select($this->db)) {
            throw new CentrifugoTransportException('Failed to select redis DB.');
        }
    }

    /**
     * Close connection if opened
     */
    protected function closeConnection()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * @return Redis
     */
    protected function getConnection()
    {
        if(!$this->connection){
            $this->openConnection();
        }

        return $this->connection;
    }

    /**
     * @inheritdoc
     */
    protected function processing(Request $request)
    {
        if (!$this->canProcessRequest($request)) {
            throw new CentrifugoTransportException('RedisTransport can\'t process request.');
        }

        $queue = $this->getQueue();
        $message = $this->makeMassage($request);

        if (false === $this->getConnection()->rPush($queue, $message)) {
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

    /**
     * Close Redis connection
     */
    public function __destruct()
    {
        $this->closeConnection();
    }
}
