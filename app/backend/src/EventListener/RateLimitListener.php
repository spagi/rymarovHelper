<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Controller\Api\AskApiResource;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: 'kernel.controller', method: 'onKernelController')]
final class RateLimitListener
{
    public function __construct(
        private readonly RateLimiterFactory $askLimiter
    ) {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller) || !$controller[0] instanceof AskApiResource) {
            return;
        }

        $request = $event->getRequest();

        $clientIp = $request->getClientIp();

        $limiter = $this->askLimiter->create($clientIp);

        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException(null, 'Příliš mnoho požadavků, zkuste to prosím později.');
        }
    }
}
