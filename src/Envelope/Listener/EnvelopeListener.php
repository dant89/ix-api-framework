<?php

namespace App\Envelope\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Util\RequestAttributesExtractor;
use App\Envelope\Service\EnvelopeService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class EnvelopeListener implements EventSubscriberInterface
{
    protected EnvelopeService $envelopeService;

    public function __construct(EnvelopeService $envelopeService, LoggerInterface $logger)
    {
        $this->envelopeService = $envelopeService;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', EventPriorities::POST_RESPOND],
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }
        $request = $event->getRequest();
        // TODO: This fails for anything that isn't an entity, include auth!
        if (!(RequestAttributesExtractor::extractAttributes($request))) {
            return;
        }
        $response = $event->getResponse();
        if (!$this->envelopeService->responseIsUnwrapped($response)) {
            return;
        }
        $response = $this->envelopeService->fromResponse($event->getResponse());
        $event->setResponse($response);
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }
        
        $e = $event->getThrowable();
        $response = $this->envelopeService->fromException($e);

        $event->setResponse($response);
        $event->allowCustomResponseCode();
    }
}
