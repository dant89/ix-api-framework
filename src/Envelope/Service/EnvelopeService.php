<?php

namespace App\Envelope\Service;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Validator\Exception\ValidationException;
use App\Exception\AuthenticationErrorException;
use App\Exception\Internal\DebugServerErrorException;
use App\Exception\NotFoundErrorException;
use App\Exception\ReportableExceptionInterface;
use App\Exception\MethodNotAllowedErrorException;
use App\Exception\PermissionDeniedErrorException;
use App\Exception\ServerErrorException;
use App\Exception\UnableToFulfillErrorException;
use App\Exception\ValidationErrorException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EnvelopeService
{
    protected bool $debug;
    protected LoggerInterface $logger;
    protected ObjectNormalizer $normalizer;

    public function __construct(bool $debug, LoggerInterface $logger, ObjectNormalizer $normalizer)
    {
        $this->debug = $debug;
        $this->logger = $logger;
        $this->normalizer = $normalizer;
    }

    public function fromResponse(Response $response): Response
    {
        $data = $response->getContent();
        $contentType = $response->headers->get('Content-Type');
        $isJson = strpos($contentType, 'application/json') !== -1;
        if ($isJson) {
            $data = json_decode($data, true);
        }
        $response = new JsonResponse($data, $response->getStatusCode(), $response->headers->all());
        $response->headers->set('X-Enveloped', [1]);
        return $response;
    }

    public function fromException(\Throwable $exception): Response
    {
        $instanceId = uniqid();
        $this->logger->error($exception->getMessage(), [
            'incident_id' => $instanceId,
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace()
        ]);

        // The Symfony Kernel firewall catches and formats BadCredentialsException into a HttpException
        // checking if an exception is a HttpException and then getting the previous exception from that
        // allows us to bypass this and retain the original exception
        if (get_class($exception) === HttpException::class && !is_null($exception->getPrevious())) {
            $exception = $exception->getPrevious();
        }

        if ($exception instanceof ReportableExceptionInterface) {
            $linxException = $exception;
        } else {
            # Map none system errors to ix-api errors, catch system errors as Server Errors
            if ($exception instanceof AuthenticationException || $exception instanceof BadCredentialsException) {
                $linxException = new AuthenticationErrorException();
            } elseif ($exception instanceof NotFoundHttpException) {
                $linxException = new NotFoundErrorException();
            } elseif ($exception instanceof AccessDeniedException || $exception instanceof AccessDeniedHttpException) {
                $linxException = new PermissionDeniedErrorException();
            } elseif ($exception instanceof InvalidArgumentException ||
                $exception instanceof \InvalidArgumentException ||
                $exception instanceof \UnexpectedValueException ||
                $exception instanceof ValidationException
            ) {
                $linxException = new ValidationErrorException();
            } elseif ($exception instanceof MethodNotAllowedHttpException ||
                $exception instanceof MethodNotAllowedException
            ) {
                $linxException = new MethodNotAllowedErrorException();
            } elseif ($exception instanceof BadRequestHttpException
            ) {
                $linxException = new UnableToFulfillErrorException();
            } else {
                if ($this->debug) {
                    $linxException = new DebugServerErrorException($exception->getMessage());
                } else {
                    $linxException = new ServerErrorException();
                }
            }
        }
        $linxException->setInstance($instanceId);

        $response = new JsonResponse($linxException->getFormattedMessage(), $linxException->getCode());
        $response->headers->set('X-Enveloped', [1]);
        return $response;
    }

    public function responseIsUnwrapped(Response $response): bool
    {
        return $response->headers->get('X-Enveloped', 0) !== 1;
    }
}
