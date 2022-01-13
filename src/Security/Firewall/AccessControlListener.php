<?php

namespace App\Security\Firewall;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Util\RequestAttributesExtractor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessControlListener implements EventSubscriberInterface
{
    private ResourceMetadataFactoryInterface $resourceMetadataFactory;
    protected AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        ResourceMetadataFactoryInterface $resourceMetadataFactory,
        AuthorizationCheckerInterface $authorizationChecker = null
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    /**
     * @throws ResourceClassNotFoundException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$attributes = RequestAttributesExtractor::extractAttributes($request)) {
            return;
        }

        $resourceMetadata = $this->resourceMetadataFactory->create($attributes['resource_class']);
        $requiredRoles = $resourceMetadata->getOperationAttribute($attributes, 'access_control_roles', [], true);

        if (!count($requiredRoles)) {
            return;
        }

        $accessControlMessage = $resourceMetadata->getOperationAttribute(
            $attributes,
            'access_control_message',
            'Access Denied.',
            true
        );
        foreach ($requiredRoles as $role) {
            if (!$this->authorizationChecker->isGranted($role)) {
                throw new AccessDeniedException($accessControlMessage);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 6],
        ];
    }
}
