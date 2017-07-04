<?php

namespace Centrifugo;

use Exception;

/**
 * Class RequestHandler
 * @package Centrifugo
 */
abstract class RequestHandler
{
    /**
     * @var RequestHandler
     */
    protected $successor;

    /**
     * @param RequestHandler $handler
     */
    public function appendHandler(RequestHandler $handler)
    {
        if ($this->successor) {
            $this->successor->appendHandler($handler);
        } else {
            $this->successor = $handler;
        }
    }

    /**
     * @param Request $request
     *
     * @return string
     * @throws Exception
     */
    public function sendRequest(Request $request)
    {
        try {
            $response = $this->processing($request);
        } catch (Exception $exception) {
            if ($this->successor) {
                return $this->successor->sendRequest($request);
            }
            throw $exception;
        }

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    abstract protected function processing(Request $request);
}
