<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CorsSubscriber implements EventSubscriberInterface
{
    /**
     * @var array<int, string>
     */
    private array $allowedOrigins;

    public function __construct()
    {
        $allowedOrigins = $_SERVER['CORS_ALLOW_ORIGIN'] ?? $_ENV['CORS_ALLOW_ORIGIN'] ?? 'http://localhost:4200';
        $this->allowedOrigins = array_values(array_filter(array_map('trim', explode(',', $allowedOrigins))));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ($request->getMethod() !== 'OPTIONS' || !str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $response = new Response('', Response::HTTP_NO_CONTENT);
        $this->applyCorsHeaders($request->headers->get('Origin'), $response);
        $event->setResponse($response);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $this->applyCorsHeaders($request->headers->get('Origin'), $event->getResponse());
    }

    private function applyCorsHeaders(?string $origin, Response $response): void
    {
        $allowedOrigin = $this->resolveAllowedOrigin($origin);
        if ($allowedOrigin === null) {
            return;
        }

        $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        $response->headers->set('Access-Control-Max-Age', '86400');

        $vary = $response->headers->get('Vary');
        $response->headers->set('Vary', $vary ? $vary.', Origin' : 'Origin');
    }

    private function resolveAllowedOrigin(?string $origin): ?string
    {
        if ($origin === null || $origin === '') {
            return $this->allowedOrigins[0] ?? null;
        }

        if (in_array('*', $this->allowedOrigins, true)) {
            return '*';
        }

        if (in_array($origin, $this->allowedOrigins, true)) {
            return $origin;
        }

        return null;
    }
}
