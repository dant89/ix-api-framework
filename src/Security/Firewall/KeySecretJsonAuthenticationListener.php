<?php

namespace App\Security\Firewall;

use App\Security\Entity\KeySecretToken;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use function is_null;
use function is_string;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use stdClass;
use function strlen;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

/**
 * KeySecretJsonAuthenticationListener is a stateless implementation of
 * authentication via a JSON document composed of a key and a secret.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class KeySecretJsonAuthenticationListener
{
    protected TokenStorageInterface $tokenStorage;
    protected AuthenticationManagerInterface $authenticationManager;
    protected HttpUtils $httpUtils;
    protected string $providerKey;
    protected AuthenticationSuccessHandlerInterface $successHandler;
    protected AuthenticationFailureHandlerInterface $failureHandler;
    protected array $options;
    protected LoggerInterface $logger;
    protected EventDispatcherInterface $eventDispatcher;
    protected PropertyAccessor $propertyAccessor;
    protected SessionAuthenticationStrategyInterface $sessionStrategy;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        HttpUtils $httpUtils,
        string $providerKey,
        AuthenticationSuccessHandlerInterface $successHandler = null,
        AuthenticationFailureHandlerInterface $failureHandler = null,
        array $options = [],
        LoggerInterface $logger = null,
        EventDispatcherInterface $eventDispatcher = null,
        PropertyAccessorInterface $propertyAccessor = null
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->httpUtils = $httpUtils;
        $this->providerKey = $providerKey;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
        $this->logger = $logger ?? new NullLogger();
        $this->eventDispatcher = $eventDispatcher;
        $this->options = array_merge([
            'api_key_path' => 'api_key',
            'secret_path' => 'api_secret',
            'customer_id_path' => 'customer_id'
        ], $options);
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    public function __invoke(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (false === strpos($request->getRequestFormat(), 'json')
            && false === strpos($request->getContentType(), 'json')
        ) {
            return;
        }

        if (isset($this->options['check_path'])
            && !$this->httpUtils->checkRequestPath($request, $this->options['check_path'])) {
            return;
        }

        $data = json_decode($request->getContent());

        try {
            if (!$data instanceof stdClass) {
                throw new BadRequestHttpException('Invalid JSON.');
            }

            try {
                $apiKey = $this->propertyAccessor->getValue($data, $this->options['api_key_path']);
            } catch (AccessException $e) {
                throw new BadRequestHttpException(
                    sprintf('The key "%s" must be provided.', $this->options['api_key_path']),
                    $e
                );
            }

            try {
                $secret = $this->propertyAccessor->getValue($data, $this->options['secret_path']);
            } catch (AccessException $e) {
                throw new BadRequestHttpException(
                    sprintf('The key "%s" must be provided.', $this->options['secret_path']),
                    $e
                );
            }

            try {
                $customerId = $this->propertyAccessor->getValue($data, $this->options['customer_id_path']);
            } catch (AccessException $e) {
                $customerId = null;
            }

            if (!is_string($apiKey)) {
                throw new BadRequestHttpException(
                    sprintf('The key "%s" must be a string.', $this->options['api_key_path'])
                );
            }

            if (strlen($apiKey) > Security::MAX_USERNAME_LENGTH) {
                throw new BadCredentialsException('Invalid api_key.');
            }

            if (!is_string($secret)) {
                throw new BadRequestHttpException(
                    sprintf('The key "%s" must be a string.', $this->options['secret_path'])
                );
            }

            if (!is_null($customerId) && !is_string($customerId)) {
                throw new BadRequestHttpException(
                    sprintf('The key "%s" must be a string.', $this->options['customer_id_path'])
                );
            }

            $token = new KeySecretToken($apiKey, $secret, $this->providerKey, $customerId);
            /** @var KeySecretToken $authenticatedToken */
            $authenticatedToken = $this->authenticationManager->authenticate($token);
            $response = $this->onSuccess($request, $authenticatedToken);
        } catch (AuthenticationException $e) {
            throw $e;
        } catch (BadRequestHttpException $e) {
            throw $e;
        }

        if (null === $response) {
            return;
        }

        $event->setResponse($response);
    }

    protected function onSuccess(Request $request, TokenInterface $token): ?Response
    {
        $this->logger->info(
            'User has been authenticated successfully.',
            ['username' => $token->getUsername()]
        );

        $this->migrateSession($request, $token);

        $this->tokenStorage->setToken($token);

        if (null !== $this->eventDispatcher) {
            $loginEvent = new InteractiveLoginEvent($request, $token);
            $this->eventDispatcher->dispatch($loginEvent, SecurityEvents::INTERACTIVE_LOGIN);
        }

        if (!$this->successHandler) {
            return null;
        }

        $response = $this->successHandler->onAuthenticationSuccess($request, $token);

        if (!$response instanceof Response) {
            throw new RuntimeException('Authentication Success Handler did not return a Response.');
        }

        return $response;
    }

    /**
     * Call this method if your authentication token is stored to a session.
     */
    public function setSessionAuthenticationStrategy(SessionAuthenticationStrategyInterface $sessionStrategy): void
    {
        $this->sessionStrategy = $sessionStrategy;
    }

       protected function migrateSession(Request $request, TokenInterface $token): void
    {
        if (!$this->sessionStrategy || !$request->hasSession() || !$request->hasPreviousSession()) {
            return;
        }

        $this->sessionStrategy->onAuthentication($request, $token);
    }
}
