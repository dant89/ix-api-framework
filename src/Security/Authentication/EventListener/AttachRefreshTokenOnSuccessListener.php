<?php

namespace App\Security\Authentication\EventListener;

use App\Security\Entity\Permission;
use App\Security\Entity\RefreshToken;
use App\Security\Event\AuthenticationSuccessEvent as ApiKeyAuthenticationSuccessEvent;
use App\Security\Repository\ApiKeyRepository;
use App\Security\Repository\PermissionRepository;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Gesdinet\JWTRefreshTokenBundle\EventListener\AttachRefreshTokenOnSuccessListener as GesdinetSuccessListener;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Request\RequestRefreshToken;
use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Namshi\JOSE\JWS;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttachRefreshTokenOnSuccessListener extends GesdinetSuccessListener
{
    protected ApiKeyRepository $apiKeyRepository;
    protected LoggerInterface $logger;
    protected PermissionRepository $permissionRepository;

    public function __construct(
        RefreshTokenManagerInterface $refreshTokenManager,
        int $ttl,
        ValidatorInterface $validator,
        RequestStack $requestStack,
        string $userIdentityField,
        string $tokenParameterName,
        bool $singleUse,
        ApiKeyRepository $apiKeyRepository,
        PermissionRepository $permissionRepository,
        ?LoggerInterface $logger = null
    ) {
        $this->apiKeyRepository = $apiKeyRepository;
        $this->permissionRepository = $permissionRepository;
        $this->logger = $logger ?? new NullLogger();
        parent::__construct(
            $refreshTokenManager,
            $ttl,
            $validator,
            $requestStack,
            $userIdentityField,
            $tokenParameterName,
            $singleUse
        );
    }

    /**
     * @throws NonUniqueResultException
     * @throws InvalidArgumentException
     */
    public function attachRefreshToken(AuthenticationSuccessEvent $event): void
    {
        if (!$event instanceof ApiKeyAuthenticationSuccessEvent) {
            throw new InvalidArgumentException("Event must be api-key aware");
        }

        $data = $event->getData();
        $user = $event->getUser();
        $apiKey = $this->apiKeyRepository->find($event->getApiKeyId());
        $request = $this->requestStack->getCurrentRequest();

        if (!$user instanceof UserInterface) {
            return;
        }

        $refreshTokenString = RequestRefreshToken::getRefreshToken($request, $this->tokenParameterName);

        if ($refreshTokenString) {
            $data['refresh_token'] = $refreshTokenString;
        } else {
            $datetime = new DateTime();
            $datetime->modify('+' . $this->ttl . ' seconds');

            /** @var RefreshToken $refreshToken */
            $refreshToken = $this->refreshTokenManager->create();

            $apiKeyPermissions = $apiKey->getPermissions();
            $apiKeyRoles = array_map(
                function (Permission $p) {
                    return $p->getPermission();
                },
                $apiKeyPermissions
            );

            $jwt = JWS::load($data['access_token']); // Validation of token is role of authenticator, not repeated here
            $roles = $jwt->getPayload()['roles'] ?? [];

            $this->logger->debug("Extracted roles from JWT: '" . implode(' / ', $roles) . '"');

            foreach ($roles as $role) {
                $permission = $this->permissionRepository->findOneByPermission($role);
                if ($permission && in_array($role, $apiKeyRoles)) {
                    $refreshToken->addPermission($permission);
                }
            }

            $accessor = new PropertyAccessor();
            $userIdentityFieldValue = $accessor->getValue($user, $this->userIdentityField);

            $refreshToken->setUsername($userIdentityFieldValue);
            $refreshToken->setRefreshToken();
            $refreshToken->setValid($datetime);
            $refreshToken->setApiKey($apiKey);

            // @see Gesdinet\JWTRefreshTokenBundle\EventListener\AttachRefreshTokenOnSuccessListener::attachRefreshToken
            $valid = false;
            while (false === $valid) {
                $valid = true;
                $errors = $this->validator->validate($refreshToken);
                if ($errors->count() > 0) {
                    foreach ($errors as $error) {
                        if ('refreshToken' === $error->getPropertyPath()) {
                            $valid = false;
                            $refreshToken->setRefreshToken();
                        }
                    }
                }
            }

            $this->refreshTokenManager->save($refreshToken);
            $refreshTokenString = $refreshToken->getRefreshToken();

            $data['refresh_token'] = $refreshToken->getRefreshToken();
        }

        $shortRefresh = substr($refreshTokenString, 0, 10);
        $shortToken = substr($data['access_token'], -20, 20); // end is unique, start isn't

        // don't log whole as these are equivalent to passwords!
        $this->logger->debug("Applied refresh token '$shortRefresh...' for '...$shortToken''");

        $event->setData($data);
    }
}
