<?php

namespace App\Security\Authentication\Provider;

use App\Security\Entity\ApiKey;
use App\Security\Entity\Permission;
use App\Security\Entity\User;
use App\Security\Role\RoleFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\PayloadAwareUserProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use App\Security\Entity\Company;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class CompanyProvider implements PayloadAwareUserProviderInterface
{
    protected string $appEnv;
    protected LoggerInterface $logger;

    public function __construct(
        string $appEnv = 'prod',
        LoggerInterface $logger = null
    ) {
        $this->appEnv = $appEnv;
        $this->logger = $logger ?? new NullLogger();
    }

    public function loadUserBySubject(string $subjectId, ?ApiKey $apiKey = null): User
    {
        $subject = $this->loadSubjectByCompanyId($subjectId);

        $user = new User($subject, []);
        if (null !== $apiKey) {
            $apiKeyPermissions = $apiKey->getPermissions();
            $roles = $this->rolesFromPermissions($apiKeyPermissions);
            $user->setRoles(array_merge($user->getRoles(), $roles));
        }

        return $user;
    }

    public function loadUserByUsername(string $username): User
    {
        return $this->loadUserBySubject($username);
    }

    public function loadSubjectByCompanyId(string $companyId): Company
    {
        // TODO this is where you implement the logic that loads the company / user from your CRM based on the
        // TODO $companyId that is linked to an API KEY in the DB.

        try {
            $entries = [
                [
                    'id' => '12345',
                    'name' => 'Sample Company',
                ]
            ]; // TODO replace with implementation

        } catch (\Exception $e) {
            throw new \Exception(sprintf('User "%s" not found.', $companyId), 0, $e);
        }

        $count = count($entries);
        if (!$count) {
            throw new \Exception(sprintf('User "%s" not found.', $companyId));
        }
        if ($count > 1) {
            throw new \Exception('More than one user found');
        }

        return $this->companyFromEntry($entries[0]['id'], $entries[0]['name']);
    }

    public function loadUserByUsernameAndPayload(string $subject, array $payload): User
    {
        $subject = Company::create($subject);
        return new User($subject, $payload['roles']);
    }

    public function refreshUser(UserInterface $user): User
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserBySubject($user->getSubject());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }

    protected function companyFromEntry(string $id, string $name): Company
    {
        return Company::create($id, $name);
    }

    /**
     * @param Permission[]
     * @return Role[]
     */
    private function rolesFromPermissions(array $permissions): array
    {
        $roleFactory = new RoleFactory();
        $roles = [];
        foreach ($permissions as $permission) {
            $permissionName = $permission->getPermission();
            try {
                $roles [] = $roleFactory->role($permissionName);
            } catch (\InvalidArgumentException $e) {
                $this->logger->debug(
                    "Tried to auth with invalid permission '$permissionName'"
                );
                throw $e;
            }
        }
        return $roles;
    }
}
