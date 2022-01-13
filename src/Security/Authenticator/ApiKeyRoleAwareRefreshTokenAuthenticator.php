<?php

namespace App\Security\Authenticator;

use App\Security\Authentication\Provider\CompanyProvider;
use App\Security\Authentication\Token\ApiKeyAwarePreAuthenticatedToken;
use App\Security\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManager;
use Gesdinet\JWTRefreshTokenBundle\Request\RequestRefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Security\Authenticator\RefreshTokenAuthenticator;
use Gesdinet\JWTRefreshTokenBundle\Security\Provider\RefreshTokenProvider;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use UnexpectedValueException;

class ApiKeyRoleAwareRefreshTokenAuthenticator extends RefreshTokenAuthenticator
{
    protected RefreshTokenManager $refreshTokenManager;
    protected UserCheckerInterface $availableUserChecker;
    protected CompanyProvider $companyProvider;
    protected string $tokenParemeterName;
    protected LoggerInterface $logger;

    public function __construct(
        UserCheckerInterface $userChecker,
        RefreshTokenManager  $refreshTokenManager,
        CompanyProvider      $companyProvider,
        string               $tokenParemeterName,
        ?LoggerInterface     $logger = null
    ) {
        $this->refreshTokenManager = $refreshTokenManager;
        $this->availableUserChecker = $userChecker;
        $this->companyProvider = $companyProvider;
        $this->tokenParemeterName = $tokenParemeterName;
        $this->logger = $logger ?? new NullLogger();
        parent::__construct($userChecker, $tokenParemeterName);
    }

    public function authenticateToken(
        Request $request,
        UserProviderInterface $userProvider,
        string $providerKey
    ): ApiKeyAwarePreAuthenticatedToken {
        if (!$userProvider instanceof RefreshTokenProvider) {
            throw new InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of RefreshTokenProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $refreshTokenString = RequestRefreshToken::getRefreshToken($request, $this->tokenParameterName);
        $this->logger->debug("Checking refresh token '$refreshTokenString'");

        /** @var RefreshToken $refreshToken */
        $refreshToken = $this->refreshTokenManager->get($refreshTokenString);

        if (!$refreshToken) {
            $message = sprintf('Refresh token "%s" does not exist.', $refreshTokenString);
            $this->logger->info($message);
            throw new AuthenticationException($message);
        }

        $username = $userProvider->getUsernameForRefreshToken($refreshTokenString);

        if (null === $username) {
            $message = sprintf('User with refresh token "%s" does not exist.', $refreshTokenString);
            $this->logger->warning($message);
            throw new AuthenticationException($message);
        }

        $user = $this->companyProvider->loadUserBySubject(
            $refreshToken->getSub(),
            $refreshToken->getApiKey()
        );

        $this->availableUserChecker->checkPreAuth($user);
        $this->availableUserChecker->checkPostAuth($user);

        $userRoles = $user->getRoles();

        $userStringRoles = array_map(
            function ($r) {
                return (string)$r;
            },
            $userRoles
        );

        $refreshTokenPermissions = $refreshToken->getPermissions()->toArray();
        $refreshTokenStringRoles = $refreshToken->getPermissionStrings();

        $safeRoles = array_unique(array_intersect($userStringRoles, $refreshTokenStringRoles) + ['ROLE_USER']);

        $this->logger->debug(
            'Found ' . count($userStringRoles) . ' roles from user: ' . implode(',', $userStringRoles)
        );
        $this->logger->debug(
            'Found ' . count($refreshTokenPermissions) . ' roles from token: ' . implode(',', $refreshTokenStringRoles)
        );
        $this->logger->debug(
            'Found ' . count($safeRoles) . ' usable roles from refresh token for JWT: ' . implode(',', $safeRoles)
        );

        // Now apply these roles to the User entity which determines what's set in the JWT token

        if (method_exists($user, 'setAppliedRoles')) {
            // We limit the roles a user asserts according to those that the refresh token permits
            $newRoles = $refreshToken->getPermissionStrings();
            $implicitRoles = ['ROLE_USER']; // Not stored in DB
            foreach ($implicitRoles as $roleName) {
                if ($role = $user->getAvailableRoleByName($roleName)) {
                    $newRoles[] = $role;
                }
            }

            $user->setAppliedRoles($newRoles);
        } else {
            throw new UnexpectedValueException('Unexpected User class: ' . get_class($user));
        }

        return new ApiKeyAwarePreAuthenticatedToken(
            $user,
            $refreshTokenString,
            $providerKey,
            $safeRoles, // Discarded later in the event processing?
            $refreshToken->getApiKey()
        );
    }
}
