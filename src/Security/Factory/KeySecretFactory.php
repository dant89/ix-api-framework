<?php

namespace App\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\JsonLoginFactory;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * KeySecretFactory creates services for api key + secret linked authentication.
 */
class KeySecretFactory extends JsonLoginFactory
{
    public function getKey(): string
    {
        return 'key-secret-login';
    }

    protected function getListenerId(): string
    {
        return 'security.authentication.listener.key_secret_json';
    }

    protected function createAuthProvider(
        ContainerBuilder $container,
        string $id,
        array $config,
        string $userProviderId
    ): string {
        $provider = 'security.authentication.provider.key_secret_.' . $id;
        $container
            ->setDefinition($provider, new ChildDefinition('security.authentication.provider.key_secret'))
            ->replaceArgument(1, new Reference('security.user_checker.' . $id))
            ->replaceArgument(2, new Reference($userProviderId))
            ->replaceArgument(3, $id)
        ;

        return $provider;
    }
}
