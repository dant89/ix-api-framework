<?php

namespace App\Security\Authentication;

use App\Security\Role\RoleFactory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Custom RoleVoter to handle our roles that do not require a ROLE_ prefix.
 *
 * Class RoleVoter
 * @package App\Security\Authentication
 */
class RoleVoter implements VoterInterface
{
    /**
     * @var RoleFactory
     */
    private $roleFactory;

    public function __construct(RoleFactory $roleFactory)
    {
        $this->roleFactory = $roleFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        $roleNames = $this->extractRoles($token);

        foreach ($attributes as $attribute) {
            if (!$this->roleFactory->valid($attribute)) {
                continue;
            }

            $result = VoterInterface::ACCESS_DENIED;
            foreach ($roleNames as $roleName) {
                if ($attribute === $roleName) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }

    protected function extractRoles(TokenInterface $token)
    {
        return $token->getRoleNames();
    }
}
