<?php

namespace Centrifugo\Exceptions;

use Centrifugo\Response;

/**
 * Class CentrifugoResponseException
 * @package Centrifugo\Exceptions
 */
class CentrifugoResponseException extends CentrifugoException
{
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var array
     */
    protected static $exceptionMap = [
        'invalid message'       => CentrifugoInvalidMessageException::class,
        'invalid token'         => CentrifugoInvalidTokenException::class,
        'unauthorized'          => CentrifugoUnauthorizedException::class,
        'method not found'      => CentrifugoMethodNotFoundException::class,
        'permission denied'     => CentrifugoPermissionDeniedException::class,
        'namespace not found'   => CentrifugoNamespaceNotFoundException::class,
        'internal server error' => CentrifugoInternalServerErrorException::class,
        'already subscribed'    => CentrifugoAlreadySubscribedException::class,
        'limit exceeded'        => CentrifugoLimitExceededException::class,
        'not available'         => CentrifugoNotAvailableException::class,
        'send timeout'          => CentrifugoSendTimeoutException::class,
        'client is closed'      => CentrifugoClientClosedException::class,
    ];

    /**
     * CentrifugoResponseException constructor.
     *
     * @param Response $response
     * @param CentrifugoException|null $previousException
     */
    public function __construct(Response $response, CentrifugoException $previousException = null)
    {
        $this->response = $response;

        parent::__construct('Invalid Centrifugo response.', 0, $previousException);
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     *
     * @return static
     */
    public static function create(Response $response)
    {
        $errorMessage = $response->getError();
        $exception = isset(static::$exceptionMap[$errorMessage])
            ? static::$exceptionMap[$errorMessage]
            : CentrifugoException::class;

        return new static($response, new $exception($errorMessage));
    }
}
