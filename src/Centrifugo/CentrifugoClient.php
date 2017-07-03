<?php

namespace Centrifugo;

/**
 * Class CentrifugoClient
 * @package Centrifugo
 */
class CentrifugoClient
{
    /**
     * @var RequestHandler
     */
    protected $transport;

    /**
     * CentrifugoClient constructor.
     *
     * @param RequestHandler $transport
     */
    public function __construct(RequestHandler $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws Exceptions\CentrifugoException
     * @throws \Exception
     */
    public function sendRequest(Request $request)
    {
        $rawResponse = $this->transport->sendRequest($request);
        $returnResponse = new Response($request, $rawResponse);

        if ($returnResponse->isError()) {
            throw $returnResponse->getThrownException();
        }

        return $returnResponse;
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
}
