<?php

namespace App\EventSubscriber;

use App\Entity\RequestLog;
use App\Service\CurrentRequestLogStore;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestLoggerSubscriber implements EventSubscriberInterface
{
    private float $startTime;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CurrentRequestLogStore $logStore
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 10]],
            KernelEvents::RESPONSE => [['onKernelResponse', -10]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->getPathInfo() !== '/calculate') {
            return;
        }

        $this->startTime = microtime(true);

        $log = new RequestLog();
        $log->setEndpoint($request->getPathInfo());
        $log->setHttpMethod($request->getMethod());
        $log->setRequestPayload($request->getContent());
        $log->setCreatedAt(new \DateTimeImmutable());
        
        // We persist it here so it gets an ID if needed, 
        // but we don't flush yet to avoid extra DB calls if we can bundle them.
        $this->entityManager->persist($log);
        $this->logStore->setRequestLog($log);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $log = $this->logStore->getRequestLog();
        if (!$log) {
            return;
        }

        $response = $event->getResponse();
        $latency = (int) ((microtime(true) - $this->startTime) * 1000);

        $log->setResponsePayload($response->getContent());
        $log->setStatusCode($response->getStatusCode());
        $log->setLatency($latency);

        if ($this->entityManager->isOpen()) {
            $this->entityManager->flush();
        }
    }
}
