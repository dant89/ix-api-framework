<?php

namespace App\Security\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    protected Company $subject;
    protected array $availableRoles;
    protected array $appliedRoles;

    public function __construct(Company $subject, array $roles = [])
    {
        $this->subject = $subject;
        $this->setRoles($roles);
    }

    public function getId(): string
    {
        return $this->getSubject()->getId();
    }

    public function getSubject(): Company
    {
        return $this->subject;
    }

    public function setSubject(Company $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Standard function in the Symfony Auth system, must return CURRENT capabilities
     */
    public function getRoles(): array
    {
        return $this->appliedRoles;
    }

    /**
     * Return all roles to which this user has authorisation, even if not currently asserted
     */
    public function getAvailableRoles(): array
    {
        return $this->availableRoles;
    }

    /**
     * Set the roles this User has access to. If we previously had no roles, assume this is initialisation
     */
    public function setRoles(array $availableRoles): self
    {
        /*
         * When called in the framework, this is set once with ROLE_USER and then later with the 'real' roles
         * In tests, it's set *blank* the first time
         */
        $oldRoles = $this->availableRoles ?? [];
        $this->availableRoles = $availableRoles;

        // We only update AppliedRoles if we're *adding* new potential roles,
        // which implies we're still building this User's permissions
        // Else we might reduce total permissions later but assert ones we didn't intend to
        if ($this->isSubsetOf(
            $this->stringifyRoles($oldRoles),
            $this->stringifyRoles($availableRoles)
        )) {
            $this->setAppliedRoles($availableRoles);
        }
        return $this;
    }

    public function getPassword(): void
    {
    }

    public function getSalt(): void
    {
    }

    public function getUsername(): string
    {
        return $this->getId();
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * Reduce the number of roles this user is currently asserting (eg via a limited API key)
     *
     * Always allows setting of ROLE_USER
     */
    public function setAppliedRoles(array $roles): void
    {
        $rolesToKeep = [];
        foreach ($roles as $role) {
            foreach ($this->availableRoles as $availableRole) {
                if ((string)$role === (string)$availableRole) {
                    $rolesToKeep[(string)$availableRole] = $availableRole; // keep unique
                    continue;
                }
            }
        }
        $this->appliedRoles = array_values($rolesToKeep);
    }

    public function getAvailableRoleByName(string $name): ?string
    {
        foreach ($this->availableRoles as $role) {
            if ($name === (string)$role) {
                return $role;
            }
        }

        return null;
    }

    /**
     * Normalise roles for comparison purposes
     */
    protected function stringifyRoles(array $roles): array
    {
        return array_unique(
            array_map(
                function ($r) {
                    return (string)$r;
                },
                $roles
            )
        );
    }

    protected function isSubsetOf(array $subset, array $superset): string
    {
        return (array_intersect($superset, $subset) === $subset);
    }
}
