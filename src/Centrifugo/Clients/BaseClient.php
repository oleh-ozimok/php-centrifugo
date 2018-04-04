<?php

namespace Centrifugo\Clients;

use Centrifugo\ClientInterface;
use Centrifugo\Request;
use Centrifugo\BatchRequest;
use Centrifugo\Response;
use Centrifugo\BatchResponse;
use Exception;

/**
 * Class BaseClient
 * @package Centrifugo\Clients
 */
abstract class BaseClient implements ClientInterface
{
    /**
     * @var BaseClient
     */
    protected $successor;

    /**
     * @param BaseClient $failover
     */
    public function setFailover(BaseClient $failover)
    {
        if ($this->successor) {
            $this->successor->setFailover($failover);
        } else {
            $this->successor = $failover;
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function sendRequest(Request $request)
    {
        try {
            $rawResponse = $this->processRequest($request);
        } catch (Exception $exception) {
            if ($this->successor) {
                return $this->successor->sendRequest($request);
            }
            throw $exception;
        }

        $response = new Response($request, $rawResponse);

        if ($response->isError()) {
            throw $response->getThrownException();
        }

        return $response;
    }

    /**
     * @param BatchRequest $request
     *
     * @return BatchResponse
     */
    public function sendBatchRequest(BatchRequest $request)
    {
        $response = $this->sendRequest($request);

        return new BatchResponse($request, $response);
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    abstract protected function processRequest(Request $request);
}
