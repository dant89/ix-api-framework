<?php

namespace App\Security\Authentication;

use App\Security\Authentication\Token\ApiKeyAwareTokenInterface;
use App\Security\Entity\ApiKey;
use App\Security\Entity\KeySecretToken;
use App\Security\Entity\Permission;
use App\Security\Event\AuthenticationSuccessEvent;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler
    as LexikAuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthenticationSuccessHandler extends LexikAuthenticationSuccessHandler
{
    protected ?LoggerInterface $logger;

    public function __construct(JWTManager $jwtManager, EventDispatcherInterface $dispatcher, ?LoggerInterface $logger)
    {
        $this->logger = $logger ?? new NullLogger();
        parent::__construct($jwtManager, $dispatcher);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JWTAuthenticationSuccessResponse
    {
        if (!$token instanceof ApiKeyAwareTokenInterface) {
            throw new Exception(
                "Got " . get_class($token) . ' in place of ' . ApiKeyAwareTokenInterface::class
            );
        }

        /** @var KeySecretToken $token */
        $apiKey = $token->getAuthenticatedApiKey();
        $keyRoles = array_map(function (Permission $p) {
            return $p->getPermission();
        }, $apiKey->getPermissions());
        $this->logger->debug('Called ' . __METHOD__ . ' for token with KEY roles: ' . implode(',', $keyRoles));

        /** @var UserInterface $user */
        $user = $token->getUser();
        return $this->handleApiAuthenticationSuccess($user, $apiKey);
    }

    public function handleApiAuthenticationSuccess(
        UserInterface $user,
        ApiKey $apiKey
    ): JWTAuthenticationSuccessResponse {
        $roles = $user->getRoles();
        $this->logger->debug('Called ' . __METHOD__ . ' for user with ' . implode(',', $roles));

        $jwt = $this->jwtManager->create($user);

        $response = new JWTAuthenticationSuccessResponse($jwt);
        $event = new AuthenticationSuccessEvent(['access_token' => $jwt], $user, $response, $apiKey->getId());

        $this->dispatcher->dispatch($event, Events::AUTHENTICATION_SUCCESS);
        $response->setData($event->getData());

        return $response;
    }
}
