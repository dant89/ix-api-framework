<?php

namespace App\Security\Guard;

use App\Exception\PermissionDeniedErrorException;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator as LexikJWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JWTTokenAuthenticator extends LexikJWTTokenAuthenticator
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $dispatcher,
        TokenExtractorInterface $tokenExtractor
    ) {
        parent::__construct($jwtManager, $dispatcher, $tokenExtractor);
        $this->dispatcher = $dispatcher;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        if ($authException instanceof PermissionDeniedErrorException) {
            $authException->setInstance(uniqid());
            return new JWTAuthenticationFailureResponse(
                $authException->getFormattedMessage(),
                $authException->getCode()
            );
        } else {
            $exception = new MissingTokenException('JWT Token not found', 0);
            $event = new JWTNotFoundEvent(
                $exception,
                new JWTAuthenticationFailureResponse($exception->getMessageKey())
            );
            $this->dispatcher->dispatch($event, Events::JWT_NOT_FOUND);
        }

        return $event->getResponse();
    }
}
