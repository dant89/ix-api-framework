<?php

namespace App\Security\Authentication\Controller;

use App\Security\Event\RefreshEvent;
use Gesdinet\JWTRefreshTokenBundle\Security\Authenticator\RefreshTokenAuthenticator;
use Gesdinet\JWTRefreshTokenBundle\Service\RefreshToken;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Security\Provider\RefreshTokenProvider;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

class KeySecretRefreshToken extends RefreshToken
{
    private RefreshTokenAuthenticator $authenticator;
    private RefreshTokenProvider $provider;
    private AuthenticationSuccessHandlerInterface $successHandler;
    private AuthenticationFailureHandlerInterface $failureHandler;
    private RefreshTokenManagerInterface $refreshTokenManager;
    private int $ttl;
    private string $providerKey;
    private bool $ttlUpdate;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        RefreshTokenAuthenticator $authenticator,
        RefreshTokenProvider $provider,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler,
        RefreshTokenManagerInterface $refreshTokenManager,
        int $ttl,
        string $providerKey,
        bool $ttlUpdate,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->authenticator = $authenticator;
        $this->provider = $provider;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->ttl = $ttl;
        $this->providerKey = $providerKey;
        $this->ttlUpdate = $ttlUpdate;
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct(
            $authenticator,
            $provider,
            $successHandler,
            $failureHandler,
            $refreshTokenManager,
            $ttl,
            $providerKey,
            $ttlUpdate,
            $eventDispatcher
        );
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function refresh(Request $request)
    {
        try {
            $postAuthenticationToken = $this->authenticator->authenticateToken(
                $request,
                $this->provider,
                $this->providerKey
            );
        } catch (AuthenticationException $e) {
            return $this->failureHandler->onAuthenticationFailure($request, $e);
        }

        $credentials = $this->authenticator->getCredentials($request);
        $refreshToken = $this->refreshTokenManager->get($credentials['token']);

        if (null === $refreshToken || !$refreshToken->isValid()) {
            return $this->failureHandler->onAuthenticationFailure(
                $request,
                new AuthenticationException(sprintf('Refresh token "%s" is invalid.', $refreshToken))
            );
        }

        if ($this->ttlUpdate) {
            $expirationDate = new \DateTime();
            $expirationDate->modify(sprintf('+%d seconds', $this->ttl));
            $refreshToken->setValid($expirationDate);

            $this->refreshTokenManager->save($refreshToken);
        }

        if ($this->eventDispatcher instanceof ContractsEventDispatcherInterface) {
            $this->eventDispatcher->dispatch(
                new RefreshEvent($refreshToken, $postAuthenticationToken),
                'gesdinet.refresh_token'
            );
        } else {
            $this->eventDispatcher->dispatch(
                'gesdinet.refresh_token',
                new RefreshEvent($refreshToken, $postAuthenticationToken)
            );
        }

        return $this->successHandler->onAuthenticationSuccess($request, $postAuthenticationToken);
    }
}
