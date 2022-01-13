<?php

namespace App\Security\Authentication\Provider;

use App\Security\Repository\ApiKeyRepository;
use App\Security\Entity\KeySecretToken;
use App\Security\Entity\User;
use App\Security\Entity\ApiKey;
use Doctrine\ORM\NonUniqueResultException;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

/**
 * KeySecretAuthenticationProvider authenticates an api key + secret pair, and returns the associated user.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class KeySecretAuthenticationProvider implements AuthenticationProviderInterface
{
    protected CompanyProvider $userProvider;
    protected ApiKeyRepository $apiKeyRepository;
    protected UserCheckerInterface $userChecker;
    protected string $providerKey;

    public function __construct(
        ApiKeyRepository     $apiKeyRepository,
        UserCheckerInterface $userChecker,
        CompanyProvider      $userProvider,
        string               $providerKey
    ) {
        if (empty($providerKey)) {
            throw new InvalidArgumentException('$providerKey must not be empty.');
        }
        $this->apiKeyRepository = $apiKeyRepository;
        $this->userChecker = $userChecker;
        $this->userProvider = $userProvider;
        $this->providerKey = $providerKey;
    }

    public function authenticate(TokenInterface $token): KeySecretToken
    {
        if (!$this->supports($token)) {
            throw new AuthenticationException('The token is not supported by this authentication provider.');
        }

        $key = $this->retrieveApiKey($token);
        if (is_null($key)) {
            throw new BadCredentialsException('Bad credentials.', 0);
        }

        try {
            $subjectId = $key->getCompanyId();
            $user = $this->retrieveUser($subjectId, $key);
        } catch (\Exception $e) {
            throw new BadCredentialsException('Bad credentials.', 0, $e);
        }

        if (!$user instanceof User) {
            throw new AuthenticationServiceException('retrieveUser() must return a User.');
        }

        try {
            $this->userChecker->checkPreAuth($user);
            $this->userChecker->checkPostAuth($user);
        } catch (BadCredentialsException $e) {
            throw new BadCredentialsException('Bad credentials.', 0, $e);
        }

        $authenticatedToken = new KeySecretToken(
            $token->getApiKey(),
            $token->getSecret(),
            $this->providerKey,
            $user->getSubject()->getId(),
            $user,
            $this->getRoles($user, $token),
            $key
        );
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    public function supports(TokenInterface $token): bool
    {
        return $token instanceof KeySecretToken && $this->providerKey === $token->getProviderKey();
    }

    protected function getRoles(User $user, TokenInterface $token): array
    {
        $roles = $user->getRoles();

        foreach ($token->getRoleNames() as $role) {
            $roles[] = $role;
        }

        return $roles;
    }

    protected function retrieveUser(?string $subjectId, ApiKey $apiKey): User
    {
        return $this->userProvider->loadUserBySubject($subjectId, $apiKey);
    }

    protected function retrieveApiKey(KeySecretToken $token): ?ApiKey
    {
        try {
            return $this->apiKeyRepository->findOneByApiKey($token->getApiKey(), $token->getSecret());
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
